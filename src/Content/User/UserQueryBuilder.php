<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use OffbeatWP\Exceptions\UserModelException;
use WP_User_Query;

class UserQueryBuilder extends AbstractQueryBuilder
{
    protected $queryVars = [];

    /**
     * @return UserCollection<UserModel>
     * @throws UserModelException
     */
    public function get(): UserCollection
    {
        do_action('offbeatwp/users/query/before_get', $this);

        $userQuery = new WP_User_Query($this->queryVars);

        return apply_filters('offbeatwp/users/query/get', new UserCollection($userQuery->get_results()), $this);
    }

    /** @throws UserModelException */
    public function take(int $numberOfUsers): UserCollection
    {
        $this->queryVars['number'] = $numberOfUsers;

        return $this->get();
    }

    /** @throws UserModelException */
    public function first(): ?UserModel
    {
        return $this->take(1)->first();
    }

    /** @throws UserModelException */
    public function findById(int $id): ?UserModel
    {
        $this->queryVars['include'] = [$id];
        return $this->first();
    }

    /** @throws OffbeatModelNotFoundException|UserModelException */
    public function findByIdOrFail(int $id): UserModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('UserModel with id ' . $id . ' could not be found');
        }

        return $result;
    }
}