<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;
use Dive\Nova\Linkable\LinkedCollection;

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

    $this->expectException(\Dive\Nova\Linkable\Exceptions\UnmappedTargetException::class);

    $navItem->getTargetsByAttribute('attribute');
});

it('can retrieve bulk attribute values', function () {
    $collection = LinkedCollection::create(NavItem::all())
        ->loadLinkedData(['url', 'internal_url']);

    $this->markTestIncomplete('The LinkedCollection does not set `linkedValues` on the model yet.');

    $this->assertNotNull($collection->first()->linkedValues);
});