<?php declare(strict_types=1);

use Dive\LinkableField\Models\LinkablePivot;
use Tests\Testbench\Models\NavItem;
use Tests\Testbench\Models\Page;

beforeEach(function () {
    Page::query()->insert([
        ['title' => 'Homepage', 'slug' => 'home', 'body' => 'This is the homepage.'],
        ['title' => 'About', 'slug' => 'about', 'body' => 'This is the about page.'],
        ['title' => 'Products', 'slug' => 'products/dive', 'body' => 'This is the page about our DIVE product.'],
    ]);
});

it('can retrieve pages', function () {
    $this->assertNotEmpty(\Tests\Testbench\Models\Page::query()->get());
});

it('can retrieve multiple nav items', function () {
    NavItem::query()->insert([
        ['title' => 'Home', 'url' => '/'],
        ['title' => 'About', 'url' => null],
    ]);

    $entries = NavItem::query()->get();

    LinkablePivot::query()->insert([
        'source_type' => NavItem::class,
        'source_id' => $entries[1]->id,
        'target_type' => Page::class,
        'target_id' => 1,
    ]);

    $this->markTestIncomplete('Linkable implementation missing');

    // $this->assertEquals('/', $entries[0]->getLinkable('url'));
    // $this->assertEquals('/path/to/home', $entries[1]->getLinkable('url'));
});
