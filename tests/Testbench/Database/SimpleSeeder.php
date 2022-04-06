<?php

namespace Tests\Testbench\Database;

use Dive\Nova\Linkable\Models\Link;
use Tests\Testbench\Models\Page;
use Tests\Testbench\Models\NavItem;

class SimpleSeeder
{
    /**
     * Idempotent seeder.
     * @return void
     */
    public static function run()
    {
        Page::query()->truncate();
        NavItem::query()->truncate();
        Link::query()->truncate();

        Page::query()->insert([
            ['title' => 'Homepage', 'slug' => 'home', 'body' => 'This is the homepage.'],
            ['title' => 'About', 'slug' => 'about', 'body' => 'This is the about page.'],
            ['title' => 'Products', 'slug' => 'products/dive', 'body' => 'This is the page about our DIVE product.'],
        ]);

        NavItem::query()->insert([
            ['title' => 'Home', 'url' => '/home'],
            ['title' => 'About', 'url' => null],
        ]);

        Link::query()->insert([
            'linkable_type' => NavItem::class,
            'linkable_id' => NavItem::query()
                ->where('title', '=', 'About')
                ->first()->getKey(),
            'target_type' => Page::class,
            'target_id' => Page::query()
                ->where('slug', '=', 'about')
                ->first()->getKey(),
            'attribute' => 'url',
        ]);
    }
}