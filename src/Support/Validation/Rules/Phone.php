<?php

namespace Kodix\LaravelHelpers\Support\Validation\Rules;

class Phone implements \Illuminate\Contracts\Validation\Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/\+7\([\d]{3}\)[\d]{3}-[\d]{2}-[\d]{2}/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.phone');
    }
}
