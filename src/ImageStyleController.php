<?php

namespace GeoffTech\LaravelImageStyle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageStyleController extends Controller
{
    public function __invoke(Request $request)
    {
        $everything = $request->everything;
        $pos = strrpos($everything, '/');
        $options = substr($everything, $pos + 1);
        $path = substr($everything, 0, $pos);

        $img = ImageStyle::make($path)
            ->parseOptions($options)
            ->generate();

        return response()->file($img->getFullPath());
    }
}
