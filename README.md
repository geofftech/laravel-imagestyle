# GeoffTech Laravel Image Styles

- resizes and stores images such as thumbnails or the original
- resized images are stored in a dedicated 'cache' disk to separate from real data
- works by generating a URL to a resized file
- if that URL is viewed, it initially hits a route which generates and saves that resized image
- this uses the concept that real files will always be served before the routes are processed

## Agent Instructions

This Laravel package provides image resizing and caching functionality. When working with this codebase:

### Key Components

- **ImageStyle.php**: Main class that handles image processing and URL generation
- **ImageStyleController.php**: Controller that handles dynamic image generation requests
- **ServiceProvider.php**: Registers the package services and routes
- **helpers.php**: Contains the `img()` helper function for easy access
- **Commands**: Clean and purge commands for cache management

### Helper Function Usage

Use the `img()` helper function to generate image style URLs:

```php
// Basic usage
img($imagePath)->thumbnail()
img($imagePath)->resize(300, 200)

// In Eloquent models
public function thumbnailUrl(): Attribute
{
    return Attribute::make(
        get: fn () => img($this->image)->thumbnail()
    );
}
```

### Configuration

- Configuration file: `config/imagestyle.php`
- Cache disk must be configured in `config/filesystems.php`
- Routes are automatically registered via the ServiceProvider

### Image Processing Flow

1. Helper function generates URL pointing to cache location
2. If cached file doesn't exist, route intercepts request
3. Controller processes original image and saves to cache
4. Subsequent requests serve the cached file directly

### Development Notes

- Uses Intervention Image v3 for image processing
- Requires properly configured cache disk and storage links
- Images are stored in `/storage/app/cache` and served from `/cache` URL

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
