<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Content\Common\AbstractOffbeatModel;
use OffbeatWP\Helpers\ArrayHelper;
use OffbeatWP\Support\Wordpress\User;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;
use WP_Error;
use WP_User;

class UserModel extends AbstractOffbeatModel
{
    protected readonly WP_User $wpUser;
    /** @var mixed[]|null */
    private ?array $metas = null;

    final private function __construct(WP_User $user)
    {
        if ($user->ID <= 0) {
            throw new InvalidArgumentException('Cannot create UserModel object: Invalid ID ' . $user->ID);
        }

        if (!ArrayHelper::intersects($user->roles, self::definedUserRoles())) {
            throw new InvalidArgumentException('Cannot create UserModel object: User does not have the necessary role');
        }

        $this->wpUser = $user;
        $this->init();
    }

    /** This method is called at the end of the UserModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
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
    final public function getMetas(): array
    {
        if ($this->metas === null) {
            $this->metas = get_user_meta($this->getId()) ?: [];
        }

        return $this->metas;
    }

    final public function getMeta(string $key, bool $single = true): mixed
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
        return $this->wpUser->user_login;
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

    /**
     * Date the user registered as a Carbon Date.
     * @throws \Exception
     */
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

    final public function hasCapability(string $capabilityName): bool
    {
        return $this->wpUser->has_cap($capabilityName);
    }

    /** @return string[] Returns the user's roles */
    final public function getRoles(): array
    {
        return $this->wpUser->roles;
    }

    /** @return string[] */
    final public function getRoleNames(): array
    {
        return array_map(fn($r) => wp_roles()->role_names[$r] ?? '', $this->wpUser->roles);
    }

    /**  Returns the role at the specified index, or null if no role exists at the specified index. */
    final public function getRole(int $index): ?string
    {
        return $this->getRoles()[$index] ?? null;
    }

    final public function getRoleLabel(int $index): ?string
    {
        return wp_roles()->role_names[$this->getRole($index)] ?? null;
    }

    final public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * Log in as this user. Only works if used is not already logged in.
     * @return bool <b>true</b> on success, <b>false</b> if the user is already logged in.
     */
    final public function loginAsUser(): bool
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
    final public function retrievePassword(): ?WP_Error
    {
        $error = retrieve_password($this->getLogin());
        return ($error instanceof WP_Error) ? $error : null;
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    /** Returns the user that is currently logged in, or null if no user is logged in. */
    final public static function getCurrentUser(): ?static
    {
        return self::query()->findById(get_current_user_id());
    }

    /** Returns the user that is currently logged in, or throws an exception if no user is logged in. */
    final public static function getCurrentUserOrFail(): static
    {
        return self::query()->findByIdOrFail(get_current_user_id());
    }

    /** Returns a user with the specified email address */
    final public static function findByEmail(string $email): ?static
    {
        $wpUser = get_user_by('email', $email);

        if ($wpUser) {
            $user = User::convertWpUserToModel($wpUser);

            if ($user instanceof static) {
                return $user;
            }
        }

        return null;
    }

    final public function isUsingDefaultPassword(): bool
    {
        return $this->getMetaBool('default_password_nag');
    }

    /**
     * Only users that match at least one of these roles will be queried.<br/>
     * Generally, you'll only want to return <b>one</b> role unless the model class is abstract.
     * @return string[]
     */
    public static function definedUserRoles(): array
    {
        return [];
    }

    /** @return mixed[] */
    public static function defaultQueryArgs(): array
    {
        return ['number' => 0];
    }

    /** @return UserQueryBuilder<static> */
    final public static function query(): UserQueryBuilder
    {
        return new UserQueryBuilder(static::class);
    }
}