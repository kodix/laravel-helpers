<?php
// @formatter:off

if (! function_exists('get_mock')) {
    /**
     * Получает файл с массивом шаблонных данных для заполнения БД.
     *
     * @param string $fileName Название файла с массивом в папке mocks.
     * @param string $directory
     *
     * @return array
     */
    function get_mock($fileName, $directory = 'database/mocks'): array
    {
        $fileName = ends_with('.php', $fileName) ? $fileName : $fileName.'.php';
        $file = require base_path($directory.DIRECTORY_SEPARATOR.$fileName);

        return is_array($file) ? $file : [];
    }
}

if (! function_exists('human_filesize')) {
    /**
     * Выводит человеко-читаемый формат переданного размера.
     *
     * @param $bytes
     * @param int $decimals
     *
     * @return string
     */
    function human_filesize($bytes, $decimals = 2)
    {
        $size = trans('lang.file.size');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }
}

if (! function_exists('is_connected')) {
    /**
     * Проверяет наличие соединения сервера с интернетом.
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
    function trans_or_default(string $key, $default, array $replace = [], $locale = null)
    {
        $message = trans($key, $replace, $locale);

        return $message === $key ? $default : $message;
    }
}

if (! function_exists('get_domain')) {
    function get_domain(string $prepend = null): string
    {
        [$scheme, $domain] = explode('://', config('app.url'), 2);

        return str_replace('www.', '', $prepend ? $prepend.'.'.$domain : $domain);
    }
}

if (! function_exists('domain_is')) {
    function domain_is(string $prefix): bool
    {
        $domain = get_domain();

        return starts_with($prefix.'.', $domain);
    }
}