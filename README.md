# Nova Linkable Field

This package allows you to provide a field that can optionally link to a different model as an alternative to a manual attribute value.

## What problem does this package solve?

Sometimes, you want users of the back-end to be able to either derive a value from a different model, or manually fill in the value.

If you have a model that *might* link to another model from which it derives an attribute, but also want to provide a fallback value (in case it isn't linked), that can get messy quick in the back-end. In that case you'll end up with two fields in the resource, and the user needs to understand that the fallback value is only used if the resource isn't linked to another model. Not exactly obvious.

With this package, you can provide a single field where the user can explicitly choose, leaving zero room for confusion: get the value for an attribute from a particular model, or fill it in manually. (Check out the use case below.)

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

You can publish the migrations with:

```bash
php artisan vendor:publish --tag=linkable-migrations
```

## Usage

### Terminology

* A model can be **linked** to another model.
* The attached model is called the **target**, and the originator is the **linked** model.
* If *no target* is specified, the fallback value is used.

### Setting up the resource

You must publish and run the included migrations:

    php artisan vendor:publish --tag=linkable-migrations
    php artisan migrate

In the resource, you can choose which field you would like to use a linkable field.

```php
LinkableField::make('URL', 'url')
    ->withLinkable(
        Page::class, // the related model that is linked
        'CMS Page', // how the model is identified to the user
        ['title'], // columns queried for use in the callback (next parameter)
        fn ($page) => $page->getAttribute('title') // callback that resolves the display value of the related model
    ),
```

### Setting up the model

First, let's start off with the **link** class, which is the originator. It needs the `InteractsWithLinks` trait.

```php
use InteractsWithLinks;
```

As a part of this trait you must implement the abstract method, `targets()` which defines how the models are linked to properties, for example:

```php
public function targets(): array
{
    return [
        'url' => Page::class,
    ];
}
```

So, if you have a homogeneous collection that contains solely models of the same type, you can load this information. Here's how you can do this:

```php
use \Dive\Nova\Linkable\LinkedCollection;

// Load the target relationships and attributes in as few queries as possible
$menuItems = LinkedCollection::create(MenuItem::all())
    ->loadLinkedData(['url']);

// Access the target (returns a model)
$menuItems->first()->linkedTargets['url'];

// Access the attribute (returns a value by calling `getLinkableValue()` on the linked target model)
$menuItems->first()->linkedAttributes['url'];
```

If you attempt to load linked relationships on a non-homogenous collection or on models that do not support linkable values, you'll get an exception explaining what went wrong.

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
