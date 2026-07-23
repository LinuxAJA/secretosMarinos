<?php
/**
 * ============================================================================
 * ImageUploadService.php — Carga segura de imágenes
 * ============================================================================
 * Valida error PHP, tamaño y MIME real; genera nombre aleatorio y almacena
 * únicamente rutas relativas dentro de /uploads/images.
 * ============================================================================
 */

namespace App\Services;

use finfo;

class ImageUploadService
{
    /** @var array<string,string> MIME real → extensión segura */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private int $maxSize;
    /** @var list<string> */
    private array $allowedMimes;

    public function __construct()
    {
        $config = require CONFIG_PATH . '/app.php';
        $this->maxSize = (int) $config['uploads']['max_size'];
        $this->allowedMimes = $config['uploads']['allowed_images'];
    }

    /**
     * @param array<string,mixed>|null $file Elemento de $_FILES
     * @return array{ok:bool,path?:?string,error?:string,uploaded?:bool}
     */
    public function upload(?array $file, string $folder): array
    {
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null, 'uploaded' => false];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'La imagen no pudo cargarse correctamente.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->maxSize) {
            $maxMb = round($this->maxSize / 1024 / 1024, 1);
            return ['ok' => false, 'error' => "La imagen debe pesar máximo {$maxMb} MB."];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'El archivo recibido no es una carga válida.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (
            !is_string($mime)
            || !in_array($mime, $this->allowedMimes, true)
            || !isset(self::EXTENSIONS[$mime])
        ) {
            return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
        }

        $safeFolder = trim($folder, '/\\');
        if (!preg_match('/^[a-z0-9_-]+$/', $safeFolder)) {
            return ['ok' => false, 'error' => 'Carpeta de destino no válida.'];
        }

        $targetDirectory = UPLOADS_PATH . '/images/' . $safeFolder;
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true)) {
            return ['ok' => false, 'error' => 'No se pudo preparar la carpeta de imágenes.'];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::EXTENSIONS[$mime];
        $target = $targetDirectory . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }

        return [
            'ok' => true,
            'path' => 'images/' . $safeFolder . '/' . $filename,
            'uploaded' => true,
        ];
    }

    /**
     * Elimina solo archivos dentro de UPLOADS_PATH/images.
     */
    public function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $normalized = str_replace('\\', '/', ltrim($relativePath, '/'));
        if (!str_starts_with($normalized, 'images/')) {
            return;
        }

        $fullPath = UPLOADS_PATH . '/' . $normalized;
        $imagesRoot = realpath(UPLOADS_PATH . '/images');
        $directory = realpath(dirname($fullPath));

        if (!$imagesRoot || !$directory || !str_starts_with($directory, $imagesRoot)) {
            return;
        }

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
