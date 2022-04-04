<?php

namespace Dive\Nova\Linkable;

use Illuminate\Support\Collection;

class LinkedCollection extends Collection
{
    public static function create($items): static
    {
        return new static($items);
    }

    public function loadLinkedData(array|string $attributes): self
    {
        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        // TODO: Load ALL the attributes for ALL of the records in this collection
        // (and try to minimize the query count)

        return $this;
    }
}