<?php declare(strict_types=1);

namespace Dive\LinkableField\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class LinkableField extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    /** @var array Additional metable information exposed to the JS. */
    protected array $extraMetable = [];

    /** @var bool */
    protected bool $isTranslatable = false;

    /** @var string The resolved class of the type we wish to link. */
    protected string $linkableType;

    /** @var int|null The resolved ID of the type that was linked to the resource. Null if not linked. */
    protected ?int $linkableId = null;

    /** @var Builder The query builder that is used to determine the linked ID. */
    protected Builder $linkableQueryBuilder;

    public function __construct(string $name, string $attribute = null, callable $resolveCallback = null)
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
                ->flatMap(function ($record) use ($displayCallback) {
                    return [$record->id => $displayCallback($record)];
                })
        ];

        return $this;
    }

    private function buildLinkableQuery(Model $model): Builder
    {
        return \DB::table('model_linkables')
            ->where('source_type', '=', get_class($model))
            ->where('source_id', '=', $model->getKey())
            ->where('target_type', $this->linkableType)
            ->select('dive_model_linkables.target_id');
    }

    private function getDisplayValue(Model $resource, $attribute): string
    {
        if ($this->linkableId == null) {
            $url = $resource->getAttribute($attribute);

            if (empty($url)) {
                $url = "<empty>";
            }

            return "Manual URL: {$url}";
        }

        $name = $this->extraMetable['linkedName'];
        $displayValue = $this->extraMetable['linkedValues'][$this->linkableId];
        return "Linked {$name}: {$displayValue}";
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

    private function setLinkedId($model, int $value)
    {
        // TODO: Check if we can use `LinkablePivot` here
        \DB::table('url_linkables')->updateOrInsert([
            'source_type' => get_class($model),
            'source_id' => $model->getKey(),
        ], [
            'source_type' => get_class($model),
            'source_id' => $model->getKey(),
            'target_type' => $this->linkableType,
            'target_id' => $value
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
