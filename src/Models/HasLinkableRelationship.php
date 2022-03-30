<?php declare(strict_types=1);

namespace Dive\LinkableField\Models;

trait HasLinkableRelationship
{
    public function getLinkable(string $attribute)
    {
        // TODO: If we do a query here, we'll need to perform a query every time we request this field.
    }
}