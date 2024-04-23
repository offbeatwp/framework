<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Content\User\UserModel;

class Context
{
    /** @var array{user?: ?UserModel, post?: ?PostModel, term?: ?TermModel} */
    protected static array $context = [];

    final public static function user(): ?UserModel
    {
        if (!array_key_exists('user', self::$context)) {
            self::$context['user'] = UserModel::getCurrentUser();
        }

        return self::$context['user'];
    }

    final public static function post(): ?PostModel
    {
        if (!array_key_exists('post', self::$context)) {
            self::$context['post'] = UserModel::getCurrentUser();
        }

        return self::$context['post'];
    }

    final public static function term(): ?TermModel
    {
        if (!array_key_exists('term', self::$context)) {
            self::$context['term'] = UserModel::getCurrentUser();
        }

        return self::$context['term'];
    }
}