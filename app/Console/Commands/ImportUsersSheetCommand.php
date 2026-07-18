<?php

namespace App\Console\Commands;

use App\Services\UserImport\ImportOptions;
use App\Services\UserImport\UserImportService;
use Illuminate\Console\Command;

class ImportUsersSheetCommand extends Command
{
    protected $signature = 'users:import-sheet
        {file : Caminho do Excel/CSV}
        {--update : Atualiza usuários já existentes (e-mail) — padrão: sim}
        {--no-update : Não atualiza existentes; apenas cria novos}
        {--extend-expired : Renova vigência vencida para +12 meses}
        {--dry-run : Só analisa, não grava no banco}';

    protected $description = 'Importa usuários de planilha com detecção automática de formato e tipo de assinatura';

    public function handle(UserImportService $service): int
    {
        $file = $this->argument('file');
        if (! is_string($file) || ! is_file($file)) {
            $this->error("Arquivo não encontrado: {$file}");

            return self::FAILURE;
        }

        $options = new ImportOptions(
            updateExisting: ! $this->option('no-update'),
            extendExpiredVigence: (bool) $this->option('extend-expired'),
            dryRun: (bool) $this->option('dry-run'),
        );

        $this->info('Importando '.$file.($options->dryRun ? ' (dry-run)' : '').'…');

        try {
            $result = $service->import($file, $options);
        } catch (\Throwable $e) {
            $this->error('Falha na importação: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($result->detectedFormat) {
            $this->line('Formato: '.$result->detectedFormat.' | Plano: '.($result->detectedPlanType ?? '?'));
        }

        $this->info($result->successMessage());
        if ($result->warningMessage()) {
            $this->warn($result->warningMessage());
        }

        if ($result->errors !== []) {
            foreach (array_slice($result->errors, 0, 20) as $err) {
                $this->line(' - '.$err);
            }
            if (count($result->errors) > 20) {
                $this->line(' - … e mais '.(count($result->errors) - 20));
            }
        }

        return $result->failed > 0 && $result->processed() === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
