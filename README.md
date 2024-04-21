# GeoffTech Laravel Image Styles

## Uses Intervention Images

- https://image.intervention.io/v3/modifying/resizing

## Alias in app.php

```php
  'aliases' => Facade::defaultAliases()->merge([
    'ImageStyle' => ImageStyle::class,
  ])->toArray(),
```

## Sample Attribute

```php
  public function thumbnailUrl(): Attribute
  {
    return Attribute::make(
      get: fn() => ImageStyle::thumbnail($this->image)
    );
  }
```
