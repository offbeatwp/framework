<?php

namespace PHPSTORM_META {

    use OffbeatWP\Content\Taxonomy\TermModel;
    use OffbeatWP\Form\Fields\PostType;
    use OffbeatWP\Routes\RoutesManager;
    use OffbeatWP\Support\Wordpress\AdminPage;
    use OffbeatWP\Support\Wordpress\Ajax;
    use OffbeatWP\Support\Wordpress\Console;
    use OffbeatWP\Support\Wordpress\Design;
    use OffbeatWP\Support\Wordpress\Hooks;
    use OffbeatWP\Support\Wordpress\Page;
    use OffbeatWP\Support\Wordpress\Post;
    use OffbeatWP\Support\Wordpress\RestApi;
    use OffbeatWP\Support\Wordpress\Taxonomy;
    use OffbeatWP\Wordpress\WordpressService;

    override(\offbeat(0), map([
        'admin-page' => AdminPage::class,
        'ajax' => Ajax::class,
        'rest-api' => RestApi::class,
        'console' => Console::class,
        'hooks' => Hooks::class,
        'post-type' => PostType::class,
        'post' => Post::class,
        'page' => Page::class,
        'taxonomy' => Taxonomy::class,
        'design' => Design::class,
        'routes' => RoutesManager::class
    ]));
}