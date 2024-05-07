<?php
namespace OffbeatWP\Services;

use OffbeatWP\Support\Wordpress\Hooks;

abstract class AbstractServicePageBuilder extends AbstractService {
    public function register(): void
    {
        if (method_exists($this, 'onRegisterComponent')) {
            offbeat(Hooks::class)->addAction('offbeat.component.register', [$this, '_onRegisterComponent']);
        }

        if (method_exists($this, 'afterRegister')) {
            $this->app->getContainer()->call([$this, 'afterRegister']);
        }
    }

    /** @param mixed[] $component */
    public function _onRegisterComponent(array $component): void
    {
        $componentClass = $component['class'];

        if(!$componentClass::supports('pagebuilder')) {
            return;
        }

        if (method_exists($this, 'onRegisterComponent')) {
            $this->onRegisterComponent($component['name'], $componentClass);
        } else {
            trigger_error('Class extending AbstractServicePageBuilder does not implement onRegisterComponent.');
        }
    }
}