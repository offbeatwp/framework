<?php
namespace OffbeatWP\Tools\Acf;

use OffbeatWP\Contracts\SiteSettings as SiteSettingsContract;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Content\Post\PostModel;

class Service extends AbstractService {

    public $bindings = [
        SiteSettingsContract::class => SiteSettings::class
    ];

    public function register() {
        offbeat('hooks')->addFilter('post_attribute', Hooks\AcfPostAttributeFilter::class, 10, 3);
        offbeat('hooks')->addFilter('term_attribute', Hooks\AcfTermAttributeFilter::class, 10, 3);

        PostModel::macro('getField', function ($name, $format = true) {
            return get_field($name, $this->id, $format);
        });

        TermModel::macro('getField', function ($name, $format = true) {
            return get_field($name, $this->wpTerm, $format);
        });

        $this->registerIntegrations();
    }

    public function registerIntegrations() {
        if (class_exists('\GFAPI')) {
            offbeat('hooks')->addAction('acf/include_field_types', function () {
                new Integrations\AcfFieldGravityForms();
            }); 
        }
    }
}