<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageStyle
{
    public ?string $path = null;

    public string $disk = 'public';

    public string $cache_disk = 'cache';

    public string $cache_folder = 'images';

    public string $mode = 'scale'; // cover|scale

    public ?int $height = null;

    public ?int $width = null;

    public ?string $placeholder = '/images/placeholder.png';

    public $image;

    public static function make(?string $path)
    {
        $img = new self;

        $img->path = $path;

        return $img;
    }

    public function height(null|int|string $height)
    {
        if ($height == '') {
            $height = null;
        }

        if (is_string($height)) {
            $height = intval($height);
        }

        $this->height = $height;

        return $this;
    }

    public function width(null|int|string $width)
    {
        if ($width == '') {
            $width = null;
        }

        if (is_string($width)) {
            $width = intval($width);
        }

        $this->width = $width;

        return $this;
    }

    public function path(string $path)
    {
        $this->path = $path;

        return $this;
    }

    public function disk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function mode(string $mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function cover(?int $width = null, ?int $height = null)
    {
        if ($width) {
            $this->width($width);
        }
        if ($height) {
            $this->height($height);
        }

        return $this->mode('cover');
    }

    public function scale(?int $width = null, ?int $height = null)
    {
        if ($width) {
            $this->width($width);
        }
        if ($height) {
            $this->width($height);
        }

        return $this->mode('scale');
    }

    public function placeholder(?string $placeholder = null)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function thumbnail(int $size = 200)
    {
        return $this->cover()
            ->width($size)
            ->height($size);
    }

    public function banner(int $width = 1200, int $height = 400)
    {
        return $this->scale()
            ->width($width)
            ->height($height);
    }

    public function getOptions(): string
    {
        return 'm' . $this->mode
            . '+h' . $this->height
            . '+w' . $this->width;
    }

    public function parseOptions(string $option_str)
    {
        $options = explode('+', $option_str);

        foreach ($options as $option) {
            $param = $option[0];
            $value = substr($option, 1);

            match ($param) {
                'm' => $this->mode($value),
                'h' => $this->height($value),
                'w' => $this->width($value),
                default => null
            };
        }

        return $this;
    }

    public function getTarget(): string
    {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        return $this->cache_folder . '/' . $this->path . '/' . $this->getOptions() . '.' . $ext;
    }

    public function __toString()
    {
        if (!$this->path) {
            return asset($this->placeholder);
        }

        if (str_starts_with($this->path, 'https://') || str_starts_with($this->path, 'http://')) {
            return $this->path;
        }

        if (!Storage::disk($this->disk)->exists($this->path)) {
            return asset($this->placeholder);
        }

        $target = $this->getTarget();

        $url = Storage::disk($this->cache_disk)->url($target);

        return $url;
    }

    public function getFullPath()
    {
        $target = $this->getTarget();

        return Storage::disk($this->cache_disk)->path($target);
    }

    private function processImage()
    {
        // match ($this->mode) {
        //     'cover' => $this->image->cover($this->width, $this->height),
        //     'scale' => $this->image->scale($this->width, $this->height),
        // };

        // cover
        if ($this->mode === 'cover') {
            $this->image->cover($this->width, $this->height);
        }

        // scale
        elseif ($this->height && $this->width) {
            $this->image->scale($this->width, $this->height);
        } elseif (!$this->height && !$this->width) {
            // do nothing
        } elseif ($this->height && !$this->width) {
            $this->image->scale(height: $this->height);
        } elseif (!$this->height && $this->width) {
            $this->image->scale(width: $this->width);
        }

        return $this;
    }

    private function saveFile($target)
    {
        $directory = dirname($target);

        Storage::disk($this->cache_disk)->makeDirectory($directory);

        $this->image->save(Storage::disk($this->cache_disk)->path($target));

        return $this;
    }

    public function generate()
    {
        if (blank($this->path)) {
            return null;
        }

        $path = Storage::disk($this->disk)->path($this->path);

        $manager = new ImageManager(Driver::class);
        $this->image = $manager->read($path);

        $this->processImage();

        $target = $this->getTarget();

        $this->saveFile($target);

        return $this;
    }
}
