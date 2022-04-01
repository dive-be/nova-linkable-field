<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

use Dive\Nova\Linkable\LinkRepository;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasLinks
{
    // TODO: Set up registry?
    abstract protected function targets(): array;

    public function links(): MorphMany
    {
        return $this->morphMany(config('nova-linkable-field.model'), 'linkable');
    }

    // TODO: Add LinkCollection
    public function getTargetsByAttribute($attribute): Collection
    {
        // TODO: Resolve LinkRepository via facade or container
        $name = config('nova-linkable-field.model');
        $linkRepository = new LinkRepository(new $name);

        return $linkRepository->getTargetsByAttribute($this->links, $attribute);
    }
}