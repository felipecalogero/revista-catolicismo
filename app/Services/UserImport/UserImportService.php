<?php

namespace App\Services\UserImport;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UserImportService
{
    public function __construct(
        protected ColumnDetector $detector = new ColumnDetector,
        protected RowNormalizer $normalizer = new RowNormalizer,
    ) {}

    public function import(string $filePath, ImportOptions $options = new ImportOptions): ImportResult
    {
        $result = new ImportResult;
        $prepared = $this->prepareSpreadsheet($filePath);
        $rows = $this->loadRows($prepared);

        try {
            $detection = $this->detector->detect($rows);
        } catch (\InvalidArgumentException $e) {
            $result->addError($e->getMessage());

            return $result;
        }

        $result->detectedFormat = $detection['format'];
        $result->detectedPlanType = $detection['plan_type'];
        $map = $detection['map'];

        if ($detection['has_header']) {
            $rows->shift();
        }

        $planType = $detection['plan_type'] ?? 'physical';
        $lineNumber = $detection['has_header'] ? 1 : 0;

        foreach ($rows as $row) {
            $lineNumber++;
            if (! $row instanceof Collection) {
                $row = collect($row);
            }

            try {
                $rowPlanType = $this->planTypeForRow($row, $map) ?? $planType;
                $mapped = $this->mapRow($row, $map, $rowPlanType, $options);
                if ($mapped === null) {
                    $result->skipped++;

                    continue;
                }

                if ($options->dryRun) {
                    $result->created++;

                    continue;
                }

                $this->persistRow($mapped, $options, $result);
            } catch (\Throwable $e) {
                Log::warning('UserImportService: falha na linha.', [
                    'line' => $lineNumber,
                    'exception' => $e->getMessage(),
                ]);
                $result->addError("Linha {$lineNumber}: ".$this->friendlyError($e));
            }
        }

        return $result;
    }

    /**
     * @param  array<string, int>  $map
     */
    protected function planTypeForRow(Collection $row, array $map): ?string
    {
        foreach (['product_name', 'plan_name'] as $field) {
            if (! isset($map[$field])) {
                continue;
            }
            $type = $this->normalizer->planTypeFromProduct($row[$map[$field]] ?? null);
            if ($type !== null) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Apenas detecta e mapeia (útil para validação / dry-run detalhado).
     *
     * @return array{detection: array, rows: list<array>, skipped: int}
     */
    public function preview(string $filePath, ImportOptions $options = new ImportOptions): array
    {
        $prepared = $this->prepareSpreadsheet($filePath);
        $rows = $this->loadRows($prepared);
        $detection = $this->detector->detect($rows);
        $map = $detection['map'];
        if ($detection['has_header']) {
            $rows->shift();
        }

        $mappedRows = [];
        $skipped = 0;
        $planType = $detection['plan_type'] ?? 'physical';

        foreach ($rows as $row) {
            $row = $row instanceof Collection ? $row : collect($row);
            $rowPlanType = $this->planTypeForRow($row, $map) ?? $planType;
            $mapped = $this->mapRow($row, $map, $rowPlanType, $options);
            if ($mapped === null) {
                $skipped++;

                continue;
            }
            $mappedRows[] = $mapped;
        }

        return [
            'detection' => $detection,
            'rows' => $mappedRows,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<string, int>  $map
     * @return array{user: array<string, mixed>, subscription: array<string, mixed>}|null
     */
    public function mapRow(Collection $row, array $map, string $planType, ImportOptions $options): ?array
    {
        $name = $this->normalizer->truncate($this->normalizer->cleanText($this->cell($row, $map, 'name')));
        $email = $this->normalizer->email($this->cell($row, $map, 'email'));

        if ($name === null || $email === null) {
            return null;
        }

        $ufRaw = $this->cell($row, $map, 'uf');
        $stateRaw = $this->cell($row, $map, 'state');
        $cityRaw = $this->normalizer->cleanText($this->cell($row, $map, 'city'));
        $neighborhoodRaw = $this->normalizer->cleanText($this->cell($row, $map, 'neighborhood'));
        $complementRaw = $this->normalizer->cleanText($this->cell($row, $map, 'complement'));

        // Heurística: se "UF" não é UF e "estado" é, trocar cidade
        $state = $this->normalizer->uf($ufRaw, $stateRaw);
        $city = $cityRaw;
        if ($state === null && $this->normalizer->uf($stateRaw) && ! $this->normalizer->uf($ufRaw)) {
            $state = $this->normalizer->uf($stateRaw);
            $city = $this->normalizer->cleanText($ufRaw) ?? $city;
        } elseif ($this->normalizer->uf($stateRaw) && ! $this->normalizer->uf($ufRaw) && $this->normalizer->cleanText($ufRaw)) {
            $state = $this->normalizer->uf($stateRaw);
            $city = $this->normalizer->cleanText($ufRaw) ?? $city;
        }

        if ($this->normalizer->looksLikeViaCepRange($neighborhoodRaw) && $cityRaw) {
            // Em exports com cabeçalho desalinhado, localidade pode ser bairro
            // No formato original, F já é bairro — não aplicar
        }

        // Complemento postal de baixa confiança (faixa ViaCEP) → só grava se não parecer faixa
        $complement = $complementRaw;
        if ($this->normalizer->looksLikeViaCepRange($complement)) {
            $complement = null;
        }

        $profession = $this->normalizer->cleanText($this->cell($row, $map, 'profession'));
        if ($profession !== null && is_numeric($profession)) {
            $profession = null;
        }

        $user = [
            'name' => $name,
            'email' => $email,
            'cpf' => $this->normalizer->cpfOrCnpj($this->cell($row, $map, 'cpf')),
            'phone' => $this->normalizer->phone($this->cell($row, $map, 'phone')),
            'address' => $this->normalizer->truncate($this->normalizer->cleanText($this->cell($row, $map, 'address'))),
            'complement' => $this->normalizer->truncate($complement),
            'neighborhood' => $this->normalizer->truncate($neighborhoodRaw),
            'city' => $this->normalizer->truncate($city),
            'state' => $state,
            'zip_code' => $this->normalizer->zipCode($this->cell($row, $map, 'zip')),
            'profession' => $this->normalizer->truncate($profession),
        ];

        if ($planType === 'virtual') {
            unset($user['address'], $user['complement'], $user['neighborhood'], $user['city'], $user['zip_code']);
        }

        $start = $this->normalizer->date($this->cell($row, $map, 'current_period_start'))
            ?? $this->normalizer->date($this->cell($row, $map, 'start'));
        $end = $this->normalizer->date($this->cell($row, $map, 'current_period_end'))
            ?? $this->normalizer->date($this->cell($row, $map, 'end'));
        $purchase = $this->normalizer->date($this->cell($row, $map, 'purchase_date')) ?? $start;

        $canceledRaw = $this->cell($row, $map, 'canceled_at');
        $canceledAt = $this->normalizer->date($canceledRaw);
        $cancelReason = $this->normalizer->cleanText($this->cell($row, $map, 'cancel_reason'));
        if ($canceledAt === null && is_string($canceledRaw) && $this->normalizer->cleanText($canceledRaw)
            && ! is_numeric($canceledRaw) && $this->normalizer->date($canceledRaw) === null) {
            $cancelReason = $cancelReason ?? $this->normalizer->cleanText($canceledRaw);
        }

        $status = $this->normalizer->status($this->cell($row, $map, 'status')) ?? 'active';
        if ($canceledAt !== null || $status === 'cancelled') {
            $status = 'cancelled';
        }

        $product = $this->normalizer->cleanText($this->cell($row, $map, 'product_name'));
        $planName = $this->normalizer->cleanText($this->cell($row, $map, 'plan_name'));
        // Colunas deslocadas: plan_name pode ser status
        if ($planName && $this->normalizer->status($planName) && ! $this->normalizer->status($this->cell($row, $map, 'status'))) {
            $status = $this->normalizer->status($planName) ?? $status;
            $planName = null;
        }
        if ($product) {
            $planName = $product;
        } elseif (! $planName) {
            $planName = $planType === 'virtual' ? 'Assinatura Digital' : 'Assinatura Física';
        }

        $amount = $this->normalizer->amount($this->cell($row, $map, 'price')) ?? 0.0;
        // Se status numérico (coluna deslocada), usar como preço
        $statusCell = $this->cell($row, $map, 'status');
        if (is_numeric($statusCell) && $amount === 0.0) {
            $amount = $this->normalizer->amount($statusCell) ?? 0.0;
        }

        $subscription = [
            'plan_type' => $planType,
            'plan_name' => $planName,
            'product_name' => $product,
            'status' => $status,
            'amount' => $amount,
            'payment_method' => $this->normalizer->cleanText($this->cell($row, $map, 'payment_method')),
            'purchase_date' => $purchase,
            'start_date' => $start,
            'end_date' => $end,
            'canceled_at' => $canceledAt,
            'cancel_reason' => $cancelReason,
        ];

        if ($options->extendExpiredVigence) {
            $this->extendExpired($subscription);
        }

        return [
            'user' => $user,
            'subscription' => $subscription,
        ];
    }

    /**
     * @param  array{user: array<string, mixed>, subscription: array<string, mixed>}  $mapped
     */
    protected function persistRow(array $mapped, ImportOptions $options, ImportResult $result): void
    {
        $email = $mapped['user']['email'];

        DB::transaction(function () use ($mapped, $options, $result, $email) {
            $existing = User::where('email', $email)->first();

            if ($existing && ! $options->updateExisting) {
                $result->addSkip("E-mail {$email} já cadastrado.");

                return;
            }

            if ($existing) {
                $payload = $this->nonEmptyOnly($mapped['user']);
                unset($payload['email']);
                if ($payload !== []) {
                    $existing->update($payload);
                }
                $user = $existing->fresh();
                $this->syncSubscription($user, $mapped['subscription']);
                $result->updated++;
            } else {
                $user = User::create(array_merge($mapped['user'], [
                    'password' => null,
                    'role' => 'user',
                ]));
                $this->syncSubscription($user, $mapped['subscription']);
                $result->created++;
            }
        });
    }

    /**
     * @param  array<string, mixed>  $subData
     */
    protected function syncSubscription(User $user, array $subData): void
    {
        $planType = $subData['plan_type'] ?? 'physical';
        $start = $subData['start_date'] ?? null;
        $end = $subData['end_date'] ?? null;

        $payload = array_merge($subData, [
            'purchase_date' => $subData['purchase_date'] ?? $start ?? now(),
            'amount' => $subData['amount'] ?? 0.00,
        ]);

        $query = $user->subscriptions()->where('plan_type', $planType);

        $match = null;
        if ($start instanceof Carbon && $end instanceof Carbon) {
            $match = (clone $query)
                ->whereDate('start_date', $start->toDateString())
                ->whereDate('end_date', $end->toDateString())
                ->latest('id')
                ->first();
        }

        if (! $match) {
            // Repara assinatura sem datas do mesmo tipo
            $match = (clone $query)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhereNull('end_date');
                })
                ->latest('id')
                ->first();
        }

        if ($match) {
            $match->update($payload);
        } else {
            $user->subscriptions()->create($payload);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function nonEmptyOnly(array $data): array
    {
        return array_filter($data, static function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * @param  array<string, mixed>  $sub
     */
    protected function extendExpired(array &$sub): void
    {
        if (! empty($sub['canceled_at']) || ($sub['status'] ?? '') === 'cancelled') {
            return;
        }
        $today = Carbon::today();
        $end = $sub['end_date'] instanceof Carbon ? $sub['end_date']->copy()->startOfDay() : null;
        if ($end === null || $end->lt($today)) {
            $sub['end_date'] = $today->copy()->addYear();
            if (! ($sub['start_date'] instanceof Carbon) || $sub['start_date']->gt($sub['end_date'])) {
                $sub['start_date'] = $today->copy();
            }
            $sub['status'] = 'active';
        }
    }

    /**
     * @param  array<string, int>  $map
     */
    protected function cell(Collection $row, array $map, string $field): mixed
    {
        if (! isset($map[$field])) {
            return null;
        }

        return $row[$map[$field]] ?? null;
    }

    protected function friendlyError(\Throwable $e): string
    {
        $raw = $e->getMessage();
        if (str_contains($raw, '1062') || str_contains($raw, 'Duplicate')) {
            return 'registro duplicado (e-mail ou CPF).';
        }
        if (str_contains($raw, '1406') || str_contains($raw, 'Data too long')) {
            return 'algum campo excede o tamanho permitido.';
        }

        return 'não foi possível importar este registro.';
    }

    /**
     * Limita colunas lidas para evitar planilhas com milhares de colunas vazias.
     */
    protected function prepareSpreadsheet(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['xlsx', 'xls'], true)) {
            return $filePath;
        }

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($filePath)->getActiveSheet();
            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            if ($highestCol <= 40) {
                return $filePath;
            }

            $out = new Spreadsheet;
            $dest = $out->getActiveSheet();
            $maxRow = (int) $sheet->getHighestDataRow();
            $maxCol = min($highestCol, 40);
            for ($r = 1; $r <= $maxRow; $r++) {
                for ($c = 1; $c <= $maxCol; $c++) {
                    $dest->setCellValue([$c, $r], $sheet->getCell([$c, $r])->getValue());
                }
            }
            $tmp = storage_path('app/imports/trimmed-'.uniqid('', true).'.xlsx');
            @mkdir(dirname($tmp), 0755, true);
            IOFactory::createWriter($out, 'Xlsx')->save($tmp);

            return $tmp;
        } catch (\Throwable) {
            return $filePath;
        }
    }

    /**
     * @return Collection<int, Collection<int, mixed>>
     */
    protected function loadRows(string $filePath): Collection
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'txt'], true)) {
            $rows = [];
            if (($handle = fopen($filePath, 'r')) === false) {
                return collect();
            }
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = collect($data);
            }
            fclose($handle);

            return collect($rows);
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }
        $sheet = $reader->load($filePath)->getActiveSheet();
        $maxRow = (int) $sheet->getHighestDataRow();
        $maxCol = min(Coordinate::columnIndexFromString($sheet->getHighestDataColumn()), 40);

        $rows = collect();
        for ($r = 1; $r <= $maxRow; $r++) {
            $row = [];
            for ($c = 1; $c <= $maxCol; $c++) {
                $row[] = $sheet->getCell([$c, $r])->getValue();
            }
            $rows->push(collect($row));
        }

        return $rows;
    }
}
