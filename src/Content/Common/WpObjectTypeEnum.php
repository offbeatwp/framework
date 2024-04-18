<?php

namespace OffbeatWP\Content\Common;

/** @internal */
enum WpObjectTypeEnum: string
{
    case POST = 'post';
    case COMMENT = 'comment';
    case TERM = 'term';
    case USER = 'user';
}