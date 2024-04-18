<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Content\Common\OffbeatObjectBuilder;
use OffbeatWP\Content\Common\WpObjectTypeEnum;
use OffbeatWP\Exceptions\OffbeatBuilderException;
use WP_Error;

final class UserBuilder extends OffbeatObjectBuilder
{
    private array $args;

    private function __construct(array $args) {
        $this->args = $args;
    }

    /**
     * The user's email address. <b>Setting this is required.</b>
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->args['user_email'] = $email;
        return $this;
    }

    /** @return $this */
    public function setNickname(string $nickname)
    {
        $this->args['nickname'] = $nickname;
        return $this;
    }

    /** @return $this */
    public function setDisplayName(string $displayName)
    {
        $this->args['display_name'] = $displayName;
        return $this;
    }

    /** @return $this */
    public function setFirstName(string $firstName)
    {
        $this->args['first_name'] = $firstName;
        return $this;
    }

    /** @return $this */
    public function setLastName(string $lastName)
    {
        $this->args['last_name'] = $lastName;
        return $this;
    }

    /** @return $this */
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

        $this->args['user_login'] = $userLogin;

        return $this;
    }

    /** @return $this */
    public function setUrl(string $url)
    {
        $this->args['user_url'] = $url;
        return $this;
    }

    public function save(): int
    {
        if (!$this->args['user_email']) {
            throw new OffbeatBuilderException('A user cannot be registered without an e-mail address.');
        }

        if (!$this->args['user_login']) {
            $this->args['user_login'] = $this->args['user_email'];
        }

        if (!$this->args['user_pass']) {
            $this->args['user_pass'] = wp_generate_password(32);
        }

        $isUpdate = array_key_exists('ID', $this->args);
        $resultId = $isUpdate ? wp_update_user($this->args) : wp_insert_user($this->args);
        if ($resultId instanceof WP_Error) {
            throw new OffbeatBuilderException('UserBuilder ' . ($isUpdate ? 'UPDATE' : 'INSERT') . ' failed: ' . $resultId->get_error_message());
        }

        if ($isUpdate && array_key_exists('user_login', $this->args)) {
            // Surprise, wp_update_user ignores updates to user_login!
            global $wpdb;
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->users} SET user_login = %s WHERE ID = %d;", $this->args['user_login'], $resultId));
        }

        $this->saveMeta($resultId);

        return $resultId;
    }

    protected function getObjectType(): WpObjectTypeEnum
    {
        Return WpObjectTypeEnum::USER;
    }

    /////////////////////
    // Factory methods //
    /////////////////////
    /** @pure */
    public static function create(): UserBuilder
    {
        return new UserBuilder([]);
    }

    /**
     * @pure
     * @param positive-int $userId The ID of the user.
     * @throws OffbeatBuilderException
     */
    public static function update(int $userId): UserBuilder
    {
        if ($userId <= 0) {
            throw new OffbeatBuilderException('UserBuilder update failed, invalid ID: ' . $userId);
        }

        return new UserBuilder(['ID' => $userId]);
    }
}