<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostTypeBuilder;
use OffbeatWP\Content\Post\PostModel;

final class PostType
{
    public const DEFAULT_POST_MODEL = PostModel::class;

    /** @var class-string<PostModel>[] */
    private array $postTypeModels = [];

    /**
     * @param string $name Must not exceed 20 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.
     * @param string $pluralName Optional. Can also be set through the labels method.
     * @param string $singleName Optional. Can also be set through the labels method.
     * @return PostTypeBuilder
     * @see sanitize_key()
     */
    public static function make(string $name, string $pluralName = '', string $singleName = ''): PostTypeBuilder
    {
        return (new PostTypeBuilder())->make($name, $pluralName ?: $name, $singleName ?: $pluralName ?: $name);
    }

    /**
     * @param string $postType
     * @param class-string<PostModel> $modelClass
     */
    public function registerPostModel(string $postType, string $modelClass): void
    {
        $this->postTypeModels[$postType] = $modelClass;
    }

    /** @return class-string<PostModel> */
    public function getModelByPostType(string $postType): ?string
    {
        return $this->postTypeModels[$postType] ?? self::DEFAULT_POST_MODEL;
    }

    /** @param class-string<PostModel> $modelClass */
    public function getPostTypeByModel(string $modelClass): string
    {
        return array_search($modelClass, $this->postTypeModels, true);
    }

    /** @return string[] Returns an array of all post types registered with an Offbeat Model */
    public function getPostTypes(): array
    {
        return array_keys($this->postTypeModels);
    }
}
