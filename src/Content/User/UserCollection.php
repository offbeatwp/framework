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
 */
class UserCollection extends OffbeatModelCollection
{
    /** @var UserModel[] */
    protected $items = [];

    /** @param int[]|WP_User[]|UserModel[] $items */
    public function __construct(iterable $items = []) {
        $users = [];

        foreach ($items as $item) {
            $userModel = $this->createValidUserModel($item);
            if ($userModel) {
                $users[] = $userModel;
            }
        }

        parent::__construct($users);
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array {
        return array_map(static function (UserModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** @return UserModel[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    /**
     * Push one or more items onto the end of the user collection.
     * @param int|WP_User|UserModel ...$values
     * @return $this
     * 
     */
    public function push(...$values)
    {
        $userModels = [];

        foreach ($values as $value) {
            $userModels[] = $this->createValidUserModel($value);
        }

        return parent::push(...$userModels);
    }

    /**
     * Set the model given the offset.
     * @param array-key $key
     * @param int|WP_User|UserModel $value
     */
    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $this->createValidUserModel($value));
    }

    /**
     * Push a model onto the beginning of the user collection.
     * @param int|WP_User|UserModel $value
     * @param array-key $key
     * @return UserCollection
     */
    public function prepend($value, $key = null)
    {
        return parent::prepend($this->createValidUserModel($value), $key);
    }

    /**
     * Add a model to the user collection.
     * @param int|WP_User|UserModel $item
     * @return UserCollection
     */
    public function add($item)
    {
        return parent::add($this->createValidUserModel($item));
    }

    /** @param int|WP_User|UserModel $item */
    protected function createValidUserModel($item): ?UserModel
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