<?php
namespace OffbeatWP\Services;

abstract class AbstractServicePageBuilder extends AbstractService {
    public function register()
    {
        if (method_exists($this, 'onRegisterComponent')) {
            offbeat('hooks')->addAction('offbeat.component.register', [$this, '_onRegisterComponent'], 10, 1);
        }

        if (method_exists($this, 'afterRegister')) {
            $this->app->container->call([$this, 'afterRegister']);
        }
    }

    public function _onRegisterComponent($component)
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