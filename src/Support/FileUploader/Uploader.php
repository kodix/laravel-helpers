<?php

namespace Kodix\LaravelHelpers\Support\FileUploader;

use Illuminate\Http\UploadedFile;
use League\Flysystem\Util;

class Uploader
{
    protected $fileNameModifier;

    protected $uploadPrefix;

    protected $uploadPath;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * Uploader constructor.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $filesystem
     * @param array $settings
     */
    public function __construct(\Illuminate\Contracts\Filesystem\Filesystem $filesystem, array $settings = [])
    {
        $this->filesystem = $filesystem;

        $this->parseSettings($settings);
    }

    /**
     * @param array $settings
     */
    protected function parseSettings(array $settings): void
    {
        $this->setUploadPrefix(array_get($settings, 'upload_prefix', ''));
        $this->setUploadPath(array_get($settings, 'path', ''));
    }

    public function setUploadPath(string $path)
    {
        $this->uploadPath = $path;

        return $this;
    }

    public function setUploadPrefix(string $prefix)
    {
        $this->uploadPrefix = $prefix;

        return $this;
    }

    /**
     * @param $modifier
     *
     * @return $this
     */
    public function modifyFileName($modifier): self
    {
        $this->fileNameModifier = $modifier;

        return $this;
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param null $modifier
     *
     * @return string
     */
    public function resolveFilename(UploadedFile $file, $modifier = null): string
    {
        $defaultFileName = $file->getClientOriginalName();

        if ($modifier === null) {
            return $defaultFileName;
        }

        return is_callable($modifier) ? $modifier($file) : $modifier;
    }

    /**
     * Uploads file to the server.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param array $settings
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Kodix\LaravelHelpers\Support\FileUploader\UploadException
     */
    public function upload(UploadedFile $file, array $settings = []): string
    {
        $filename = $this->resolveFilename($file, array_get($settings, 'filename'));
        $imageSettings = (array) array_get($settings, 'image_settings', []);

        $path = $this->getRelativePath($filename, array_get($settings, 'prefix', ''));

        if (! $this->filesystem->makeDirectory($path)) {
            throw new UploadException("Failed to create directory {$path}");
        }

        $content = $file->get();

        if (count($imageSettings) > 0 && $this->shouldModifyAsImage($file)) {
            $image = \Intervention\Image\Facades\Image::make($this->file);
            foreach ($imageSettings as $method => $args) {
                call_user_func_array([$image, $method], $args);
            }

            $content = $image->stream()->__toString();
        }

        $fullPath = $path.DIRECTORY_SEPARATOR.$filename;

        $this->filesystem->put($fullPath, $content);

        return $fullPath;
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return bool
     */
    protected function shouldModifyAsImage(UploadedFile $file): bool
    {
        return class_exists('Intervention\Image\Facades\Image') && $this->isImage($file);
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return bool
     */
    public static function isImage(UploadedFile $file): bool
    {
        $size = getimagesize($file->getRealPath());

        return (bool) $size;
    }

    /**
     * @param string $filename
     *
     * @param string $dynamicPrefix
     *
     * @return string
     */
    protected function getRelativePath(string $filename, $dynamicPrefix = ''): string
    {
        $uploadPath = $this->uploadPath;
        $prefix = $this->getUploadPrefix().DIRECTORY_SEPARATOR.$dynamicPrefix;

        // returns {config_path}/{generated_directories}
        return $uploadPath ?: Util::normalizePath($prefix.DIRECTORY_SEPARATOR.$this->getPathHashParts($filename, 2));
    }

    /**
     * @param $string
     * @param int $parts
     *
     * @return string
     */
    private function getPathHashParts($string, $parts = 1): string
    {
        $currentOffset = 2;
        do {
            $output[] = substr(sha1($string), $currentOffset, 2);
            $currentOffset += 4;
            $parts--;
        } while ($parts !== 0);

        return implode(DIRECTORY_SEPARATOR, $output);
    }

    public function getUploadPrefix(): string
    {
        return $this->uploadPrefix;
    }
}
