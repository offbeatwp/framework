<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\WpQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use WP_Error;
use WP_Taxonomy;
use WP_Term;

class TermModel implements TermModelInterface
{
    use BaseModelTrait;
    use GetMetaTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    public $wpTerm;
    public $id;
    protected array $metaInput = [];
    protected array $metaToUnset = [];

    /**
     * @final
     * @param WP_Term|int|null $term
     */
    public function __construct($term)
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

    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return static::query()->$method(...$parameters);
    }

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
            trigger_error('Called a QueryBuilder method on a model instance through magic.');
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

    public function getSlug(): string
    {
        return $this->wpTerm->slug ?? '';
    }

    public function getDescription(): string
    {
        return $this->wpTerm->description;
    }

    /** @return string|WP_Error */
    public function getLink()
    {
        return get_term_link($this->wpTerm);
    }

    public function getTaxonomy(): string
    {
        return $this->wpTerm->taxonomy;
    }

    public function getParentId(): ?int
    {
        return ($this->wpTerm->parent) ?: null;
    }

    /** @return static|null */
    public function getParent()
    {
        if ($this->getParentId()) {
            return static::query()->findById($this->getParentId()) ?: null;
        }

        return null;
    }

    public function getAncestorIds(): Collection
    {
        return collect(get_ancestors( $this->getId(), $this->getTaxonomy(), 'taxonomy'));
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

    /** @return array|false|string */
    public function getMetas()
    {
        return get_term_meta($this->getId());
    }

    /**
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    public function getMeta(string $key, bool $single = true)
    {
        return get_term_meta($this->getId(), $key, $single);
    }

    /**
     * <b>This will immideatly update the term meta, even is save() is not called!</b>
     * @param string $key
     * @param mixed $value
     * @return bool|int|WP_Error
     */
    public function setMeta(string $key, $value)
    {
        return update_term_meta($this->getId(), $key, $value);
    }

    /** @param string|string[]|null $postTypes */
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

    /**
     * Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on.
     * @return static|null
     */
    public static function current()
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

    /** @return TermsCollection<static> Empty terms <b>will</b> be included. */
    public static function all(): TermsCollection
    {
        return static::query()->excludeEmpty(false)->get();
    }

    /**
     * Checks if a model with the given ID exists.
     * @param int|null $id
     * @return bool
     */
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
