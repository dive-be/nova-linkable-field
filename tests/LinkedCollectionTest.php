<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

it('must use trait', function () {
    $this->expectException(\Exception::class);
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(['hello', 'primitive']);
});

it('can create from items', function () {
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(NavItem::all());
    $this->assertInstanceOf(\Dive\Nova\Linkable\LinkedCollection::class, $collection);
});

it('can retrieve linked records', function () {
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(NavItem::all())
        ->loadLinkedData('url');

    $this->assertNotNull($collection->first());

    $this->assertNotNull($collection->first()->linkedValues);
});