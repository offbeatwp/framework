<?php

namespace OffbeatWP\Services\PageTypes;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Services\PageTypes\Models\PageTypeModel;

class PageTypesService extends AbstractService
{
    const TAXONOMY = 'page-type';
    const POST_TYPES = 'page';

    public $isPageTypeSaved = false;

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

    public function tsm_filter_post_type_by_taxonomy()
    {
        global $typenow;

        $post_type = self::POST_TYPES;
        $taxonomy = self::TAXONOMY;

        if ($typenow == $post_type) {
            $selected = $_GET[$taxonomy] ?? '';

            wp_dropdown_categories([
                'show_option_all' => __("Show all page types", 'offbeatwp'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => false,
            ]);
        }
    }

    public function tsm_convert_id_to_term_in_query($query)
    {
        global $pagenow;

        $post_type = self::POST_TYPES;
        $taxonomy = self::TAXONOMY;

        $q_vars = &$query->query_vars;
        if (isset($q_vars['post_type'], $q_vars[$taxonomy]) && $pagenow === 'edit.php' && $q_vars['post_type'] == $post_type && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    public function show_required_field_error_msg($post)
    {
        $taxonomy = self::TAXONOMY;
        $post_type = self::POST_TYPES;

        if ($post_type === get_post_type($post) && 'auto-draft' !== get_post_status($post)) {
            $pageType = wp_get_object_terms($post->ID, $taxonomy, ['orderby' => 'term_id', 'order' => 'ASC']);
            if (is_wp_error($pageType) || empty($pageType)) {
                printf(
                    '<div class="error below-h2"><p>%s</p></div>',
                    esc_html__('Page type is mandatory', 'offbeatwp')
                );
            }
        }
    }

    public function savePageType($post_id)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $this->isPageTypeSaved) {
            return;
        }

        $taxonomy = self::TAXONOMY;

        if (!isset($_POST['page_type'])) {
            return;
        }

        $pageType = sanitize_text_field($_POST['page_type']);

        if (empty($pageType)) {
            $postdata = [
                'ID' => $post_id,
                'post_status' => 'draft',
            ];
            wp_update_post($postdata);
        } else {
            $term = get_term_by('slug', $pageType, $taxonomy);

            if (!empty($term) && !is_wp_error($term)) {
                wp_set_object_terms($post_id, $term->term_id, $taxonomy, false);
            }
        }

        $this->isPageTypeSaved = true;
    }

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

        $terms->each(function ($term) use ($slug) {
            ?>
            <label title='<?php esc_attr_e($term->getName()); ?>'>
                <input type="radio" name="page_type" value="<?= $term->getSlug(); ?>" <?php checked($term->getSlug(), $slug); ?>>
                <span><?php esc_html_e($term->getName()); ?></span>
            </label><br>
            <?php
        });
    }
}
