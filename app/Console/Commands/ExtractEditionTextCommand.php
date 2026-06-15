<?php

namespace App\Console\Commands;

use App\Models\Edition;
use App\Services\EditionPdfTextExtractor;
use App\Services\LegacyEditionImportService;
use Illuminate\Console\Command;

class ExtractEditionTextCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'editions:extract-text
                            {--all : Processa todas as edições aplicáveis (PDFs novos + legacy agrupado)}
                            {--legacy : Apenas reagrupa o texto a partir dos EditionArticle (acervo legado)}
                            {--pdf : Apenas extrai texto dos PDFs (edições não-legacy)}
                            {--id= : Processa apenas a edição com este ID}
                            {--slug= : Processa apenas a edição com este slug}
                            {--force : Sobrescreve páginas marcadas como editadas manualmente}';

    /**
     * @var string
     */
    protected $description = 'Popula edition_page_texts a partir do PDF (extração via smalot/pdfparser) e/ou dos EditionArticle (acervo legado).';

    public function handle(EditionPdfTextExtractor $extractor, LegacyEditionImportService $legacyService): int
    {
        $all = (bool) $this->option('all');
        $onlyLegacy = (bool) $this->option('legacy');
        $onlyPdf = (bool) $this->option('pdf');
        $force = (bool) $this->option('force');
        $idOpt = $this->option('id');
        $slugOpt = $this->option('slug');

        if (! $all && ! $onlyLegacy && ! $onlyPdf && ! $idOpt && ! $slugOpt) {
            $this->error('Especifique pelo menos uma das opções: --all, --legacy, --pdf, --id=, --slug=.');

            return self::FAILURE;
        }

        $query = Edition::query();
        if ($idOpt) {
            $query->where('id', (int) $idOpt);
        } elseif ($slugOpt) {
            $query->where('slug', (string) $slugOpt);
        } else {
            // Sem filtro pontual: aplica os escopos pedidos.
            if ($onlyLegacy && ! $onlyPdf) {
                $query->where('is_legacy', true);
            } elseif ($onlyPdf && ! $onlyLegacy) {
                $query->where('is_legacy', false)->whereNotNull('pdf_file');
            }
        }

        $editions = $query->orderBy('id')->get();

        if ($editions->isEmpty()) {
            $this->warn('Nenhuma edição encontrada com os filtros informados.');

            return self::SUCCESS;
        }

        $this->info('Processando '.$editions->count().' edição(ões)...');

        $stats = [
            'pdf_ok' => 0,
            'pdf_no_text' => 0,
            'pdf_skipped' => 0,
            'legacy_ok' => 0,
            'legacy_skipped' => 0,
            'errors' => 0,
        ];

        $bar = $this->output->createProgressBar($editions->count());
        $bar->start();

        foreach ($editions as $edition) {
            try {
                if ($edition->is_legacy) {
                    if ($onlyPdf) {
                        $stats['legacy_skipped']++;
                    } else {
                        $count = $legacyService->syncPageTextsFromArticles($edition, force: $force);
                        if ($count > 0) {
                            $stats['legacy_ok']++;
                        } else {
                            $stats['legacy_skipped']++;
                        }
                    }
                } else {
                    if ($onlyLegacy) {
                        $stats['pdf_skipped']++;
                    } elseif (! $edition->pdf_file || Edition::isAbsoluteUrl($edition->pdf_file)) {
                        $stats['pdf_skipped']++;
                    } else {
                        $count = $extractor->extractFromEdition($edition, force: $force);
                        if ($count > 0) {
                            $stats['pdf_ok']++;
                        } else {
                            $stats['pdf_no_text']++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error('Edição #'.$edition->id.': '.$e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Resumo:');
        $this->line('  PDFs extraídos com texto:        '.$stats['pdf_ok']);
        $this->line('  PDFs sem camada de texto:        '.$stats['pdf_no_text'].' (sugira edição manual no admin)');
        $this->line('  PDFs ignorados (sem arquivo):    '.$stats['pdf_skipped']);
        $this->line('  Legacy reagrupados com sucesso:  '.$stats['legacy_ok']);
        $this->line('  Legacy sem matérias (ignorados): '.$stats['legacy_skipped']);
        $this->line('  Erros:                            '.$stats['errors']);

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
