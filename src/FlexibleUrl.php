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
                'initial_manual_value' => $this->isTranslatable
                    ? $resource->getTranslations($attribute)
                    : $resource->getAttribute($attribute)
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
            $value = $request[$requestAttribute];
            $type = $request[$requestAttribute . "-type"];

            if ($this->isTranslatable) {
                $value = json_decode($value);
            }

            if ($type === 'linked') {
                $model->linkable_type = $this->linkableType;
                $model->linkable_id = $value;
                return;
            }

            if ($this->isTranslatable) {
                $model->setTranslations($requestAttribute, $value);
            } else {
                $model->{$requestAttribute} = $value;
            }

            dd($model);
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
            ->map(function ($record) use ($displayCallback) {
                return [
                    'id' => $record->id,
                    'display' => $displayCallback($record),
                ];
            });

        $this->linked = [
            'linked_name' => $readableName,
            'linked_values' => $values
        ];

        return $this;
    }

    private function isTranslatable(Model|string $model): bool
    {
        return array_key_exists(
            "Spatie\Translatable\HasTranslations",
            class_uses($model)
        );
    }
}
