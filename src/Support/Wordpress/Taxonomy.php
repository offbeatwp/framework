<?php
namespace OffbeatWP\Support\Wordpress;

use GuzzleHttp\Psr7\Uri;
use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;
use Symfony\Component\HttpFoundation\Request;

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

        if ($term == null && (is_tax() || is_tag() || is_category()) ) {
            return $this->convertWpPostToModel(get_queried_object());
        }


        $term = get_term($term);

        if (!empty($term)) {
            return $this->convertWpPostToModel($term);
        }

        return null;
    }

    public function maybeRedirect($term)
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = $request->getPathInfo();

        $url = $term->getLink();
        $url = str_replace(home_url(), '', $url);
        $postUri    = new Uri($url);

        if (rtrim($requestUri, '/') !== rtrim($postUri->getPath(), '/')) {
            $url = $term->getLink();

            if (!empty($_GET))
                $url .= '?' . http_build_query($_GET);

            offbeat('http')->redirect($url);
            exit;
        }
    }

}
