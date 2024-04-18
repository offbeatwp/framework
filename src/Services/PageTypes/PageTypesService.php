<?php

namespace OffbeatWP\Services\PageTypes;

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Contracts\View;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Services\PageTypes\Models\PageTypeModel;
use OffbeatWP\Support\Wordpress\Taxonomy;
use WP_Post;
use WP_Query;

final class PageTypesService extends AbstractService
{
    public const TAXONOMY = 'page-type';
    public const POST_TYPES = 'page';

    public bool $isPageTypeSaved = false;

    public function register(SiteSettings $settings, View $view): void
    {
        offbeat(Taxonomy::class)::make(self::TAXONOMY, self::POST_TYPES, 'Page types', 'Page type')
            ->model(PageTypeModel::class)
            ->showAdminColumn()
            ->set();

        add_action('restrict_manage_posts', [$this, 'filterPostTypeByTaxonomy']);

        add_action('parse_query', [$this, 'converIdToTermInQuery']);

        add_action('save_post_page', [$this, 'savePageType']);

        add_action('edit_form_top', [$this, 'showRequiredFieldErrorMsg']);
    }

    public function filterPostTypeByTaxonomy(): void
    {
        global $typenow;

        $post_type = self::POST_TYPES;
        $taxonomy = self::TAXONOMY;

        if ($typenow === $post_type) {
            $selected = $_GET[$taxonomy] ?? '';

            wp_dropdown_categories([
                'show_option_all' => __('Show all page types', 'offbeatwp'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => false,
            ]);
        }
    }

    public function converIdToTermInQuery(WP_Query $query): void
    {
        global $pagenow;

        $postType = self::POST_TYPES;
        $taxonomy = self::TAXONOMY;

        $qVars = &$query->query_vars;
        if (isset($qVars['post_type'], $qVars[$taxonomy]) && $pagenow === 'edit.php' && $qVars['post_type'] === $postType && is_numeric($qVars[$taxonomy]) && $qVars[$taxonomy]) {
            $term = get_term_by('id', $qVars[$taxonomy], $taxonomy);
            $qVars[$taxonomy] = $term->slug;
        }
    }

    public function showRequiredFieldErrorMsg(WP_Post $post): void
    {
        $taxonomy = self::TAXONOMY;
        $post_type = self::POST_TYPES;

        if ($post_type === get_post_type($post) && get_post_status($post) !== 'auto-draft') {
            $pageType = wp_get_object_terms($post->ID, $taxonomy, ['orderby' => 'term_id', 'order' => 'ASC']);
            if (is_wp_error($pageType) || !$pageType) {
                printf(
                    '<div class="error below-h2"><p>%s</p></div>',
                    esc_html__('Page type is mandatory', 'offbeatwp')
                );
            }
        }
    }

    public function savePageType(int $postId): void
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $this->isPageTypeSaved) {
            return;
        }

        $taxonomy = self::TAXONOMY;

        if (!isset($_POST['page_type'])) {
            return;
        }

        $pageType = sanitize_text_field($_POST['page_type']);

        if ($pageType) {
            $term = get_term_by('slug', $pageType, $taxonomy);

            if ($term) {
                wp_set_object_terms($postId, $term->term_id, $taxonomy);
            }
        } else {
            $postdata = [
                'ID' => $postId,
                'post_status' => 'draft',
            ];
            wp_update_post($postdata);
        }

        $this->isPageTypeSaved = true;
    }
}
