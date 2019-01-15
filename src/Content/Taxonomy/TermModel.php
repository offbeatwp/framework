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

    function __construct($post)
    {
        if ($post instanceof \WP_Term) {
            $this->wpTerm = $post;
        } elseif (is_numeric($term)) {
            $this->wpTerm = get_term($term, static::TAXONOMY);
        }

        if (isset($this->wpTerm)) {
            $this->id = $this->wpTerm->term_id;
        }
    }

    function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCallStatic($method, $parameters);
        }

        return (new TermQueryBuilder(static::class))->$method(...$parameters);
    }

    function __call($method, $parameters)
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

        return false;
    }

    function getId()
    {
        return $this->wpTerm->term_id;
    }

    function getName()
    {
        return $this->wpTerm->name;
    }

    function getSlug()
    {
        return $this->wpTerm->slug;
    }

    function getDescription()
    {
        return $this->wpTerm->description;
    }

    function getLink()
    {
        return get_term_link($this->wpTerm);
    }

    function getParentId()
    {
        return ($this->wpTerm->parent) ? $this->wpTerm->parent : false;
    }

    function getParent()
    {
        if ($this->getParentId()) {
            return (new static())->findById($this->getParentId());
        }

        return false;
    }

    function getMeta($key, $single = true)
    {
        return get_term_meta($this->getID(), $single);
    }

    function setMeta($key, $value)
    {
        return update_term_meta($this->getID(), $key, $value);
    }

    function getPosts($postTypes = ['any'])
    {
        return (new WpQueryBuilder)->wherePostType($postTypes)->whereTerm(static::TAXONOMY, $this->getId(), 'term_id');
    }
}
