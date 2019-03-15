<?php
// @formatter:off

if (! function_exists('get_fixture')) {
    /**
     * Returns file with fixture data from given directory.
     *
     * @param string $fileName Название файла с массивом в папке mocks.
     * @param string $directory
     *
     * @return array
     */
    function get_fixture($fileName, $directory = 'database/mocks'): array
    {
        $fileName = ends_with('.php', $fileName) ? $fileName : $fileName.'.php';
        $file = require base_path($directory.DIRECTORY_SEPARATOR.$fileName);

        return is_array($file) ? $file : [];
    }
}

if (! function_exists('human_filesize')) {
    /**
     * Returns human-readable size of file.
     * NOTE: you should set translations for sizes formats in file.php lang file
     *
     * @param $bytes
     * @param int $decimals
     *
     * @return string
     */
    function human_filesize($bytes, $decimals = 2)
    {
        $size = trans('file.size');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }
}

if (! function_exists('is_connected')) {
    /**
     * Checks whether server is connected to internet by ping google as the most tolerant resource.
     *
     * @return bool
     */
    function is_connected()
    {
        static $isConnected = null;
        if ($isConnected !== null) {
            return $isConnected;
        }
        $isConnected = false;
        $connection = env('IS_CONNECTED', true) ? @fsockopen('www.google.com', 80) : null;
        if ($connection) {
            fclose($connection);
            $isConnected = true;
        }

        return $isConnected;
    }
}

if (! function_exists('trans_or_default')) {
    /**
     * Returns translated key or default value.
     *
     * @param string $key
     * @param $default
     * @param array $replace
     * @param null $locale
     *
     * @return string
     */
    function trans_or_default(string $key, $default, array $replace = [], $locale = null): string
    {
        $message = trans($key, $replace, $locale);

        return $message === $key ? $default : $message;
    }
}

if (! function_exists('get_domain')) {
    /**
     * Returns domain name from application config.
     *
     * @param string|null $prepend
     *
     * @return string
     */
    function get_domain(string $prepend = null): string
    {
        [$scheme, $domain] = explode('://', config('app.url'), 2);

        return str_replace('www.', '', $prepend ? $prepend.'.'.$domain : $domain);
    }
}

if (! function_exists('domain_is')) {
    /**
     * Checks whether domain name equals to given.
     *
     * @param string $prefix
     *
     * @return bool
     */
    function domain_is(string $prefix): bool
    {
        $domain = get_domain();

        return starts_with($prefix.'.', $domain);
    }
}
