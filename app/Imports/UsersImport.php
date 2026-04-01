<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class UsersImport implements ToCollection
{
    public $count = 0;
    public $errors = [];
    protected $jobId;

    public function __construct($jobId = null)
    {
        // $jobId is used to track progress in cache
        $this->jobId = $jobId;
    }

    public function collection(Collection $rows)
    {
        // Skip header if it exists
        $firstRow = $rows->first();
        if ($firstRow && (strtolower($firstRow[0] ?? '') == 'nome')) {
            $rows->shift();
        }

        \Illuminate\Support\Facades\Log::info("Starting import with " . $rows->count() . " rows.");

        $totalRows = $rows->count();
        $processedCount = 0;

        if ($this->jobId) {
            Cache::put('import_progress_' . $this->jobId, [
                'current' => 0,
                'total' => $totalRows,
                'status' => 'processing'
            ], 300);
        }

        foreach ($rows as $row) {
            $processedCount++;
            
            if ($this->jobId && $processedCount % 10 === 0) {
                Cache::put('import_progress_' . $this->jobId, [
                    'current' => $processedCount,
                    'total' => $totalRows,
                    'status' => 'processing'
                ], 300);
            }

            // Basic structure check: if it has at least Name and Email
            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');

            if (empty($name) || empty($email)) {
                \Illuminate\Support\Facades\Log::warning("Skipping row: Name or Email empty. Row data: " . json_encode($row->toArray()));
                continue;
            }

            $cols = count($row);

            // If it's the 16-column format (Standard):
            // 0: Nome, 1: Email, 2: Endereco, 3: Bairro, 4: Cidade, 5: Estado, 6: CEP, 7: CPF/CNPJ,
            // 8: Plano, 9: Produto, 10: Status, 11: Inicio, 12: Fim, 13: Cancelamento, 14: Motivo, 15: Profissao
            
            // If it's the "Physical" original format (17 columns):
            // 0: NOME, 1: Email, 2: CPF, 3: TELEFONE, 4: Endereço, 5: bairro, 6: CIDADE, 7: UF, 8: estado, 9: Cep, 10: INICIO, 11: FIM, ...
            
            // If it's the "Digital" original format (8 columns):
            // 0: NOME, 1: EMAIL, 2: Customer CPF / CNPJ, 3: FONE, 4: UF, 5: inicio, 6: fim, 7: Profissão

            $userData = [
                'name' => $name,
                'email' => $email,
            ];

            $subData = [
                'status' => 'active',
            ];

            if ($cols >= 17) {
                // Original Physical format
                $userData['cpf'] = !empty($row[2]) ? preg_replace('/[^0-9]/', '', (string)$row[2]) : null;
                $userData['address'] = $row[4] ?? null;
                $userData['neighborhood'] = $row[5] ?? null;
                $userData['city'] = $row[6] ?? null;
                $userData['state'] = $row[8] ?? ($row[7] ?? null);
                if (is_string($userData['state']) && strlen($userData['state']) > 2) {
                    $userData['state'] = substr($userData['state'], 0, 2);
                }
                $userData['zip_code'] = !empty($row[9]) ? preg_replace('/[^0-9]/', '', (string)$row[9]) : null;
                $userData['profession'] = $row[16] ?? null;

                $subData['plan_type'] = 'physical';
                $subData['plan_name'] = 'Assinatura Física';
                $subData['start_date'] = $this->parseDate($row[10] ?? null);
                $subData['end_date'] = $this->parseDate($row[11] ?? null);

            } elseif ($cols >= 16) {
                // Standard format
                $userData['address'] = $row[2] ?? null;
                $userData['neighborhood'] = $row[3] ?? null;
                $userData['city'] = $row[4] ?? null;
                $userData['state'] = $row[5] ?? null;
                $userData['zip_code'] = !empty($row[6]) ? preg_replace('/[^0-9]/', '', (string)$row[6]) : null;
                $userData['cpf'] = !empty($row[7]) ? preg_replace('/[^0-9]/', '', (string)$row[7]) : null;
                $userData['profession'] = $row[15] ?? null;

                $planName = $row[8] ?? null;
                $productName = $row[9] ?? null;
                $subData['status'] = trim(strtolower($row[10] ?? 'active')) ?: 'active';
                $subData['start_date'] = $this->parseDate($row[11] ?? null);
                $subData['end_date'] = $this->parseDate($row[12] ?? null);
                $subData['canceled_at'] = $this->parseDate($row[13] ?? null);
                $subData['cancel_reason'] = $row[14] ?? null;

                $planTypeSearch = strtolower(($planName ?? '') . ' ' . ($productName ?? ''));
                $subData['plan_type'] = str_contains($planTypeSearch, 'física') ? 'physical' : 'virtual';
                if (str_contains($planTypeSearch, 'digital')) $subData['plan_type'] = 'virtual';
                $subData['plan_name'] = $planName ?: ($subData['plan_type'] === 'physical' ? 'Assinatura Física' : 'Assinatura Digital');

            } elseif ($cols >= 8) {
                // Original Digital format
                $userData['cpf'] = !empty($row[2]) ? preg_replace('/[^0-9]/', '', (string)$row[2]) : null;
                $userData['state'] = $row[4] ?? null;
                $userData['profession'] = $row[7] ?? null;

                $subData['plan_type'] = 'virtual';
                $subData['plan_name'] = 'Assinatura Digital';
                $subData['start_date'] = $this->parseDate($row[5] ?? null);
                $subData['end_date'] = $this->parseDate($row[6] ?? null);
            }

            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->errors[] = "E-mail {$email} já cadastrado.";
                continue;
            }

            try {
                $user = User::create(array_merge($userData, [
                    'password' => null,
                    'role' => 'user',
                ]));

                // Create subscription
                if (isset($subData['start_date']) || isset($subData['plan_type'])) {
                    $user->subscriptions()->create(array_merge($subData, [
                        'purchase_date' => $subData['start_date'] ?? now(),
                        'amount' => 0.00,
                    ]));
                }

                // Send reset link
                try {
                    Password::sendResetLink($user->only('email'));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Falha ao enviar e-mail de redefinição na importação para {$email}: " . $e->getMessage());
                }

                $this->count++;
            } catch (\Exception $e) {
                $this->errors[] = "Erro ao importar {$email}: " . $e->getMessage();
            }
        }

        if ($this->jobId) {
            Cache::put('import_progress_' . $this->jobId, [
                'current' => $totalRows,
                'total' => $totalRows,
                'status' => 'done'
            ], 300);
        }
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        // Handle Excel numeric dates if they come as numbers
        if (is_numeric($date)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
        }

        try {
            if (str_contains($date, '/')) {
                return Carbon::createFromFormat('d/m/Y', $date);
            }
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
