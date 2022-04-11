<?php

namespace Dive\Nova\Linkable;

use Illuminate\Support\Collection;

class ModelTargetProvider
{
    protected Collection $modelTargetMapping;

    public function __construct(array $modelTargetMapping)
    {
        $this->modelTargetMapping = collect($modelTargetMapping);
    }

    public function register($model, array $mapping)
    {
        $this->modelTargetMapping->put($model, $mapping);
    }

    public function allMapping(): array
    {
        return $this->modelTargetMapping->all();
    }

    public function getMapping($model): array
    {
        return $this->modelTargetMapping->get($model) ?? [];
    }
}