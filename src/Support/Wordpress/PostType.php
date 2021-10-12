<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Post\PostTypeBuilder;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Exceptions\PostTypeException;

class PostType
{
    public const DEFAULT_POST_MODEL = PostModel::class;

    /** @var class-string<PostModel>[] */
    private $postTypeModels = [];
    /** @var class-string<PostModel>|null */
    private $defaultPostType;

    public static function make(string $name, string $pluralName, string $singleName): PostTypeBuilder
    {
        return (new PostTypeBuilder())->make($name, $pluralName, $singleName);
    }

    /**
     * @param string|class-string<PostModel> $postType  Either the class-string of a PostModel with a defined POST_TYPE or the slug of the post type to register
     * @param class-string<PostModel> $modelClass       The className of the PostModel. Only required if the first passed parameter was a slug
     */
    public function registerPostModel(string $postType, string $modelClass = ""): void
    {
        if (!$modelClass) {
            $modelClass = $postType;
            $postType = $modelClass::POST_TYPE;
        }

        $this->postTypeModels[$postType] = $modelClass;
    }

    /**
     * @param class-string<PostModel> $modelClass
     * @throws PostTypeException
     */
    public function registerDefaultPostModel(string $modelClass): void
    {
        if ($this->defaultPostType) {
            throw new PostTypeException('Default post type has already been set to ' . $this->defaultPostType);
        } else if (in_array($modelClass, $this->postTypeModels, true)) {
            throw new PostTypeException($this->defaultPostType . ' was already registered as a regular PostModel.');
        }

        $this->defaultPostType = $modelClass;
        $this->registerPostModel($modelClass);
    }

    public function getModelByPostType(string $postType): string
    {
        return $this->postTypeModels[$postType] ?? $this->getDefaultPostModel();
    }

    public function getPostTypeByModel(string $model): string
    {
        return array_search($model, $this->postTypeModels, true) ?: '';
    }

    private function getDefaultPostModel(): string
    {
        return $this->defaultPostType ?? self::DEFAULT_POST_MODEL;
    }
}
