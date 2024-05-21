<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use OffbeatWP\Content\Post\WpQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Content\Traits\SetMetaTrait;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use WP_Taxonomy;
use WP_Term;

class TermModel implements TermModelInterface
{
    public const TAXONOMY = '';

    use BaseModelTrait;
    use SetMetaTrait;
    use GetMetaTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    public ?WP_Term $wpTerm = null;
    public ?int $id = null;
    /** @var array<string, mixed> */
    protected array $metaInput = [];
    protected array $metaToUnset = [];
    private ?array $meta = null;
    /** @var array{slug?: string, description?: string, parent?: int} */
    private array $args = [];

    /**
     * @final
     * @param WP_Term|int|null $term
     */
    public function __construct(int|null|WP_Term $term)
    {
        if ($term instanceof WP_Term) {
            $this->wpTerm = $term;
        } elseif (is_numeric($term)) {
            $retrievedTerm = get_term($term, static::TAXONOMY);
            if ($retrievedTerm instanceof WP_Term) {
                $this->wpTerm = $retrievedTerm;
            }
        }

        if (isset($this->wpTerm)) {
            $this->id = $this->wpTerm->term_id;
        }

        $this->init();
    }

    /** This method is called at the end of the TermModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
    }

    /**
     * @param string $method
     * @param mixed[] $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return static::query()->$method(...$parameters);
    }

    /**
     * @param string $method
     * @param mixed[] $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (isset($this->wpPost->$method)) {
            return $this->wpPost->$method;
        }

        $hookValue = offbeat('hooks')->applyFilters('term_attribute', null, $method, $this);
        if ($hookValue !== null) {
            return $hookValue;
        }
        
        if (method_exists(TermQueryBuilder::class, $method)) {
            trigger_error('Called TermQueryBuilder method on a model instance through magic method. Please use the static TermModel::query method instead.', E_USER_DEPRECATED);
            return static::query()->$method(...$parameters);
        }

        return false;
    }

    public function __clone()
    {
        $this->wpTerm = clone $this->wpTerm;
    }

    public function getId(): ?int
    {
        return $this->wpTerm->term_id ?? null;
    }

    public function getName(): string
    {
        return $this->wpTerm->name;
    }

    final public function setSlug(string $slug): void
    {
        $this->args['slug'] = $slug;
    }

    public function getSlug(): string
    {
        if (array_key_exists('slug', $this->args)) {
            return $this->args['slug'];
        }

        return $this->wpTerm->slug ?? '';
    }

    final public function setDescription(string $description): void
    {
        $this->args['description'] = $description;
    }

    public function getDescription(): string
    {
        if (array_key_exists('description', $this->args)) {
            return $this->args['description'];
        }

        return $this->wpTerm->description;
    }

    /** @return string|\WP_Error */
    public function getLink()
    {
        return get_term_link($this->wpTerm);
    }

    public function getTaxonomy(): string
    {
        return $this->wpTerm->taxonomy;
    }

    final public function setParentId(int $parentId): void
    {
        $this->args['parent'] = $parentId;
    }

    final public function getParentId(): ?int
    {
        if (array_key_exists('parent', $this->args)) {
            return $this->args['parent'];
        }

        return $this->wpTerm->parent ?: null;
    }

    final public function getParent(): ?static
    {
        return static::find($this->getParentId());
    }

    /** @return Collection<int> */
    public function getAncestorIds(): Collection
    {
        return collect(get_ancestors($this->getId(), $this->getTaxonomy(), 'taxonomy'));
    }

    public function getEditLink(): string
    {
        return get_edit_term_link($this->wpTerm ?: $this->getId()) ?: '';
    }

    public function getAncestors(): Collection
    {
        return $this->getAncestorIds()->map(function ($ancestorId) {
            return static::query()->findById($ancestorId);
        });
    }

    final public function getMetas(): array
    {
        if ($this->meta === null) {
            $this->meta = get_term_meta($this->getId()) ?: [];
        }

        return $this->meta;
    }

    /**
     * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys. Default empty.
     * @param bool $single Optional. Whether to return a single value. This parameter has no effect if `$key` is not specified. Default false.
     * @return mixed
     */
    final public function getMeta(string $key, bool $single = true): mixed
    {
        return $this->getMetas()[$key] ?? null;
    }

    /**
     * @param string|string[]|null $postTypes
     * @return WpQueryBuilder<\OffbeatWP\Content\Post\PostModel>
     */
    public function getPosts($postTypes = null): WpQueryBuilder
    {
        global $wp_taxonomies;

        // If no posttypes defined, get posttypes where the taxonomy is assigned to
        if (!$postTypes) {
            $postTypes = isset($wp_taxonomies[static::TAXONOMY]) ? $wp_taxonomies[static::TAXONOMY]->object_type : ['any'];
        }

        return (new WpQueryBuilder())->wherePostType($postTypes)->whereTerm(static::TAXONOMY, $this->getId(), 'term_id');
    }

    /**
     * Removes a term from the database.<br>
     * If the term is a parent of other terms, then the children will be updated to that term's parent.<br>
     * Metadata associated with the term will be deleted.
     * @return bool Whetever the term was deleted.
     */
    public function delete(): bool
    {
        return wp_delete_term($this->getId(), $this->getTaxonomy()) === true;
    }

    /** Retrieves the current term from the wordpress loop, provided the TermModel is or extends the TermModel class that it is called on. */
    final public static function current(): ?static
    {
        $taxonomy = offbeat('taxonomy')->get();
        return ($taxonomy instanceof static) ? $taxonomy : null;
    }

    public function getTaxonomyInstance(): ?WP_Taxonomy
    {
        return get_taxonomy($this->wpTerm->taxonomy) ?: null;
    }

    public function count(): int
    {
        return $this->wpTerm->count;
    }

    final public function save(): int
    {
        $currentId = $this->wpTerm->term_id;
        if ($currentId) {
            // Update
            $result = wp_update_term($currentId, static::TAXONOMY, $this->args);
        } else {
            // Insert
            $result = wp_insert_term($this->wpTerm->slug, static::TAXONOMY, $this->args);
        }

        $newId = is_array($result) ? $result['term_id'] : 0;
        if ($newId) {
            $this->wpTerm->term_id = $newId;

            // Update the term meta
            foreach ($this->metaInput as $key => $value) {
                update_term_meta($newId, $key, $value);
            }
        }

        return $newId;
    }

    final public function saveOrFail(): int
    {
        $result = $this->save();

        if ($result <= 0) {
            throw new OffbeatInvalidModelException('Failed to save ' . static::class);
        }

        return $result;
    }

    final public static function from(WP_Term $wpTerm): static
    {
        if ($wpTerm->term_id <= 0) {
            throw new InvalidArgumentException('Cannot create ' . static::class . ' from WP_Term object: Invalid ID');
        }

        if (static::TAXONOMY && !in_array($wpTerm->taxonomy, (array)static::TAXONOMY, true)) {
            throw new InvalidArgumentException('Cannot create ' . static::class . ' from WP_Term object: Invalid Taxonomy');
        }

        return new static($wpTerm);
    }

    /** @return TermsCollection<static> Empty terms <b>will</b> be included. */
    public static function all(): TermsCollection
    {
        return static::query()->excludeEmpty(false)->get();
    }

    /** Checks if a model with the given ID exists. */
    public static function exists(?int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return static::query()->excludeEmpty(false)->include([$id])->exists();
    }

    /** @return TermQueryBuilder<static> */
    public static function query(): TermQueryBuilder
    {
        return new TermQueryBuilder(static::class);
    }
}
