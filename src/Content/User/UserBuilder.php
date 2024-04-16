<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use UnexpectedValueException;
use WP_User;

final class UserBuilder
{
    private readonly WP_User $wpUser;
    private ?string $newUserLogin = null;
    private array $metaInput = [];
    private array $metaToUnset = [];

    public function __construct(WP_User $wpUser) {
        $this->wpUser = $wpUser;
    }

    /** The user's email address. <b>Setting this is required.</b> */
    public function setEmail(string $email)
    {
        $this->wpUser->user_email = $email;
        return $this;
    }

    public function setNickname(string $nickname)
    {
        $this->wpUser->nickname = $nickname;
        return $this;
    }

    public function setDisplayName(string $displayName)
    {
        $this->wpUser->display_name = $displayName;
        return $this;
    }

    public function setFirstName(string $firstName)
    {
        $this->wpUser->first_name = $firstName;
        return $this;
    }

    public function setLastName(string $lastName)
    {
        $this->wpUser->last_name = $lastName;
        return $this;
    }

    public function setLogin(string $userLogin)
    {
        if (!$userLogin) {
            throw new InvalidArgumentException('Username cannot be empty');
        }

        if (strlen($userLogin) > 60) {
            throw new InvalidArgumentException('Username cannot be longer than 60 characters');
        }

        $username = wp_strip_all_tags($userLogin);
        $username = remove_accents($username);
        $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
        $username = preg_replace('/&.+?;/', '', $username);

        if ($userLogin !== $username) {
            throw new InvalidArgumentException($userLogin . ' is not a valid username. You could instead use: ' . $username);
        }

        if ($this->wpUser->user_login !== $userLogin) {
            $this->newUserLogin = $userLogin;
        }

        return $this;
    }

    public function setUrl(string $url)
    {
        $this->wpUser->user_url = $url;
        return $this;
    }

    /**
     * @param string $key Metadata name.
     * @param mixed $value The new metadata value.
     * @return $this
     */
    public function setMeta(string $key, $value)
    {
        $this->metaInput[$key] = $value;
        unset($this->metaToUnset[$key]);

        return $this;
    }

    public function unsetMeta(string $key)
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }

    /** @return int|\WP_Error */
    private function _save()
    {
        if (!$this->wpUser->user_email) {
            throw new UnexpectedValueException('A user cannot be registered without an e-mail address.');
        }

        if (!$this->wpUser->user_login) {
            $this->wpUser->user_login = $this->wpUser->user_email;
        }

        if (!$this->wpUser->user_pass) {
            $this->wpUser->user_pass = wp_generate_password(32);
        }

        $userData = $this->wpUser->to_array();
        $userData['meta_input'] = $this->metaInput;

        if ($this->wpUser->ID) {
            $userId = wp_update_user($userData);

            // Surprise, wp_update_user ignores updates to user_login!
            if (is_int($userId) && $this->newUserLogin) {
                global $wpdb;
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->users} SET user_login = %s WHERE ID = %d;", $this->newUserLogin, $userId));
                $this->newUserLogin = '';
            }
        } else {
            $userId = wp_insert_user($userData);
        }

        if (!is_int($userId)) {
            return $userId;
        }

        $wpUser = get_user_by('id', $userId);
        foreach ($this->wpUser->roles as $role) {
            $this->wpUser->set_role($role);
        }

        return $userId;
    }

    final public function save(): int
    {
        $result = $this->_save();
        return is_int($result) ? $result : 0;
    }

    final public function saveOrFail(): int
    {
        $result = $this->_save();

        if (!is_int($result)) {
            throw new OffbeatInvalidModelException('Failed to save UserModel: ' . $result->get_error_message());
        }

        return $result;
    }
}