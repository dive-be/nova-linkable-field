<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;
use Tests\Testbench\Models\Page;
use Dive\Nova\Linkable\LinkedCollection;

beforeEach(function () {
    SimpleSeeder::run();
});

it('must use trait', function () {
    $this->expectException(\Exception::class);
    LinkedCollection::create(Page::all());
});

it('cannot create from mixed types', function () {
    $this->expectException(\Exception::class);
    LinkedCollection::create(
        Page::all()->merge(NavItem::all())
    );
});

it('can create from items', function () {
    $collection = LinkedCollection::create(NavItem::all());
    $this->assertInstanceOf(LinkedCollection::class, $collection);
});

it('can retrieve linked records', function () {
    DB::connection()->enableQueryLog();

    $collection = LinkedCollection::create(NavItem::all())
        ->loadLinkedData(['url', 'internal_url']);

    $this->assertNotNull($collection->first()->linkedTargets);
    $this->assertNotNull($collection->first()->linkedAttributes);

    $this->assertArrayHasKey('url', $collection->first()->linkedAttributes->all());
    $this->assertArrayHasKey('internal_url', $collection->first()->linkedAttributes->all());

    $queries = DB::getQueryLog();

    // We expect three actual queries
    $this->assertEquals(3, count($queries), "Too many queries were performed.");

    // 1. The fetching of the nav items
    $this->assertEquals('select * from "nav_items"', $queries[0]["query"]);

    // 2. The fetching of the links for all nav items in question (for all given attributes)
    $this->assertEquals('select * from "links" where "linkable_id" in (?, ?) '
        . 'and "linkable_type" = ? and "attribute" in (?, ?)', $queries[1]["query"]);

    // 3. The fetching of targets for each type (same type for both attributes, so still one)
    $this->assertEquals('select * from "pages" where "id" in (?)', $queries[2]["query"]);
});

it('can retrieve limited linked records', function () {
    DB::connection()->enableQueryLog();

    $collection = LinkedCollection::create(NavItem::all())
        ->loadLinkedData(['url']);

    $this->assertNotNull($collection->first()->linkedTargets->all());
    $this->assertNotNull($collection->first()->linkedAttributes->all());

    $this->assertArrayHasKey('url', $collection->first()->linkedAttributes->all());
    $this->assertArrayNotHasKey('internal_url', $collection->first()->linkedAttributes->all());

    $queries = DB::getQueryLog();

    $this->assertEquals(3, count($queries), "Too many queries were performed.");

    // 1. The fetching of the nav items
    $this->assertEquals('select * from "nav_items"', $queries[0]["query"]);

    // 2. The fetching of the links for all nav items in question (for one single attribute)
    $this->assertEquals('select * from "links" where "linkable_id" in (?, ?) '
        . 'and "linkable_type" = ? and "attribute" in (?)', $queries[1]["query"]);

    // 3. The fetching of targets for each type (just one here, as we have one single attribute)
    $this->assertEquals('select * from "pages" where "id" in (?)', $queries[2]["query"]);
});