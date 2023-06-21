<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\User\UserModel;
use WP_User;

class User
{
    /**
     * Convert a user to the <b>first</b> matching UserModel or the preferred model, if provided
     * @param WP_User $user
     * @param class-string<UserModel>|'' $preferredModel
     * @return UserModel
     */
    public static function convertWpUserToModel(WP_User $user, string $preferredModel = ''): UserModel
    {
        $modelClass = null;

        foreach ($user->roles as $role) {
            $modelRoleClass = UserRole::getModelByUserRole($role);

            if ($modelRoleClass) {
                if (!$preferredModel || $preferredModel === $modelRoleClass) {
                    return new $modelRoleClass($user);
                }

                if (!$modelClass) {
                    $modelClass = $modelRoleClass;
                }
            }
        }

        $modelClass = $modelClass ?: UserRole::getDefaultUserModel();
        return new $modelClass($user);
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

    public static function removeUserColumn(string $slug): void
    {
        add_filter('manage_users_columns', static function (array $columnHeaders) use ($slug) {
            unset($columnHeaders[$slug]);
            return $columnHeaders;
        });
    }

    /**
     * @param string $slug
     * @param string $header
     * @param callable $callback Expects a callback that returns a string. Callback will get the following 3 args:<br>
     * string <b>$output</b><br>
     * string <b>$columnName</b><br>
     * int <b>$userId</b>
     * @return void
     */
    public static function addUserColumn(string $slug, string $header, callable $callback)
    {
        add_action('manage_users_columns', static function ($columnHeaders) use ($slug, $header) {
            $columnHeaders[$slug] = $header;
            return $columnHeaders;
        });

        add_action('manage_users_custom_column', static function ($output, $columnName, $userId) use ($slug, $callback) {
            if ($columnName === $slug) {
                $output = $callback($output, $columnName, $userId);
            }

            return $output;
        }, 10, 3);
    }
}