<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostTypeBuilder;
use OffbeatWP\Content\Post\PostModel;

class PostType
{
    const DEFAULT_POST_MODEL = PostModel::class;

    private $postTypeModels = [];

    public static function make(string $name, string $pluralName, string $singleName): PostTypeBuilder
    {
        return (new PostTypeBuilder)->make($name, $pluralName, $singleName);
    }

    public function registerPostModel(string $postType, string $modelClass): void
    {
        $this->postTypeModels[$postType] = $modelClass;
    }

    public function getModelByPostType(string $postType): ?string
    {
        if (isset($this->postTypeModels[$postType])) {
            return $this->postTypeModels[$postType];
        }

        return self::DEFAULT_POST_MODEL;
    }

    public function getPostTypeByModel(string $model): string
    {
        return array_search($model, $this->postTypeModels);
    }
}
