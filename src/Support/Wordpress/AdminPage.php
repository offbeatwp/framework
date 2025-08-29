<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Foundation\App;

final class AdminPage
{
    /**
     * @param string $title The text to be displayed in the title tags of the page when the menu is selected.
     * @param string $slug The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters.
     * @param string $icon The URL to the icon to be used for this menu.
     * <br>Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme. This should begin with 'data:image/svg+xml;base64,'.
     * <br>Pass the name of a Dashicons helper class to use a font icon, e.g. 'dashicons-chart-pie'.
     * <br>Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
     * @param int $position The position in the menu order this item should appear.
     * @param string|null $capability The capability required for this menu to be displayed to the user. Default is 'edit_posts'.
     * @param callable|null|string $callback Optional. The function to be called to output the content for this page or the string <i>'controller'</i> to use a controller instead.
     * @return void
     */
    public static function make(string $title, string $slug, string $icon = '', int $position = 30, ?string $capability = null, $callback = null)
    {
        if (is_admin()) {
            if ($capability === null) {
                $capability = 'edit_posts';
            }

            if ($callback) {
                $callback = function () use ($callback) {
                    App::singleton()->container->call($callback);
                };
            }

            add_action('admin_menu', function () use ($title, $slug, $icon, $position, $capability, $callback) {
                add_menu_page($title, $title, $capability, $slug, function () use ($callback) {
                    App::singleton()->container->call($callback);
                }, $icon, $position);
            });
        }
    }

    /**
     * @param string $parent The slug name for the parent menu or the file name of a standard WordPress admin page.
     * @param string $title The text to be displayed in the title tags of the page when the menu is selected.
     * @param string $slug The slug name to refer to this menu by. Should be unique for this menu and only include lowercase alphanumeric, dashes, and underscores.
     * @param string|null $capability The capability required for this menu to be displayed to the user.
     * @param callable|null|string $callback Optional. The function to be called to output the content for this page or the string <i>'controller'</i> to use a controller instead.
     * @param int|null $position Optional. The position in the menu order this item should appear.
     * @return void
     */
    public static function makeSub(string $parent, string $title, string $slug, ?string $capability = null, $callback = null, ?int $position = null)
    {
        if (is_admin()) {
            if ($capability === null) {
                $capability = 'edit_posts';
            }

            if ($callback) {
                $callback = function () use ($callback) {
                    App::singleton()->container->call($callback);
                };
            }

            $positions = [
                'dashboard' => 'index.php',
                'posts' => 'edit.php',
                'media' => 'upload.php',
                'pages' => 'edit.php?post_type=page',
                'comments' => 'edit-comments.php',
                'appearance' => 'themes.php',
                'plugins' => 'plugins.php',
                'users' => 'users.php',
                'tools' => 'tools.php',
                'settings' => 'options-general.php',
                'network-settings' => 'settings.php',
            ];

            if (isset($positions[$parent])) {
                $parent = $positions[$parent];
            } elseif (preg_match('/^post-type:(.*)/', $parent, $matches)) {
                $parent = 'edit.php?post_type=' . $matches[1];
            }

            add_action('admin_menu', function () use ($parent, $title, $slug, $capability, $callback, $position) {
                add_submenu_page($parent, $title, $title, $capability, $slug, $callback, $position);
            });
        }
    }
}
