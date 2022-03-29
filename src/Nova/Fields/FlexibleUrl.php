<?php declare(strict_types=1);

namespace Dive\FlexibleUrlField\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class FlexibleUrl extends Field
{
    /** @var string */
    public $component = 'flexible-url-field';

    protected array $extraMetable = [];

    protected bool $isTranslatable = false;

    protected string $linkableType;
    protected array $linkableQuery = [];

    public function __construct(string $name, string $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, function ($value, $resource) use ($attribute) {
            $this->isTranslatable = $this->isTranslatable($resource);

            $columnsToQuery = $this->linkableQuery['columns'];
            $displayCallback = $this->linkableQuery['callback'];

            $this->extraMetable = [
                'locales' => config('nova-translatable.locales'),
                'translatable' => $this->isTranslatable,
                'initialType' => empty($resource->linkable_type) ? 'manual' : 'linked',
                'initialId' => $resource->linkable_id,
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
                'linked' => $this->setManualUrl(
                    model: $model,
                    value: $request[$requestAttribute]
                ),
                'manual' => $this->setLinkedId(
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

        $this->linkableQuery = [
            'columns' => $columnsToQuery,
            'callback' => $displayCallback
        ];

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

    private function getDisplayValue($resource, $attribute): string
    {
        $related = \DB::table('url_linkables')
            ->where('source_type', '=', get_class($resource))
            ->where('source_id', '=', $resource->id)
            ->where('target_type', $this->linkableType)
            ->select('url_linkables.target_id')
            ->get()
            ->first();

        if ($related == null) {
            $url = $resource->getAttribute($attribute);

            if (empty($url)) {
                $url = "<empty>";
            }

            return "Manual URL: {$url}";
        }

        $name = $this->extraMetable['linkedName'];
        $displayValue = $this->extraMetable['linkedValues'][$related->target_id];
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

    private function setManualUrl($model, $value)
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
