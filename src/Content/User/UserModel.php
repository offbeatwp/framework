<?php

namespace OffbeatWP\Content\User;

use BadMethodCallException;
use InvalidArgumentException;
use OffbeatWP\Content\Common\OffbeatModel;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use OffbeatWP\Exceptions\UserModelException;
use OffbeatWP\Support\Wordpress\User;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;
use UnexpectedValueException;
use WP_Error;
use WP_User;

class UserModel extends OffbeatModel
{
    protected WP_User $wpUser;
    /** @var mixed[]|null */
    protected ?array $metas = null;
    /** @var mixed[] */
    protected array $metaInput = [];
    /** @var ("")[] */
    protected array $metaToUnset = [];
    private string $newUserLogin = '';
    private bool $isInitialised = false;

    use BaseModelTrait;
    use GetMetaTrait;

    /** @param WP_User|null $user */
    final private function __construct($user = null)
    {
        if ($user === null) {
            $user = new WP_User();
            $this->metas = [];
        }

        if (is_int($user)) {
            trigger_error('Constructed UserModel with integer. Use UserModel::find instead.', E_USER_DEPRECATED);
            $userData = get_userdata($user);

            if (!$userData) {
                throw new OffbeatModelNotFoundException('Could not find WP_User with ID ' . $user);
            }

            $user = $userData;
        }

        if (!$user instanceof WP_User) {
            throw new UserModelException('UserModel constructor requires a WP_User as parameter, or an integer representing the ID of an existing user.');
        }

        $this->wpUser = $user;
        $this->init();
        $this->isInitialised = true;
    }

    /** This method is called at the end of the UserModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
    }

    public function __clone()
    {
        $this->wpUser = clone $this->wpUser;
    }

    ///////////////
    /// Setters ///
    ///////////////
    /** The user's email address. <b>Setting this is required.</b> */
    final public function setEmail(string $email): self
    {
        $this->wpUser->user_email = $email;
        return $this;
    }

    final public function setNickname(string $nickname): self
    {
        $this->wpUser->nickname = $nickname;
        return $this;
    }

    final public function setDisplayName(string $displayName): self
    {
        $this->wpUser->display_name = $displayName;
        return $this;
    }

    final public function setFirstName(string $firstName): self
    {
        $this->wpUser->first_name = $firstName;
        return $this;
    }

    final public function setLastName(string $lastName): self
    {
        $this->wpUser->last_name = $lastName;
        return $this;
    }

    final public function setLogin(string $userLogin): self
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

    final public function setUrl(string $url): self
    {
        $this->wpUser->user_url = $url;
        return $this;
    }

    /**
     * @param string $key Metadata name.
     * @param mixed $value The new metadata value.
     * @return $this
     */
    final public function setMeta(string $key, $value): self
    {
        $this->metaInput[$key] = $value;
        unset($this->metaToUnset[$key]);

        return $this;
    }

    final public function unsetMeta(string $key): self
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }

    ///////////////
    /// Getters ///
    ///////////////
    final public function getWpUser(): WP_User
    {
        return $this->wpUser;
    }

    final public function getId(): int
    {
        return $this->wpUser->ID;
    }

    /** @return mixed[] */
    public function getMetas(): array
    {
        if ($this->metas === null) {
            $this->metas = get_user_meta($this->getId()) ?: [];
        }

        return $this->metas;
    }

    /** @return mixed|null */
    public function getMeta(string $key, bool $single = true)
    {
        $metas = $this->getMetas();

        if (isset($metas[$key])) {
            return ($single && is_array($metas[$key])) ? reset($metas[$key]) : $metas[$key];
        }

        return null;
    }

    /** The user's login username. */
    final public function getLogin(): string
    {
        return $this->newUserLogin ?: $this->wpUser->user_login;
    }

    /** The URL-friendly user name. Defaults to first name + last name. */
    final public function getDisplayName(): string
    {
        return $this->wpUser->display_name;
    }

    /** The user's nickname. Default is the user's username. */
    final public function getNickname(): string
    {
        return $this->wpUser->nickname;
    }

    /** The user's display name. Default is the user's username. */
    final public function getNiceName(): string
    {
        return $this->wpUser->user_nicename;
    }

    final public function getFirstName(): string
    {
        return $this->wpUser->first_name;
    }

    final public function getLastName(): string
    {
        return $this->wpUser->last_name;
    }

    final public function getPassword(): string
    {
        return $this->wpUser->user_pass;
    }

    /** Password reset key. Default empty. */
    final public function getActivationKey(): string
    {
        return $this->wpUser->user_activation_key;
    }

    final public function getUrl(): string
    {
        return $this->wpUser->user_url;
    }

    /** The URL of the avatar on success, null on failure. */
    public function getAvatarUrl(): ?string
    {
        $url = get_avatar_url($this->getId());
        return ($url !== false) ? $url : null;
    }

    /** The user's email. */
    final public function getEmail(): string
    {
        return $this->wpUser->user_email;
    }

    /** The user's biographical description. */
    final public function getDescription(): string
    {
        return $this->wpUser->description;
    }

    /** User's locale. Default empty. */
    final public function getLocale(): string
    {
        return $this->wpUser->locale;
    }

    final public function getEditLink(): string
    {
        return get_edit_user_link($this->getId());
    }

    /** Date the user registered as a Carbon Date. */
    final public function getRegistrationDate(): ?WpDateTimeImmutable
    {
        return ($this->wpUser->user_registered) ? new WpDateTimeImmutable($this->wpUser->user_registered) : null;
    }

    /** Whether the user has the rich-editor enabled for writing. */
    final public function isRichEditingEnabled(): bool
    {
        return $this->wpUser->rich_editing === 'true';
    }

    /** Whether the user has syntax highlighting enabled when editing code. */
    public function isSyntaxHighlightingEnabled(): bool
    {
        return $this->wpUser->syntax_highlighting === 'true';
    }

    /** Whether the user is marked as spam. Multisite only. Default false. */
    final public function isSpam(): bool
    {
        return (bool)$this->wpUser->spam;
    }

    /** Whether this user is the currently logged in user. */
    final public function isCurrentUser(): bool
    {
        return (get_current_user_id() && $this->wpUser->ID === get_current_user_id());
    }

    public function hasCapability(string $capabilityName): bool
    {
        return $this->wpUser->has_cap($capabilityName);
    }

    /** @return string[] Returns the user's roles */
    public function getRoles(): array
    {
        return $this->wpUser->roles;
    }

    /** @return string[] */
    public function getRoleLabels(): array
    {
        $roles = [];

        foreach ($this->wpUser->roles as $role) {
            $roles[] = wp_roles()->role_names[$role] ?? '';
        }

        return $roles;
    }

    /**  Returns the role at the specified index, or null if no role exists at the specified index. */
    public function getRole(int $index): ?string
    {
        return $this->getRoles()[$index] ?? null;
    }

    public function getRoleLabel(int $index): string
    {
        return wp_roles()->role_names[$this->getRole($index)] ?? '';
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    ///////////////////////
    /// Special Methods ///
    ///////////////////////
    private function _save(): int|WP_Error
    {
        if (!$this->isInitialised) {
            throw new BadMethodCallException('The save method cannot be called before a model is initialised.');
        }

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

        if ($this->getId()) {
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
        if ($wpUser) {
            $this->wpUser = $wpUser;
        }

        $roles = static::definedUserRoles();
        if ($roles) {
            foreach ($roles as $role) {
                $this->wpUser->set_role($role);
            }
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

    /**
     * Log in as this user. Only works if used is not already logged in.
     * @return bool <b>true</b> on success, <b>false</b> if the user is already logged in.
     */
    public function loginAsUser(): bool
    {
        if (!is_user_logged_in() && $this->getId()) {
            wp_clear_auth_cookie();
            wp_set_current_user($this->getId());
            wp_set_auth_cookie($this->getId());
            return true;
        }

        return false;
    }

    /** Handles sending a password retrieval email to a user. */
    public function retrievePassword(): ?WP_Error
    {
        $error = retrieve_password($this->getLogin());
        return ($error instanceof WP_Error) ? $error : null;
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    /** @return static|null Returns the user that is currently logged in, or null if no user is logged in. */
    public static function getCurrentUser()
    {
        return self::query()->findById(get_current_user_id());
    }

    /** @return static Returns the user that is currently logged in, or throws an exception if no user is logged in. */
    public static function getCurrentUserOrFail()
    {
        return self::query()->findByIdOrFail(get_current_user_id());
    }

    /** @return static|null Returns a user with the specified email address */
    public static function findByEmail(string $email)
    {
        $wpUser = get_user_by('email', $email);

        if ($wpUser) {
            $user = User::convertWpUserToModel($wpUser);

            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if ($user instanceof static) {
                return $user;
            }
        }

        return null;
    }

    public function isUsingDefaultPassword(): bool
    {
        return $this->getMetaBool('default_password_nag');
    }

    /**
     * Only users that match at least one of these roles will be queried.<br/>
     * Generally, you'll only want to return <b>one</b> role unless the model class is abstract.<br/>
     * Default return value is null.
     * @return string[]|null
     */
    public static function definedUserRoles(): ?array
    {
        return null;
    }

    /** @return UserCollection<static> */
    public static function all(): UserCollection
    {
        return static::query()->get();
    }

    /** @return UserQueryBuilder<static> */
    public static function query(): UserQueryBuilder
    {
        return new UserQueryBuilder(static::class);
    }

    /**
     * Inserts a new user into the WordPress database.<br>
     * This function is used when a new user registers through WordPress’ Login Page.<br>
     * It requires a valid username and email address but doesn’t allow to choose a password, generating a random one using wp_generate_password().
     * @param string $userEmail User's email address to send password.
     * @param string $userLogin User's username for logging in. Default to email if omitted.
     * @return static|null Returns the registered user if the user was registered successfully.
     */
    public static function registerNewUser(string $userEmail, string $userLogin = ''): ?UserModel
    {
        $result = register_new_user($userLogin ?: $userEmail, $userEmail);

        if (!is_int($result)) {
            return null;
        }

        // Assign the appropriate roles defined by this class.
        if (static::definedUserRoles()) {
            $wpUser = get_user_by('ID', $result);
            $wpUser->set_role('');

            foreach(static::definedUserRoles() as $role) {
                $wpUser->add_role($role);
            }

            return new static($wpUser);
        }

        return static::find($result);
    }

    final public function delete(?int $reassign = null): bool
    {
        if ($this->getId()) {
            return wp_delete_user($this->getId(), $reassign);
        }

        return false;
    }
}