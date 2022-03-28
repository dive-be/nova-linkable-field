<?php declare(strict_types=1);

namespace Dive\FlexibleUrlField;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class FlexibleUrl extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    protected string $linkableType;
    protected bool $isTranslatable = false;
    protected array $linked = [];

    public function __construct(string $name, string $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, function ($value, $resource) use ($attribute) {
            $this->isTranslatable = $this->isTranslatable($resource);

            $this->linked = [
                'locales' => config('nova-translatable.locales'),
                'translatable' => $this->isTranslatable,
                'initial_type' => $resource->linkable_type,
                'initial_id' => $resource->linkable_id,
                'initial_manual_value' => $this->getValue($resource, $attribute),
                'display_value' => $this->getDisplayValue($resource, $attribute)
            ] + $this->linked;

            $this->withMeta($this->linked);
        });
    }

    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            $args = [$model, $requestAttribute, $request[$requestAttribute]];

            match ($request["$requestAttribute-type"]) {
                'linked' => $this->setManualUrl(...$args),
                'manual' => $this->setLinkedId(...$args)
            };
        }
    }

    public function withLinkable(
        string $class,
        string $readableName,
        array $columnsToQuery,
        callable $displayCallback = null
    ): self {
        $this->linkableType = $class;

        $values = $class::query()
            ->get(array_merge(['id'], $columnsToQuery))
            ->flatMap(function ($record) use ($displayCallback) {
                return [$record->id => $displayCallback($record)];
            });

        $this->linked = [
            'linked_name' => $readableName,
            'linked_values' => $values,
        ];

        return $this;
    }

    private function getDisplayValue($resource, $attribute): string
    {
        $linkableId = $resource->getAttribute('linkable_id');

        if ($linkableId != 0) {
            $name = $this->linked['linked_name'];
            $displayValue = $this->linked['linked_values'][$linkableId];
            return "Linked {$name}: {$displayValue}";
        }

        $url = $resource->getAttribute($attribute);

        if (empty($url)) {
            $url = "<empty>";
        }

        return "Manual URL: {$url}";
    }

    private function getValue($resource, $attribute): array|string
    {
        if (!$this->isTranslatable) {
            return $resource->getAttribute($attribute);
        }

        $translations = $resource->getTranslations($attribute);

        collect(config('nova-translatable.locales'))
            ->keys()
            ->each(function ($key) use (&$translations) {
                if (!array_key_exists($key, $translations)) {
                    $translations[$key] = "";
                }
            });

        return $translations;
    }

    private function isTranslatable(Model|string $model): bool
    {
        return array_key_exists(
            "Spatie\Translatable\HasTranslations",
            class_uses($model)
        );
    }

    private function setManualUrl($model, $requestAttribute, $value)
    {
        $model->linkable_type = $this->linkableType;
        $model->linkable_id = $value;
    }

    private function setLinkedId($model, $requestAttribute, $value)
    {
        $model->linkable_id = 0;
        $model->linkable_type = '';

        if ($this->isTranslatable) {
            $model->setTranslations($requestAttribute, json_decode($value, true));
        } else {
            $model->{$requestAttribute} = $value;
        }
    }
}
