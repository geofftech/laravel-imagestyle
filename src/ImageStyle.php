<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageStyle
{
    public string $path = '';

    public string $disk = 'public';

    public string $process = 'scale'; // cover|scale

    public ?int $height = null;

    public ?int $width = null;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function setProcess(string $process)
    {
        $this->process = $process;

        return $this;
    }

    public function setHeight(?int $height)
    {
        $this->height = $height;

        return $this;
    }

    public function setWidth(?int $width)
    {
        $this->width = $width;

        return $this;
    }

    public function thumbnail(int $scale = 200)
    {
        return $this->setProcess('cover')
            ->setWidth($scale)
            ->setHeight($scale);
    }

    public function banner(int $width = 500, int $height = 1000)
    {
        return $this->setProcess('scale')
            ->setWidth($width)
            ->setHeight($height);
    }

    public function getKey()
    {
        return md5($this->path.'-'.$this->process.'-'.$this->height.'-'.$this->width);
    }

    public function getCachePath()
    {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);
        $key = $this->getKey();
        $path = 'image_style/'.$key.'.'.$ext;

        return $path;
    }

    public function get(): ?string
    {
        if (blank($this->path)) {
            return null;
        }

        if (str_starts_with($this->path, 'https://') || str_starts_with($this->path, 'http://')) {
            return $this->path;
        }

        $path = $this->getCachePath();

        if (!Storage::disk('public')->exists($path)) {
            try {

                $oldPath = Storage::disk($this->disk)->get($this->path);
                $newPath = Storage::disk($this->disk)->path($path);

                $manager = new ImageManager(Driver::class);
                $image = $manager->read($oldPath);

                match ($this->process) {
                    'cover' => $image->cover($this->width, $this->height),
                    'scale' => $image->scale($this->width, $this->height),
                };

                $image->save($newPath);

                Log::info('imageStyle', [
                    'path' => $this->path,
                    'cache' => $path,
                ]);

            } catch (\Throwable $th) {

                Log::error('imageStyle', [
                    'path' => $this->path,
                    'cache' => $path,
                    'error' => $th->getMessage(),
                ]);

                return null;

            }
        }

        return asset(Storage::disk('public')->url($path));
    }
}
