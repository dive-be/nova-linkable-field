<?php

namespace Dive\FlexibleUrlField\Models;

interface HasUrlField
{
    /**
     * Any model implementing this method should return the URL associated with the provided field.
     * Most Eloquent models will have just one URL, but if a model must support multiple URLs,
     * then you can return different results by checking the `$field` value.
     */
    public function getUrl($field = 'url');
}