<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\User\UserModelAbstract;

class UserRole
{
    /** @var class-string<UserModelAbstract>[] */
    private static array $userModels = [];
    /** @var class-string<UserModelAbstract> */
    private static string $defaultUserModel = UserModelAbstract::class;

    /**
     * @param string $userType
     * @param class-string<UserModelAbstract> $userModelClass
     */
    public static function registerUserRole(string $userType, string $userModelClass): void
    {
        self::$userModels[$userType] = $userModelClass;
    }

    /** @return class-string<UserModelAbstract> */
    public static function getModelByUserRole(string $userType): ?string
    {
        return self::$userModels[$userType] ?? null;
    }

    /** @param class-string<UserModelAbstract> $userModelClass */
    public static function getUserRoleByModel(string $userModelClass): string
    {
        return array_search($userModelClass, self::$userModels, true);
    }

    /** @param class-string<UserModelAbstract> $userModelClass */
    public static function setDefaultUserModel(string $userModelClass): void
    {
        self::$defaultUserModel = $userModelClass;
    }

    /** @return class-string<UserModelAbstract> */
    public static function getDefaultUserModel(): string
    {
        return self::$defaultUserModel;
    }
}