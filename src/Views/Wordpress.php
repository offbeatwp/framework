<?php

namespace OffbeatWP\Views;

use OffbeatWP\Support\Objects\WpImageSrc;
use WP_Post;
use WP_Site;

final class Wordpress
{
    public function head(): ?string
    {
        ob_start();
        wp_head();
        return ob_get_clean() ?: null;
    }

    public function footer(): ?string
    {
        ob_start();
        wp_footer();
        return ob_get_clean() ?: null;
    }

    public function title(): ?string
    {
        return wp_title('&raquo;', false);
    }

    public function languageAttributes(): string
    {
        return get_language_attributes();
    }

    public function archiveUrl(string $postType): ?string
    {
        return get_post_type_archive_link($postType) ?: null;
    }

    public function navMenu(array $args = []): ?string
    {
        $args['echo'] = false;
        return wp_nav_menu($args) ?: null;
    }

    public function homeUrl(): string
    {
        return get_home_url();
    }

    public function blogUrl(?int $id = null): string
    {
        return get_site_url($id);
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

    /**
     * Echo's the class names for the body element.
     * @param string|array $class
     * @return void
     */
    public function bodyClass(string|array $class = ''): void
    {
        body_class($class);
    }

    /**
     * Retrieve the output of an action as string.
     * @param string $actionHookName
     * @param mixed[] $args
     * @return string|null
     */
    public function action(string $actionHookName, $args = []): ?string
    {
        ob_start();
        do_action($actionHookName, $args);
        return ob_get_clean() ?: null;
    }

    /**
     * Searches content for shortcodes and filter shortcodes through their hooks.
     * If there are no shortcode tags defined, then the content will be returned without any filtering.<br>
     * This might cause issues when plugins are disabled but the shortcode will still show up in the post or content.<br>
     * @param string $code
     * @param bool $ignoreHtml
     * @return string
     */
    public function shortcode(string $code, bool $ignoreHtml = false): string
    {
        return do_shortcode($code, $ignoreHtml);
    }

    public function sidebar(int|string $name): ?string
    {
        ob_start();
        dynamic_sidebar($name);
        return ob_get_clean() ?: null;
    }

    /** @deprecated */
    public function getAllPostMeta(?int $postId = null)
    {
        if ($postId) {
            return get_post_meta($postId);
        }

        /** @global WP_Post $post */
        global $post;
        return ($post) ? get_post_meta($post->ID) : null;
    }

    /**
     * @param int $attachmentId Image attachment ID.
     * @param string|int[] $size Optional. Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
     * @param bool $icon Optional. Whether the image should fall back to a mime type icon. Default false.
     * @return WpImageSrc|null
     */
    public function getAttachmentImageSrc(int $attachmentId, string|array $size = 'thumbnail', bool $icon = false): ?WpImageSrc
    {
        $attachment = wp_get_attachment_image_src($attachmentId, $size, $icon);
        return ($attachment) ? new WpImageSrc($attachment) : null;
    }

    public function attachmentUrl(int $attachmentID, string|array $size = 'full'): ?string
    {
        $attachment = wp_get_attachment_image_src($attachmentID, $size);
        return ($attachment) ? $attachment[0] : null;
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

    public function getAttachmentImageSrcSet(int $attachmentId, array $sizes = ['thumbnail']): string
    {
        $srcSet = [];

        foreach ($sizes as $size) {
            $imageSrc = $this->getAttachmentImageSrc($attachmentId, $size);

            if ($imageSrc) {
                $srcSet[] = $imageSrc->getUrl() . ' ' . $imageSrc->getWidth() . 'w';
            }
        }

        return implode(', ', $srcSet);
    }

    /**
     * @deprecated
     * @param string|null $format
     * @param string|int $date
     * @param bool $strtotime
     * @return string
     */
    public function formatDate(?string $format, $date, bool $strtotime = false): string
    {
        $unixTimestamp = ($strtotime) ? strtotime($date) : $date;

        if (is_string($unixTimestamp)) {
            $unixTimestamp = is_numeric($unixTimestamp) ? (int)$unixTimestamp : false;
        }

        return date_i18n($format, $unixTimestamp);
    }

    public function resetPostdata(): void
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
