<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Linkable extends MorphPivot
{
    protected $table = 'linkables';
}
