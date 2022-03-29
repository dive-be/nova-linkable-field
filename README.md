# Nova Linkable Field

This package allows you to provide a field that can **optionally link to a different model as an alternative to a manual attribute value**.

## Use Case

For example: you have a `MenuItem` model in your application.

Maybe you want this `MenuItem` to link to a particular model (a `Page` model, perhaps?), which has its own URL. You can do that, or provide a manual URL alternative (if you do not want to link a model).

This makes it very obvious to users in Nova that the field in question is *either* linked or a fixed, manual value.

## Requirements

- `laravel/framework: ^9.0`
- `laravel/nova: ^3.0`

## Installation

You can install the package via Composer:

```bash
composer require dive-be/nova-linkable-field
```

### Usage

You must run the included migrations:

    php artisan vendor:publish --tag=linkable-migrations
    php artisan migrate

In the resource you can specify which URL you would like to link.  It is currently only possible to attach one flexible URL per model due to this relationship, but this may change until v1.0 is released.

```php
FlexibleUrl::make('URL', 'url')
    ->withLinkable(
        Page::class, // the related model that is linked
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
