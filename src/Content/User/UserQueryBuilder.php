<?php

namespace OffbeatWP\Content\User;

use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Exceptions\InvalidQueryOperatorException;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use UnexpectedValueException;
use WP_User_Query;

/** @template TValue of UserModel */
final class UserQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var class-string<UserModel> */
    protected string $modelClass;
    /** @var array<string, mixed> */
    protected array $queryVars = ['number' => 0];
    protected bool $skipOnLimit = false;
    protected bool $skipOnInclude = false;

    /** @param class-string<TValue> $modelClass */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        if ($this->modelClass::definedUserRoles() !== null) {
            $this->whereRoleIn($this->modelClass::definedUserRoles());
        }
    }

    /** @return UserCollection<int, TValue> */
    public function get(): UserCollection
    {
        do_action('offbeatwp/users/query/before_get', $this);

        $results = $this->getQueryResults();

        return apply_filters('offbeatwp/users/query/get', new UserCollection($results, $this->modelClass), $this);
    }

    /**
     * @deprecated Use the <b>get</b> method instead.
     * @return UserCollection<int, TValue>
     */
    public function all(): UserCollection
    {
        return $this->take(0);
    }

    /** @return UserCollection<int, TValue> */
    public function take(int $numberOfUsers): UserCollection
    {
        $this->queryVars['number'] = $numberOfUsers;
        return $this->get();
    }

    /** @phpstan-return TValue|null */
    public function first(): ?UserModel
    {
        return $this->take(1)->first();
    }

    /** @phpstan-return TValue|null */
    public function findById(?int $id): ?UserModel
    {
        if ($id <= 0) {
            return null;
        }

        $this->queryVars['include'] = [$id];
        return $this->first();
    }

    /** @phpstan-return TValue */
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
     * @param 'ASC'|'DESC'|'' $direction Either <i>ASC</i> for lowest to highest or <i>DESC</i> for highest to lowest. Defaults to <i>ASC</i>.
     * @return $this
     */
    public function orderBy($properties, string $direction = '')
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
    public function withRoles(array $roles)
    {
        $this->queryVars['role'] = $roles;
        return $this;
    }

    /**
     * @param string[] $roles An array of role names. Matched users must have at least one of these roles.
     * @return $this
     */
    public function whereRoleIn(array $roles)
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

    /**
     * @param mixed[] $metaQueryArray
     * @return $this
     */
    public function whereMeta(array $metaQueryArray)
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $this->queryVars['meta_query'][] = $metaQueryArray;

        return $this;
    }

    /**
     * @param int[]|int $ids
     * @return $this
     */
    public function whereIdIn($ids)
    {
        $this->skipOnInclude = !$ids;
        $this->queryVars['include'] = (array)$ids;
        return $this;
    }

    /**
     * @param int[]|int $ids
     * @return $this<TValue>
     */
    public function whereIdNotIn($ids)
    {
        $this->queryVars['exclude'] = (array)$ids;

        return $this;
    }

    /** @return $this<TValue> */
    public function limit(int $amount)
    {
        $this->skipOnLimit = ($amount <= 0);
        $this->queryVars['number'] = $amount;

        return $this;
    }

    /** @return int[] */
    public function ids(): array
    {
        $this->queryVars['fields'] = 'ID';
        return $this->getQueryResults();
    }

    /**
     * @param string $fieldName Allowed values: display_name, login, nicename, email, url
     * @return string[]
     */
    public function fields(string $fieldName): array
    {
        if (!in_array($fieldName, ['display_name', 'login', 'nicename', 'email', 'url'], true)) {
            throw new UnexpectedValueException('Illegal value for fields: ' . $fieldName);
        }

        $this->queryVars['fields'] = $fieldName;
        return $this->getQueryResults();
    }

    public function firstDisplayName(): ?string
    {
        $this->queryVars['fields'] = 'display_name';
        $this->queryVars['number'] = 1;
        return $this->getQueryResults()[0] ?? null;
    }

    /** @return mixed[] */
    private function getQueryResults(): array
    {
        if ($this->skipOnLimit || $this->skipOnInclude) {
            return [];
        }

        $query = new WP_User_Query($this->queryVars);

        self::$lastRequest = $query->request;

        return $query->get_results();
    }
}
