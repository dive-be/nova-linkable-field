<?php declare(strict_types=1);

namespace Tests\Testbench\Models;

use Dive\Nova\Linkable\Models\HasLinks;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use HasLinks;

    protected $table = 'nav_items';

    protected function targets(): array
    {
        return [
            'url' => \Domain\Editorial\Models\Page::class,
            'internal_url' => \Domain\Editorial\Models\Page::class,
        ];
    }
}
