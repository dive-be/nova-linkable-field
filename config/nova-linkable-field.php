<?php declare(strict_types=1);

use Dive\Nova\Linkable\Models\Link;

return [
    /**
     * The fully qualified class names of models that should be used.
     */
    'model' => Link::class,

    /**
     * How models are linked to each other.
     * Format: e.g.: `NavItem::class => ['url' => Page::class]`
     */
    'mapping' => [
        // You can define your mapping here or do it programmatically.
    ]
];