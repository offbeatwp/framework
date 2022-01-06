<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_User_Query;

class UserQueryBuilder extends AbstractQueryBuilder
{
    /** @var array */
    protected $queryVars = [];
    /** @var class-string<UserModel> */
    protected $modelClass;

    /** @param class-string<UserModel> $modelClass */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        if (!is_null($this->modelClass::definedUserRoles())) {
            $this->whereRoleIn($this->modelClass::definedUserRoles());
        }
    }

    /** @return UserCollection<UserModel> */
    protected function get(): UserCollection
    {
        do_action('offbeatwp/users/query/before_get', $this);

        $userQuery = new WP_User_Query($this->queryVars);

        return apply_filters('offbeatwp/users/query/get', new UserCollection($userQuery->get_results()), $this);
    }

    public function all(): UserCollection
    {
        return $this->take(0);
    }

    public function take(int $numberOfUsers): UserCollection
    {
        $this->queryVars['number'] = $numberOfUsers;

        return $this->get();
    }

    public function first(): ?UserModel
    {
        return $this->take(1)->first();
    }

    public function findById(int $id): ?UserModel
    {
        $this->queryVars['include'] = [$id];
        return $this->first();
    }

    public function findByIdOrFail(int $id): UserModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('UserModel with id ' . $id . ' could not be found');
        }

        return $result;
    }

    /**
     * @param string[]|string $properties  Sort retrieved users by parameter. Defaults to <i>login</i>.
     * @param string $direction Either <i>ASC</i> for lowest to highest or <i>DESC</i> for highest to lowest. Defaults to <i>ASC</i>.
     * @return $this;
     */
    public function orderBy($properties, string $direction = ''): UserQueryBuilder
    {
        $this->queryVars['orderby'] = $properties;

        if ($direction === 'ASC' || $direction === 'DESC') {
            $this->queryVars['order'] = $direction;
        } elseif ($direction !== '') {
            throw new InvalidQueryOperatorException('Attempted to use incorrect direction in UserQueryBuilder. Only ASC and DESC are valid.');
        }

        return $this;
    }

    /**
     * @param string[] $roles An array of role names that users must match to be included in results. Note that this is an inclusive list: users must match <i>each</i> role.
     * @return $this
     */
    public function withRoles(array $roles): UserQueryBuilder
    {
        $this->queryVars['role'] = $roles;
        return $this;
    }

    /**
     * @param string[] $roles An array of role names. Matched users must have at least one of these roles.
     * @return $this
     */
    public function whereRoleIn(array $roles): UserQueryBuilder
    {
        $this->queryVars['role__in'] = $roles;
        return $this;
    }

    /**
     * @param string[] $roles An array of role names to exclude. Users matching one or more of these roles will not be included in results.
     * @return $this
     */
    public function whereRoleNotIn(array $roles): UserQueryBuilder
    {
        $this->queryVars['role__not_in'] = $roles;
        return $this;
    }

    public function whereMeta(array $metaQueryArray): UserQueryBuilder
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $this->queryVars['meta_query'][] = $metaQueryArray;

        return $this;
    }

    public function whereMetaIs(string $metaKey, $value): UserQueryBuilder
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => '==', 'value' => $value]);
        return $this;
    }

    public function whereMetaIn(string $metaKey, array $values): UserQueryBuilder
    {
        $this->whereMeta(['key' => $metaKey, 'compare' => 'IN', 'value' => $values]);
        return $this;
    }
}