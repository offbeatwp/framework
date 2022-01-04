<?php

namespace OffbeatWP\Content\User;

use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Traits\FindModelTrait;
use OffbeatWP\Exceptions\UserModelException;
use WP_User;

class UserModel
{
    protected $wpUser;

    use FindModelTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    /** @var WP_User|int|null */
    public function __construct($user = null)
    {
        if ($user === null) {
            $user = new WP_User();
        }

        if (is_int($user)) {
            $user = get_userdata($user);
        }

        if (!$user instanceof WP_User) {
            throw new UserModelException('UserModel constructor requires a WP_User as parameter, or an integer representing the ID of an existing user.');
        }

        $this->wpUser = $user;
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (isset($this->wpPost->$method)) {
            return $this->wpPost->$method;
        }

        if (!is_null($hookValue = offbeat('hooks')->applyFilters('post_attribute', null, $method, $this))) {
            return $hookValue;
        }

        return null;
    }

    public function __get($name)
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

    public function __isset($name): bool
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        return method_exists($this, $methodName);
    }

    public function getWpUser(): WP_User
    {
        return $this->wpUser;
    }

    public function getId(): int
    {
        return $this->wpUser->ID;
    }

    public function getDisplayName(): string
    {
        return $this->wpUser->display_name;
    }

    public function getFirstName(): string
    {
        return $this->wpUser->first_name;
    }

    public function getLastName(): string
    {
        return $this->wpUser->last_name;
    }

    public function getLogin(): string
    {
        return $this->wpUser->user_login;
    }

    public function getEmail(): string
    {
        return $this->wpUser->user_email;
    }

    /** Returns true if this user is the currently logged in user. */
    public function isCurrentUser(): bool
    {
        return (get_current_user_id() && $this->wpUser->ID === get_current_user_id());
    }

    /**
     * Returns the user that is currently logged in, or null if no user is logged in.
     * @return static|null
     */
    public static function getCurrentUser(): ?UserModel
    {
        return self::query()->findById(get_current_user_id());
    }

    /**
     * Returns the user that is currently logged in, or null if no user is logged in.
     * @return static
     */
    public static function getCurrentUserOrFail(): UserModel
    {
        return self::query()->findByIdOrFail(get_current_user_id());
    }

    public static function query(): UserQueryBuilder
    {
        return new UserQueryBuilder();
    }
}