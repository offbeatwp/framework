<?php
namespace OffbeatWP\Support\Wordpress;

class Ajax {
    public static function isAjaxRequest()
    {
        return wp_doing_ajax();
    }

    public function make($action, $ajaxClass, $noPriv = true, $priv = true)
    {
        if (!self::isAjaxRequest()) {
            return null;
        }

        if ($priv) {
            add_action("wp_ajax_{$action}", function () use ($ajaxClass) {
                container()->call([$ajaxClass, 'execute']);

                wp_die();
            });
        }

        if ($noPriv) {
            add_action("wp_ajax_nopriv_{$action}", function () use ($ajaxClass) {
                container()->call([$ajaxClass, 'execute']);

                wp_die();
            });
        }
    }
}