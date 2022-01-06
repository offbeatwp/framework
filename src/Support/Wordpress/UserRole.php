<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\User\UserModel;

class UserRole
{
    public const DEFAULT_USER_MODEL = UserModel::class;

    /** @var class-string<UserModel>[] */
    private static $userModels = [];

    /**
     * @param string $userType
     * @param class-string<UserModel> $modelClass
     */
    public static function registerUserRole(string $userType, string $modelClass): void
    {
        self::$userModels[$userType] = $modelClass;
    }

    /** @return class-string<UserModel> */
    public static function getModelByUserRole(string $userType): ?string
    {
        return self::$userModels[$userType] ?? self::DEFAULT_USER_MODEL;
    }

    /** @param class-string<UserModel> $modelClass */
    public static function getUserRoleByModel(string $modelClass): string
    {
        return array_search($modelClass, self::$userModels, true);
    }
}