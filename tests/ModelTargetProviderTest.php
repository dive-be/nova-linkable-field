<?php declare(strict_types=1);

use Tests\Testbench\Database\SimpleSeeder;
use Dive\Nova\Linkable\ModelTargetProvider;
use Tests\Testbench\Models\NavItem;
use Tests\Testbench\Models\Page;

beforeEach(function () {
    SimpleSeeder::run();
});

test('service container can resolve model target provider', function () {
    $modelTargetProvider = app(ModelTargetProvider::class);
    $this->assertNotNull($modelTargetProvider);
});

test('resolved link repository is singleton', function () {
    $modelTargetProvider1 = app(ModelTargetProvider::class);
    $modelTargetProvider2 = app(ModelTargetProvider::class);

    $this->assertEquals($modelTargetProvider1, $modelTargetProvider2);
});

test('mapping can be registered via config', function () {
    config()->set(
        'nova-linkable-field.mapping',
        [NavItem::class => ['url' => Page::class]]
    );

    $targetProvider = app(ModelTargetProvider::class);

    $this->assertEquals(
        [NavItem::class => ['url' => Page::class]],
        $targetProvider->allMapping()
    );

    $this->assertEquals(
        ['url' => Page::class],
        $targetProvider->getMapping(NavItem::class)
    );
});

test('mapping can be registered via methods', function () {
    config()->set('nova-linkable-field.mapping', []);

    $targetProvider = app(ModelTargetProvider::class);

    $this->assertEquals([], $targetProvider->allMapping());

    $targetProvider->register(NavItem::class, [
        'url' => Page::class
    ]);

    $this->assertEquals(
        [NavItem::class => ['url' => Page::class]],
        $targetProvider->allMapping()
    );

    $this->assertEquals(
        ['url' => Page::class],
        $targetProvider->getMapping(NavItem::class)
    );
});