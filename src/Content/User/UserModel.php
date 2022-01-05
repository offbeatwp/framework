<?php

namespace OffbeatWP\Content\User;

use Carbon\Carbon;
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

        return $this->wpUser->$method ?? null;
    }

    ///////////////
    /// Getters ///
    ///////////////
    public function getWpUser(): WP_User
    {
        return $this->wpUser;
    }

    public function getId(): int
    {
        return $this->wpUser->ID;
    }

    /** @return string The user's login username. */
    public function getLogin(): string
    {
        return $this->wpUser->user_login;
    }

    /** @return string The URL-friendly user name. Defaults to first name + last name is not specified. */
    public function getDisplayName(): string
    {
        return $this->wpUser->display_name;
    }

    /** @return string The user's nickname. Default is the user's username. */
    public function getNickname(): string
    {
        return $this->wpUser->nickname;
    }

    /** @return string The user's display name. Default is the user's username. */
    public function getNiceName(): string
    {
        return $this->wpUser->user_nicename;
    }

    public function getFirstName(): string
    {
        return $this->wpUser->first_name;
    }

    public function getLastName(): string
    {
        return $this->wpUser->last_name;
    }

    /** @return string The plain-text user password. */
    public function getPassword(): string
    {
        return $this->wpUser->user_pass;
    }

    /** @return string Password reset key. Default empty. */
    public function getActivationKey(): string
    {
        return $this->wpUser->user_activation_key;
    }

    public function getUrl(): string
    {
        return $this->wpUser->user_url;
    }

    public function getEmail(): string
    {
        return $this->wpUser->user_email;
    }

    /** @return string The user's biographical description. */
    public function getDescription(): string
    {
        return $this->wpUser->description;
    }

    /** @return string User's locale. Default empty. */
    public function getLocale(): string
    {
        return $this->wpUser->locale;
    }

    /** @return Carbon Date the user registered as a Carbon Date. */
    public function getRegistrationDate(): Carbon
    {
        return Carbon::parse($this->wpUser->user_registered);
    }

    /** @return bool Returns true if the rich-editor is enabled for the user. */
    public function isRichEditingEnabled()
    {
        return $this->wpUser->rich_editing === 'true';
    }

    /** @return bool Returns true if rich code editing is enabled for the user. */
    public function isSyntaxHighlightingEnabled()
    {
        return $this->wpUser->syntax_highlighting === 'true';
    }

    /** @return bool Multisite only. Whether the user is marked as spam. Default false. */
    public function isSpam(): bool
    {
        return (bool)$this->wpUser->spam;
    }

    /** @return bool Returns true if this user is the currently logged in user. */
    public function isCurrentUser(): bool
    {
        return (get_current_user_id() && $this->wpUser->ID === get_current_user_id());
    }

    /** @return string[] User's roles */
    public function getRoles(): array
    {
        return $this->wpUser->roles;
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    /** @return static|null Returns the user that is currently logged in, or null if no user is logged in. */
    public static function getCurrentUser(): ?UserModel
    {
        return self::query()->findById(get_current_user_id());
    }

    /** @return static Returns the user that is currently logged in, or null if no user is logged in. */
    public static function getCurrentUserOrFail(): UserModel
    {
        return self::query()->findByIdOrFail(get_current_user_id());
    }

    /**
     * Only users match at least one of these roles will be queried.<br/>
     * Default return value is an empty array.
     * @return string[]
     */
    public static function definedUserRoles(): array
    {
        return [];
    }

    public static function query(): UserQueryBuilder
    {
        return new UserQueryBuilder(static::class);
    }
}