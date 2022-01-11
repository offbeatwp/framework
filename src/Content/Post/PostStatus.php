<?php

namespace OffbeatWP\Content\Post;

/** The default WordPress post statuses as of WP 5.5. Keep in mind that themes/plugin can add custom statuses. */
class PostStatus
{
    final private function __construct() {}

    public const PUBLISHED = 'publish';
    public const FUTURE = 'future';
    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const PRIVATE = 'private';
    public const TRASH = 'trash';
    public const AUTO_DRAFT = 'auto-draft';
    public const INHERIT = 'inherit';
}