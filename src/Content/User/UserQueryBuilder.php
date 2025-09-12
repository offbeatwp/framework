<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use UnexpectedValueException;
use WP_User_Query;

/** @template TValue of UserModel */
final class UserQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var class-string<UserModel> */
    protected readonly string $modelClass;
    /** @var array{
     *   blog_id?: int,
     *   role?: string|string[],
     *   role__in?: string[],
     *   role__not_in?: string[],
     *   meta_key?: string|string[],
     *   meta_value?: string|string[],
     *   meta_compare?: string,
     *   meta_compare_key?: string,
     *   meta_type?: string,
     *   meta_type_key?: string,
     *   meta_query?: mixed[],
     *   capability?: string|string[],
     *   capability__in?: string[],
     *   capability__not_in?: string[],
     *   include?: int[],
     *   exclude?: int[],
     *   search?: string,
     *   search_columns?: string[],
     *   orderby?: string|mixed[],
     *   order?: string,
     *   offset?: int,
     *   number?: int,
     *   paged?: int,
     *   count_total?: bool,
     *   fields?: string|string[],
     *   who?: string,
     *   has_published_posts?: bool|string[],
     *   nicename?: string,
     *   nicename__in?: string[],
     *   nicename__not_in?: string[],
     *   login?: string,
     *   login__in?: string[],
     *   login__not_in?: string[],
     *   cache_results?: bool,
     * }
     */
    protected array $queryVars = ['number' => 0];
    protected bool $skipOnLimit = false;
    protected bool $skipOnInclude = false;

    /** @param class-string<TValue> $modelClass */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $roles = $this->modelClass::definedUserRoles();

        if ($roles !== null) {
            $this->whereRoleIn($roles);
        }
    }

    /** @return UserCollection<int, TValue> */
    public function get(): UserCollection
    {
        /** @var list<\WP_User> $results */
        $results = $this->getQueryResults();
        return new UserCollection($results, $this->modelClass);
    }

    /**
     * @return UserCollection<int, TValue>
     * @deprecated Use the <b>get</b> method instead.
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
     * @return $this
     */
    public function orderBy($properties)
    {
        $this->queryVars['orderby'] = $properties;
        return $this;
    }

    /**
     * @param string $email Must be a valid email address, or an exception will be thrown.
     * @return $this
     */
    public function whereEmail(string $email)
    {
        $this->queryVars['search'] = $email;
        $this->queryVars['search_columns'] = ['user_email'];

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
     * @return $this
     */
    public function whereIdNotIn($ids)
    {
        $this->queryVars['exclude'] = (array)$ids;

        return $this;
    }

    /** @return $this */
    public function limit(int $amount)
    {
        $this->skipOnLimit = ($amount <= 0);
        $this->queryVars['number'] = $amount;

        return $this;
    }

    /** @return positive-int[] */
    public function ids(): array
    {
        $this->queryVars['fields'] = 'ID';
        /** @var positive-int[] */
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
        /** @var string[] */
        return $this->getQueryResults();
    }

    public function firstDisplayName(): ?string
    {
        $this->queryVars['fields'] = 'display_name';
        $this->queryVars['number'] = 1;
        /** @var string|null */
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
