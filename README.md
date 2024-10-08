# GeoffTech Laravel Image Styles

- resizes and stores images such as thumbnails or the original
- resized images are stored in a dedicated 'cache' disk to separate from real data
- works by generating a URL to a resized file
- if that URL is viewed, it initially hits a route which generates and saves that resized image
- this uses the concept that real files will always be served before the routes are processed

## Uses Intervention Images

- https://image.intervention.io/v3/modifying/resizing

## Sample Attribute

```php
    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => img($this->image)->thumbnail()
        );
    }
```

## Setup

### 1. Setup Cache Disk

- create and link a new local disk
- edit `/config/filesystem.php`
- add a cache disk

```php
    'disks' => [

        ...

        'cache' => [
            'driver' => 'local',
            'root' => storage_path('app/cache'),
            'url' => env('APP_URL') . '/cache',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],
```

### 2. Add a Storage link entry

```php
    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('cache') => storage_path('app/cache'),
    ],
```

### 3. Run Storage Link

```bash
a storage:link
```

- now files in the `/storage/app/cache` folder are accessible from the `/cache` folder in the domain name
