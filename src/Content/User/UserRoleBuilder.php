<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Support\Wordpress\UserRole;

final class UserRoleBuilder
{
    /** @var class-string<UserModel>|null */
    private ?string $modelClass = null;
    private string $roleName;
    private string $roleDisplayName;
    private array $capabilities = [];

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
        if (!is_a($modelClass, UserModel::class, true)) {
            throw new InvalidArgumentException($modelClass . ' does not extend UserModel.');
        }

        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Add a capability to a role. The default WordPress capabilities include:<br>
     * activate_plugins
     * delete_others_pages
     * delete_others_posts
     * delete_pages
     * delete_posts
     * delete_private_pages
     * delete_private_posts
     * delete_published_pages
     * delete_published_posts
     * edit_dashboard
     * edit_others_pages
     * edit_others_posts
     * edit_pages
     * edit_posts
     * edit_private_pages
     * edit_private_posts
     * edit_published_pages
     * edit_published_posts
     * edit_theme_options
     * export
     * import
     * list_users
     * manage_categories
     * manage_links
     * manage_options
     * moderate_comments
     * promote_users
     * publish_pages
     * publish_posts
     * read_private_pages
     * read_private_posts
     * remove_users
     * switch_themes
     * upload_files
     * customize
     * delete_site
     * @param string $capabilityName
     * @param bool $hasCapability
     * @return $this
     */
    public function setCapability(string $capabilityName, bool $hasCapability): UserRoleBuilder
    {
        $this->capabilities[$capabilityName] = $hasCapability;
        return $this;
    }

    public static function make(string $roleName, string $roleDisplayName = ''): UserRoleBuilder
    {
        return new static($roleName, $roleDisplayName ?: $roleName);
    }

    public function set(): void
    {
        add_role($this->roleName, $this->roleDisplayName, $this->capabilities);

        if ($this->modelClass !== null) {
            UserRole::registerUserRole($this->roleName, $this->modelClass);
        }
    }
}