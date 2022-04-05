<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

use Dive\Nova\Linkable\Exceptions\UnmappedTargetException;
use Dive\Nova\Linkable\LinkRepository;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait InteractsWithLinks
{
    // TODO: Set up registry
    // Like: https://github.com/facade/ignition/blob/main/src/SolutionProviders/SolutionProviderRepository.php
    abstract protected function targets(): array;

    public function links(): MorphMany
    {
        return $this->morphMany(config('nova-linkable-field.model'), 'linkable');
    }

    public function getLinksRepository(): LinkRepository
    {
        return app(LinkRepository::class);
    }

    public function getTargetsByAttribute(string $attribute): Collection
    {
        if (! array_key_exists($attribute, $this->targets())) {
            throw new UnmappedTargetException("This attribute (`$attribute`) must be mapped on the model before the associated model(s) can be retrieved.");
        }

        return $this->getLinksRepository()
            ->getTargetsByAttribute($this->links, $attribute);
    }
}