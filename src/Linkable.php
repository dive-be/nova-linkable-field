<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Linkable extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    /** @var array Additional metable information exposed to the JS. */
    protected array $extraMetable = [];

    protected bool $isTranslatable = false;

    /** @var string The resolved class of the type we wish to link. */
    protected string $linkableType;

    /** @var int|null The resolved ID of the type that was linked to the resource. Null if not linked. */
    protected ?int $linkableId = null;

    /** @var Builder The query builder that is used to determine the linked ID. */
    protected Builder $linkableQueryBuilder;

    public function __construct(string $name, string $attribute = null)
    {
        parent::__construct($name, $attribute, function ($value, $resource) use ($attribute) {
            $this->isTranslatable = $this->isTranslatable($resource);

            $this->linkableQueryBuilder = $this->buildLinkableQuery($resource);

            $this->linkableId = $this->linkableQueryBuilder->first()?->target_id;

            $this->extraMetable = [
                'locales' => config('nova-translatable.locales'),
                'translatable' => $this->isTranslatable,
                'initialType' => $this->linkableId === null ? 'manual' : 'linked',
                'initialId' => $this->linkableId,
                'initialManualValue' => $this->getValue($resource, $attribute),
                'displayValue' => $this->getDisplayValue($resource, $attribute),
            ] + $this->extraMetable;

            $this->withMeta($this->extraMetable);
        });
    }

    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            match ($request["$requestAttribute-type"]) {
                'linked' => $this->setLinkedId(
                    model: $model,
                    requestAttribute: $requestAttribute,
                    value: (int) $request[$requestAttribute]
                ),
                'manual' => $this->setManualUrl(
                    model: $model,
                    requestAttribute: $requestAttribute,
                    value: $request[$requestAttribute]
                )
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

        $this->extraMetable = [
            'linkedName' => $readableName,
            'linkedValues' => $this->linkableType::query()
                ->get(array_merge(['id'], $columnsToQuery))
                ->mapWithKeys(function ($record) use ($displayCallback) {
                    return [$record->id => $displayCallback($record)];
                }),
        ];

        return $this;
    }

    private function buildLinkableQuery(Model $model): Builder
    {
        $linkModel = config('nova-linkable-field.model');

        $linkModelTable = (new $linkModel())->getTable();

        return $linkModel::query()
            ->where('linkable_type', '=', get_class($model))
            ->where('linkable_id', '=', $model->getKey())
            ->where('target_type', $this->linkableType)
            ->select("$linkModelTable.target_id");
    }

    private function getDisplayValue(Model $resource, $attribute): string
    {
        if ($this->linkableId == null) {
            $url = $resource->getAttribute($attribute);

            if (empty($url)) {
                $url = '<empty>';
            }

            return "Manual URL: {$url}";
        }

        $name = $this->extraMetable['linkedName'];
        $displayValue = $this->extraMetable['linkedValues'][$this->linkableId];

        return "Linked {$name}: {$displayValue}";
    }

    private function getValue($resource, $attribute): array|string
    {
        if (! $this->isTranslatable) {
            return $resource->getAttribute($attribute);
        }

        $translations = $resource->getTranslations($attribute);

        collect(config('nova-translatable.locales'))
            ->keys()
            ->each(function ($key) use (&$translations) {
                if (! array_key_exists($key, $translations)) {
                    $translations[$key] = '';
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

    private function setLinkedId($model, $requestAttribute, int $value)
    {
        $linkModel = config('nova-linkable-field.model');

        $linkModel::query()->updateOrInsert([
            'linkable_type' => get_class($model),
            'linkable_id' => $model->getKey(),
        ], [
            'linkable_type' => get_class($model),
            'linkable_id' => $model->getKey(),
            'target_type' => $this->linkableType,
            'target_id' => $value,
            'attribute' => $requestAttribute,
        ]);
    }

    private function setManualUrl($model, $requestAttribute, $value)
    {
        $this->linkableQueryBuilder->delete();

        if ($this->isTranslatable) {
            $model->setTranslations($requestAttribute, json_decode($value, true));
        } else {
            $model->{$requestAttribute} = $value;
        }
    }
}
