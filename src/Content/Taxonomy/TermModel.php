<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\WpQueryBuilder;
use OffbeatWP\Content\Traits\FindModelTrait;
use WP_Term;

/**
 * @method mixed getField() getField(string $selector, bool $format_value = true)
 */
class TermModel implements TermModelInterface
{
    public const TAXONOMY = null;

    public $wpTerm;
    public $id;

    use FindModelTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    /** @param WP_Term|int|null */
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

        if (!is_null($hookValue = offbeat('hooks')->applyFilters('term_attribute', null, $method, $this))) {
            return $hookValue;
        }
        
        if (method_exists(TermQueryBuilder::class, $method)) {
            return static::query()->$method(...$parameters);
        }

        return false;
    }

    public function getId(): int
    {
        return $this->wpTerm->term_id;
    }

    public function getName(): string
    {
        return $this->wpTerm->name;
    }

    public function getSlug(): string
    {
        return $this->wpTerm->slug;
    }

    public function getDescription(): string
    {
        return $this->wpTerm->description;
    }

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

    public function getParent(): ?TermModel
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

    public function getAncestors(): Collection
    {
        return $this->getAncestorIds()->map(function ($ancestorId) {
            return static::query()->findById($ancestorId);
        });
    }

    public function getMeta(string $key, bool $single = true)
    {
        return get_term_meta($this->getId(), $key, $single);
    }

    public function setMeta(string $key, $value)
    {
        return update_term_meta($this->getId(), $key, $value);
    }

    /** @param string|string[]|null $postTypes */
    public function getPosts($postTypes = null): WpQueryBuilder
    {
        global $wp_taxonomies;

        $type = self::getModelType();

        // If no post types defined, get post types where the taxonomy is assigned to
        if (empty($postTypes)) {
            $postTypes = isset($wp_taxonomies[$type]) ? $wp_taxonomies[$type]->object_type : ['any'];
        }

        return (new WpQueryBuilder())->wherePostType($postTypes)->whereTerm($type, $this->getId(), 'term_id');
    }

    protected static function getModelType(): ?string
    {
        return static::TAXONOMY;
    }

    public static function query(): TermQueryBuilder
    {
        return new TermQueryBuilder(static::class);
    }
}
