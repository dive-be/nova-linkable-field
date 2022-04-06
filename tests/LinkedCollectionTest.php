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
    $collection = \Dive\Nova\Linkable\LinkedCollection::create(NavItem::all())
        ->loadLinkedData(['url', 'internal_url']);

    $this->assertNotNull($collection->first()->linkedTargets);
    $this->assertNotNull($collection->first()->linkedAttributes);
});