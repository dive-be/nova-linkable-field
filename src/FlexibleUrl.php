<?php declare(strict_types=1);

namespace Dive\FlexibleUrlField;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class FlexibleUrl extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

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
            ];
        });
    }

    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        // Check if the model is translatable
        if ($request->exists($requestAttribute)) {
            $data = $request[$requestAttribute];

            dd($data);

            /*
            if ($isRegularUrl) {
                // 1. Check if translatable: $this->isTranslatable($model)
                // 1. The user has entered fixed URLs
                $model->{$requestAttribute} = $data['value'];
            } else {
                // 1. The user has chosen a related model
                $model->{$requestAttribute} = null;
                // Use the newly migrated field to store the relationship
                $model->linkable_type = $data['value']['type']; // TODO: get type from FlexibleUrl field instead?
                $model->linkable_id = $data['value']['id'];
            }
            */
        }
    }

    public function addLinkable(
        string $class,
        array $columnsToQuery,
        callable $displayCallback = null
    ): self {
        $values = $class::query()
            ->get(array_merge(['id'], $columnsToQuery))
            ->map(function ($record) use ($displayCallback) {
                return [
                    'id' => $record->id,
                    'display' => $displayCallback($record),
                ];
            });

        return $this->withMeta([
            'linked_values' => $values
        ] + $this->linked);
    }

    private function isTranslatable(Model|string $model): bool
    {
        return array_key_exists(
            "Spatie\Translatable\HasTranslations",
            class_uses($model)
        );
    }
}
