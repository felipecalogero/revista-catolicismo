<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupCompressedPdfsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'editions:cleanup-compressed-pdfs
                            {--dry-run : Apenas exibe o que seria feito, sem alterar arquivos}
                            {--disk=public : Disco do storage onde estão os PDFs}
                            {--path=editions/pdfs : Subcaminho dentro do disco onde procurar}
                            {--force : Promove o .compressed mesmo se for igual/maior que o original}';

    /**
     * @var string
     */
    protected $description = 'Promove arquivos .pdf.compressed órfãos para substituir o PDF original (legado de bug onde o rename falhava após compressão)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $disk = (string) $this->option('disk');
        $path = trim((string) $this->option('path'), '/');

        $storage = Storage::disk($disk);
        $absoluteDir = $storage->path($path);

        if (! is_dir($absoluteDir)) {
            $this->error("Diretório não encontrado: {$absoluteDir}");

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhum arquivo será alterado.');
        }

        $this->info("Procurando arquivos .pdf.compressed em: {$absoluteDir}");

        $pattern = $absoluteDir.DIRECTORY_SEPARATOR.'*.pdf.compressed';
        $compressedFiles = glob($pattern) ?: [];

        if (empty($compressedFiles)) {
            $this->info('Nenhum arquivo .pdf.compressed encontrado. Nada a fazer.');

            return self::SUCCESS;
        }

        $this->info('Encontrados '.count($compressedFiles).' arquivo(s) .pdf.compressed.');
        $this->newLine();

        $promoted = 0;
        $skipped = 0;
        $failed = 0;
        $totalBytesSaved = 0;

        foreach ($compressedFiles as $compressedPath) {
            $originalPath = preg_replace('/\.compressed$/', '', $compressedPath);
            $relativeOriginal = ltrim(str_replace($storage->path(''), '', $originalPath), DIRECTORY_SEPARATOR);

            if ($originalPath === $compressedPath || ! is_string($originalPath)) {
                $this->warn("[SKIP] Não foi possível derivar o caminho original de: {$compressedPath}");
                $skipped++;
                continue;
            }

            if (! file_exists($compressedPath)) {
                $this->warn("[SKIP] Arquivo comprimido sumiu: {$compressedPath}");
                $skipped++;
                continue;
            }

            $compressedSize = filesize($compressedPath) ?: 0;

            if (! $this->isValidPdf($compressedPath)) {
                $this->warn("[SKIP] Arquivo .compressed não parece um PDF válido: {$compressedPath}");
                $skipped++;
                continue;
            }

            if (! file_exists($originalPath)) {
                $this->line("[PROMOVE] Original não existe, apenas renomeando: {$relativeOriginal}");

                if (! $dryRun) {
                    if (! @rename($compressedPath, $originalPath)) {
                        $this->error("  [FAIL] rename() falhou para {$compressedPath}");
                        $failed++;
                        continue;
                    }
                }

                $promoted++;
                $totalBytesSaved += 0;
                continue;
            }

            $originalSize = filesize($originalPath) ?: 0;

            if (! $force && $compressedSize >= $originalSize) {
                $this->warn(sprintf(
                    '[SKIP] Comprimido (%s) não é menor que o original (%s): %s',
                    $this->formatBytes($compressedSize),
                    $this->formatBytes($originalSize),
                    $relativeOriginal
                ));
                $skipped++;
                continue;
            }

            $saved = $originalSize - $compressedSize;

            $this->line(sprintf(
                '[PROMOVE] %s  (%s → %s, economiza %s)',
                $relativeOriginal,
                $this->formatBytes($originalSize),
                $this->formatBytes($compressedSize),
                $this->formatBytes($saved)
            ));

            if (! $dryRun) {
                if (! @rename($compressedPath, $originalPath)) {
                    $this->error("  [FAIL] rename() falhou. Tentando copy+unlink como fallback.");

                    if (! @copy($compressedPath, $originalPath)) {
                        $this->error("  [FAIL] copy() também falhou. Pulando.");
                        $failed++;
                        continue;
                    }

                    if (! @unlink($compressedPath)) {
                        $this->warn("  [WARN] copy ok, mas falhou ao remover o .compressed: {$compressedPath}");
                    }
                }
            }

            $promoted++;
            $totalBytesSaved += $saved;
        }

        $this->newLine();
        $this->info('============================================');
        $this->info("  Promovidos : {$promoted}");
        $this->info("  Ignorados  : {$skipped}");
        $this->info("  Falhas     : {$failed}");
        $this->info('  Espaço economizado: '.$this->formatBytes($totalBytesSaved));
        $this->info('============================================');

        if ($dryRun) {
            $this->warn('Nenhuma alteração foi feita (dry-run). Rode novamente sem --dry-run para aplicar.');
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Verifica se o arquivo tem o cabeçalho PDF (%PDF-).
     */
    private function isValidPdf(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 5);
        fclose($handle);

        return $header === '%PDF-';
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $size = (float) $bytes;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $precision).' '.$units[$i];
    }
}
