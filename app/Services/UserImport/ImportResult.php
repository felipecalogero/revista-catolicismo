<?php

namespace App\Services\UserImport;

class ImportResult
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public int $failed = 0;

    /** @var list<string> */
    public array $errors = [];

    public ?string $detectedFormat = null;

    public ?string $detectedPlanType = null;

    public function processed(): int
    {
        return $this->created + $this->updated;
    }

    public function successMessage(): string
    {
        $parts = [];
        if ($this->created > 0) {
            $parts[] = "{$this->created} criados";
        }
        if ($this->updated > 0) {
            $parts[] = "{$this->updated} atualizados";
        }
        if ($parts === []) {
            return 'Nenhum usuário importado.';
        }

        $prefix = $this->detectedPlanType === 'virtual' ? 'Assinatura virtual' : 'Assinatura física';

        return $prefix.': '.implode(', ', $parts).'.';
    }

    public function warningMessage(): ?string
    {
        $bits = [];
        if ($this->skipped > 0) {
            $bits[] = $this->skipped === 1
                ? '1 linha ignorada'
                : "{$this->skipped} linhas ignoradas";
        }
        if ($this->failed > 0) {
            $bits[] = $this->failed === 1
                ? '1 registro falhou'
                : "{$this->failed} registros falharam";
        }

        return $bits === [] ? null : implode('; ', $bits).'.';
    }

    public function addError(string $message): void
    {
        $this->errors[] = $message;
        $this->failed++;
    }

    public function addSkip(string $message): void
    {
        $this->errors[] = $message;
        $this->skipped++;
    }
}
