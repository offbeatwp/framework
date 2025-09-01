<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;
use WP_Term;

final class Taxonomy
{
    public const DEFAULT_TERM_MODEL = TermModel::class;

    /** @var class-string<TermModel>[] */
    private array $taxonomyModels = [];

    /**
     * @param string $name Name should only contain lowercase letters and the underscore character, and not be more than 32 characters long.
     * @param string|string[] $postTypes Object types with which the taxonomy should be associated.
     * @param string $pluralName Optional. Can also be set through the labels method.
     * @param string $singleName Optional. Can also be set through the labels method.
     */
    public static function make(string $name, string|array $postTypes, string $pluralName = '', string $singleName = ''): TaxonomyBuilder
    {
        return (new TaxonomyBuilder())->make($name, $postTypes, $pluralName ?: $name, $singleName ?: $pluralName ?: $name);
    }

    /** @param class-string<TermModel> $modelClass */
    public function registerTermModel(string $taxonomy, string $modelClass): void
    {
        $this->taxonomyModels[$taxonomy] = $modelClass;
    }

    /** @return class-string<TermModel> */
    public function getModelByTaxonomy(string $taxonomy): string
    {
        return $this->taxonomyModels[$taxonomy] ?? self::DEFAULT_TERM_MODEL;
    }

    public function convertWpTermToModel(WP_Term $term): TermModel
    {
        $model = $this->getModelByTaxonomy($term->taxonomy);
        return new $model($term);
    }

    /**
     * Get a taxonomy by either the WP_TERM or the taxonomy's ID
     * Attempts to get the currently queried object if no parameter is passed
     */
    public function get(WP_Term|int|null $term = null): ?TermModel
    {
        if ($term instanceof WP_Term) {
            return $this->convertWpTermToModel($term);
        }

        if ($term === null && (is_tax() || is_tag() || is_category())) {
            $obj = get_queried_object();
            if ($obj instanceof WP_Term) {
                return $this->convertWpTermToModel($obj);
            }
        }

        if (is_numeric($term)) {
            $retrievedTerm = get_term($term);

            if ($retrievedTerm instanceof WP_Term) {
                return $this->convertWpTermToModel($retrievedTerm);
            }
        }

        return null;
    }
}
