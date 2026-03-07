<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Comprime uma imagem e converte para WebP
     * 
     * @param string $inputPath Caminho real do arquivo original
     * @param string $outputPath Caminho real onde salvar a imagem WebP
     * @param int $quality Qualidade da compressão (0-100)
     * @return bool True se for bem-sucedido
     */
    public function compressAndConvertToWebP(string $inputPath, string $outputPath, int $quality = 80): bool
    {
        try {
            if (!file_exists($inputPath)) {
                Log::error('ImageService: Arquivo não encontrado', ['path' => $inputPath]);
                return false;
            }

            // Detectar o tipo da imagem
            $imageInfo = getimagesize($inputPath);
            if (!$imageInfo) {
                Log::error('ImageService: Não foi possível obter informações da imagem', ['path' => $inputPath]);
                return false;
            }

            $mimeType = $imageInfo['mime'];
            $image = null;

            // Criar a imagem baseada no tipo
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($inputPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($inputPath);
                    // Preservar transparência no PNG se necessário, 
                    // mas ao converter para WebP, o imagewebp lida com isso.
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($inputPath);
                    imagepalettetotruecolor($image);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($inputPath);
                    break;
                default:
                    Log::warning('ImageService: Formato não suportado para conversão', ['mime' => $mimeType]);
                    return false;
            }

            if (!$image) {
                Log::error('ImageService: Falha ao criar imagem a partir do arquivo', ['path' => $inputPath, 'mime' => $mimeType]);
                return false;
            }

            // Salvar como WebP
            $result = imagewebp($image, $outputPath, $quality);
            
            // Liberar memória
            imagedestroy($image);

            if ($result) {
                Log::info('ImageService: Imagem convertida para WebP com sucesso', [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'quality' => $quality
                ]);
                return true;
            }

            Log::error('ImageService: Falha ao salvar imagem WebP', ['output' => $outputPath]);
            return false;

        } catch (\Exception $e) {
            Log::error('ImageService: Exceção ao processar imagem', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Processa um arquivo do storage, converte para WebP e deleta o original se solicitado
     * 
     * @param string $path Caminho relativo no disco (ex: 'editions/covers/image.jpg')
     * @param string $disk Nome do disco do storage
     * @param int $quality Qualidade do WebP
     * @param bool $deleteOriginal Se deve deletar o arquivo original após conversão
     * @return string|null O novo caminho do arquivo WebP ou null em caso de falha
     */
    public function processStorageImage(string $path, string $disk = 'public', int $quality = 80, bool $deleteOriginal = true): ?string
    {
        $storage = Storage::disk($disk);
        if (!$storage->exists($path)) {
            return null;
        }

        $fullPath = $storage->path($path);
        $pathInfo = pathinfo($path);
        
        // Se já for WebP, apenas retornar o caminho (ou poderíamos re-comprimir se necessário)
        if (strtolower($pathInfo['extension']) === 'webp') {
            return $path;
        }

        $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        $fullOutputPath = $storage->path($newPath);

        if ($this->compressAndConvertToWebP($fullPath, $fullOutputPath, $quality)) {
            if ($deleteOriginal) {
                $storage->delete($path);
            }
            return $newPath;
        }

        return null;
    }
}
