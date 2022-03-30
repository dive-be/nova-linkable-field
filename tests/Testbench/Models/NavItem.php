<?php

namespace Tests\Testbench\Models;

use Dive\LinkableField\HasLinkableRelationship;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use HasLinkableRelationship;

    protected $table = 'nav_items';

    protected array $linkables = ['url'];
}