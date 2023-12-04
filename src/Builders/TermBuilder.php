<?php

namespace OffbeatWP\Builders;

use InvalidArgumentException;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Exceptions\WpErrorException;
use WP_Error;
use WP_Term;

final class TermBuilder
{
    private int $id = 0;
    private string $name;
    private string $taxonomy;
    /** @var array{name?: string, taxonomy?: string, alias_of?: string, description?: string, parent?: int, slug?: string} */
    private array $args = [];

    /** @param WP_Term|mixed[]|null $term */
    private function __construct($term)
    {
        if (is_array($term)) {
            $this->name = $term['name'];
            $this->taxonomy = $term['taxonomy'];
        } else {
            $this->id = $term->term_id;
            $this->name = $term->name;
            $this->taxonomy = $term->taxonomy;
            $this->args = [
                'description' => $term->description,
                'parent' => $term->parent,
                'slug' => $term->slug
            ];
        }
    }

    /**
     * @pure
     * @param string $term The term name. Must not exceed 200 characters.
     * @param string $taxonomy The taxonomy to which to add the term.
     * @return TermBuilder
     */
    public static function create(string $term, string $taxonomy): TermBuilder
    {
        return new TermBuilder(['term' => $term, 'taxonomy' => $taxonomy]);
    }

    /**
     * @pure
     * @param TermModel|WP_Term $term
     * @return TermBuilder
     */
    public static function from($term): TermBuilder
    {
        if ($term instanceof WP_Term) {
            return new TermBuilder($term);
        }

        if ($term instanceof TermModel) {
            return new TermBuilder($term->wpTerm);
        }

        throw new InvalidArgumentException('TermBuilder::from expects a WP_Term or TermModel as argument, but got ' . gettype($term));
    }

    /**
     * The term description. Default empty string.
     * @return $this
     */
    public function description(string $description): self
    {
        $this->args['description'] = $description;
        return $this;
    }

    /**
     * The ID of the parent term.
     * @phpstan-param int<0, max> $parent
     * @return $this
     */
    public function parent(int $parent): self
    {
        $this->args['parent'] = $parent;
        return $this;
    }

    /**
     * The term slug to use. Default empty string.
     * @return $this
     */
    public function slug(string $slug): self
    {
        $this->args['slug'] = $slug;
        return $this;
    }

    /**
     * Slug of the term to make this term an alias of.<br>
     * Default empty string. Accepts a term slug.
     * @return $this
     */
    public function aliasOf(string $aliasOf): self
    {
        $this->args['alias_of'] = $aliasOf;
        return $this;
    }

    /**
     * The term name. Must not exceed 200 characters.
     * @return $this
     */
    public function name(string $name): self
    {
        $this->args['name'] = $name;
        return $this;
    }

    /**
     * Inserts or updates the term in the database.<br>
     * Return term ID on success, throw a WpErrorException on failure.
     * @return int
     * @throws WpErrorException
     */
    public function save(): int
    {
        if ($this->id) {
            $result = wp_update_term($this->id, $this->taxonomy, $this->args);
        } else {
            $result = wp_insert_term($this->name, $this->taxonomy, $this->args);
        }

        if ($result instanceof WP_Error) {
            throw new WpErrorException($result->get_error_message());
        }

        return $result['term_id'];
    }
}