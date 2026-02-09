<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfCompressor
{
    /**
     * Comprime um arquivo PDF usando Ghostscript
     * 
     * @param string $inputPath Caminho completo do arquivo PDF original
     * @param string $outputPath Caminho onde salvar o PDF comprimido
     * @param string $quality Nível de qualidade: 'screen', 'ebook', 'printer', 'prepress'
     * @return bool True se a compressão foi bem-sucedida
     */
    public function compress(string $inputPath, string $outputPath, string $quality = 'ebook'): bool
    {
        try {
            // Verificar se o arquivo existe
            if (!file_exists($inputPath)) {
                Log::error('PDF Compressor: Arquivo não encontrado', ['path' => $inputPath]);
                return false;
            }

            // Verificar se Ghostscript está disponível
            $gsPath = $this->getGhostscriptPath();
            if (!$gsPath) {
                Log::warning('PDF Compressor: Ghostscript não encontrado, pulando compressão');
                return false;
            }

            // Verificar tamanho original
            $originalSize = filesize($inputPath);
            if ($originalSize < 1024 * 1024) { // Menor que 1MB, não precisa comprimir
                Log::info('PDF Compressor: Arquivo muito pequeno, pulando compressão', [
                    'size' => $this->formatBytes($originalSize)
                ]);
                return false;
            }

            // Configurações de qualidade
            $qualitySettings = $this->getQualitySettings($quality);

            // Criar diretório de saída se não existir
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Montar comando Ghostscript para compressão
            $command = sprintf(
                '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/%s -dNOPAUSE -dQUIET -dBATCH -dColorImageResolution=%d -dGrayImageResolution=%d -dMonoImageResolution=%d -dColorImageDownsampleType=/Bicubic -dGrayImageDownsampleType=/Bicubic -dMonoImageDownsampleType=/Subsample -sOutputFile=%s %s 2>&1',
                escapeshellarg($gsPath),
                $qualitySettings['pdfsettings'],
                $qualitySettings['image_resolution'],
                $qualitySettings['image_resolution'],
                $qualitySettings['mono_resolution'],
                escapeshellarg($outputPath),
                escapeshellarg($inputPath)
            );

            // Executar compressão
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('PDF Compressor: Erro ao comprimir PDF', [
                    'error' => implode("\n", $output),
                    'return_code' => $returnVar,
                    'input' => $inputPath,
                    'output' => $outputPath
                ]);
                return false;
            }

            // Verificar se o arquivo foi criado e se é menor que o original
            if (!file_exists($outputPath)) {
                Log::error('PDF Compressor: Arquivo comprimido não foi criado', ['output' => $outputPath]);
                return false;
            }

            $compressedSize = filesize($outputPath);

            // Se o arquivo comprimido for maior, usar o original
            if ($compressedSize >= $originalSize) {
                Log::info('PDF Compressor: Arquivo comprimido não é menor, mantendo original', [
                    'original' => $this->formatBytes($originalSize),
                    'compressed' => $this->formatBytes($compressedSize)
                ]);
                unlink($outputPath);
                return false;
            }

            $reduction = round((1 - ($compressedSize / $originalSize)) * 100, 2);

            Log::info('PDF Compressor: Compressão bem-sucedida', [
                'original' => $this->formatBytes($originalSize),
                'compressed' => $this->formatBytes($compressedSize),
                'reduction' => $reduction . '%',
                'quality' => $quality
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('PDF Compressor: Exceção ao comprimir PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Comprime um arquivo PDF no storage do Laravel
     * 
     * @param string $storagePath Caminho do arquivo no storage (ex: 'editions/pdfs/file.pdf')
     * @param string $disk Disco do storage (padrão: 'public')
     * @param string $quality Nível de qualidade
     * @return bool True se a compressão foi bem-sucedida
     */
    public function compressStorageFile(string $storagePath, string $disk = 'public', string $quality = 'ebook'): bool
    {
        $storage = Storage::disk($disk);
        $fullPath = $storage->path($storagePath);

        // Criar caminho temporário para o arquivo comprimido
        $tempPath = $fullPath . '.compressed';

        // Comprimir
        $success = $this->compress($fullPath, $tempPath, $quality);

        if ($success) {
            // Substituir o arquivo original pelo comprimido
            if (file_exists($tempPath)) {
                rename($tempPath, $fullPath);
                return true;
            }
        } else {
            // Limpar arquivo temporário se existir
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return false;
    }

    /**
     * Obtém o caminho do executável Ghostscript
     */
    private function getGhostscriptPath(): ?string
    {
        // Tentar encontrar o Ghostscript
        $possiblePaths = [
            '/usr/bin/gs',
            '/usr/local/bin/gs',
            'gs', // Se estiver no PATH
        ];

        foreach ($possiblePaths as $path) {
            $output = [];
            $returnVar = 0;
            exec(escapeshellarg($path) . ' --version 2>&1', $output, $returnVar);
            
            if ($returnVar === 0) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Configurações de qualidade para compressão
     */
    private function getQualitySettings(string $quality): array
    {
        $settings = [
            'screen' => [
                'pdfsettings' => 'screen',
                'image_resolution' => 72,
                'mono_resolution' => 72,
            ],
            'ebook' => [
                'pdfsettings' => 'ebook',
                'image_resolution' => 150,
                'mono_resolution' => 300,
            ],
            'printer' => [
                'pdfsettings' => 'printer',
                'image_resolution' => 300,
                'mono_resolution' => 300,
            ],
            'prepress' => [
                'pdfsettings' => 'prepress',
                'image_resolution' => 300,
                'mono_resolution' => 1200,
            ],
        ];

        return $settings[$quality] ?? $settings['ebook'];
    }

    /**
     * Formata bytes para formato legível
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
