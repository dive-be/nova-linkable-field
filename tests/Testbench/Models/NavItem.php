<?php

namespace Tests\Testbench\Models;

use Dive\LinkableField\Models\HasLinkableRelationship;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use HasLinkableRelationship;

    protected $table = 'nav_items';

    protected array $linkables = [
        'url' => \Domain\Editorial\Models\Page::class,
        // 'body' => \Domain\Editorial\Models\ContentBlock::class
    ];
}