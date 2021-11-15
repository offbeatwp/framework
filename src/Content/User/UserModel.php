<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Exceptions\UserModelException;
use WP_User;

class UserModel
{
    protected $wpUser;

    /**
     * @throws UserModelException
     * @var WP_User|int
     */
    public function __construct($user)
    {
        if (is_int($user)) {
            $user = get_userdata($user);
        }

        if (!$user instanceof WP_User) {
            throw new UserModelException('UserModel constructor requires a WP_User as parameter, or an integer represesting the ID of an existing user.');
        }

        $this->wpUser = $user;
    }

    public function getWpUser(): WP_User
    {
        return $this->wpUser;
    }
}