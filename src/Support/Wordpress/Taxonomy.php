<?php

namespace OffbeatWP\Support\Wordpress;

use GuzzleHttp\Psr7\Uri;
use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;
use Symfony\Component\HttpFoundation\Request;
use WP_Term;

class Taxonomy
{
    const DEFAULT_TERM_MODEL = TermModel::class;

    private $taxonomyModels = [];

    /**
     * @param string $name
     * @param string|string[] $postTypes
     * @param string $pluralName
     * @param string $singleName
     * @return TaxonomyBuilder
     */
    public static function make(string $name, $postTypes, string $pluralName, string $singleName): TaxonomyBuilder
    {
        return (new TaxonomyBuilder())->make($name, $postTypes, $pluralName, $singleName);
    }

    public function registerTermModel(string $taxonomy, string $modelClass)
    {
        $this->taxonomyModels[$taxonomy] = $modelClass;
    }

    public function getModelByTaxonomy(string $taxonomy)
    {
        if (isset($this->taxonomyModels[$taxonomy])) {
            return $this->taxonomyModels[$taxonomy];
        }

        return self::DEFAULT_TERM_MODEL;
    }

    public function convertWpPostToModel(WP_Term $term)
    {
        $model = $this->getModelByTaxonomy($term->taxonomy);

        return new $model($term);
    }

    /**
     * Get a taxonomy by either the WP_TERM or the taxonomy's ID
     * Attempts to get the currently queried object if no parameter is passed
     * @param WP_Term|int|null $term
     * @return TermModel|null
     */
    public function get($term = null)
    {
        if ($term instanceof WP_Term) {
            return $this->convertWpPostToModel($term);
        }

        if ($term === null && (is_tax() || is_tag() || is_category())) {
            $obj = get_queried_object();
            if ($obj instanceof WP_Term) {
                return $this->convertWpPostToModel($obj);
            }
        }

        $term = get_term($term);

        if (!empty($term)) {
            return $this->convertWpPostToModel($term);
        }

        return null;
    }

    public function maybeRedirect(TermModel $term): void
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = $request->getPathInfo();

        $url = $term->getLink();
        $url = str_replace(home_url(), '', $url);
        $postUri = new Uri($url);

        if (rtrim($requestUri, '/') !== rtrim($postUri->getPath(), '/')) {
            $url = $term->getLink();

            if (!empty($_GET)) {
                $url .= '?' . http_build_query($_GET);
            }

            offbeat('http')->redirect($url);
            exit;
        }
    }

}
