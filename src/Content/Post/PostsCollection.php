<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatModelCollection;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use TypeError;
use WP_Post;
use WP_Query;

/**
 * @method PostModel|mixed pull(int|string $key, mixed $default = null)
 * @method PostModel|mixed first(callable $callback = null, mixed $default = null)
 * @method PostModel|mixed last(callable $callback = null, mixed $default = null)
 * @method PostModel|static|null pop(int $count = 1)
 * @method PostModel|static|null shift(int $count = 1)
 * @method PostModel|null reduce(callable $callback, mixed $initial = null)
 * @method PostModel offsetGet(int|string $key)
 */
class PostsCollection extends OffbeatModelCollection
{
    protected $query = null;

    /** @var int[]|WP_Post[]|WP_Query $items */
    public function __construct($items = [])
    {
        $postItems = [];

        if ($items instanceof WP_Query || $items instanceof IWpQuerySubstitute) {
            $this->query = $items;

            if (!empty($items->posts)) {
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

    /** @return WpPostsIterator|PostModel[] */
    public function getIterator(): WpPostsIterator
    {
        return new WpPostsIterator($this->items);
    }

    public function getPagination(array $args = []): string
    {
        // Don't print empty markup if there's only one page.
        if ($this->query && $this->query->max_num_pages > 1) {
            // Make sure the nav element has an aria-label attribute: fallback to the screen reader text.
            if (!empty($args['screen_reader_text']) && empty($args['aria_label'])) {
                $args = ['aria_label' => $args['screen_reader_text']];
            }

            $parsedArgs = wp_parse_args($args, [
                'mid_size' => 1,
                'prev_text' => _x('Previous', 'previous set of posts'),
                'next_text' => _x('Next', 'next set of posts'),
                'screen_reader_text' => __('Posts navigation'),
                'aria_label' => __('Posts'),
                'class' => 'pagination'
            ]);

            // Make sure we get a string back. Plain is the next best thing.
            if (isset($parsedArgs['type']) && $parsedArgs['type'] === 'array') {
                $parsedArgs['type'] = 'plain';
            }

            // Set up paginated links.
            $links = $this->paginateLinks($parsedArgs);

            if ($links) {
                return _navigation_markup($links, $parsedArgs['class'], $parsedArgs['screen_reader_text'], $parsedArgs['aria_label']);
            }
        }

        return '';
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

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(static function (PostModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** @return PostModel[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    /**
     * Deletes <b>all</b> the items in this collection.
     * @param bool $force
     */
    public function deleteAll(bool $force)
    {
        $this->each(function (PostModel $model) use ($force) {
            $model->delete($force);
        });

        $this->items = [];
    }

    /**
     * @param object|array $rawArgs
     * @return string|null|array
     */
    private function paginateLinks($rawArgs)
    {
        global $wp_rewrite;

        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);

        // Get max pages and current page out of the current query, if available.
        $total = $this->query->max_num_pages ?? 1;
        $current = get_query_var('paged') ? (int)get_query_var('paged') : 1;

        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';

        // URL base depends on permalink settings.
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit(
            $wp_rewrite->pagination_base . '/%#%',
            'paged'
        ) : '?paged=%#%';

        $defaults = [
            'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below).
            'format' => $format, // ?page=%#% : %#% is replaced by the page number.
            'total' => $total,
            'current' => $current,
            'aria_current' => 'page',
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => [], // Array of query args to add.
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => '',
        ];

        $args = wp_parse_args($rawArgs, $defaults);

        if (!is_array($args['add_args'])) {
            $args['add_args'] = [];
        }

        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            wp_parse_str($format[1] ?? '', $format_args);

            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);

            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }

            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }

        // Who knows what else people pass in $args.
        $total = (int)$args['total'];
        if ($total < 2) {
            return null;
        }
        $current = (int)$args['current'];
        $endSize = (int)$args['end_size']; // Out of bounds? Make it the default.
        if ($endSize < 1) {
            $endSize = 1;
        }
        $midSize = (int)$args['mid_size'];
        if ($midSize < 0) {
            $midSize = 2;
        }

        $addArgs = $args['add_args'];
        $r = '';
        $page_links = [];
        $dots = false;

        if ($args['prev_next'] && $current && 1 < $current) :
            $rep = ($current === 2) ? '' : $args['format'];
            $link = str_replace('%_%', $rep, $args['base']);
            $rep = (string)$current - 1;
            $link = str_replace('%#%', $rep, $link);

            if ($addArgs) {
                $link = add_query_arg($addArgs, $link);
            }

            $link .= $args['add_fragment'];

            $page_links[] = sprintf(
                '<a class="prev page-numbers" href="%s">%s</a>',
                /**
                 * Filters the paginated links for the given archive pages.
                 *
                 * @param string $link The paginated link URL.
                 * @since 3.0.0
                 *
                 */
                esc_url(apply_filters('paginate_links', $link)),
                $args['prev_text']
            );
        endif;

        for ($n = 1; $n <= $total; $n++) :
            if ($n === $current) {
                $page_links[] = sprintf('<span aria-current="%s" class="page-numbers current">%s</span>', esc_attr($args['aria_current']),
                    $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number']
                );

                $dots = true;
            } elseif ($args['show_all'] || ($n <= $endSize || ($current && $n >= $current - $midSize && $n <= $current + $midSize) || $n > $total - $endSize)) {
                $link = str_replace('%_%', $n === 1 ? '' : $args['format'], $args['base']);
                $link = str_replace('%#%', $n, $link);
                if ($addArgs) {
                    $link = add_query_arg($addArgs, $link);
                }
                $link .= $args['add_fragment'];

                $page_links[] = sprintf(
                    '<a class="page-numbers" href="%s">%s</a>',
                    /** This filter is documented in wp-includes/general-template.php */
                    esc_url(apply_filters('paginate_links', $link)),
                    $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number']
                );

                $dots = true;
            } elseif ($dots && !$args['show_all']) {
                $page_links[] = '<span class="page-numbers dots">' . __('&hellip;') . '</span>';

                $dots = false;
            }
        endfor;

        if ($args['prev_next'] && $current && $current < $total) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);

            if ($addArgs) {
                $link = add_query_arg($addArgs, $link);
            }

            $link .= $args['add_fragment'];

            $page_links[] = sprintf(
                '<a class="next page-numbers" href="%s">%s</a>',
                /** This filter is documented in wp-includes/general-template.php */
                esc_url(apply_filters('paginate_links', $link)),
                $args['next_text']
            );
        endif;

        switch ($args['type']) {
            case 'array':
                return $page_links;

            case 'list':
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= implode("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;

            default:
                $r = implode("\n", $page_links);
                break;
        }

        /**
         * Filters the HTML output of paginated links for archives.
         *
         * @param string $r HTML output.
         * @param array $args An array of arguments. See paginate_links()
         *                     for information on accepted arguments.
         * @since 5.7.0
         *
         */
        $r = apply_filters('paginate_links_output', $r, $args);

        return $r;
    }
}
