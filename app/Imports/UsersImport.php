<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class UsersImport implements ToCollection
{
    public int $count = 0;

    /** @var list<string> */
    public array $errors = [];

    /** @var array<string, int>|null índice 0-based por campo lógico */
    protected ?array $columnMap = null;

    public function __construct(
        protected ?string $jobId,
        protected string $importMode = 'physical',
        protected bool $extendExpiredVigence = false,
    ) {
        $normalized = strtolower(trim($importMode));
        if ($normalized === 'digital') {
            $normalized = 'virtual';
        }
        $this->importMode = in_array($normalized, ['physical', 'virtual'], true)
            ? $normalized
            : 'physical';

        if ($normalized !== '' && $this->importMode !== $normalized) {
            Log::warning('UsersImport: valor de import_mode ignorado; usando physical.', ['recebido' => $importMode]);
        }
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $first = $rows->first();
        $this->columnMap = $this->tryBuildColumnMapFromHeaderRow($first);

        if ($this->columnMap !== null) {
            $rows->shift();
        }

        Log::info('UsersImport: '.$rows->count().' linhas de dados (modo '.$this->importMode.', mapa '.($this->columnMap ? 'cabeçalhos' : 'posicional').').');

        $totalRows = $rows->count();
        $processedCount = 0;

        if ($this->jobId) {
            Cache::put('import_progress_'.$this->jobId, [
                'current' => 0,
                'total' => $totalRows,
                'status' => 'processing',
            ], 300);
        }

        foreach ($rows as $row) {
            $processedCount++;

            if ($this->jobId && $processedCount % 50 === 0) {
                Cache::put('import_progress_'.$this->jobId, [
                    'current' => $processedCount,
                    'total' => $totalRows,
                    'status' => 'processing',
                ], 300);
            }

            [$userData, $subData] = $this->extractUserAndSubscription($row);
            $this->maybeExtendExpiredVigence($subData);

            $email = $userData['email'] ?? '';
            $name = $userData['name'] ?? '';

            if ($name === '' || $email === '') {
                Log::warning('UsersImport: linha ignorada (nome ou e-mail vazio).', ['row' => $row->toArray()]);

                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->errors[] = "E-mail {$email} já cadastrado.";

                continue;
            }

            try {
                $user = User::create(array_merge($userData, [
                    'password' => null,
                    'role' => 'user',
                ]));

                if (! empty($subData['start_date']) || isset($subData['plan_type'])) {
                    $user->subscriptions()->create(array_merge($subData, [
                        'purchase_date' => $subData['start_date'] ?? now(),
                        'amount' => 0.00,
                    ]));
                }

                $this->count++;
            } catch (\Exception $e) {
                $this->errors[] = "Erro ao importar {$email}: ".$e->getMessage();
            }
        }

        if ($this->jobId) {
            Cache::put('import_progress_'.$this->jobId, [
                'current' => $totalRows,
                'total' => $totalRows,
                'status' => 'done',
            ], 300);
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function extractUserAndSubscription(Collection $row): array
    {
        $map = $this->columnMap;

        if ($this->importMode === 'virtual') {
            return [
                $this->buildVirtualUserData($row, $map),
                $this->buildVirtualSubscriptionData($row, $map),
            ];
        }

        return [
            $this->buildPhysicalUserData($row, $map),
            $this->buildPhysicalSubscriptionData($row, $map),
        ];
    }

    /**
     * @param  array<string, int>|null  $map
     * @return array<string, mixed>
     */
    protected function buildPhysicalUserData(Collection $row, ?array $map): array
    {
        if ($map !== null) {
            $name = trim((string) $this->valueAt($row, $map, 'name'));
            $email = trim((string) $this->valueAt($row, $map, 'email'));

            $ufRaw = $this->valueAt($row, $map, 'uf');
            $estadoRaw = $this->valueAt($row, $map, 'state');

            return [
                'name' => $name,
                'email' => $email,
                'cpf' => $this->digitsOrNull($this->valueAt($row, $map, 'cpf')),
                'phone' => $this->stringOrNull($this->valueAt($row, $map, 'phone')),
                'address' => $this->stringOrNull($this->valueAt($row, $map, 'address')),
                'neighborhood' => $this->stringOrNull($this->valueAt($row, $map, 'neighborhood')),
                'city' => $this->stringOrNull($this->valueAt($row, $map, 'city')),
                'state' => $this->resolveBrazilianUf($ufRaw, $estadoRaw),
                'zip_code' => $this->digitsOrNull($this->valueAt($row, $map, 'zip')),
                'profession' => $this->stringOrNull($this->valueAt($row, $map, 'profession')),
            ];
        }

        return [
            'name' => trim((string) ($row[0] ?? '')),
            'email' => trim((string) ($row[1] ?? '')),
            'cpf' => $this->digitsOrNull($row[2] ?? null),
            'phone' => $this->stringOrNull($row[3] ?? null),
            'address' => $this->stringOrNull($row[4] ?? null),
            'neighborhood' => $this->stringOrNull($row[5] ?? null),
            'city' => $this->stringOrNull($row[6] ?? null),
            'state' => $this->resolveBrazilianUf($row[7] ?? null, $row[8] ?? null),
            'zip_code' => $this->digitsOrNull($row[9] ?? null),
            'profession' => $this->stringOrNull($row[16] ?? null),
        ];
    }

    /**
     * @param  array<string, int>|null  $map
     * @return array<string, mixed>
     */
    protected function buildPhysicalSubscriptionData(Collection $row, ?array $map): array
    {
        $sub = [
            'plan_type' => 'physical',
            'plan_name' => 'Assinatura Física',
            'status' => 'active',
        ];

        if ($map !== null) {
            $startValue = $this->valueAt($row, $map, 'current_period_start');
            if ($startValue === null || $startValue === '') {
                $startValue = $this->valueAt($row, $map, 'start');
            }

            $endValue = $this->valueAt($row, $map, 'current_period_end');
            if ($endValue === null || $endValue === '') {
                $endValue = $this->valueAt($row, $map, 'end');
            }

            $sub['start_date'] = $this->parseDate($startValue);
            $sub['end_date'] = $this->parseDate($endValue);
            $sub['canceled_at'] = $this->parseDate($this->valueAt($row, $map, 'canceled_at'));
            $sub['cancel_reason'] = $this->stringOrNull($this->valueAt($row, $map, 'cancel_reason'));
        } else {
            $sub['start_date'] = $this->parseDate($row[10] ?? null);
            $sub['end_date'] = $this->parseDate($row[11] ?? null);
            $sub['canceled_at'] = $this->parseDate($row[12] ?? null);
            $sub['cancel_reason'] = $this->stringOrNull($row[13] ?? null);
        }

        if (! empty($sub['canceled_at'])) {
            $sub['status'] = 'cancelled';
        }

        return $sub;
    }

    /**
     * @param  array<string, int>|null  $map
     * @return array<string, mixed>
     */
    protected function buildVirtualUserData(Collection $row, ?array $map): array
    {
        if ($map !== null) {
            $ufRaw = $this->valueAt($row, $map, 'uf');
            $estadoRaw = $this->valueAt($row, $map, 'state');

            return [
                'name' => trim((string) $this->valueAt($row, $map, 'name')),
                'email' => trim((string) $this->valueAt($row, $map, 'email')),
                'cpf' => $this->digitsOrNull($this->valueAt($row, $map, 'cpf')),
                'phone' => $this->stringOrNull($this->valueAt($row, $map, 'phone')),
                'state' => $this->resolveBrazilianUf($ufRaw, $estadoRaw),
                'profession' => $this->stringOrNull($this->valueAt($row, $map, 'profession')),
            ];
        }

        return [
            'name' => trim((string) ($row[0] ?? '')),
            'email' => trim((string) ($row[1] ?? '')),
            'cpf' => $this->digitsOrNull($row[2] ?? null),
            'phone' => $this->stringOrNull($row[3] ?? null),
            'state' => $this->resolveBrazilianUf($row[4] ?? null, null),
            'profession' => $this->stringOrNull($row[7] ?? null),
        ];
    }

    /**
     * @param  array<string, int>|null  $map
     * @return array<string, mixed>
     */
    protected function buildVirtualSubscriptionData(Collection $row, ?array $map): array
    {
        $sub = [
            'plan_type' => 'virtual',
            'plan_name' => 'Assinatura Digital',
            'status' => 'active',
        ];

        if ($map !== null) {
            $sub['start_date'] = $this->parseDate($this->valueAt($row, $map, 'start'));
            $sub['end_date'] = $this->parseDate($this->valueAt($row, $map, 'end'));
            $sub['canceled_at'] = $this->parseDate($this->valueAt($row, $map, 'canceled_at'));
            $sub['cancel_reason'] = $this->stringOrNull($this->valueAt($row, $map, 'cancel_reason'));
        } else {
            $sub['start_date'] = $this->parseDate($row[5] ?? null);
            $sub['end_date'] = $this->parseDate($row[6] ?? null);
        }

        if (! empty($sub['canceled_at'])) {
            $sub['status'] = 'cancelled';
        }

        return $sub;
    }

    /**
     * @param  array<string, int>  $map
     */
    protected function valueAt(Collection $row, array $map, string $field): mixed
    {
        if (! isset($map[$field])) {
            return null;
        }

        return $row[$map[$field]] ?? null;
    }

    /**
     * @return array<string, int>|null
     */
    protected function tryBuildColumnMapFromHeaderRow(Collection $headerRow): ?array
    {
        $map = [];
        $aliasGroups = $this->fieldAliasGroups();

        foreach ($headerRow->values() as $i => $cell) {
            $key = $this->normalizeHeader((string) $cell);
            if ($key === '') {
                continue;
            }
            foreach ($aliasGroups as $field => $aliases) {
                if (isset($map[$field])) {
                    continue;
                }
                if (in_array($key, $aliases, true)) {
                    $map[$field] = (int) $i;

                    break;
                }
            }
        }

        if (! isset($map['name'], $map['email'])) {
            return null;
        }

        return $map;
    }

    /**
     * @return array<string, list<string>>
     */
    protected function fieldAliasGroups(): array
    {
        return [
            'name' => ['nome', 'name', 'nomecompleto', 'assinante', 'cliente'],
            'email' => ['email', 'mail'],
            'cpf' => ['cpf', 'cnpj', 'documento', 'cpfcnpj', 'customercpfcnpj', 'documentocpf'],
            'phone' => ['telefone', 'fone', 'phone', 'celular', 'cel', 'whatsapp', 'contato'],
            'address' => ['endereco', 'logradouro', 'rua', 'morada'],
            'neighborhood' => ['bairro', 'distrito'],
            'city' => ['cidade', 'municipio', 'localidade'],
            'uf' => ['uf', 'sigla', 'estadosigla'],
            'state' => ['estado', 'estadonome', 'estadocompleto'],
            'zip' => ['cep', 'zip', 'codigopostal', 'postal'],
            'start' => ['inicio', 'datainicio', 'inicioassinatura', 'start', 'startdate', 'datadeinicio'],
            'end' => ['fim', 'datafim', 'termino', 'validade', 'end', 'enddate', 'datafinal'],
            'current_period_start' => ['currentperiodstart'],
            'current_period_end' => ['currentperiodend'],
            'canceled_at' => ['canceledat', 'canceladoem', 'datacancelamento', 'datadecancelamento'],
            'cancel_reason' => ['cancelreason', 'motivocancelamento', 'motivodocancelamento', 'motivo'],
            'profession' => ['profissao', 'ocupacao', 'cargo'],
        ];
    }

    protected function normalizeHeader(string $header): string
    {
        $h = mb_strtolower(trim($header), 'UTF-8');
        if ($h === '') {
            return '';
        }
        if (class_exists(\Normalizer::class)) {
            $d = \Normalizer::normalize($h, \Normalizer::FORM_D);
            if (is_string($d)) {
                $h = preg_replace('/\pM/u', '', $d) ?? $h;
            }
        }

        return preg_replace('/[^a-z0-9]+/i', '', $h) ?? '';
    }

    protected function resolveBrazilianUf(mixed $ufCell, mixed $estadoCell): ?string
    {
        $uf = is_string($ufCell) ? strtoupper(preg_replace('/[^A-Za-z]/', '', trim($ufCell)) ?? '') : '';
        if (strlen($uf) === 2 && $this->isValidUf($uf)) {
            return $uf;
        }

        $nameKey = $this->normalizeStateNameKey($estadoCell);
        if ($nameKey !== null && isset(self::BRAZIL_STATE_BY_NAME_KEY[$nameKey])) {
            return self::BRAZIL_STATE_BY_NAME_KEY[$nameKey];
        }

        return null;
    }

    protected function normalizeStateNameKey(mixed $estadoCell): ?string
    {
        if ($estadoCell === null || $estadoCell === '') {
            return null;
        }
        $s = trim((string) $estadoCell);
        if ($s === '') {
            return null;
        }
        $key = $this->normalizeHeader($s);

        return $key !== '' ? $key : null;
    }

    protected function isValidUf(string $two): bool
    {
        return in_array(strtoupper($two), self::ALL_UFS, true);
    }

    private const ALL_UFS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    /** Chaves = normalizeHeader(nome do estado), valores = UF */
    private const BRAZIL_STATE_BY_NAME_KEY = [
        'acre' => 'AC',
        'alagoas' => 'AL',
        'amapa' => 'AP',
        'amazonas' => 'AM',
        'bahia' => 'BA',
        'ceara' => 'CE',
        'distritofederal' => 'DF',
        'espiritosanto' => 'ES',
        'goias' => 'GO',
        'maranhao' => 'MA',
        'matogrosso' => 'MT',
        'matogrossodosul' => 'MS',
        'minasgerais' => 'MG',
        'para' => 'PA',
        'paraiba' => 'PB',
        'parana' => 'PR',
        'pernambuco' => 'PE',
        'piaui' => 'PI',
        'piau' => 'PI',
        'riodejaneiro' => 'RJ',
        'riograndedonorte' => 'RN',
        'riograndedosul' => 'RS',
        'rondonia' => 'RO',
        'roraima' => 'RR',
        'santacatarina' => 'SC',
        'saopaulo' => 'SP',
        'sergipe' => 'SE',
        'tocantins' => 'TO',
    ];

    protected function digitsOrNull(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $d = preg_replace('/\D+/', '', (string) $v);

        return $d !== '' ? $d : null;
    }

    protected function stringOrNull(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s !== '' ? $s : null;
    }

    /**
     * Se a planilha trouxer vigência já encerrada, opcionalmente renova o término
     * para 12 meses após hoje (sem cancelamento registrado).
     *
     * @param  array<string, mixed>  $subData
     */
    protected function maybeExtendExpiredVigence(array &$subData): void
    {
        if (! $this->extendExpiredVigence) {
            return;
        }

        if (! empty($subData['canceled_at'])) {
            return;
        }

        $today = Carbon::today();
        $end = isset($subData['end_date']) && $subData['end_date'] instanceof Carbon
            ? $subData['end_date']->copy()->startOfDay()
            : null;

        if ($end === null || $end->lt($today)) {
            $subData['end_date'] = $today->copy()->addYear();
            if (empty($subData['start_date']) || ! $subData['start_date'] instanceof Carbon) {
                $subData['start_date'] = $today->copy();
            } elseif ($subData['start_date']->copy()->startOfDay()->gt($subData['end_date'])) {
                $subData['start_date'] = $today->copy();
            }
            $subData['status'] = 'active';
        }
    }

    protected function parseDate(mixed $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        if (is_numeric($date)) {
            $serial = (float) $date;
            if ($serial <= 0) {
                return null;
            }
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($serial));
            } catch (\Throwable) {
                return null;
            }
        }

        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTimeImmutable::createFromInterface($date));
        }

        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $formats = [
            'd/m/Y',
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'Y/m/d',
            'Y/m/d H:i',
            'Y/m/d H:i:s',
            'm/d/Y',
            'm/d/Y H:i',
            'm/d/Y H:i:s',
            'Y-m-d',
            'Y-m-d H:i',
            'Y-m-d H:i:s',
            \DateTimeInterface::ATOM,
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Throwable) {
                // tenta próximo formato
            }
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception) {
            return null;
        }
    }
}
