<?php

namespace OffbeatWP\Support\Wordpress;

class AdminPage
{
    public static function make(string $title, string $slug, string $icon = '', int $position = 30, ?string $capability = null, $callback = null)
    {
        if (is_admin()) {
            if (is_null($capability)) {
                $capability = 'edit_posts';
            }

            if ($callback === 'controller') {
                $callback = [static::class, 'callbackController'];
            }

            if ($callback) {
                $callback = function () use ($callback) {
                    offbeat()->container->call($callback);
                };
            }

            add_action('admin_menu', function () use ($title, $slug, $icon, $position, $capability, $callback) {
                add_menu_page($title, $title, $capability, $slug, function () use ($callback) {
                    offbeat()->container->call($callback);
                }, $icon, $position);
            });
        }
    }

    public static function makeSub(string $parent, string $title, string $slug, ?string $capability = null, $callback = null, ?int $position = null)
    {
        if (is_admin()) {
            if (is_null($capability)) {
                $capability = 'edit_posts';
            }

            if ($callback === 'controller') {
                $callback = [static::class, 'callbackController'];
            }

            if (!empty($callback)) {
                $callback = function () use ($callback) {
                    offbeat()->container->call($callback);
                };
            }

            $positions = [
                'dashboard' => 'index.php',
                'posts' => 'edit.php',
                'media' => 'upload.php',
                'pages' => 'edit.php?post_type=page',
                'comments' => 'edit-comments.php',
                'appearance' => 'themes.php',
                'plubins' => 'plugins.php',
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

    public function callbackController()
    {
        offbeat()->findRoute();
        offbeat()->run();
    }
}
