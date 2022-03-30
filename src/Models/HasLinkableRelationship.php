<?php declare(strict_types=1);

namespace Dive\LinkableField;

use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

trait HasLinkableRelationship
{
    protected array $linkables = [];

    public function getLinkable(string $attribute)
    {

    }

    public function linkables(): MorphOneOrMany
    {

    }
}