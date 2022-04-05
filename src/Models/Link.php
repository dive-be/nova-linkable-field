<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Link extends Model
{
    public function targets(): MorphMany
    {
        return $this->morphMany(
            $this->linkable_type,
            'target'
        );
    }
}
