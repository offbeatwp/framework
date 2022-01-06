<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Support\Wordpress\UserRole;

class UserRoleBuilder
{
    private $roleName;
    private $roleDisplayName;
    /** @var class-string<UserModel> */
    private $modelClass;

    private function __construct(string $roleName, string $roleDisplayName)
    {
        $this->roleName = $roleName;
        $this->roleDisplayName = $roleDisplayName;
    }

    /**
     * @param class-string<UserModel> $modelClass
     * @return UserRoleBuilder
     */
    public function model(string $modelClass): UserRoleBuilder
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public static function make(string $roleName, string $roleDisplayName): UserRoleBuilder
    {
        return new static($roleName, $roleDisplayName);
    }

    public function set(): void
    {
        add_role($this->roleName, $this->roleDisplayName);

        if (!is_null($this->modelClass)) {
            UserRole::registerUserRole($this->roleName, $this->modelClass);
        }
    }
}