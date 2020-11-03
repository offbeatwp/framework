<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\WpQueryBuilder;

class TermModel implements TermModelInterface
{

    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    public $wpTerm;
    public $id;

    public function __construct($term)
    {
        if ($term instanceof \WP_Term) {
            $this->wpTerm = $term;
        } elseif (is_numeric($term)) {
            $this->wpTerm = get_term($term, static::TAXONOMY);
        }

        if (isset($this->wpTerm)) {
            $this->id = $this->wpTerm->term_id;
        }
    }

    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCallStatic($method, $parameters);
        }

        return (new TermQueryBuilder(static::class))->$method(...$parameters);
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
            return (new TermQueryBuilder(static::class))->$method(...$parameters);
        }

        return false;
    }

    public function getId()
    {
        return $this->wpTerm->term_id;
    }

    public function getName()
    {
        return $this->wpTerm->name;
    }

    public function getSlug()
    {
        return $this->wpTerm->slug;
    }

    public function getDescription()
    {
        return $this->wpTerm->description;
    }

    public function getLink()
    {
        return get_term_link($this->wpTerm);
    }

    public function getTaxonomy()
    {
        return $this->wpTerm->taxonomy;
    }

    public function getParentId()
    {
        return ($this->wpTerm->parent) ? $this->wpTerm->parent : false;
    }

    public function getParent()
    {
        if ($this->getParentId()) {
            return (new static($this))->findById($this->getParentId());
        }

        return false;
    }

    public function getMeta($key, $single = true)
    {
        return get_term_meta($this->getID(), $key, $single);
    }

    public function setMeta($key, $value)
    {
        return update_term_meta($this->getID(), $key, $value);
    }

    public function getPosts($postTypes = ['any'])
    {
        return (new WpQueryBuilder)->wherePostType($postTypes)->whereTerm(static::TAXONOMY, $this->getId(), 'term_id');
    }
}
