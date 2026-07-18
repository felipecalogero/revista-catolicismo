<?php

namespace App\Services\UserImport;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RowNormalizer
{
    private const ALL_UFS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    private const STATE_BY_NAME = [
        'acre' => 'AC', 'alagoas' => 'AL', 'amapa' => 'AP', 'amazonas' => 'AM',
        'bahia' => 'BA', 'ceara' => 'CE', 'distritofederal' => 'DF', 'espiritosanto' => 'ES',
        'goias' => 'GO', 'maranhao' => 'MA', 'matogrosso' => 'MT', 'matogrossodosul' => 'MS',
        'minasgerais' => 'MG', 'para' => 'PA', 'paraiba' => 'PB', 'parana' => 'PR',
        'pernambuco' => 'PE', 'piaui' => 'PI', 'piau' => 'PI', 'riodejaneiro' => 'RJ',
        'riograndedonorte' => 'RN', 'riograndedosul' => 'RS', 'rondonia' => 'RO',
        'roraima' => 'RR', 'santacatarina' => 'SC', 'saopaulo' => 'SP',
        'sergipe' => 'SE', 'tocantins' => 'TO',
    ];

    public function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = (string) $value;
        // Remove caracteres invisíveis (ex.: U+2060) e normaliza barras escapadas
        $s = preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]/u', '', $s) ?? $s;
        $s = str_replace(['\\/', '\\'], ['/', ''], $s);
        $s = trim($s);

        return $s !== '' ? $s : null;
    }

    public function email(mixed $value): ?string
    {
        $s = $this->cleanText($value);
        if ($s === null) {
            return null;
        }
        $s = mb_strtolower($s);
        if (! filter_var($s, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $s;
    }

    public function digits(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Excel pode entregar CPF/CEP como float
        if (is_float($value) || (is_numeric($value) && ! is_string($value))) {
            $value = sprintf('%.0f', (float) $value);
        }
        $d = preg_replace('/\D+/', '', (string) $value);

        return $d !== '' ? $d : null;
    }

    public function cpfOrCnpj(mixed $value): ?string
    {
        $d = $this->digits($value);
        if ($d === null) {
            return null;
        }
        if (strlen($d) < 11) {
            $d = str_pad($d, 11, '0', STR_PAD_LEFT);
        } elseif (strlen($d) > 11 && strlen($d) < 14) {
            $d = str_pad($d, 14, '0', STR_PAD_LEFT);
        }
        if (strlen($d) === 11 || strlen($d) === 14) {
            return $d;
        }

        return null;
    }

    public function zipCode(mixed $value): ?string
    {
        $d = $this->digits($value);
        if ($d === null) {
            return null;
        }
        if (strlen($d) === 7) {
            $d = str_pad($d, 8, '0', STR_PAD_LEFT);
        }

        return strlen($d) === 8 ? $d : null;
    }

    public function phone(mixed $value): ?string
    {
        $d = $this->digits($value);
        if ($d === null) {
            return null;
        }
        if (str_starts_with($d, '55') && (strlen($d) === 12 || strlen($d) === 13)) {
            $d = substr($d, 2);
        }
        $d = substr($d, 0, 11);

        return strlen($d) >= 10 ? $d : null;
    }

    public function uf(mixed ...$candidates): ?string
    {
        foreach ($candidates as $cell) {
            $s = $this->cleanText($cell);
            if ($s === null) {
                continue;
            }
            $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $s) ?? '');
            if (strlen($letters) === 2 && in_array($letters, self::ALL_UFS, true)) {
                return $letters;
            }
            $key = $this->normalizeKey($s);
            if (isset(self::STATE_BY_NAME[$key])) {
                return self::STATE_BY_NAME[$key];
            }
        }

        return null;
    }

    public function status(mixed $value): ?string
    {
        $s = $this->cleanText($value);
        if ($s === null) {
            return null;
        }

        return match (mb_strtolower($s)) {
            'active', 'ativo' => 'active',
            'pending', 'pendente' => 'pending',
            'cancelled', 'canceled', 'cancelado' => 'cancelled',
            'expired', 'expirado' => 'expired',
            'suspended', 'suspenso' => 'suspended',
            default => null,
        };
    }

    public function planTypeFromProduct(mixed $value): ?string
    {
        $s = $this->cleanText($value);
        if ($s === null) {
            return null;
        }
        $key = $this->normalizeKey($s);
        if (str_contains($key, 'fisic') || str_contains($key, 'physical') || str_contains($key, 'impress')) {
            return 'physical';
        }
        if (str_contains($key, 'digit') || str_contains($key, 'virtu') || str_contains($key, 'online')) {
            return 'virtual';
        }

        return null;
    }

    public function amount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }
        $s = $this->cleanText($value);
        if ($s === null) {
            return null;
        }
        $s = str_replace(['R$', ' '], '', $s);
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);
        }
        if (! is_numeric($s)) {
            return null;
        }

        return round((float) $s, 2);
    }

    public function date(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $serial = (float) $value;
            if ($serial <= 0) {
                return null;
            }
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($serial))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTimeImmutable::createFromInterface($value))->startOfDay();
        }

        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        foreach (['d/m/Y', 'd/m/Y H:i', 'd/m/Y H:i:s', 'Y-m-d', 'Y-m-d H:i:s', 'Y/m/d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);

                return $parsed->startOfDay();
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function looksLikeViaCepRange(mixed $value): bool
    {
        $s = $this->cleanText($value);
        if ($s === null) {
            return false;
        }

        return (bool) preg_match('/^(até|ate)\s+\d+/iu', $s) || str_contains(mb_strtolower($s), 'lado');
    }

    public function normalizeKey(string $value): string
    {
        $h = mb_strtolower(trim($value), 'UTF-8');
        if (class_exists(\Normalizer::class)) {
            $d = \Normalizer::normalize($h, \Normalizer::FORM_D);
            if (is_string($d)) {
                $h = preg_replace('/\pM/u', '', $d) ?? $h;
            }
        }

        return preg_replace('/[^a-z0-9]+/i', '', $h) ?? '';
    }

    public function truncate(?string $value, int $max = 255): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $max);
    }
}
