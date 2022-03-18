<?php declare(strict_types=1);

namespace Dive\FlexibleUrlField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class FlexibleUrl extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    protected $linkedType = null;

    protected $linked = [
        'initial_type' => null,
        'initial_id' => null,
    ];

    public function __construct(string $name, string $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, function ($value, $resource) {
            $this->linked = [
                'initial_type' => $resource->linkable_type,
                'initial_id' => $resource->linkable_id,
            ];
        });
    }

    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            $data = $request[$requestAttribute];

            dd($data);

            /*
            if ($isRegularUrl) {
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
        bool $translatable,
        array $columnsToQuery,
        callable $displayCallback = null
    ): self {
        $this->linkedType = $class;

        $values = $class::query()
            ->get(array_merge(['id'], $columnsToQuery))
            ->map(function ($record) use ($displayCallback) {
                return [
                    'id' => $record->id,
                    'display' => $displayCallback($record),
                ];
            });

        return $this->withMeta([
            'values' => $values,
            'translatable' => $translatable,
        ] + $this->linked);
    }
}
