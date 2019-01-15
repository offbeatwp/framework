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
}
