<?php
namespace OffbeatWP\Tools\Acf;

use OffbeatWP\Services\AbstractServicePageBuilder;
use OffbeatWP\Content\Post\PostModel;

class AcfLayoutService extends AbstractServicePageBuilder {

    public $components = [];

    public function afterRegister()
    {
        if (is_admin()) {
            new Layout\Admin($this);     
        }

        new Layout\Fields($this);
        new Layout\Renderer($this);

        PostModel::macro('isLayoutEditorActive', function () {
            return get_field('layout_enabled', $this->id);
        });
    }

    public function onRegisterComponent($event)
    {
        $this->components[$event->getName()] = $event->getComponentClass();
    }
}