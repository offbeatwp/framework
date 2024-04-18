<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatObjectBuilder;
use OffbeatWP\Content\Common\WpObjectTypeEnum;
use OffbeatWP\Exceptions\OffbeatBuilderException;

use WP_Error;

/** @TODO */
final class PostBuilder extends OffbeatObjectBuilder
{
    /** @var array{ID?: int, post_title?: string, post_name?: string} */
    private array $args;

    private function __construct(array $args)
    {
        $this->args = $args;
    }

    /** @return $this */
    public function title(string $title)
    {
        $this->args['post_title'] = $title;
        return $this;
    }

    /**
     * Set the post's slug (also known as <i>post-name</i>)<br>
     * Will throw a PostBuilderException if given string is empty or not a valid post slug
     * @param non-empty-string $slug
     * @return $this
     * @throws \OffbeatWP\Exceptions\OffbeatBuilderException
     */
    public function slug(string $slug)
    {
        if (!$slug) {
            throw new OffbeatBuilderException('Post slug cannot be empty.');
        }

        if ($slug !== sanitize_title($slug)) {
            throw new OffbeatBuilderException('Invalid post slug: ' . $slug);
        }

        $this->args['post_name'] = $slug;
        return $this;
    }

    /**
     * Inserts or updates the post in the database.<br>
     * Returns post ID on success, throws PostBuilderException on failure.
     * @return positive-int
     * @throws OffbeatBuilderException
     */
    public function save(): int
    {
        // Determine post id (if update)
        if (empty($this->args['ID'])) {
            $idToUpdate = null;
        } else {
            $idToUpdate = $this->args['ID'];
            unset($this->args['ID']);
        }

        // Either insert or update the post
        if ($idToUpdate) {
            $resultId = wp_update_post($this->args, true);
        } else {
            $resultId = wp_insert_post($this->args, true);
        }

        if ($resultId instanceof WP_Error) {
            throw new OffbeatBuilderException('PostBuilder ' . ($idToUpdate ? 'UPDATE' : 'INSERT') . ' failed: ' . $resultId->get_error_message());
        }

        $this->saveMeta($resultId);

        return $resultId;
    }

    protected function getObjectType(): WpObjectTypeEnum
    {
        Return WpObjectTypeEnum::POST;
    }

    /////////////////////
    // Factory methods //
    /////////////////////
    /** @pure */
    public static function create(): PostBuilder
    {
        return new PostBuilder([]);
    }

    /**
     * @pure
     * @param positive-int $postId The ID of the post.
     * @throws OffbeatBuilderException
     */
    public static function update(int $postId): PostBuilder
    {
        if ($postId <= 0) {
            throw new OffbeatBuilderException('PostBuilder update failed, invalid ID: ' . $postId);
        }

        return new PostBuilder(['ID' => $postId]);
    }
}