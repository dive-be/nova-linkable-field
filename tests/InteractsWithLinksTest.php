<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

it('can retrieve model', function () {
    $this->assertNotEmpty(\Tests\Testbench\Models\Page::query()->get());
});

it('can retrieve linked items via model', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::query()
        ->where('title', '=', 'About')
        ->firstOrFail();

    $this->assertNotNull($navItem);
    $this->assertNotNull($links = $navItem->links());
    $this->assertNotEmpty($links);
});

it('can retrieve all linked values', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::query()
        ->where('title', '=', 'About')
        ->firstOrFail();

    $pages = $navItem->getTargetsByAttribute('url');
    $this->assertEquals('/path/to/about', $pages->first()->getLinkableValue('url'));
});

it('cannot retrieve value that is not mapped (throws exception)', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::query()
        ->where('title', '=', 'About')
        ->firstOrFail();

    $this->expectException(\Dive\Nova\Linkable\Exceptions\UnmappedAttributeException::class);

    $data = $navItem->getTargetsByAttribute('some_attribute');
});