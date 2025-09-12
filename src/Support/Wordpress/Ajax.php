<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Common\Singleton;

final class Ajax extends Singleton
{
    public static function isAjaxRequest(): bool
    {
        return wp_doing_ajax();
    }

    public function make(string $action, string $ajaxClass, bool $noPriv = true, bool $priv = true): void
    {
        if (!self::isAjaxRequest()) {
            return;
        }

        if ($priv) {
            add_action("wp_ajax_{$action}", function () use ($ajaxClass) {
                (new $ajaxClass())->execute();
                wp_die();
            });
        }

        if ($noPriv) {
            add_action("wp_ajax_nopriv_{$action}", function () use ($ajaxClass) {
                (new $ajaxClass())->execute();
                wp_die();
            });
        }
    }
}
