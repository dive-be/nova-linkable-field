<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;
use Dive\Nova\Linkable\Exceptions\UnmappedTargetException;
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

it('can retrieve nav item with nested links', function () {
    $this->assertNotNull(NavItem::with('links')->get());
});

it('cannot retrieve value that is not mapped (throws exception)', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::with('links')
        ->where('title', '=', 'About')
        ->firstOrFail();

    $this->expectException(UnmappedTargetException::class);

    $navItem->getTargetsByAttribute('attribute');
});

it('can retrieve url via linked model', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::query()->where('title', '=', 'About')->firstOrFail();

    $this->assertEquals(
        '/path/to/about',
        $navItem->getLinkedAttributeValue('url')
    );
});

it('can retrieve url via fallback property', function () {
    /** @var NavItem $navItem */
    $navItem = NavItem::query()->where('title', '=', 'Home')->firstOrFail();

    $this->assertEquals(
        '/home',
        $navItem->getLinkedAttributeValue('url')
    );
});

/**
 * When attempting to retrieve linked attributes on an item that was populated
 * with relevant data via `loadLinkedData` the cached data is used.
 */
it('retrieving targets from attribute uses existing data', function () {
    DB::connection()->enableQueryLog();

    $collection = LinkedCollection::create(NavItem::all())
        ->loadLinkedData(['url', 'internal_url']);

    $this->assertEquals(3, count(DB::getQueryLog()), "Too many queries were performed.");

    $collection->first()->getLinkedAttributeValue('url');
    $this->assertEquals(3, count(DB::getQueryLog()), "Too many queries were performed.");
});

/**
 * When attempting to retrieve linked attributes on an item that was not populated
 * with relevant data, a fresh query is executed.
 */
it('retrieving targets without calling `loadLinkedData` performs individual queries', function () {
    DB::connection()->enableQueryLog();

    $collection = LinkedCollection::create(NavItem::all());
    $this->assertEquals(1, count(DB::getQueryLog()), "Too many queries were performed.");

    $collection->first()->getLinkedAttributeValue('url');
    $this->assertEquals(2, count(DB::getQueryLog()), "Too many queries were performed.");
});