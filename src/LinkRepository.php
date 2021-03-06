<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Dive\Nova\Linkable\Models\Link;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LinkRepository
{
    public function __construct(
        protected Link $model
    ) {}

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function applyTargetAttributes(Collection $collection, string|array $attributes): LinkedCollection
    {
        return LinkedCollection::make($collection)
            ->loadLinkedData($attributes);
    }

    public function getLinks(LinkedCollection $linkables, $attributes): Collection
    {
        if ($linkables->count() == 0) {
            return collect();
        }

        $ids = $linkables->pluck('id');
        $type = $linkables->first()::class;

        return $this->query()
            ->whereIn('linkable_id', $ids)
            ->where('linkable_type', $type)
            ->whereIn('attribute', $attributes)
            ->get();
    }

    public function getTargetIdsByType(Collection $links): Collection
    {
        return $links
            ->groupBy('target_type')
            ->mapWithKeys(fn ($items, string $target) => [
                $target => $target::query()
                    ->whereIn('id', $items->pluck('target_id'))
                    ->get()
                    ->mapWithKeys(fn ($item) => [$item->getKey() => $item])
            ]);
    }
}