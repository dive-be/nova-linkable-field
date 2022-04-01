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

    public function getTargetsByAttribute(Collection $links, string $attribute): Collection
    {
        // Extract the IDs from the links and query the related models
        // Return the list
        return collect([]);
    }
}