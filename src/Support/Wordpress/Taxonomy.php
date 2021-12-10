<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;
use Symfony\Component\HttpFoundation\Request;
use WP_Term;

class Taxonomy
{
    public const DEFAULT_TERM_MODEL = TermModel::class;

    /** @var class-string<TermModel> */
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

    /**
     * @param string $taxonomy
     * @param class-string<TermModel> $modelClass
     */
    public function registerTermModel(string $taxonomy, string $modelClass)
    {
        $this->taxonomyModels[$taxonomy] = $modelClass;
    }

    /** @return class-string<TermModel> */
    public function getModelByTaxonomy(string $taxonomy): string
    {
        return $this->taxonomyModels[$taxonomy] ?? self::DEFAULT_TERM_MODEL;
    }

    /** @deprecated Use convertWpTermToModel instead */
    public function convertWpPostToModel(WP_Term $term)
    {
        return $this->convertWpTermToModel($term);
    }

    /** @return TermModel */
    public function convertWpTermToModel(WP_Term $term)
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
            return $this->convertWpTermToModel($term);
        }

        if ($term === null && (is_tax() || is_tag() || is_category())) {
            $obj = get_queried_object();
            if ($obj instanceof WP_Term) {
                return $this->convertWpTermToModel($obj);
            }
        }

        $retrievedTerm = get_term($term);

        if ($retrievedTerm) {
            return $this->convertWpTermToModel($retrievedTerm);
        }

        return null;
    }

    public function maybeRedirect(TermModel $term): void
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = $request->getPathInfo();

        $url = $term->getLink();
        $url = str_replace(home_url(), '', $url);
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (rtrim($requestUri, '/') !== rtrim($urlPath, '/')) {
            $url = $term->getLink();

            if (!empty($_GET)) {
                $url .= '?' . http_build_query($_GET);
            }

            offbeat('http')->redirect($url);
            exit;
        }
    }
}
