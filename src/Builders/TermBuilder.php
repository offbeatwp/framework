<?php

namespace OffbeatWP\Builders;

use InvalidArgumentException;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Exceptions\WpErrorException;
use WP_Error;
use WP_Term;

final class TermBuilder
{
    private int $id;
    private ?WP_Term $sourceTerm;
    /** @var array{name?: string, taxonomy?: string, alias_of?: string, description?: string, parent?: int, slug?: string} */
    private array $args;

    /** @param WP_Term|mixed[]|null $sourceTerm */
    private function __construct(int $id, $sourceTerm)
    {
        $this->id = $id;

        if (is_array($sourceTerm)) {
            $this->args = $sourceTerm;
            $this->sourceTerm = null;
        } else {
            $this->args = get_object_vars($sourceTerm);
            $this->sourceTerm = $sourceTerm;
        }
    }

    /**
     * @pure
     * @param string $termName The term name. Must not exceed 200 characters.
     * @param string $taxonomy The taxonomy to which to add the term.
     * @return TermBuilder
     */
    public static function create(string $termName, string $taxonomy): TermBuilder
    {
        return new TermBuilder(0, ['name' => $termName, 'taxonomy' => $taxonomy]);
    }

    /**
     * @pure
     * @param TermModel|WP_Term $term
     * @return TermBuilder
     */
    public static function from($term): TermBuilder
    {
        $term = self::getWpTerm($term);
        return new TermBuilder($term->term_id, $term);
    }

    /**
     * @pure
     * @param TermModel|WP_Term $term
     * @return TermBuilder
     */
    public static function copy($term): TermBuilder
    {
        $term = self::getWpTerm($term);
        return new TermBuilder(0, $term);
    }

    /**
     * @param TermModel|WP_Term $term
     * @return WP_Term
     */
    private static function getWpTerm($term): WP_Term
    {
        if ($term instanceof TermModel) {
            $term = $term->wpTerm;
        }

        if ($term instanceof WP_Term) {
            return $term;
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
     * @param int<0, max> $parent
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
     * @return positive-int
     * @throws WpErrorException
     */
    public function save(): int
    {
        if ($this->id && $this->sourceTerm->term_id === $this->id) {
            $result = wp_update_term($this->id, $this->args['taxonomy'], $this->args);
        } else {
            $result = wp_insert_term($this->args['name'], $this->args['taxonomy'], $this->args);
        }

        if ($result instanceof WP_Error) {
            throw new WpErrorException($result->get_error_message());
        }

        // Copy the meta from the original term meta to the new term
        if ($this->sourceTerm && $this->sourceTerm->term_id !== $this->id) {
            global $wpdb;

            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->termmeta} (term_id, meta_key, meta_value) 
                SELECT %d, meta_key, meta_value FROM {$wpdb->termmeta} 
                WHERE term_id = %d;",
                $this->id,
                $this->sourceTerm->term_id
            ));
        }

        return $result['term_id'];
    }
}