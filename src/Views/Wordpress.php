<?php

namespace OffbeatWP\Views;

use WP_Post;

class Wordpress
{
    public function head()
    {
        ob_start();
        wp_head();
        return ob_get_clean();
    }

    public function footer()
    {
        ob_start();
        wp_footer();
        return ob_get_clean();
    }

    public function title(): ?string
    {
        return wp_title('&raquo;', false);
    }

    public function languageAttributes()
    {
        return get_language_attributes();
    }

    public function archiveUrl(string $postType = 'post')
    {
        return get_post_type_archive_link($postType);
    }

    public function navMenu(array $args = [])
    {
        $args['echo'] = false;

        return wp_nav_menu($args);
    }

    public function homeUrl(): string
    {
        return get_home_url();
    }

    public function siteUrl(): string
    {
        return site_url();
    }

    public function blogId(): int
    {
        return get_current_blog_id();
    }

    public function bloginfo($name): string
    {
        return get_bloginfo($name, 'display');
    }

    public function bodyClass($class = '')
    {
        body_class($class);
    }

    public function action($action, $args = [])
    {
        ob_start();
        do_action($action, $args);
        return ob_get_clean();
    }

    public function shortcode($code): string
    {
        return do_shortcode($code);
    }

    public function sidebar($name)
    {
        ob_start();
        dynamic_sidebar($name);
        return ob_get_clean();
    }

    public function getAllPostMeta(int $postId = null)
    {
        if ($postId) {
            return get_post_meta($postId);
        }

        /** @global WP_Post $post */
        global $post;

        if (!$post) {
            return false;
        }

        return get_post_meta($post->ID);
    }

    public function attachmentUrl(int $attachmentID, $size = 'full')
    {
        $attachment = wp_get_attachment_image_src($attachmentID, $size);

        if (!$attachment) {
            return false;
        }

        return $attachment[0];
    }

    public function getAttachmentImage(int $attachmentID, $size = 'thumbnail', $classes = ['img-fluid']): string
    {
        return wp_get_attachment_image($attachmentID, $size, false, ['class' => implode(' ', $classes)]);
    }

    public function formatDate(string $format, $date, bool $strtotime = false): string
    {
        if ($strtotime) {
            $date = strtotime($date);
        }

        return date_i18n($format, $date);
    }

    public function resetPostdata()
    {
        wp_reset_postdata();
    }

    public function isFrontPage(): bool
    {
        return is_front_page();
    }

    public function templateUrl(string $path = null): string
    {
        $url = untrailingslashit(get_template_directory_uri());

        if (!is_null($path)) {
            $url .= $path;
        }

        return $url;
    }

    public function isSearchPage(): bool
    {
        return is_search();
    }

    public function getSearchQuery(): ?string
    {
        return ($this->isSearchPage()) ? get_search_query() : null;
    }

    public function getPageTitle(): string
    {
        return get_the_title();
    }

    public function getPermalink(): ?string
    {
        return get_permalink() ?: null;
    }
}
