<?php declare(strict_types=1);

namespace Tests\Testbench\Models;

use Dive\Nova\Linkable\Models\HasLinkableValue;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements HasLinkableValue
{
    protected $table = 'pages';

    public function getLinkableValues(): array
    {
        return array_map(
            fn ($key, $value) => $this->getLinkableValue($key),
            $this->targets()
        );
    }

    public function getLinkableValue(string $field): ?string
    {
        return match ($field) {
            'url' => '/path/to/' . $this->slug,
            default => null
        };
    }
}
