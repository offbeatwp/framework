<?php

namespace OffbeatWP\Content\Post;

use DOMDocument;
use OffbeatWP\Content\Common\OffbeatModelCollection;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use TypeError;
use WP_Post;
use WP_Query;

/**
 * @template TKey of array-key
 * @template TValue of \OffbeatWP\Content\Post\PostModel
 * @extends OffbeatModelCollection<TKey, TValue>
 *
 * @method TValue|mixed pull(int|string $key, mixed $default = null)
 * @method TValue|mixed first(callable $callback = null, mixed $default = null)
 * @method TValue|mixed last(callable $callback = null, mixed $default = null)
 * @method TValue|static|null pop(int $count = 1)
 * @method TValue|static|null shift(int $count = 1)
 * @method TValue|null reduce(callable $callback, mixed $initial = null)
 * @method TValue offsetGet(int|string $key)
 */
class PostsCollection extends OffbeatModelCollection
{
    protected IWpQuerySubstitute|WP_Query|null $query = null;

    /** @param int[]|WP_Post[]|WP_Query $items */
    public function __construct($items = [])
    {
        $postItems = [];

        if ($items instanceof WP_Query || $items instanceof IWpQuerySubstitute) {
            $this->query = $items;

            if ($items->posts) {
                foreach ($items->posts as $post) {
                    $postItems[] = offbeat('post')->convertWpPostToModel($post);
                }
            }
        } elseif (is_iterable($items)) {
            foreach ($items as $key => $item) {
                $postModel = $this->createValidPostModel($item);
                if ($postModel) {
                    $postItems[$key] = $postModel;
                }
            }
        }

        parent::__construct($postItems);
    }

    /** @param int|WP_Post|PostModel $item */
    protected function createValidPostModel($item): ?PostModel
    {
        if ($item instanceof PostModel) {
            return $item;
        }

        if (is_int($item) || $item instanceof WP_Post) {
            return offbeat('post')->get($item);
        }

        throw new TypeError(gettype($item) . ' cannot be used to generate a PostModel.');
    }

    /** @return \OffbeatWP\Content\Post\WpPostsIterator<array-key, TValue> */
    public function getIterator(): WpPostsIterator
    {
        /** @var \OffbeatWP\Content\Post\WpPostsIterator<array-key, TValue> */
        return new WpPostsIterator($this->items);
    }

    /**
     * Returns the maximum number of pages to display.
     * If this PostsCollection has no query associated with it, then <b>0</b> is returned.
     * @return int
     */
    public function getMaxPages(): int
    {
        return $this->query->max_num_pages ?? 0;
    }

    public function hasPagination(): bool
    {
        return $this->getMaxPages() > 1;
    }

    /**
     * Retrieves a paginated navigation to next/previous set of posts, when applicable.
     * @param array{base?: string, use_buttons?: bool, format?: string, total?: int, current?: int, aria_current?: string, show_all?: bool, end_size?: int, mid_size?: int, prev_next?: bool, prev_text?: string, next_text?: string, type?: string, add_args?: mixed[], add_fragment?: string, before_page_number?: string, after_page_number?: string, attribs?: string[]} $rawArgs
     * @see paginate_links().
     */
    public function getPagination(array $rawArgs = [], string $slug = ''): string
    {
        if ($this->hasPagination()) {
            $args = wp_parse_args($rawArgs, [
                'mid_size'              => 2,
                'prev_text'             => '&#171;',
                'next_text'             => '&#187;',
                'screen_reader_text'    => __('Posts navigation'),
                'aria_label'            => __('Posts'),
                'class'                 => 'pagination'
            ]);

            if ($slug) {
                $args['base'] = '%_%';
                $args['total'] = $this->getMaxPages();
                $args['current'] = max(1, (int)($_GET[$slug] ?? 1));
                $args['format']  = '?' . $slug . '=%#%';
            }

            // Make sure the nav element has an aria-label attribute: fallback to the screen reader text.
            if ($args['screen_reader_text'] && !$args['aria_label']) {
                $args['aria_label'] = $args['screen_reader_text'];
            }

            $links = $this->getPaginatedLinks($args);

            if (isset($args['attribs'])) {
                $attributes = (array)$args['attribs'];
                $dom = new DOMDocument();
                $dom->loadHTML($links);

                $nodes = $dom->getElementsByTagName(empty($args['use_buttons']) ? 'a' : 'button');
                foreach ($nodes as $node) {
                    foreach ($attributes as $key => $value) {
                        $node->setAttribute($key, $value);
                    }
                }

                $links = $dom->saveHTML();
            }

            if ($links) {
                return _navigation_markup($links, $args['class'], $args['screen_reader_text'], $args['aria_label']);
            }
        }

        return '';
    }

    /** @param array{base?: string, use_buttons?: bool, format?: string, total?: int, current?: int, aria_current?: string, show_all?: bool, end_size?: int, mid_size?: int, prev_next?: bool, prev_text?: string, next_text?: string, type?: string, add_args?: mixed[], add_fragment?: string, before_page_number?: string, after_page_number?: string, attribs?: string[], always_show_buttons?: bool} $args */
    private function getPaginatedLinks(array $args): string
    {
        $GLOBALS['wp_query'] = $this->query;
        $args['type'] = 'plain';
        $args['format'] = $args['format'] ?? 'page/%#%/';
        $links = paginate_links($args);
        wp_reset_query();

        if (!empty($args['always_show_buttons'])) {
            if (!strpos($links, 'class="prev')) {
                $links = '<span class="prev page-numbers placeholder-prevnext disabled">' . $args['prev_text'] . '</span>' . $links;
            }

            if (!strpos($links, 'class="next')) {
                $links .= '<span class="next page-numbers placeholder-prevnext disabled">'. $args['next_text'] .'</span>';
            }
        }

        if (!empty($args['use_buttons'])) {
            // Replace A tag with BUTTON tag
            $links = str_replace(['<a', '</a>'], ['<button', '</button>'], $links);

            // Replace href with data-page
            $chunks = explode('>', $links);
            for ($i = 0, $l = count($chunks); $i < $l; $i++) {
                if (str_contains($chunks[$i], '<button')) {
                    $chunks[$i] = preg_replace_callback('/href=".*(\/page\/(\d*+)\/?.*?)?"/U', fn ($matches) => 'data-page="' . ($matches[2] ?? 1) . '"', $chunks[$i]);
                }
            }

            $links = implode('>', $chunks);
        }

        return $links;
    }

    /** @return IWpQuerySubstitute|WP_Query|null */
    public function getQuery()
    {
        return $this->query;
    }

    public function foundPosts(): int
    {
        return empty($this->query->found_posts) ? $this->count() : $this->query->found_posts;
    }

    public function hasItemsLeft(): bool
    {
        return $this->foundPosts() > ($this->count() + ($this->query->query_vars['offset'] ?? 0));
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(static fn (PostModel $model) => $model->getId() ?: 0, $this->items);
    }

    /**
     * @return PostModel[]|TValue[]
     * @phpstan-return TValue[]
     */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    /**
     * Deletes <b>all</b> the posts in this collection from the database.
     * @param bool $force
     * @return void
     */
    public function deleteAll(bool $force)
    {
        $this->each(function (PostModel $model) use ($force) {
            $model->delete($force);
        });

        $this->items = [];
    }
}
