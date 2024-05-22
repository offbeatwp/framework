<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\User\UserModel;
use WP_User;

final class User
{
    /**
     * Convert a user to the <b>first</b> matching UserModel which also matches/extends the preferred model
     * <b>Beware:</b> It is possible that this method will NOT always return a class that extends the prefferred class, as it can return the default User Model.
     * @param WP_User $user
     * @param class-string<UserModel> $preferredModel
     * @return UserModel
     */
    public static function convertWpUserToModel(WP_User $user, string $preferredModel = UserModel::class): UserModel
    {
        $modelClass = null;

        foreach ($user->roles as $role) {
            $modelRoleClass = UserRole::getModelByUserRole($role);

            if ($modelRoleClass) {
                if (is_a($modelRoleClass, $preferredModel, true)) {
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

    /**
     * @param int|WP_User $id
     * @param class-string<UserModel> $preferredModel
     */
    public static function get($id, string $preferredModel = UserModel::class): ?UserModel
    {
        $user = is_int($id) ? get_userdata($id) : $id;

        if ($user) {
            return self::convertWpUserToModel($user, $preferredModel);
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
     * @param callable(string, string, int): string $callback Expects a callback that returns a string. Callback will get the following 3 args:<br>
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