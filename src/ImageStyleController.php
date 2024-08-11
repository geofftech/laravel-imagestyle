<?php

namespace GeoffTech\LaravelImageStyle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Route::get('/storage/image_style/{everything}', ImageStyleController::class)->where(['everything' => '.*']);
 */
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

        return response()->download($img->getFullPath());
    }
}
