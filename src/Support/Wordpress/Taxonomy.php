<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;

class Taxonomy
{
    const DEFAULT_TERM_MODEL = TermModel::class;

    private $taxonomyModels = [];

    public static function make($name, $postTypes, $pluralName, $singleName)
    {
        return (new TaxonomyBuilder)->make($name, $postTypes, $pluralName, $singleName);
    }

    public function registerTermModel($taxonomy, $modelClass)
    {
        $this->taxonomyModels[$taxonomy] = $modelClass;
    }

    public function getModelByTaxonomy($taxonomy)
    {
        if (isset($this->taxonomyModels[$taxonomy]))
            return $this->taxonomyModels[$taxonomy];

        return self::DEFAULT_TERM_MODEL;
    }

    public function convertWpPostToModel(\WP_Term $term) {
        $model = $this->getModelByTaxonomy($term->taxonomy);
        // $model = offbeat('hooks')->applyFilters('post_model', $model, $post);

        return new $model($term);
    }

    public function get($term = null) {
        if ($term instanceof \WP_Term) {
            return $this->convertWpPostToModel($term);
        }

        if ($term == null && is_tax()) {
            return $this->convertWpPostToModel(get_queried_object());
        }


        $term = get_term($term);

        if (!empty($term)) {
            return $this->convertWpPostToModel($term);
        }

        return null;
    }
}
