<?php
/**
 * This file is a part of Yamaha Web CIS project.
 * Email:       support@kodix.ru
 * Company:     Kodix LLC <https://kodix.com>
 * Developer:   Igor Malyuk <https://github.com/malyusha>
 */

namespace Kodix\LaravelHelpers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Kodix\LaravelHelpers\Support\FileUploader\Uploader;

trait HasFiles
{
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

    protected static function bootHasFiles()
    {
        /**@var \Illuminate\Contracts\Filesystem\Filesystem $filesystem */
        $filesystem = app('filesystem');

        static::updating(function (Model $model) use ($filesystem) {
            foreach ($model->getUploadFields() as $key) {
                if (strpos($key, '->') !== false) {
                    // If we have json key with sequence, we need to get the value from model json attribute (we'll
                    // suppose, that model has this attribute in `casts` property)
                    $values = explode('->', $key);
                    $jsonValues = (array) $model->fromJson($model->getOriginal(reset($values), '{}'));
                    $originalFile = array_get($jsonValues, last($values));
                    // It's like if we getting model attribute from json property (model->images['key'] or
                    // model->images['height']['thumb'])
                    $newFile = array_get((array) $model->{reset($values)}, last($values), '');
                } else {
                    $newFile = $model->getAttribute($key);
                    $originalFile = $model->getOriginal($key);
                }

                if ($originalFile && $originalFile !== $newFile && $filesystem->exists($originalFile)) {
                    $filesystem->delete($originalFile);
                }
            }
        });

        static::saving(function (Model $model) use ($filesystem) {
            foreach ($model->getUploadFields() as $key) {
                if ($model->{$key} instanceof UploadedFile) {
                    $model->{$key} = (new Uploader($filesystem))->upload($model->{$key});
                }
            }
        });

        static::deleting(function (Model $model) use ($filesystem) {
            if (in_array(SoftDeletes::class, trait_uses_recursive($model)) && ! $model->forceDeleting) {
                return;
            }

            foreach ($model->getUploadFields() as $key) {
                $filePath = $model->{$key.'_path'};
                if ($filePath && $filesystem->exists($filePath)) {
                    $filesystem->delete($model->{$key.'_path'});
                }
            }
        });
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
            [$method, $originalKey] = $this->uploadGetKeys[$key];

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
     * @return mixed
     */
    public function getUploadFields()
    {
        $this->findUploadFields();

        return $this->uploadFieldsKeys;
    }

    public function getUploadUrl($attribute, $value)
    {
        return app('filesystem')->url($value);
    }

    public function getUploadPath($attribute, $value)
    {
        return $value;
    }

    public function setUploadFile($attribute, $value)
    {

    }

    protected function findUploadFields()
    {
        if (is_array($this->uploadGetKeys) && is_array($this->uploadSetKeys) && is_array($this->uploadFieldsKeys)) {
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
}
