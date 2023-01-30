<?php declare(strict_types=1);

namespace Dive\Nova\Linkable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Linkable extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    /** @var array Additional metable information exposed to the JS. */
    protected array $extraMetable = [];

    protected bool $isTranslatable = false;

    /** @var array<string> The resolved classes of the types we wish to link. */
    protected array $linkableTypes = [];

    /** @var int|null The resolved ID of the type that was linked to the resource. Null if not linked. */
    protected ?int $linkedId = null;

    /** @var string|null The resolved type that was linked to the resource. Null if not linked. */
    protected ?string $linkedType = null;

    /** @var Builder The query builder that is used to determine the linked ID. */
    protected Builder $linkableQueryBuilder;

    public function __construct(string $name, string $attribute = null)
    {
        parent::__construct($name, $attribute, function ($value, $resource) use ($attribute) {
            $this->isTranslatable = $this->isTranslatable($resource);

            $this->linkableQueryBuilder = $this->buildLinkableQuery($resource);

            $this->linkedId = $this->linkableQueryBuilder->first()?->target_id;
            $this->linkedType = $this->linkableQueryBuilder->first()?->target_type;

            $this->extraMetable = [
                'locales' => config('nova-translatable.locales'),
                'translatable' => $this->isTranslatable,
                'initialType' => $this->linkedType,
                'initialId' => $this->linkedId,
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
        if (! $request->exists("linked_{$requestAttribute}_type")) {
            $this->setManualUrl(
                model: $model,
                requestAttribute: $requestAttribute,
                value: $request[$requestAttribute]
            );

            return;
        }

        $this->setLinkedId(
            model: $model,
            requestAttribute: $requestAttribute,
            type: (string) $request["linked_{$requestAttribute}_type"],
            value: (int) $request["linked_{$requestAttribute}_id"]
        );
    }

    public function withLinkable(
        string $class,
        string $readableName,
        array $columnsToQuery,
        callable $displayCallback = null
    ): self {
        if (! is_subclass_of($class, Model::class)) {
            throw new \Exception("Invalid model configuration: $class must be an Eloquent model.");
        }

        $this->linkableTypes[] = $class;
        $this->extraMetable['linked'][$class] = [
            'linkedName' => $readableName,
            'linkedValues' => $class::query()
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
            ->select("$linkModelTable.target_id", "$linkModelTable.target_type");
    }

    private function getDisplayValue(Model $resource, $attribute): string
    {
        if ($this->linkedId == null) {
            $url = $resource->getAttribute($attribute);

            if (empty($url)) {
                $url = '<empty>';
            }

            return "Manual: {$url}";
        }

        // Find the correct metable information
        $linked = $this->extraMetable['linked'][$this->linkedType];

        $name = $linked['linkedName'];
        $displayValue = $linked['linkedValues'][$this->linkedId];

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

    /**
     * @param class-string $type
     */
    private function setLinkedId($model, $requestAttribute, $type, int $value)
    {
        $linkModel = config('nova-linkable-field.model');

        if (! class_exists($type)) {
            throw ValidationException::withMessages([
                'details' => 'This is not a valid linked type.',
            ]);
        }

        if ($type::find($value) == null) {
            throw ValidationException::withMessages([
                'details' => 'You must select a valid value.',
            ]);
        }

        $linkModel::query()->updateOrInsert([
            'linkable_type' => get_class($model),
            'linkable_id' => $model->getKey(),
        ], [
            'linkable_type' => get_class($model),
            'linkable_id' => $model->getKey(),
            'target_type' => $type,
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
