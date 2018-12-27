<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Traits\Macroable;

class TermModel implements TermModelInterface {

    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    public $wpTerm;
    public $id;

    public function __construct($post)
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

        if (!is_null($hookValue = raowApp('hooks')->applyFilters('term_attribute', null, $method, $this))) {
            return $hookValue;
        }

        return false;
    }


    public function getName() {
        return $this->wpTerm->name;
    }

    public function getSlug() {
        return $this->wpTerm->slug;
    }
}