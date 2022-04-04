<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

it('can create from items', function () {
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(NavItem::all());
    $this->assertInstanceOf(\Dive\Nova\Linkable\LinkedCollection::class, $collection);
});

it('can retrieve linked records', function () {
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(NavItem::all())
        ->loadLinkedData('url');

    $this->assertNotNull($collection->first());

    $this->markTestIncomplete('The LinkedCollection does not set `linkedValues` on the model yet.');

    // TODO: How do we cache this on the LinkedCollection's individual models?
    $this->assertNotNull($collection->first()->linkedValues);
});