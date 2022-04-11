<?php declare(strict_types=1);

namespace Tests\Testbench\Models;

use Dive\Nova\Linkable\Models\InteractsWithLinks;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use InteractsWithLinks;

    protected $table = 'nav_items';
}
