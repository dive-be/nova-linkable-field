<?php

namespace Dive\Nova\Linkable;

use Dive\Nova\Linkable\Models\InteractsWithLinks;
use Dive\Nova\Linkable\Models\Link;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LinkedCollection extends Collection
{
    public static function create(Arrayable|array $items): static
    {
        $uniqueTypes = collect($items)
            ->map(fn ($item) => $item::class)
            ->unique()->count();

        if ($uniqueTypes > 1) {
            throw new \Exception("This collection cannot contain distinct types.");
        }

        if (! is_a($items[0], Model::class)) {
            throw new \Exception("The item(s) in the collection must be an Eloquent model.");
        }

        if (! class_uses($items[0], InteractsWithLinks::class)) {
            throw new \Exception("The model must use the `InteractsWithLinks` trait.");
        }

        return new static($items);
    }

    public function loadLinkedData(string|array $attributes): self
    {
        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        /*
        // Filter out the targets we don't need to query
        // TODO: This can be removed
        $targets = collect($this->first()->targets())
            ->filter(fn ($value, $key) => in_array($key, $attributes))
            ->map(fn ($value, $key) => ['attribute' => $key, 'class' => $value])
            ->values()
            ->groupBy('class')
            ->map(fn ($values, $key) => [$key => $values->pluck('attribute')])
            ->values();
        */

        // Then we'll query all the links (not via the relationship, because that'd require too many queries)
        $links = app(LinkRepository::class)->getLinks($this, $attributes);

        // Query all the target types and keep track of them by ID
        $targets = [];
        $links->groupBy('target_type')->each(function ($items, string $target) use (&$targets) {
            $targets[$target] = $target::query()
                ->whereIn('id', $items->pluck('target_id'))
                ->get()
                ->mapWithKeys(fn ($item) => [$item->getKey() => $item]);
        });

        $class = array_keys($targets)[0];
        // TODO: For each target we now have a list of IDs
        dd($targets[$class]);

        // What needs to happen now is each of those ids needs to be resolved in $this

        return $this;
    }
}