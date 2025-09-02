<?php

namespace OffbeatWP\Content\Taxonomy;

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
    use BaseModelTrait;
    use SetMetaTrait;
    use GetMetaTrait;

    public const TAXONOMY = '';

    public ?WP_Term $wpTerm = null;
    public ?int $id = null;
    /** @var array<string, mixed> */
    protected array $metaInput = [];
    /** @var ("")[] */
    protected array $metaToUnset = [];
    /** @var array<string|int|float|bool|\stdClass|\Serializable>|null */
    private ?array $meta = null;
    /** @var array{slug?: string, description?: string, parent?: int} */
    private array $args = [];

    final public function __construct(?WP_Term $term)
    {
        if ($term) {
            $this->wpTerm = $term;
        } else {
            $this->wpTerm = new WP_Term((object)[]);
        }

        $this->id = $this->wpTerm->term_id;

        $this->init();
    }

    /** This method is called at the end of the TermModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
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

    /** @return array<int, int> */
    public function getAncestorIds(): array
    {
        return get_ancestors($this->getId(), $this->getTaxonomy(), 'taxonomy');
    }

    public function getEditLink(): string
    {
        return get_edit_term_link($this->wpTerm ?: $this->getId()) ?: '';
    }

    /** @return array<int, TermModel> */
    public function getAncestors(): array
    {
        return array_map(fn ($id) => static::query()->findById($id), $this->getAncestorIds());
    }

    /** @return mixed[] */
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
     * @return ($single is true ? mixed : mixed[])
     */
    final public function getMeta(string $key, bool $single = true): mixed
    {
        $value = $this->getMetas()[$key] ?? null;
        return $single && is_array($value) ? reset($value) : $value;
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
        $taxonomy = container('taxonomy')->get();
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

    /** @return positive-int */
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

    /** @return TermsCollection<int, static> Empty terms <b>will</b> be included. */
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
