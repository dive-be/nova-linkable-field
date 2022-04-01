<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

it('can retrieve pages', function () {
    $this->assertNotEmpty(\Tests\Testbench\Models\Page::query()->get());
});

it('can retrieve links via model', function () {
    $entries = NavItem::query()->get();
    $this->assertNotNull($entries->first->links());
});
