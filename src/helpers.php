<?php

if (!function_exists('lang')) {
    /**
     * @param null $key
     * @param array $replace
     * @param null $locale
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     */
    function lang($key = null, $replace = [], $locale = null)
    {
        if (!trans()->has($key)) {
            return ucfirst(substr($key, strripos($key, '.') + 1));
        }

        return __($key, $replace, $locale);
    }
}
