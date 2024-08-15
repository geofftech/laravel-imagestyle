<?php

use GeoffTech\LaravelImageStyle\ImageStyleController;
use Illuminate\Support\Facades\Route;

Route::get('/cache/images/{everything}', ImageStyleController::class)->where(['everything' => '.*']);
