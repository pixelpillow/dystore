<?php

namespace Dystcz\LunarApi\Domain\JsonApi\Eloquent;

use Dystcz\LunarApi\Base\Contracts\Extendable as ExtendableContract;
use Dystcz\LunarApi\Base\Contracts\SchemaExtension as SchemaExtensionContract;
use Dystcz\LunarApi\Base\Contracts\SchemaManifest as SchemaManifestContract;
use Dystcz\LunarApi\Domain\JsonApi\Contracts\Schema as SchemaContract;
use Dystcz\LunarApi\LunarApi;
use Dystcz\LunarApi\Support\Models\Actions\GetModelKey;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Schema\IncludePathIterator;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema as BaseSchema;
use LaravelJsonApi\HashIds\HashId;

abstract class Schema extends BaseSchema implements ExtendableContract, SchemaContract
{
    /**
     * The maximum depth of include paths.
     */
    protected int $maxDepth = 0;

    /**
     * The default paging parameters to use if the client supplies none.
     */
    protected ?array $defaultPagination = ['number' => 1];

    /**
     * Allow viewing of related resources.
     *
     * @property string[] $showRelated
     */
    protected array $showRelated = [];

    /**
     * Allow viewing of relationships.
     *
     * @property string[] $showRelationship
     */
    protected array $showRelationship = [];

    /**
     * Schema extension.
     */
    protected SchemaExtensionContract $extension;

    /**
     * Flag to stop recursion when merging include paths from other schemas.
     */
    protected $mergingIncludePaths = false;

    /**
     * Schema constructor.
     */
    public function __construct(
        Server $server,
    ) {
        $this->extension = App::make(SchemaManifestContract::class)::for(static::class);

        $this->server = $server;
    }

    /**
     * {@inheritDoc}
     */
    public static function resource(): string
    {
        $type = Str::snake(static::type());

        return Config::get(
            "lunar-api.domains.{$type}.resource",
            parent::resource(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function authorizer(): string
    {
        $type = Str::snake(static::type());

        return Config::get(
            "lunar-api.domains.{$type}.authorizer",
            parent::authorizer(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function repository(): Repository
    {
        return new Repository(
            $this,
            $this->driver(),
            $this->parser(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function with(): array
    {
        $paths = array_merge(
            parent::with(),
            Arr::wrap($this->with),
            Arr::wrap($this->extension->with()->resolve($this)),
        );

        return array_values(array_unique($paths));
    }

    /**
     * Merge include paths from other schema types.
     *
     * @return array<string,string> Schema type and relationship name pairs.
     */
    public function mergeIncludePathsFrom(): iterable
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function includePaths(): iterable
    {
        if ($this->maxDepth > 0) {
            return new IncludePathIterator(
                $this->server->schemas(),
                $this,
                $this->maxDepth
            );
        }

        $extendedIncludePaths = $this->extension->includePaths()->resolve($this);
        $mergedIncludePaths = $this->getMergedIncludePaths();

        ray([
            ...$mergedIncludePaths,
            ...$extendedIncludePaths,
        ]);

        return [
            ...$mergedIncludePaths,
            ...$extendedIncludePaths,

            ...parent::includePaths(),
        ];
    }

    /**
     * Get merged include paths from other schemas.
     */
    protected function getMergedIncludePaths(): array
    {
        $mergedIncludePaths = Collection::make($this->newStatic()->mergeIncludePathsFrom())
            ->reduce(
                function (array $carry, string $type, string|int $relationship) {
                    return array_merge(
                        $carry,
                        $this->getIncludePathsFor($type, is_string($relationship) ? $relationship : null),
                    );
                },
                [],
            );

        $this->mergingIncludePaths = false;

        return $mergedIncludePaths;
    }

    /**
     * Get include paths for schema type.
     *
     * @return string[]|iterable
     */
    protected function getIncludePathsFor(string $type, ?string $relationship = null): array
    {
        if ($this->mergingIncludePaths) {
            return [];
        }

        if ($type === static::type()) {
            $this->mergingIncludePaths = true;
        }

        $includePaths = $this->server->schemas()->schemaFor($type)->includePaths();

        $relationship = $relationship ?? $type;

        return array_merge(
            [$relationship],
            array_map(
                fn (string $path) => "{$relationship}.{$path}",
                $includePaths,
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): iterable
    {
        return $this->extension->fields()->resolve($this);

    }

    /**
     * {@inheritDoc}
     */
    public function filters(): iterable
    {
        return [
            WhereIdIn::make($this),

            ...$this->extension->filters()->resolve($this),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function sortables(): iterable
    {
        return $this->extension->sortables()->resolve($this);
    }

    /**
     * {@inheritDoc}
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make()
            ->withDefaultPerPage(
                Config::get('lunar-api.general.pagination.per_page', 24)
            );
    }

    /**
     * Allow specific related resources to be accessed.
     */
    public function showRelated(): array
    {
        $relations = array_merge(
            Arr::wrap($this->showRelated),
            Arr::wrap($this->extension->showRelated()->resolve($this)),
        );

        return array_values(array_unique($relations));
    }

    /**
     * Allow specific relationships to be accessed.
     */
    public function showRelationship(): array
    {
        if (empty($this->showRelationship)) {
            return $this->showRelated();
        }

        $paths = array_merge(
            Arr::wrap($this->showRelationship),
            Arr::wrap($this->extension->showRelationship()->resolve($this)),
        );

        return array_values(array_unique($paths));
    }

    /**
     * Get id or hashid field based on configuration.
     */
    protected function idField(?string $column = null): ID|HashId
    {
        if (LunarApi::usesHashids()) {
            return HashId::make($column)
                ->useConnection((new GetModelKey)(self::model()))
                ->alreadyHashed();
        }

        return ID::make($column);
    }

    /**
     * Get a new instance of the schema.
     */
    protected function newStatic(): static
    {
        return new static($this->server);
    }
}
