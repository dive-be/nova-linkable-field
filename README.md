# A Nova field to link URLs (manually or to models)

This package allows you to provide a field that can **link to a model instance (of your choosing)** or to **a manual url**.

## Requirements

- `laravel/nova: ^3.0`

## Installation

You can install the package via composer:

```bash
composer require dive-be/nova-flexible-url-field
```

### Usage

You must run the included migrations:

    php artisan vendor:publish --tag=flexible-url-migrations
    php artisan migrate

In the resource you can specify which URL you would like to link.  It is currently only possible to attach one flexible URL per model due to this relationship, but this may change until v1.0 is released.

```php
FlexibleUrl::make('URL', 'url')
    ->withLinkable(
        Page::class, // related the model that is linked
        'CMS Page', // how the model is identified to the user
        ['title'], // columns queried for use in the callback (next parameter)
        fn ($page) => $page->getAttribute('title') // callback that resolves the display value of the related model
    ),
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email oss@dive.be instead of using the issue tracker.

## Credits

- [Nico Verbruggen](https://github.com/nicoverbruggen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
