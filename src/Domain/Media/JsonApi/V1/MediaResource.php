<?php

namespace Dystcz\LunarApi\Domain\Media\JsonApi\V1;

use Dystcz\LunarApi\Domain\JsonApi\Eloquent\Fields\MediaConversion;
use Dystcz\LunarApi\Domain\JsonApi\Resources\JsonApiResource;
use Dystcz\LunarApi\Domain\Media\Contracts\MediaConversion as MediaConversionContract;
use Dystcz\LunarApi\Domain\Media\Data\ConversionOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use LaravelJsonApi\Contracts\Schema\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param  Request|null  $request
     */
    public function attributes($request): iterable
    {
        /** @var Media $model */
        $model = $this->resource;

        return parent::attributes($request);
    }

    /**
     * Get all resource's attributes.
     *
     * @param  Request|null  $request
     */
    protected function allAttributes($request): iterable
    {
        if (! $request->has('media_conversions')) {
            return parent::allAttributes($request);
        }

        /** @var Media $model */
        $model = $this->resource;

        $conversionsInParams = array_filter(
            explode(',', $request->get('media_conversions', '')),
            fn ($conversion) => $model->hasGeneratedConversion($conversion) || in_array($conversion, ['default']),
        );

        /** @var array<int, MediaConversionContract> $registeredConversions */
        $registeredConversions = Config::get('lunar.media.conversions', []);

        /** @var array<int, ConversionOptions> $conversionOptions */
        $conversionOptions = Arr::flatten(array_map(
            fn (string $class) => $class::conversions(),
            $registeredConversions,
        ));

        $conversions = array_values(array_unique(array_map(
            fn (ConversionOptions $options) => $options->key,
            array_filter(
                $conversionOptions,
                fn (ConversionOptions $options) => in_array($options->key, $conversionsInParams),
            ),
        )));

        if (empty($conversions) || empty($registeredConversions)) {
            return parent::allAttributes($request);
        }

        $allAttributes = array_filter(
            parent::allAttributes($request),
            function (Attribute $value, mixed $key) use ($conversionsInParams) {

                if (! in_array('default', $conversionsInParams)) {
                    return ! in_array($key, [
                        'path',
                        'url',
                        'srcset',
                        'file_name',
                        'mime_type',
                    ]);
                }

                return true;

            }, ARRAY_FILTER_USE_BOTH);

        return [
            ...$allAttributes,
            ...$this->conversions($conversions),
        ];
    }

    /**
     * Map media conversions to fields.
     *
     * @param  array<int, MediaConversionContract>  $conversions
     */
    protected function conversions(array $conversions): array
    {
        return array_reduce($conversions, function (array $carry, string $conversion) {
            array_push($carry, MediaConversion::make($conversion));

            return $carry;
        }, []);
    }
}
