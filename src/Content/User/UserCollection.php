<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\OffbeatModelCollection;
use TypeError;
use WP_User;
use ArrayAccess;

/**
 * @template T of UserModel
 * @template-extends ArrayAccess<array-key|null, T>
 */
class UserCollection extends OffbeatModelCollection
{
    /** @var UserModel[] */
    protected $items = [];

    /** @param int[]|WP_User[]|UserModel[] $items */
    public function __construct(iterable $items = []) {
        $users = [];

        foreach ($items as $item) {
            $termModel = $this->createValidUserModel($item);
            if ($termModel) {
                $users[] = $termModel;
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

    /** @return UserModel[]|T[] */
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
     * @param int|WP_User|UserModel $value
     * @return UserCollection
     */
    public function add($value)
    {
        return parent::add($this->createValidUserModel($value));
    }

    /** @return T|UserModel|mixed */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return T|UserModel|mixed */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return T|UserModel|static|null */
    public function pop($count = 1)
    {
        return parent::pop($count);
    }

    /** @return T|UserModel|mixed */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return T|UserModel|null */
    public function reduce(callable $callback, $initial = null)
    {
        return parent::reduce($callback, $initial);
    }

    /** @return T|UserModel|static|null */
    public function shift($count = 1)
    {
        return parent::shift($count);
    }

    /** @param int|WP_User|UserModel $item */
    protected function createValidUserModel($item): ?UserModel
    {
        if ($item instanceof UserModel) {
            return $item;
        }

        if (is_int($item) || $item instanceof WP_User) {
            return new UserModel($item);
        }

        throw new TypeError(gettype($item) . ' cannot be used to generate a UserModel.');
    }
}