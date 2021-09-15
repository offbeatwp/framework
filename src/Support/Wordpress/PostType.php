<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostTypeBuilder;
use OffbeatWP\Content\Post\PostModel;

class PostType
{
    public const DEFAULT_POST_MODEL = PostModel::class;

    /** @var class-string<PostModel>[] */
    private $postTypeModels = [];

    public static function make(string $name, string $pluralName, string $singleName): PostTypeBuilder
    {
        return (new PostTypeBuilder())->make($name, $pluralName, $singleName);
    }

    /**
     * @param string $postType
     * @param class-string<PostModel> $modelClass
     */
    public function registerPostModel(string $postType, string $modelClass): void
    {
        $this->postTypeModels[$postType] = $modelClass;
    }

    public function getModelByPostType(string $postType): ?string
    {
        return $this->postTypeModels[$postType] ?? self::DEFAULT_POST_MODEL;
    }

    public function getPostTypeByModel(string $model): string
    {
        return array_search($model, $this->postTypeModels, true);
    }
}
