<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\User\UserModel;
use WP_User;

class User
{
    /** Convert a user to the <b>first</b> matching UserModel. */
    public static function convertWpUserToModel(WP_User $user): UserModel
    {
        foreach ($user->roles as $role) {
            $model = UserRole::getModelByUserRole($role);
            if ($model) {
                return new $model($user);
            }
        }

        return new UserModel($user);
    }

    /** @param int|WP_User $id */
    public static function get($id): ?UserModel
    {
        $user = is_int($id) ? get_userdata($id) : $id;

        if ($user) {
            return self::convertWpUserToModel($user);
        }

        return null;
    }
}