<?php
/**
 * This file is a part of Yamaha Experience project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Support\FileUploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    /**
     * Uploader constructor.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array $settings
     */
    public function __construct(UploadedFile $file, array $settings = [])
    {
        if (isset($settings['filename'])) {
            $this->modifyFileName($settings['filename']);
        }
        $this->file = $file;
    }

    protected $imageUploadSettings = [];

    protected $fileNameModifier;

    /**
     * @return array
     */
    public function getUploadSettings(): array
    {
        if (property_exists($this, 'uploadSettings')) {
            return (array) $this->uploadSettings;
        }

        return $this->imageUploadSettings;
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

    public function setImageUploadSettings(array $settings): self
    {
        $this->imageUploadSettings = $settings;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        if ($this->fileNameModifier !== null) {
            return is_callable($this->fileNameModifier) ? call_user_func($this->fileNameModifier, $this->file)
                : $this->fileNameModifier;
        }

        return $this->file->getClientOriginalName().'.'.$this->file->getClientOriginalExtension();
    }

    public function upload(): string
    {
        $filename = $this->getFilename();
        $path = $this->getRelativePath($filename);

        if (! app('filesystem')->makeDirectory($path)) {
            throw new \Exception("Failed to create directory {$path}");
        }

        return $path.DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * @return bool
     */
    protected function isImage(): bool
    {
        $size = getimagesize($this->file->getRealPath());

        return (bool) $size;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getRelativePath(string $filename): string
    {
        // returns {config_path}/{model_prefix}/{generated_directories}
        $prefix = $this->getEntityPrefix();

        if ($prefix !== '') {
            $prefix .= DIRECTORY_SEPARATOR;
        }

        if (property_exists($this, 'uploadPath') && $this->uploadPath !== null) {
            $path = is_callable($this->uploadPath) ? call_user_func($this->uploadPath, $this) : $this->uploadPath;
        } else {
            $path = $this->getPathHashParts($filename, 2);
        }

        return $this->getSystemUploadPrefix().DIRECTORY_SEPARATOR.$prefix.$path;
    }

    protected function getSystemUploadPrefix(): string
    {
        return '';
    }
}
