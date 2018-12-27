<?php
namespace OffbeatWP\Support\Wordpress;

class AdminPage
{
    public static function make($title, $slug, $icon = '', $position = 30, $capabilities = null, $callback = null)
    {
        if (is_admin()) {
            if (is_null($callback)) {
                $callback = function () {echo __('No items linked', 'raow');};
            }

            if (is_null($capabilities)) {
                $capabilities = 'edit_posts';
            }

            if ($callback == 'controller') {
                $callback = [static::class, 'callbackController'];
            }

            add_action('admin_menu', function () use ($title, $slug, $icon, $position, $capabilities, $callback) {
                add_menu_page($title, $title, $capabilities, $slug, function () use ($callback) {
                    offbeat()->container->call($callback);
                }, $icon, $position);
            });
        }
    }

    public static function makeSub($parent, $title, $slug, $capabilities = null, $callback = null)
    {
        if (is_admin()) {
            if (is_null($callback)) {
                $callback = function () {echo __('No items linked', 'raow');};
            }

            if (is_null($capabilities)) {
                $capabilities = 'edit_posts';
            }

            if ($callback == 'controller') {
                $callback = [static::class, 'callbackController'];
            }

            $positions = [
                'dashboard'        => 'index.php',
                'posts'            => 'edit.php',
                'media'            => 'upload.php',
                'pages'            => 'edit.php?post_type=page',
                'comments'         => 'edit-comments.php',
                'appearance'       => 'themes.php',
                'plubins'          => 'plugins.php',
                'users'            => 'users.php',
                'tools'            => 'tools.php',
                'settings'         => 'options-general.php',
                'network-settings' => 'settings.php',
            ];

            if (isset($positions[$parent])) {
                $parent = $positions[$parent];
            } elseif (preg_match('/^post-type:(.*)/', $parent, $matches)) {
                $parent = 'edit.php?post_type=' . $matches[1];
            }

            add_action('admin_menu', function () use ($parent, $title, $slug, $capabilities, $callback) {
                add_submenu_page($parent, $title, $title, $capabilities, $slug, function () use ($callback) {
                    offbeat()->container->call($callback);
                });
            });
        }
    }

    public function callbackController()
    {
        offbeat()->run();
    }
}
