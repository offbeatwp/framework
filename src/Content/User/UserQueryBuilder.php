<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_User_Query;

/** @template TModel of UserModel */
class UserQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var array */
    protected $queryVars = [];
    /** @var class-string<UserModel> */
    protected $modelClass;

    /** @param class-string<TModel> $modelClass */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        if ($this->modelClass::definedUserRoles() !== null) {
            $this->whereRoleIn($this->modelClass::definedUserRoles());
        }
    }

    /** @return UserCollection<TModel> */
    protected function get(): UserCollection
    {
        do_action('offbeatwp/users/query/before_get', $this);

        $userQuery = $this->runQuery();

        return apply_filters('offbeatwp/users/query/get', new UserCollection($userQuery->get_results()), $this);
    }

    /** @return UserCollection<TModel> */
    public function all(): UserCollection
    {
        return $this->take(0);
    }

    /**
     * @param int $numberOfUsers
     * @return UserCollection
     */
    public function take(int $numberOfUsers): UserCollection
    {
        $this->queryVars['number'] = $numberOfUsers;

        return $this->get();
    }

    /** @return TModel|null */
    public function first(): ?UserModel
    {
        return $this->take(1)->first();
    }

    /** @return TModel|null */
    public function findById(?int $id): ?UserModel
    {
        if ($id < 0) {
            return null;
        }

        $this->queryVars['include'] = [$id];
        return $this->first();
    }

    /** @return TModel */
    public function findByIdOrFail(int $id): UserModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('UserModel with id ' . $id . ' could not be found');
        }

        return $result;
    }

    /**
     * @param string[]|string $properties Sort retrieved users by parameter. Defaults to <i>login</i>.
     * @param string $direction Either <i>ASC</i> for lowest to highest or <i>DESC</i> for highest to lowest. Defaults to <i>ASC</i>.
     * @return $this
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
     * @param string $email Must be a valid email address, or an exception will be thrown.
     * @return $this
     */
    public function whereEmail(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('whereEmail only accepts valid email strings.');
        }

        $this->queryVars['search'] = $email;
        $this->queryVars['search_columns'] = 'user_email';

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

    /**
     * @param positive-int $amount
     * @return $this
     */
    public function limit(int $amount): UserQueryBuilder
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Limit expects a positive number, but received {$amount}.");
        }

        $this->queryVars['number'] = $amount;
        return $this;
    }

    /** @return int[] */
    public function Ids(): array
    {
        $this->queryVars['fields'] = 'ID';
        return $this->runQuery()->get_results();
    }

    public function firstDisplayName(): ?string
    {
        $this->queryVars['fields'] = 'display_name';
        $this->queryVars['numbers'] = 1;
        return $this->runQuery()->get_results()[0] ?? null;
    }

    public function runQuery(): WP_User_Query
    {
        $query = new WP_User_Query($this->queryVars);

        self::$lastRequest = $query->request;

        return $query;
    }
}