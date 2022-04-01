<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Dive\Nova\Linkable\LinkRepository;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

it('can create link repository', function () {
    $name = config('nova-linkable-field.model');

    /** @var \Dive\Nova\Linkable\Models\Link $link */
    $link = new $name;

    $this->linkRepository = new LinkRepository($link);

    $this->assertNotNull($this->linkRepository);
});

it('can retrieve all linked pages', function () {
    $navItem = NavItem::query()
        ->where('title', '=', 'About')
        ->firstOrFail();

    $this->assertNotNull($navItem);
});