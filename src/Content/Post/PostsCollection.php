<?php

namespace OffbeatWP\Content\Post;

use ArrayAccess;
use OffbeatWP\Content\Common\ReadOnlyCollection;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use TypeError;
use WP_Post;
use WP_Query;

/**
 * @template TModel
 *
 * @implements ArrayAccess<array-key, TModel>
 */
final class PostsCollection extends ReadOnlyCollection
{
    private readonly IWpQuerySubstitute|WP_Query $query;

    public function __construct(IWpQuerySubstitute|WP_Query $query)
    {
        $postItems = [];
        $this->query = $query;

        foreach ($query->posts as $post) {
            $postItems[] = offbeat('post')->convertWpPostToModel($post);
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

    /** @phpstan-return WpPostsIterator<TModel>|TModel[] */
    public function getIterator(): WpPostsIterator
    {
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

            if ($links) {
                return _navigation_markup($links, $args['class'], $args['screen_reader_text'], $args['aria_label']);
            }
        }

        return '';
    }

    /** @param array{base?: string, use_buttons?: bool, format?: string, total?: int, current?: int, aria_current?: string, show_all?: bool, end_size?: int, mid_size?: int, prev_next?: bool, prev_text?: string, next_text?: string, type?: string, add_args?: mixed[], add_fragment?: string, before_page_number?: string, after_page_number?: string, attribs?: string[]} $args */
    private function getPaginatedLinks(array $args): string
    {
        $GLOBALS['wp_query'] = $this->query;
        $args['type'] = 'plain';
        $args['format'] = 'page/%#%/';
        $links = paginate_links($args);
        wp_reset_query();

        if (!empty($args['use_buttons'])) {
            $links = str_replace(['<a', '</a>'], ['<button', '</button>'], $links);
            // TODO: Page is translateable
            $links = preg_replace_callback('/href=".*(\/page\/(\d+)\/?.*?)?"/U', fn($matches) => 'data-page="' . ($matches[2] ?? 1) . '"', $links);
        }

        return $links;
    }

    public function getQuery(): IWpQuerySubstitute|WP_Query
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
        return array_map(static fn(PostModel $model) => $model->getId() ?: 0, $this->items);
    }

    /**
     * @param int $offset
     * @phpstan-return TModel|null
     */
    public function offsetGet(mixed $offset): ?PostModel
    {
        return parent::offsetGet($offset);
    }

    /** Get the first item from the collection. */
    public function first(): ?PostModel
    {
        return parent::first();
    }

    /** Get the last item from the collection. */
    public function last(): ?PostModel
    {
        return parent::last();
    }
}
