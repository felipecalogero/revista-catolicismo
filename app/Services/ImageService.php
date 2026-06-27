<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Comprime uma imagem e converte para WebP
     *
     * @param  string  $inputPath  Caminho real do arquivo original
     * @param  string  $outputPath  Caminho real onde salvar a imagem WebP
     * @param  int  $quality  Qualidade da compressão (0-100)
     */
    public function compressAndConvertToWebP(string $inputPath, string $outputPath, int $quality = 80): bool
    {
        try {
            if (! file_exists($inputPath)) {
                Log::error('ImageService: Arquivo não encontrado', ['path' => $inputPath]);

                return false;
            }

            $imageInfo = getimagesize($inputPath);
            if (! $imageInfo) {
                Log::error('ImageService: Não foi possível obter informações da imagem', ['path' => $inputPath]);

                return false;
            }

            $mimeType = $imageInfo['mime'];
            $image = $this->createImageFromFile($inputPath, $mimeType);
            if (! $image) {
                Log::error('ImageService: Falha ao criar imagem a partir do arquivo', ['path' => $inputPath, 'mime' => $mimeType]);

                return false;
            }

            $image = $this->applyExifOrientation($image, $inputPath, $mimeType);

            $result = imagewebp($image, $outputPath, $quality);
            imagedestroy($image);

            if ($result) {
                Log::info('ImageService: Imagem convertida para WebP com sucesso', [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'quality' => $quality,
                ]);

                return true;
            }

            Log::error('ImageService: Falha ao salvar imagem WebP', ['output' => $outputPath]);

            return false;
        } catch (\Exception $e) {
            Log::error('ImageService: Exceção ao processar imagem', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Redimensiona proporcionalmente para caber dentro de maxWidth x maxHeight.
     *
     * @return \GdImage|resource|false
     */
    public function resizeToFit($image, int $maxWidth, int $maxHeight)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    /**
     * Processa um arquivo do storage, converte para WebP e deleta o original se solicitado
     *
     * @param  string  $path  Caminho relativo no disco (ex: 'editions/covers/image.jpg')
     * @param  string  $disk  Nome do disco do storage
     * @param  int  $quality  Qualidade do WebP
     * @param  bool  $deleteOriginal  Se deve deletar o arquivo original após conversão
     * @param  int|null  $maxWidth  Largura máxima antes da conversão (null = sem resize)
     * @param  int|null  $maxHeight  Altura máxima antes da conversão (null = sem resize)
     */
    public function processStorageImage(
        string $path,
        string $disk = 'public',
        int $quality = 80,
        bool $deleteOriginal = true,
        ?int $maxWidth = null,
        ?int $maxHeight = null
    ): ?string {
        $storage = Storage::disk($disk);
        if (! $storage->exists($path)) {
            return null;
        }

        $fullPath = $storage->path($path);
        $pathInfo = pathinfo($path);
        $newPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
        $fullOutputPath = $storage->path($newPath);

        if ($maxWidth !== null && $maxHeight !== null) {
            if (! $this->resizeStorageImageFile($fullPath, $fullPath, $maxWidth, $maxHeight)) {
                Log::warning('ImageService: resize falhou, prosseguindo sem redimensionar', ['path' => $path]);
            }
        }

        if (strtolower($pathInfo['extension'] ?? '') === 'webp' && $path === $newPath) {
            return $path;
        }

        if ($this->compressAndConvertToWebP($fullPath, $fullOutputPath, $quality)) {
            if ($deleteOriginal && $path !== $newPath) {
                $storage->delete($path);
            }

            return $newPath;
        }

        return null;
    }

    /**
     * Redimensiona arquivo de imagem no disco, sobrescrevendo o original.
     */
    public function resizeStorageImageFile(string $inputPath, string $outputPath, int $maxWidth, int $maxHeight): bool
    {
        try {
            $imageInfo = getimagesize($inputPath);
            if (! $imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];
            $image = $this->createImageFromFile($inputPath, $mimeType);
            if (! $image) {
                return false;
            }

            $image = $this->applyExifOrientation($image, $inputPath, $mimeType);
            $resized = $this->resizeToFit($image, $maxWidth, $maxHeight);

            $saved = match ($mimeType) {
                'image/jpeg', 'image/jpg' => imagejpeg($resized, $outputPath, 90),
                'image/png' => imagepng($resized, $outputPath, 6),
                'image/gif' => imagegif($resized, $outputPath),
                'image/webp' => imagewebp($resized, $outputPath, 90),
                default => false,
            };

            imagedestroy($resized);

            return (bool) $saved;
        } catch (\Exception $e) {
            Log::error('ImageService: falha ao redimensionar imagem', [
                'path' => $inputPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return \GdImage|resource|false|null
     */
    protected function createImageFromFile(string $path, string $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => $this->createImageFromPng($path),
            'image/gif' => $this->createImageFromGif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };
    }

    /**
     * @return \GdImage|resource|false
     */
    protected function createImageFromPng(string $path)
    {
        $image = imagecreatefrompng($path);
        if (! $image) {
            return false;
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @return \GdImage|resource|false
     */
    protected function createImageFromGif(string $path)
    {
        $image = imagecreatefromgif($path);
        if (! $image) {
            return false;
        }
        imagepalettetotruecolor($image);

        return $image;
    }

    /**
     * @param  \GdImage|resource  $image
     * @return \GdImage|resource
     */
    protected function applyExifOrientation($image, string $path, string $mimeType)
    {
        if (! in_array($mimeType, ['image/jpeg', 'image/jpg'], true) || ! function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 1);
        } catch (\Throwable) {
            return $image;
        }

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
