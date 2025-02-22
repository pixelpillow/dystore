<?php

namespace Dystore\Api\Domain\Media\JsonApi\V1;

use Dystore\Api\Domain\JsonApi\Eloquent\Schema;
use Dystore\Api\Domain\JsonApi\Eloquent\Sorts\InDefaultOrder;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaSchema extends Schema
{
    /**
     * {@inheritDoc}
     */
    public static string $model = Media::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultSort = 'ordered';

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            ID::make(),

            Str::make('path')->extractUsing(
                static fn (Media $model) => $model->getPath()
            ),

            Str::make('url')->extractUsing(
                static fn (Media $model) => $model->getFullUrl()
            ),

            Str::make('file_name'),
            Str::make('mime_type'),
            Str::make('size'),
            Str::make('order_column'),

            Str::make('position')->extractUsing(
                static fn (Media $model) => $model->getCustomProperty('position', 0)
            ),

            Str::make('srcset')->extractUsing(
                static fn (Media $model) => ($model->getSrcSet('webp') ? ($model->getSrcSet('webp').', ') : '').$model->getSrcSet()
            ),

            ArrayHash::make('custom_properties'),

            ...parent::fields(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function sortables(): iterable
    {
        return [
            ...parent::sortables(),

            InDefaultOrder::make('ordered'),
        ];
    }
}
