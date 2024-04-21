<?php

namespace GeoffTech\LaravelImageStyle;

use Closure;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;

class ImageStyle
{
  public static function thumbnail(string|null $oldValue, int $scale = 200)
  {
    return self::cover($oldValue, $scale);
  }

  public static function banner(string|null $oldValue)
  {
    return self::scale($oldValue, 1000, 500);
  }

  public static function height(string|null $oldValue, int $height = 200)
  {
    // return self::scale($oldValue, null, $height);
    return self::process(
      $oldValue,
      '-scale-' . '-' . $height,
      function (ImageInterface $image) use ($height) {
        $image->scale(height: $height);
      }
    );
  }

  public static function scale(string|null $oldValue, ?int $width = 800, ?int $height = 800)
  {
    return self::process(
      $oldValue,
      '-scale-' . $width . '-' . $height,
      function (ImageInterface $image) use ($width, $height) {
        $image->scale($width, $height);
      }
    );
  }

  public static function cover(string|null $oldValue, int $scale = 800)
  {
    return self::process(
      $oldValue,
      '-cover-' . $scale,
      function (ImageInterface $image) use ($scale) {
        $image->cover($scale, $scale);
      }
    );
  }

  public static function process(string|null $oldValue, string $tag, Closure $process)
  {

    if (is_null($oldValue)) {
      return null;
    }

    if (str_starts_with($oldValue, 'https://') || str_starts_with($oldValue, 'http://')) {
      return $oldValue;
    }

    $ext = pathinfo($oldValue, PATHINFO_EXTENSION);
    $token = md5($oldValue . $tag);
    $newValue = 'image_style/' . $token . '.' . $ext;

    if (!Storage::disk('public')->exists($newValue)) {
      $oldPath = Storage::disk('public')->get($oldValue);
      $newPath = Storage::disk('public')->path($newValue);

      $manager = new ImageManager(Driver::class);
      $image = $manager->read($oldPath);
      $process($image);
      $image->save($newPath);
    }

    return asset(Storage::disk('public')->url($newValue));
  }

}