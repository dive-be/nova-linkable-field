<?php declare(strict_types=1);

namespace Dive\Nova\Linkable\Models;

// In the future, replace with registry, akin to:
// https://github.com/facade/ignition/blob/main/src/SolutionProviders/SolutionProviderRepository.php
// e.g. "LinkableProviderRegistry"
interface HasLinkableValue
{
    public function getLinkableValue(string $field): mixed;
}
