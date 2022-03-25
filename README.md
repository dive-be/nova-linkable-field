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

You must make sure to add a `linkable` morph to the table of the model you'd like to make linkable. You can do this via a migration.

```php
$table->morphs('linkable');
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