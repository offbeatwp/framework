<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Exceptions\PostBuilderException;;
use WP_Error;

final class PostBuilder
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
     * @throws \OffbeatWP\Exceptions\PostBuilderException
     */
    public function slug(string $slug)
    {
        if (!$slug) {
            throw new PostBuilderException('Post slug cannot be empty.');
        }

        if ($slug !== sanitize_title($slug)) {
            throw new PostBuilderException('Invalid post slug: ' . $slug);
        }

        $this->args['post_name'] = $slug;
        return $this;
    }

    /**
     * Inserts or updates the post in the database.<br>
     * Returns post ID on success, throws PostBuilderException on failure.
     * @return positive-int
     * @throws PostBuilderException
     */
    public function save(): int
    {
        // Determine post id (if update)
        if (empty($this->args['ID'])) {
            $postId = null;
        } else {
            $postId = $this->args['ID'];
            unset($this->args['ID']);
        }

        // Either insert or update the post
        if ($postId) {
            $result = wp_update_post($this->args, true);
        } else {
            $result = wp_insert_post($this->args, true);
        }

        if ($result instanceof WP_Error) {
            throw new PostBuilderException('PostBuilder ' . ($postId ? 'UPDATE' : 'INSERT') . ' failed: ' . $result->get_error_message());
        }

        return $result;
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
     * @throws PostBuilderException
     */
    public static function update(int $postId): PostBuilder
    {
        if ($postId <= 0) {
            throw new PostBuilderException('PostBuilder update failed, invalid ID: ' . $postId);
        }

        return new PostBuilder(['ID' => $postId]);
    }
}