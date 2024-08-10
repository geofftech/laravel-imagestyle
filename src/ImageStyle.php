<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageStyle
{
    public ?string $path = null;

    public string $disk = 'public';

    public string $mode = 'scale'; // cover|scale

    public ?int $height = null;

    public ?int $width = null;

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

    public function cover()
    {
        return $this->mode('cover');
    }

    public function scale()
    {
        return $this->mode('scale');
    }

    public function thumbnail(int $size = 100)
    {
        return $this->cover()
            ->width($size)
            ->height($size);
    }

    public function banner(int $width = 500, int $height = 1000)
    {
        return $this->scale()
            ->width($width)
            ->height($height);
    }

    public function getOptions(): string
    {
        return 'm'.$this->mode
            .'+h'.$this->height
            .'+w'.$this->width;
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

        return 'image_style/'.$this->path.'/'.$this->getOptions().'.'.$ext;
    }

    public function __toString()
    {
        if (!$this->path) {
            return asset('/images/placeholder.png');
        }

        if (str_starts_with($this->path, 'https://') || str_starts_with($this->path, 'http://')) {
            return $this->path;
        }

        if (!Storage::disk('public')->exists($this->path)) {
            return asset('/images/placeholder.png');
        }

        $target = $this->getTarget();

        $url = Storage::disk($this->disk)->url($target);

        return $url;
    }

    public function getFullPath()
    {
        $target = $this->getTarget();

        return Storage::disk($this->disk)->path($target);
    }

    private function processImage()
    {
        match ($this->mode) {
            'cover' => $this->image->cover($this->width, $this->height),
            'scale' => $this->image->scale($this->width, $this->height),
        };

        return $this;
    }

    private function saveFile($target)
    {
        $directory = dirname($target);

        Storage::disk($this->disk)->makeDirectory($directory);

        $this->image->save(Storage::disk($this->disk)->path($target));

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
