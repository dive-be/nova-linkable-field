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

        // Then we'll query all the links (not via the relationship, because that'd require too many queries)
        $links = app(LinkRepository::class)->getLinks($this, $attributes);

        // Query all the target types and keep track of them by ID
        $targets = $links->groupBy('target_type')->mapWithKeys(fn ($items, string $target) => [
            $target => $target::query()
                ->whereIn('id', $items->pluck('target_id'))
                ->get()
                ->mapWithKeys(fn ($item) => [$item->getKey() => $item])
        ]);

        $this->each(function ($element) use ($links, $attributes, $targets) {
            // We're not using the relationship here, because that'd introduce extra queries
            $elementLinks = $links->where('linkable_id', $element->getKey())
                ->where('linkable_type', $element::class);

            // 1. Map the targets that are linked for each of the attributes
            $element->linkedTargets = collect($attributes)
                ->mapWithKeys(fn ($attribute) => [
                    $attribute => $elementLinks
                        ->where('attribute', $attribute)
                        ->map(fn ($link) => $targets[$link->target_type][$link->target_id])
                        ->all()
                ]);

            // 2. Pre-populate the attribute values too
            $element->linkedAttributes = $element->linkedTargets->map(
                fn ($items, $attribute) => count($items) > 0
                    ? $items[0]->getLinkableValue($attribute)
                    : null
            );
        });

        return $this;
    }
}