<?php

namespace App\Console\Commands;

use App\Services\LegacyEditionImportService;
use Illuminate\Console\Command;

class ImportLegacyEditionsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'editions:import-legacy
                            {--dry-run : Exibe o que seria importado sem gravar no banco}
                            {--limit= : Número máximo de pastas a processar}
                            {--force : Atualiza registros já importados (mesmo legacy_issue_number)}
                            {--issue= : Importa apenas um número de edição (ex.: 475)}
                            {--from= : Importa edições com número >= valor}
                            {--to= : Importa edições com número <= valor}
                            {--skip-existing-assets : Não recopia arquivos JPG/PDF se já existirem no storage}';

    /**
     * @var string
     */
    protected $description = 'Importa edições do acervo HTML (versaoantiga/Acervo/Num) para o site novo (banco + storage local)';

    public function handle(LegacyEditionImportService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $skipExistingAssets = (bool) $this->option('skip-existing-assets');
        $limit = $this->option('limit');
        $max = $limit !== null && $limit !== '' ? max(1, (int) $limit) : null;
        $single = $this->option('issue');
        $from = $this->option('from');
        $to = $this->option('to');
        $fromInt = ($from !== null && $from !== '') ? (int) $from : null;
        $toInt = ($to !== null && $to !== '') ? (int) $to : null;

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração no banco.');
        }

        $numbers = [];
        if ($single !== null && $single !== '') {
            $numbers = [(int) $single];
        } else {
            foreach ($service->discoverIssueNumbers() as $n) {
                if ($fromInt !== null && $n < $fromInt) {
                    continue;
                }
                if ($toInt !== null && $n > $toInt) {
                    continue;
                }
                $numbers[] = $n;
            }
            sort($numbers);
        }

        if ($max !== null) {
            $numbers = array_slice($numbers, 0, $max);
        }

        if ($numbers === []) {
            $this->error('Nenhuma pasta de edição encontrada em: '.$service->acervoNumBasePath());

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $updated = 0;
        $totalPages = 0;
        $totalArticles = 0;

        foreach ($numbers as $issueNum) {
            try {
                if ($dryRun) {
                    $padded = $service->paddedIssueDir($issueNum);
                    $title = 'Catolicismo nº '.$issueNum.' (dry-run)';
                    $this->line("[{$padded}] {$title}");

                    continue;
                }

                $result = $service->importIssueFolder($issueNum, false, $force, $skipExistingAssets);
                $edition = $result['edition'];
                $pages = (int) ($result['pages'] ?? 0);
                $articles = (int) ($result['articles'] ?? 0);
                $totalPages += $pages;
                $totalArticles += $articles;

                if ($result['was_created']) {
                    $this->info("Criada: [{$edition->legacy_issue_number}] {$edition->slug} (p:{$pages}, a:{$articles})");
                    $created++;
                } elseif ($result['was_updated']) {
                    $this->info("Atualizada: [{$edition->legacy_issue_number}] {$edition->slug} (p:{$pages}, a:{$articles})");
                    $updated++;
                } elseif ($result['was_skipped']) {
                    $this->line("Ignorada (já existe, use --force): [{$issueNum}]");
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $this->error("Falha na edição {$issueNum}: ".$e->getMessage());
            }
        }

        if (! $dryRun) {
            $this->newLine();
            $this->info("Resumo: {$created} criada(s), {$updated} atualizada(s), {$skipped} ignorada(s). Páginas: {$totalPages}. Artigos: {$totalArticles}.");
        }

        return self::SUCCESS;
    }
}
