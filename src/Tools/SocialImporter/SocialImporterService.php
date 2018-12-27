<?php
namespace OffbeatWP\Tools\SocialImporter;

use OffbeatWP\Services\AbstractService;
use Illuminate\Support\Collection;

class SocialImporterService extends AbstractService {

    public $bindings = [
        'social_importer' => SocialImporterRepository::class,
    ];

    public function register()
    {
        $this->registerPostType();
        $this->registerRoutes();

        add_filter('acf/settings/remove_wp_meta_box', function ($remove) {
            global $post;

            if (isset($post->post_type) && $post->post_type == Models\SocialPostModel::POST_TYPE)
                $remove = false;

            return $remove;
        });

        raowApp('admin-page')->makeSub('tools', __('Social Importer', 'raow'), 'social-importer', 'edit_posts', 'controller');

        raowApp('console')->register(Console\SocialImporterCommand::class);

        raowApp('ajax')->make('social_embed', Actions\SocialEmbed::class);
    }

    public function registerPostType()
    {
        raowApp('post-type')
            ->make(Models\SocialPostModel::POST_TYPE, __('Social Posts', 'raow'), __('Social Post', 'raow'))
            ->supports(['title', 'editor', 'thumbnail', 'custom-fields'])
            ->notPubliclyQueryable()
            ->inMenu('misc-content')
            ->public()
            ->set();
    }

    public function registerRoutes()
    {
        raowApp('routes')->register([Controllers\SocialImporterController::class, 'actionConfig'], function () {
            return is_admin() && $_GET['page'] == 'social-importer';
        });
    }
}