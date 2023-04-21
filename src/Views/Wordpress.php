<?php

namespace OffbeatWP\Views;

use OffbeatWP\Content\User\UserModel;
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

    /**
     * Gets the language attributes for the 'html' tag.
     * Builds up a set of HTML attributes containing the text direction and language information for the page.
     * @return string
     */
    public function languageAttributes()
    {
        return get_language_attributes();
    }

    /**
     * Retrieves the permalink for a post type archive.
     * @param string|null $postType
     * @return false|string
     */
    public function archiveUrl(?string $postType = 'post')
    {
        return get_post_type_archive_link($postType);
    }

    /**
     * Returns a navigation menu.
     * @param array|null $args Optional. Array of nav menu arguments.
     * @return false|string|null
     */
    public function navMenu(?array $args = [])
    {
        $args['echo'] = false;
        return wp_nav_menu($args);
    }

    /**
     * Retrieves the URL for the current site.<br>
     * Returns the 'home' option with the appropriate protocol.<br>
     * The protocol will be 'https' if is_ssl() evaluates to true; otherwise, it will be the same as the 'home' option.
     */
    public function homeUrl(): string
    {
        return get_home_url();
    }

    /**
     * Retrieves the URL for a given site where WordPress application files (e.g. wp-blog-header.php or the wp-admin/ folder) are accessible.<br>
     * Returns the 'site_url' option with the appropriate protocol, 'https' if is_ssl() and 'http' otherwise.
     * @param int|null $id
     * @return string
     */
    public function blogUrl(?int $id = null): string
    {
        return get_site_url($id);
    }

    /** Retrieves stylesheet directory URI for the active theme. */
    public function themeRootUrl(): string
    {
        return get_stylesheet_directory_uri();
    }

    /**
     * Retrieves the URL for the current site where WordPress application files (e.g. wp-blog-header.php or the wp-admin/ folder) are accessible.<br>
     * Returns the 'site_url' option with the appropriate protocol, 'https' if is_ssl() and 'http' otherwise.
     * @return string
     */
    public function siteUrl(): string
    {
        return site_url();
    }

    /** Retrieve the current site ID. */
    public function blogId(): int
    {
        return get_current_blog_id();
    }

    /**
     * Retrieves information about the current site.<br>
     * Possible values for <b>$show</b> include:<br>
     * - 'name' - Site title (set in Settings > General)
     * - 'description' - Site tagline (set in Settings > General)
     * - 'wpurl' - The WordPress address (URL) (set in Settings > General)
     * - 'url' - The Site address (URL) (set in Settings > General)
     * - 'admin_email' - Admin email (set in Settings > General)
     * - 'charset' - The "Encoding for pages and feeds"  (set in Settings > Reading)
     * - 'version' - The current WordPress version
     * - 'html_type' - The content-type (default: "text/html"). Themes and plugins
     *   can override the default value using the {@see 'pre_option_html_type'} filter
     * - 'text_direction' - The text direction determined by the site's language. is_rtl()
     *   should be used instead
     * - 'language' - Language code for the current site
     * - 'stylesheet_url' - URL to the stylesheet for the active theme. An active child theme
     *   will take precedence over this value
     * - 'stylesheet_directory' - Directory path for the active theme.  An active child theme
     *   will take precedence over this value
     * - 'template_url' / 'template_directory' - URL of the active theme's directory. An active
     *   child theme will NOT take precedence over this value
     * - 'pingback_url' - The pingback XML-RPC file URL (xmlrpc.php)
     * - 'atom_url' - The Atom feed URL (/feed/atom)
     * - 'rdf_url' - The RDF/RSS 1.0 feed URL (/feed/rdf)
     * - 'rss_url' - The RSS 0.92 feed URL (/feed/rss)
     * - 'rss2_url' - The RSS 2.0 feed URL (/feed)
     * - 'comments_atom_url' - The comments Atom feed URL (/comments/feed)
     * - 'comments_rss2_url' - The comments RSS 2.0 feed URL (/comments/feed)
     *
     * @param string|null $show   Optional. Site info to retrieve. Default empty (site name).
     * @return string Mostly string values, might be empty.
     */
    public function bloginfo(?string $show): string
    {
        return get_bloginfo($show ?? '', 'display');
    }

    /** Retrieve the details for a blog from the blogs table and blog options. */
    public function blogDetails(int $id): ?WP_Site
    {
        return get_blog_details($id) ?: null;
    }

    /** Echos the class names for the body element. */
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

    /**
     * Searches content for shortcodes and filter shortcodes through their hooks.<br>
     * If there are no shortcode tags defined, then the content will be returned without any filtering.<br>
     * This might cause issues when plugins are disabled but the shortcode will still show up in the post or content.
     * @param string|null $code
     * @return string
     */
    public function shortcode(?string $code): ?string
    {
        if ($code === null) {
            trigger_error('Wordpress::shortcode expects a string as parameter but received NULL.', E_USER_DEPRECATED);
        }

        return do_shortcode($code);
    }

    /**
     * @param int|string $name
     * @return false|string
     */
    public function sidebar($name)
    {
        ob_start();
        dynamic_sidebar($name);
        return ob_get_clean();
    }

    /**
     * @param int|null $postId
     * @return false|mixed
     */
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

    /**
     * Retrieves the URL for an attachment
     * @param int $attachmentId Attachment ID
     * @return string|null Attachment URL, otherwise <i>NULL</i>
     * @see wp_get_attachment_url()
     */
    public function getAttachmentUrl(int $attachmentId): ?string
    {
        return wp_get_attachment_url($attachmentId) ?: null;
    }

    /**
     * @deprecated Use getAttachmentImage instead, or getAttachmentUrl if you want to retrieve a file attachment
     * @param int|null $attachmentID
     * @param string|int[] $size
     * @return false|string
     */
    public function attachmentUrl(?int $attachmentID, $size = 'full')
    {
        if ($attachmentID === null) {
            trigger_error('Wordpress::attachmentUrl expects an integer as parameter, but received NULL.', E_USER_DEPRECATED);
        }

        $attachment = wp_get_attachment_image_src($attachmentID, $size);
        if (!$attachment) {
            return false;
        }

        return $attachment[0];
    }

    /**
     * Gets an HTML img element representing an image attachment.<br>
     * While <b>$size/b> will accept an array, it is better to register a size with add_image_size() so that a cropped version is generated.<br>
     * It's much more efficient than having to find the closest-sized image and then having the browser scale down the image.
     * @param int $attachmentID
     * @param int[]|string $size
     * @param string[] $classes
     */
    public function getAttachmentImage($attachmentID, $size = 'thumbnail', ?array $classes = ['img-fluid']): string
    {
        if (!is_int($attachmentID)) {
            trigger_error('Wordpress::getAttachmentImage expects an integer as parameter.', E_USER_DEPRECATED);
        }

        return wp_get_attachment_image($attachmentID, $size, false, ['class' => implode(' ', $classes)]);
    }

    /**
     * @param int $attachmentId
     * @param int[][]|string[] $sizes
     * @return string
     */
    public function getAttachmentImageSrcSet(int $attachmentId, $sizes = ['thumbnail']): string
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

    /** @deprecated */
    public function formatDate(?string $format, $date, bool $strtotime = false): string
    {
        if ($strtotime) {
            $date = strtotime($date);
        }

        return date_i18n($format, $date);
    }

    /** After looping through a separate query, this function restores the $post global to the current post in the main query. */
    public function resetPostdata(): void
    {
        wp_reset_postdata();
    }

    /**
     * Determines whether the query is for the front page of the site.<br>
     * This is for what is displayed at your site's main URL.<br>
     * Depends on the site's “Front page displays” Reading Settings 'show_on_front' and 'page_on_front'.<br>
     * If you set a static page for the front page of your site, this function will return true when viewing that page.<br>
     * Otherwise the same as is_home().
     * @see is_home()
     */
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

    /** Determines whether the query is for a search. */
    public function isSearchPage(): bool
    {
        return is_search();
    }

    /**
     * Retrieves the contents of the search WordPress query variable.<br>
     * The search query string is passed through esc_attr() to ensure that it is safe for placing in an HTML attribute.
     * @see esc_attr()
     */
    public function getSearchQuery(): ?string
    {
        return ($this->isSearchPage()) ? get_search_query() : null;
    }

    /**
     * Retrieves the post title.<br>
     * If the post is protected and the visitor is not an admin, then “Protected” will be inserted before the post title.<br>
     * If the post is private, then “Private” will be inserted before the post title.
     * @return string
     */
    public function getPageTitle(): string
    {
        return get_the_title();
    }

    /**
     * Display the search form.
     * @param string $ariaLabel ARIA label for the search form. Useful to distinguish multiple search forms on the same page and improve accessibility.
     * @return string
     */
    public function getSearchForm(string $ariaLabel = ''): string
    {
        return get_search_form(['echo' => false, 'aria_label' => $ariaLabel]);
    }

    /** Retrieve the ID of the current item in the WordPress Loop. */
    public function getQueriedObjectId(): int
    {
        return get_queried_object_id();
    }

    /** Retrieves the customisable header image url. */
    public function getHeaderImageUrl(): ?string
    {
        return get_header_image() ?: null;
    }

    /** Test if the current browser runs on a mobile device (smart phone, tablet, etc.) */
    public function isMobile(): bool
    {
        return wp_is_mobile();
    }

    public function getCurrentUser(): ?UserModel
    {
        return UserModel::getCurrentUser();
    }
}
