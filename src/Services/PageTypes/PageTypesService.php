<?php

namespace OffbeatWP\Services\PageTypes;

use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Services\PageTypes\Models\PageTypeModel;
use WP_Post;
use WP_Query;

class PageTypesService extends AbstractService
{
    public const TAXONOMY = 'page-type';
    public const POST_TYPES = 'page';

    /** @var bool */
    public $isPageTypeSaved = false;

    /** @return void */
    public function register()
    {
        offbeat('taxonomy')::make(self::TAXONOMY, self::POST_TYPES, 'Page types', 'Page type')
            ->metaBox([$this, 'metaBox'])
            ->model(PageTypeModel::class)
            ->showAdminColumn()
            ->set();

        add_action('restrict_manage_posts', [$this, 'tsm_filter_post_type_by_taxonomy']);
        add_filter('parse_query', [$this, 'tsm_convert_id_to_term_in_query']);

        add_action('save_post_page', [$this, 'savePageType']);

        add_action('edit_form_top', [$this, 'show_required_field_error_msg']);
    }

    /** @return void */
    public function tsm_filter_post_type_by_taxonomy()
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

    /**
     * @param WP_Query $query
     * @return void
     */
    public function tsm_convert_id_to_term_in_query($query)
    {
        global $pagenow;

        $postType = self::POST_TYPES;
        $taxonomy = self::TAXONOMY;

        $qVars = &$query->query_vars;
        if (isset($qVars['post_type'], $qVars[$taxonomy]) && $pagenow === 'edit.php' && $qVars['post_type'] === $postType && is_numeric($qVars[$taxonomy]) && $qVars[$taxonomy] != 0) {
            $term = get_term_by('id', $qVars[$taxonomy], $taxonomy);
            $qVars[$taxonomy] = $term->slug;
        }
    }

    /**
     * @param WP_Post $post
     * @return void
     */
    public function show_required_field_error_msg($post)
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

    /**
     * @param int $postId
     * @return void
     */
    public function savePageType($postId)
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

    /** @return void */
    public function metaBox()
    {
        $terms = PageTypeModel::query()->excludeEmpty(false)->order('term_id', 'ASC')->all();

        if ($terms->isEmpty()) {
            return;
        }

        $pagePageType = offbeat('post')->get()->getTerms(self::TAXONOMY)->all();

        if ($pagePageType->isNotEmpty()) {
            $slug = $pagePageType->first()->getSlug();
        } else {
            $slug = $terms->first()->getSlug();
        }

        $terms->each(function (TermModel $term) use ($slug) {
            ?>
            <label title='<?php esc_attr_e($term->getName()); ?>'>
                <input type="radio" name="page_type" value="<?= $term->getSlug() ?>" <?php checked($term->getSlug(), $slug); ?>>
                <span><?php esc_html_e($term->getName()); ?></span>
            </label><br>
            <?php
        });
    }
}
