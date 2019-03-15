<?php

namespace Kodix\LaravelHelpers\Support\Validation\Rules;

class Hex implements \Illuminate\Contracts\Validation\Rule
{
    /**
     * Whether hex must be full-formatted like #ffffff, not #fff.
     *
     * @var bool
     */
    protected $full = false;

    public function full(): Hex
    {
        $this->full = true;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $pattern = '/^\#([a-fA-F0-9]{6}';
        if (! $this->full) {
            $pattern .= '|[a-fA-F0-9]{3}';
        }
        $pattern .= ')$/';

        return (bool) preg_match($pattern, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.hex');
    }
}
