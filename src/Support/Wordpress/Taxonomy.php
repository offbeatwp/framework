<?php

namespace OffbeatWP\Support\Wordpress;

use GuzzleHttp\Psr7\Uri;
use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Exceptions\TaxonomyException;
use Symfony\Component\HttpFoundation\Request;
use WP_Term;

class Taxonomy
{
    public const DEFAULT_TERM_MODEL = TermModel::class;

    /** @var class-string<TermModel>[] */
    private $taxonomyModels = [];
    /** @var class-string<TermModel>|null */
    private $defaultTaxonomy;

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
     * @param string|class-string<TermModel> $taxonomy  Either the class-string of a TermModel with a defined POST_TYPE or the slug of the taxonomy to register
     * @param class-string<TermModel> $modelClass       The className of the TermModel. Only required if the first passed parameter was a slug
     */
    public function registerTermModel(string $taxonomy, string $modelClass = ""): void
    {
        if (!$modelClass) {
            $modelClass = $taxonomy;
            $taxonomy = $modelClass::TAXONOMY;
        }

        $this->taxonomyModels[$taxonomy] = $modelClass;
    }

    /**
     * @param class-string<TermModel> $modelClass
     * @throws TaxonomyException
     */
    public function registerDefaultTermModel(string $modelClass): void
    {
        if ($this->defaultTaxonomy) {
            throw new TaxonomyException('Could not set ' . $modelClass . ' as default taxonomy because default taxonomy has already been set to ' . $this->defaultTaxonomy);
        } else if (in_array($modelClass, $this->taxonomyModels, true)) {
            throw new TaxonomyException($this->defaultTaxonomy . ' was already registered as a regular TermModel.');
        }

        $this->defaultTaxonomy = $modelClass;
        $this->registerTermModel($modelClass);
    }

    public function getModelByTaxonomy(string $taxonomy): string
    {
        return $this->taxonomyModels[$taxonomy] ?? self::DEFAULT_TERM_MODEL;
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
    public function get($term = null): ?TermModel
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

        $retrievedTerm = get_term($term);
        if (!empty($retrievedTerm)) {
            return $this->convertWpPostToModel($retrievedTerm);
        }

        return null;
    }

    public function maybeRedirect(TermModel $term): void
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
        $requestUri = $request->getPathInfo();

        $url = $term->getLink();
        $url = str_replace(home_url(), '', $url);

        if (!class_exists('\GuzzleHttp\Psr7\Uri')) {
            exit;
        }

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
