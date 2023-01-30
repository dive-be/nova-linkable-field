<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Dive\Nova\Linkable\Models\InteractsWithLinks;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LinkedCollection extends Collection
{
    public static function create(Arrayable|array $items): static
    {
        $uniqueTypes = collect($items)
            ->map(fn ($item) => $item::class)
            ->unique()->count();

        if (count($items) == 0) {
            return new static([]);
        }

        if ($uniqueTypes > 1) {
            throw new \Exception('This collection cannot contain distinct types.');
        }

        if (! is_a($items[0], Model::class)) {
            throw new \Exception('The item(s) in the collection must be an Eloquent model.');
        }

        if (! in_array(InteractsWithLinks::class, class_uses($items[0]))) {
            throw new \Exception('The model must use the `InteractsWithLinks` trait.');
        }

        return new static($items);
    }

    public function getLinkRepository(): LinkRepository
    {
        return app(LinkRepository::class);
    }

    public function loadLinkedData(string|array $attributes): self
    {
        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        // Query links (not via the relationship, because that'd require too many queries)
        $links = $this->getLinkRepository()->getLinks($this, $attributes);

        // Query all the target types and keep track of them by ID
        $targets = $this->getLinkRepository()->getTargetIdsByType($links);

        // Map these for each entry in the collection
        $this->each(function ($element) use ($links, $attributes, $targets) {
            // We're not using the relationship here, because that'd introduce extra queries
            $elementLinks = $links
                ->where('linkable_id', $element->getKey())
                ->where('linkable_type', $element::class);

            // 1. Map the targets that are linked for each of the attributes
            $element->linkedTargets = collect($attributes)
                ->mapWithKeys(fn ($attribute) => [
                    $attribute => $elementLinks
                        ->where('attribute', $attribute)
                        ->map(fn ($link) => $targets->get($link->target_type)?->get($link->target_id))
                        ->filter(fn ($element) => $element != null),
                ]);

            // 2. Pre-populate the attribute values too
            $element->linkedAttributes = $element->linkedTargets->map(
                function (?EloquentCollection $items, string $attribute) use ($element) {
                    if ($items === null) {
                        return null;
                    }

                    return count($items) > 0
                        ? $items->first()->getLinkableValue($attribute)
                        : $element->getAttribute($attribute) ?? null;
                }
            );
        });

        return $this;
    }
}
