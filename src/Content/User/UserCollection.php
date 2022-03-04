<?php

namespace OffbeatWP\Content\User;

use ArrayIterator;
use OffbeatWP\Content\Common\OffbeatModelCollection;
use OffbeatWP\Support\Wordpress\User;
use TypeError;
use WP_User;

/**
 * @method UserModel|mixed pull(int|string $key, mixed $default = null)
 * @method UserModel|mixed first(callable $callback = null, mixed $default = null)
 * @method UserModel|mixed last(callable $callback = null, mixed $default = null)
 * @method UserModel|static|null pop(int $count = 1)
 * @method UserModel|static|null shift(int $count = 1)
 * @method UserModel|null reduce(callable $callback, mixed $initial = null)
 * @method UserModel offsetGet(int|string $key)
 * @method ArrayIterator|UserModel[] getIterator()
 * @method UserModel[] toArray()
 */
class UserCollection extends OffbeatModelCollection
{
    /** @var UserModel[] */
    protected $items = [];

    /** @param int[]|WP_User[]|UserModel[] $items */
    public function __construct(iterable $items = []) {
        $users = [];

        foreach ($items as $item) {
            $userModel = $this->convertToModel($item);
            if ($userModel) {
                $users[] = $userModel;
            }
        }

        parent::__construct($users);
    }

    /** @param int|WP_User|UserModel $item */
    protected function convertToModel($item): ?UserModel
    {
        if ($item instanceof UserModel) {
            return $item;
        }

        if (is_int($item) || $item instanceof WP_User) {
            return User::get($item);
        }

        throw new TypeError(gettype($item) . ' cannot be used to generate a UserModel.');
    }
}