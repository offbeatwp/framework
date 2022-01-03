<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_User_Query;

class UserQueryBuilder extends AbstractQueryBuilder
{
    protected $queryVars = [];

    /** @return UserCollection<UserModel> */
    protected function get(): UserCollection
    {
        do_action('offbeatwp/users/query/before_get', $this);

        $userQuery = new WP_User_Query($this->queryVars);

        return apply_filters('offbeatwp/users/query/get', new UserCollection($userQuery->get_results()), $this);
    }

    public function all()
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
}