<?php declare(strict_types=1);

namespace Dive\LinkableField;

interface HasLinkableValue
{
    public function getLinkableValue(string $field): mixed;
}