<?php

namespace GeoffTech\LaravelImageStyle;

use Closure;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;
use Illuminate\Support\Facades\Log;

class ImageStyle
{
  public static function thumbnail(string|null $path, int $scale = 200)
  {
    return self::cover($path, $scale);
  }

  public static function banner(string|null $path)
  {
    return self::scale($path, 1000, 500);
  }

  public static function height(string|null $path, int $height = 200)
  {
    // return self::scale($path, null, $height);
    return self::process(
      $path,
      '-scale-' . '-' . $height,
      function (ImageInterface $image) use ($height) {
        $image->scale(height: $height);
      }
    );
  }

  public static function scale(string|null $path, ?int $width = 800, ?int $height = 800)
  {
    return self::process(
      $path,
      '-scale-' . $width . '-' . $height,
      function (ImageInterface $image) use ($width, $height) {
        $image->scale($width, $height);
      }
    );
  }

  public static function cover(string|null $path, int $scale = 800)
  {
    return self::process(
      $path,
      '-cover-' . $scale,
      function (ImageInterface $image) use ($scale) {
        $image->cover($scale, $scale);
      }
    );
  }

  public static function process(string|null $path, string $tag, Closure $process)
  {

    if (is_null($path)) {
      return null;
    }

    if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
      return $path;
    }

    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $token = md5($path . $tag);
    $newValue = 'image_style/' . $token . '.' . $ext;

    if (!Storage::disk('public')->exists($newValue)) {
      try {

        $oldPath = Storage::disk('public')->get($path);
        $newPath = Storage::disk('public')->path($newValue);

        $manager = new ImageManager(Driver::class);
        $image = $manager->read($oldPath);

        $process($image);
        $image->save($newPath);

        Log::info('image-style', ['path' => $path, 'tag' => $tag]);

      } catch (\Throwable $th) {

        Log::error('image-style', ['path' => $path, 'tag' => $tag, 'error' => $th->getMessage()]);
        return null;

      }
    }

    return asset(Storage::disk('public')->url($newValue));

  }

}