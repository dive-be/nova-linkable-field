<?php

namespace Tests\Testbench\Models;

use Dive\LinkableField\HasLinkableValue;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements HasLinkableValue
{
    protected $table = 'pages';

    public function getLinkableValue(string $field): ?string
    {
        return match ($field) {
            'url' => "/path/to/" . $this->slug,
            default => null
        };
    }
}