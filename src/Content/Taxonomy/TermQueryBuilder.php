<?php

namespace OffbeatWP\Content\Taxonomy;

use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use OffbeatWP\Support\Wordpress\Taxonomy;
use UnexpectedValueException;
use WP_Term_Query;

/** @template TValue of TermModel */
final class TermQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var class-string<TValue> */
    protected readonly string $modelClass;
    /** @var string|list<string> */
    protected readonly string|array $taxonomy;
    /**
     * @var array{
     *   current_category?: int|int[],
     *   depth?: int,
     *   echo?: bool|int,
     *   exclude?: int[]|string,
     *   exclude_tree?: int[]|string,
     *   feed?: string,
     *   feed_image?: string,
     *   feed_type?: string,
     *   hide_title_if_empty?: bool,
     *   separator?: string,
     *   show_count?: bool|int,
     *   show_option_all?: string,
     *   show_option_none?: string,
     *   style?: string,
     *   taxonomy?: string,
     *   title_li?: string,
     *   use_desc_for_title?: bool|int,
     *   walker?: \Walker,
     *   taxonomy?: string,
     *   meta_query?: mixed[],
     *   object_ids?: int[]|int
     * }
     */
    protected array $queryVars = [];

    /** @param class-string<TValue> $model */
    public function __construct(string $model)
    {
        $this->modelClass = $model;
        $this->taxonomy = $model::TAXONOMY;

        if ($this->taxonomy) {
            $this->queryVars['taxonomy'] = $this->taxonomy;
        }
    }

    /**
     * @param int[] $ids Array of term IDs to include.
     * @return $this
     */
    public function include(array $ids)
    {
        $this->queryVars['include'] = $ids ?: [0];
        return $this;
    }

    /**
     * @param int[] $ids Array of term IDs to exclude.
     * @return $this
     */
    public function exclude(array $ids)
    {
        $this->queryVars['exclude'] = $ids;
        return $this;
    }

    /**
     * @param int[] $ids Array of term IDs to exclude along with all of their descendant terms. If include is non-empty, excludeTree is ignored
     * @return $this
     */
    public function excludeTree(array $ids)
    {
        $this->queryVars['exclude_tree'] = $ids;
        return $this;
    }

    /**
     * True to limit results to terms that have no children.<br>This parameter has no effect on non-hierarchical taxonomies.
     * @return $this
     */
    public function childless(bool $childless = true)
    {
        $this->queryVars['childless'] = true;
        return $this;
    }

    /** @return TermsCollection<int, TValue> */
    public function get(): TermsCollection
    {
        /** @var \WP_Term[] $terms */
        $terms = $this->runQuery()->get_terms();
        return new TermsCollection(array_map(fn($t) => $this->modelClass::from($t), $terms), $this->modelClass);
    }

    /**
     * Keep in mind that empty terms are excluded by default. Set excludeEmpty to false to include empty terms
     * @return TermsCollection<int, TValue>
     */
    public function all(): TermsCollection
    {
        return $this->take(0);
    }

    /**
     * @param int $numberOfItems
     * @return TermsCollection<int, TValue>
     */
    public function take(int $numberOfItems): TermsCollection
    {
        $this->queryVars['number'] = $numberOfItems;
        return $this->get();
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function limit(int $amount)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Limit expects a positive number, but received {$amount}.");
        }

        $this->queryVars['number'] = $amount;
        return $this;
    }

    /** @return array<non-negative-int, positive-int> */
    public function ids(): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'ids';
        $this->queryVars['no_found_rows'] = true;

        /** @var array<non-negative-int, positive-int> */
        return $this->runQuery()->get_terms();
    }

    /**
     * Returns an associative array of parent term IDs, keyed by term ID
     * @return array<non-negative-int, positive-int>
     */
    public function parentIds(): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'id=>parent';
        $this->queryVars['no_found_rows'] = true;

        /** @var array<non-negative-int, positive-int> */
        return $this->runQuery()->get_terms();
    }

    public function count(): int
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'count';
        $this->queryVars['no_found_rows'] = true;

        return (int)$this->runQuery()->get_terms();
    }

    /**
     * @param bool $indexById When true, the names will be indexed with their respective term ID.
     * @return array<non-negative-int, string>
     */
    public function names(bool $indexById): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = ($indexById) ? 'id=>name' : 'names';
        $this->queryVars['no_found_rows'] = true;

        /** @var array<non-negative-int, string> */
        return $this->runQuery()->get_terms();
    }

    public function firstName(): ?string
    {
        return $this->limit(1)->names(false)[0] ?? null;
    }

    /**
     * @param bool $indexById When true, the slugs will be indexed with their respective term ID.
     * @return array<non-negative-int, string>
     */
    public function slugs(bool $indexById): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = ($indexById) ? 'id=>slug' : 'slugs';
        $this->queryVars['no_found_rows'] = true;

        /** @var array<non-negative-int, string> */
        return $this->runQuery()->get_terms();
    }

    public function firstSlug(): ?string
    {
        return $this->limit(1)->slugs(false)[0] ?? null;
    }

    /** @phpstan-return TValue|null */
    public function first(): ?TermModel
    {
        return $this->take(1)->first();
    }

    /** @phpstan-return TValue */
    public function firstOrFail(): TermModel
    {
        $result = $this->first();

        if (!$result) {
            throw new OffbeatModelNotFoundException('The query did not return any TermModels');
        }

        return $result;
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue|null
     */
    public function findById(?int $id): ?TermModel
    {
        if ($id <= 0) {
            return null;
        }

        if (!array_key_exists('hide_empty', $this->queryVars)) {
            $this->queryVars['hide_empty'] = false;
        }

        $this->queryVars['include'] = [$id];

        return $this->first();
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue
     */
    public function findByIdOrFail(int $id): TermModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' with id ' . $id);
        }

        return $result;
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue|null
     */
    public function findBySlug(string $slug): ?TermModel
    {
        if (!$slug) {
            return null;
        }

        if (!array_key_exists('hide_empty', $this->queryVars)) {
            $this->queryVars['hide_empty'] = false;
        }

        $this->queryVars['slug'] = $slug;

        return $this->first();
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue
     */
    public function findBySlugOrFail(string $slug): TermModel
    {
        $result = $this->findBySlug($slug);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' with slug ' . $slug);
        }

        return $result;
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue|null
     */
    public function findByName(string $name): ?TermModel
    {
        if (!$name) {
            return null;
        }

        if (!array_key_exists('hide_empty', $this->queryVars)) {
            $this->queryVars['hide_empty'] = false;
        }

        $this->queryVars['name'] = $name;

        return $this->first();
    }

    /**
     * Note: Will return empty terms unless <i>hide_empty</i> is explicitly set to true.
     * @phpstan-return TValue
     */
    public function findByNameOrFail(string $name): TermModel
    {
        $result = $this->findByName($name);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' with name ' . $name);
        }

        return $result;
    }

    /**
     * @param string[] $slugs Array of slugs to return term(s) for.
     * @return $this
     */
    public function whereSlugIn(array $slugs)
    {
        $this->queryVars['slug'] = $slugs;
        return $this;
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function whereParent(int $parentId)
    {
        $this->queryVars['parent'] = $parentId;
        return $this;
    }

    /**
     * @param string|mixed[] $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '=')
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $parameters = $key;

        if (!is_array($parameters)) {
            $parameters = [
                'key' => $key,
                'value' => $value,
                'compare' => $compare
            ];
        }

        $this->queryVars['meta_query'][] = $parameters;

        return $this;
    }

    /**
     * @param int[] $postIds
     * @return $this
     */
    public function whereRelatedToPost(array $postIds)
    {
        $this->queryVars['object_ids'] = $postIds ?: [0];
        return $this;
    }

    /** @return $this */
    public function excludeEmpty(bool $hideEmpty = true)
    {
        $this->queryVars['hide_empty'] = $hideEmpty;
        return $this;
    }

    private function runQuery(): WP_Term_Query
    {
        // This is a fix for to ensure that passing an empty array to include returns no results.
        if (isset($this->queryVars['include']) && $this->queryVars['include'] === [0]) {
            $this->queryVars['object_ids'] = 0;
        }

        $query = new WP_Term_Query($this->queryVars);

        self::$lastRequest = $query->request;

        return $query;
    }
}
