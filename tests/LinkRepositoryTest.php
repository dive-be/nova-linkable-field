<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Dive\Nova\Linkable\LinkRepository;
use Tests\Testbench\Models\NavItem;

beforeEach(function () {
    SimpleSeeder::run();
});

test('service container can resolve link repository', function () {
    $this->linkRepository = app(LinkRepository::class);
    $this->assertNotNull($this->linkRepository);
});

test('resolved link repository is singleton', function () {
    $repository1 = app(LinkRepository::class);
    $repository2 = app(LinkRepository::class);

    $this->assertEquals($repository1, $repository2);
});