<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

trait Upload
{
    protected static function bootUpload()
    {
        static::updating(function (Model $model) {
            foreach ($model->getUploadFields() as $key) {
                if (strpos($key, '->') !== false) {
                    // If we have json key with sequence, we need to get the value from model json attribute (we'll
                    // suppose, that model has this attribute in `casts` property)
                    $values = explode('->', $key);
                    $jsonValues = (array) $model->fromJson($model->getOriginal(reset($values), '{}'));
                    $originalValue = array_get($jsonValues, last($values));
                    // It's like if we getting model attribute from json property (model->images['key'] or
                    // model->images['hight']['thumb'])
                    $modelValue = array_get((array) $model->{reset($values)}, last($values), '');
                } else {
                    $modelValue = $model->getAttribute($key);
                    $originalValue = $model->getOriginal($key);
                }
                if ($originalValue && $originalValue !== $modelValue && file_exists($filePath = public_path($originalValue))) {
                    unlink($filePath);
                }
            }
        });

        static::saving(function (Model $model) {
            foreach ($model->getUploadFields() as $key) {
                if ($model->{$key} instanceof UploadedFile) {
                    $model->attachFile($key, $model->{$key});
                }
            }
        });

        static::deleting(function (Model $model) {
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, trait_uses_recursive($model)) && ! $model->forceDeleting) {
                return;
            }

            foreach ($model->getUploadFields() as $key) {
                $filePath = $model->{$key.'_path'};
                if (! empty($filePath) and file_exists($filePath)) {
                    unlink($model->{$key.'_path'});
                }
            }
        });
    }

    /**
     * @var array
     */
    protected $uploadGetKeys;

    /**
     * @var array
     */
    protected $uploadSetKeys;

    /**
     * @var array
     */
    protected $uploadFieldsKeys;

    /**
     * @return array
     */
    public function getUploadSettings(): array
    {
        if (property_exists($this, 'uploadSettings')) {
            return (array) $this->uploadSettings;
        }

        return [];
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return string
     */
    public function getUploadFilename(UploadedFile $file): string
    {
        if (property_exists($this, 'uploadFileName') && $this->uploadFileName !== null) {
            return is_callable($this->uploadFileName) ? call_user_func($this->uploadFileName, $file) :
                $this->uploadFileName;
        }

        // return hash('sha256', uniqid('file_', false)).'.'.$file->getClientOriginalExtension();

        return $file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
    }

    /**
     * @param string $field
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return string
     */
    protected function attachFile($field, UploadedFile $file): string
    {
        return $this->attach($file, true, $field);
    }

    public function attachFileWithoutModel(UploadedFile $file): string
    {
        return $this->attach($file);
    }

    public function attach(UploadedFile $file, $hasModel = false, string $field = null): string
    {
        $filename = $this->getUploadFilename($file);
        $relativePath = $this->getRelativePath($filename);

        if (! is_dir($absolutePath = public_path($relativePath))) {
            \File::makeDirectory($absolutePath, 493, true);
        }

        $path = $absolutePath.DIRECTORY_SEPARATOR.$filename;

        if ($this->isImageUploadedFile($file)) {
            if ($hasModel && $this->hasCast($field, 'image')) {
                $settings = (array) array_get($this->getUploadSettings(), $field, []);
            } else {
                $settings = $this->getUploadSettings();
            }

            $image = Image::make($file);

            foreach ($settings as $method => $args) {
                call_user_func_array([$image, $method], $args);
            }

            $image->save($path);
        } else {
            $file->move($absolutePath, $filename);
        }

        $result = $relativePath.DIRECTORY_SEPARATOR.$filename;

        if ($hasModel) {
            $this->{$field} = $result;
        }

        return $result;
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

        return config('sleeping_owl.imagesUploadDirectory').DIRECTORY_SEPARATOR.$prefix.$path;
    }

    /**
     * @return string
     */
    protected function getEntityPrefix(): string
    {
        if (method_exists($this, 'getUploadPrefix') && ($prefix = $this->getUploadPrefix()) !== null) {
            return $prefix;
        }

        return (method_exists($this, 'getModel') ? $this->getModel()->getTable() : $this->getTable());
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $this->findUploadFields();

        if ($this->isUploadField($key)) {
            list($method, $originalKey) = $this->uploadGetKeys[$key];

            $value = $this->getAttribute($originalKey);

            if ($this->hasGetMutator($key)) {
                return $this->mutateAttribute($key, $value);
            }

            return $this->{$method}($originalKey, $value);
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        $this->findUploadFields();

        if ($this->isUploadField($key)) {
            return $this->getAttribute($key);
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->findUploadFields();

        if ($this->isUploadField($key)) {
            list($method, $originalKey) = $this->uploadSetKeys[$key];

            if ($this->hasSetMutator($key)) {
                $method = 'set'.Str::studly($key).'Attribute';

                return $this->{$method}($value);
            }

            return $this->{$method}($originalKey, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isUploadField($key)
    {
        return array_key_exists($key, $this->uploadGetKeys) || array_key_exists($key, $this->uploadSetKeys);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return string|null
     */
    public function getUploadUrl($key, $value)
    {
        if (! empty($value)) {
            return url($value);
        }
    }

    /**
     * @param string $key
     * @param UploadedFile|null $file
     */
    public function setUploadFile($key, UploadedFile $file = null)
    {
        $this->{$key} = $file;
    }

    /**
     * @return mixed
     */
    public function getUploadFields()
    {
        $this->findUploadFields();

        return $this->uploadFieldsKeys;
    }

    protected function findUploadFields()
    {
        if (is_array($this->uploadGetKeys) and is_array($this->uploadSetKeys) and is_array($this->uploadFieldsKeys)) {
            return;
        }

        $fields = [];

        $casts = $this->getCasts();

        foreach ($casts as $field => $type) {
            if (in_array($type, ['upload', 'file', 'image'])) {
                $fields[] = $field;
            }
        }

        $this->uploadFieldsKeys = array_unique($fields);
        $this->uploadGetKeys = $this->uploadSetKeys = [];

        foreach ($this->uploadFieldsKeys as $field) {
            $this->uploadGetKeys[$field.'_url'] = ['getUploadUrl', $field];
            $this->uploadGetKeys[$field.'_path'] = ['getUploadPath', $field];

            $this->uploadSetKeys[$field.'_file'] = ['setUploadFile', $field];
        }
    }

    /**
     * @param UploadedFile $file
     *
     * @return bool
     */
    protected function isImageUploadedFile(UploadedFile $file)
    {
        $size = getimagesize($file->getRealPath());

        return (bool) $size;
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

    /**
     * @param string $key
     * @param string $value
     *
     * @return string|null
     */
    public function getUploadPath($value)
    {
        if (! empty($value)) {
            return public_path($value);
        }
    }
}
