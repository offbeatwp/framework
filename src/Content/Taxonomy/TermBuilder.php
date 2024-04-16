<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Builders\Exceptions\TermBuilderException;
use WP_Error;

final class TermBuilder
{
    /** @var array{name?: string, taxonomy?: string, alias_of?: string, description?: string, parent?: int, slug?: string} */
    private array $args;

    private function __construct(array $args)
    {
        $this->args = $args;
    }

    /** The term description. Default empty string. */
    public function description(string $description): TermBuilder
    {
        $this->args['description'] = $description;
        return $this;
    }

    /**
     * The ID of the parent term.
     * @param int<0, max> $parent
     */
    public function parent(int $parent): TermBuilder
    {
        $this->args['parent'] = $parent;
        return $this;
    }

    /** The term slug to use. Default empty string. */
    public function slug(string $slug): TermBuilder
    {
        $this->args['slug'] = $slug;
        return $this;
    }

    /** Slug of the term to make this term an alias of. Default empty string. Accepts a term slug. */
    public function aliasOf(string $aliasOf): TermBuilder
    {
        $this->args['alias_of'] = $aliasOf;
        return $this;
    }

    /** The term name. Must not exceed 200 characters. */
    public function name(string $name): TermBuilder
    {
        $this->args['name'] = $name;
        return $this;
    }

    /**
     * Inserts or updates the term in the database.<br>
     * Returns term ID on success, throws TermSaveException on failure.
     * @return positive-int
     * @throws TermBuilderException
     */
    public function save(): int
    {
        // Determine term id (if update)
        if (empty($this->args['term_id'])) {
            $termId = null;
        } else {
            $termId = $this->args['term_id'];
            unset($this->args['term_id']);
        }

        // Either insert or update the term
        if ($termId) {
            $result = wp_update_term($termId, $this->args['taxonomy'], $this->args);
        } else {
            $result = wp_insert_term($this->args['name'], $this->args['taxonomy'], $this->args);
        }

        if ($result instanceof WP_Error) {
            throw new TermBuilderException('TermBuilder ' . ($termId ? 'UPDATE' : 'INSERT') . ' failed: ' . $result->get_error_message());
        }

        return $result['term_id'];
    }

    /////////////////////
    // Factory methods //
    /////////////////////
    /**
     * @pure
     * @param string $termName The term name. Must not exceed 200 characters.
     * @param string $taxonomy The taxonomy to which to add the term.
     */
    public static function create(string $termName, string $taxonomy): TermBuilder
    {
        return new TermBuilder(['name' => $termName, 'taxonomy' => $taxonomy]);
    }

    /**
     * @pure
     * @param positive-int $termId The ID of the term.
     * @param string $taxonomy The taxonomy of the term.
     * @throws TermBuilderException
     */
    public static function update(int $termId, string $taxonomy): TermBuilder
    {
        if ($termId <= 0) {
            throw new TermBuilderException('Termbuilder update failed, invalid ID: ' . $termId);
        }

        return new TermBuilder(['taxonomy' => $taxonomy]);
    }
}