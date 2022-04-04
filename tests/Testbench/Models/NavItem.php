<?php declare(strict_types=1);

namespace Tests\Testbench\Models;

use Dive\Nova\Linkable\Models\InteractsWithLinks;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use InteractsWithLinks;

    protected $table = 'nav_items';

    protected function targets(): array
    {
        return [
            'url' => \Domain\Editorial\Models\Page::class,
            'internal_url' => \Domain\Editorial\Models\Page::class,
        ];
    }
}
