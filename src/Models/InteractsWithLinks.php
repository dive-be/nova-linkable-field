<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

use Dive\Nova\Linkable\Exceptions\UnmappedTargetException;
use Dive\Nova\Linkable\LinkedCollection;
use Dive\Nova\Linkable\LinkRepository;
use Dive\Nova\Linkable\ModelTargetProvider;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait InteractsWithLinks
{
    public function links(): MorphMany
    {
        return $this->morphMany(config('nova-linkable-field.model'), 'linkable');
    }

    public function getLinkRepository(): LinkRepository
    {
        return app(LinkRepository::class);
    }

    /**
     * Retrieve the actual targets belonging to a particular attribute.
     *
     * @throws UnmappedTargetException
     */
    public function getTargetsByAttribute(string $attribute): Collection
    {
        if (! array_key_exists($attribute, app(ModelTargetProvider::class)->getMapping(self::class))) {
            throw new UnmappedTargetException("This attribute (`$attribute`) must be mapped on the model.");
        }

        if (isset($this->linkedTargets)) {
            return $this->linkedTargets->get($attribute);
        }

        return LinkedCollection::create([$this])
            ->loadLinkedData([$attribute])
            ->first()->linkedTargets->get($attribute);
    }

    /**
     * Returns the attribute value, determined by checking the first target's properties.
     * If no link (and as such no target) exists, the local attribute is used as a fallback.
     * If the local attribute does not exist, `null` is returned.
     *
     * The attribute *must* be mapped or an exception will be thrown.
     *
     * @param string $attribute
     * @return mixed
     *
     * @throws UnmappedTargetException
     */
    public function getLinkedAttributeValue(string $attribute): mixed
    {
        if (isset($this->linkedAttributes)) {
            return $this->linkedAttributes->get($attribute);
        }

        $target = $this->getTargetsByAttribute($attribute)->first();

        return $target?->getLinkableValue($attribute)
            ?? $this->getAttribute($attribute);
    }
}