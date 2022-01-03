<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Exceptions\UserModelException;
use WP_User;

class UserModel
{
    protected $wpUser;

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

    public function getWpUser(): WP_User
    {
        return $this->wpUser;
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