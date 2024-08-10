<?php

use GeoffTech\LaravelImageStyle\ImageStyle;

if (!function_exists('img')) {
    function img(?string $name)
    {
        return ImageStyle::make($name);
    }
}
