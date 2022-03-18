<?php

namespace OffbeatWP\Views;

use OffbeatWP\Support\Objects\OffbeatImageSrc;
use WP_Post;
use WP_Site;

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

    public function archiveUrl(?string $postType = 'post')
    {
        return get_post_type_archive_link($postType);
    }

    public function navMenu(?array $args = [])
    {
        $args['echo'] = false;

        return wp_nav_menu($args);
    }

    public function homeUrl(): string
    {
        return get_home_url();
    }

    public function blogUrl(?int $id = null): string
    {
        return get_site_url($id);
    }

    public function themeRootUrl(): string
    {
        return get_stylesheet_directory_uri();
    }

    public function siteUrl(): string
    {
        return site_url();
    }

    public function blogId(): int
    {
        return get_current_blog_id();
    }

    public function bloginfo(?string $name): string
    {
        return get_bloginfo($name, 'display');
    }

    public function blogDetails(int $id): ?WP_Site
    {
        return get_blog_details($id) ?: null;
    }

    public function bodyClass($class = '')
    {
        body_class($class);
    }

    public function action(?string $action, $args = [])
    {
        ob_start();
        do_action($action, $args);
        return ob_get_clean();
    }

    public function shortcode(?string $code): string
    {
        return do_shortcode($code);
    }

    public function sidebar($name)
    {
        ob_start();
        dynamic_sidebar($name);
        return ob_get_clean();
    }

    public function getAllPostMeta(?int $postId = null)
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

    /**
     * @param int $attachmentId Image attachment ID.
     * @param string|int[] $size Optional. Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
     * @param bool $icon Optional. Whether the image should fall back to a mime type icon. Default false.
     * @return OffbeatImageSrc|null
     */
    public function getAttachmentImageSrc(int $attachmentId, $size = 'thumbnail', bool $icon = false): ?OffbeatImageSrc
    {
        $attachment = wp_get_attachment_image_src($attachmentId, $size, $icon);
        return ($attachment) ? new OffbeatImageSrc($attachment) : null;
    }

    public function attachmentUrl(?int $attachmentID, $size = 'full')
    {
        $attachment = wp_get_attachment_image_src($attachmentID, $size);

        if (!$attachment) {
            return false;
        }

        return $attachment[0];
    }

    /**
     * @param int[]|string[]|string $attachmentID
     * @param int[]|string $size
     * @param string[] $classes
     */
    public function getAttachmentImage($attachmentID, $size = 'thumbnail', ?array $classes = ['img-fluid']): string
    {
        return wp_get_attachment_image($attachmentID, $size, false, ['class' => implode(' ', $classes)]);
    }

    public function getAttachmentImageSrcSet($attachmentId, $sizes = ['thumbnail']): string
    {
        $srcset = [];

        foreach ($sizes as $size) {
            $imageSrc = $this->getAttachmentImageSrc($attachmentId, $size);
            
            $srcset[] = $imageSrc->getUrl() . ' ' . $imageSrc->getWidth() . 'w';
        }

        return implode(', ', $srcset);
    }

    public function formatDate(?string $format, $date, bool $strtotime = false): string
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

    public function templateUrl(?string $path = null): string
    {
        $url = untrailingslashit(get_template_directory_uri());

        if ($path !== null) {
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
}
