<?php

namespace Dive\Nova\Linkable;

use Dive\Nova\Linkable\Models\InteractsWithLinks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LinkedCollection extends Collection
{
    public static function create($items): static
    {
        if (! is_a($items[0], Model::class)) {
            throw new \Exception("The item(s) in the collection must be an Eloquent model.");
        }

        if (! class_uses($items[0], InteractsWithLinks::class)) {
            throw new \Exception("The model must use the `InteractsWithLinks` trait.");
        }

        return new static($items);
    }

    public function loadLinkedData(array|string $attributes): self
    {
        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        foreach ($attributes as $attribute) {
            $ids = $this
                ->flatMap(fn ($entry) => $entry
                ->links()
                ->where('links.attribute', $attribute)
                ->pluck('linkable_id'));

            dd([$attribute => $ids->all()]);
        }

        // TODO: Load ALL the attributes for ALL of the records in this collection
        // (and try to minimize the query count)

        return $this;
    }
}