<?php

namespace OffbeatWP\Services;

use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractServicePageBuilder extends AbstractService {
    public function register(EventDispatcher $eventDispatcher)
    {
        if (method_exists($this, 'onRegisterComponent')) {
            $eventDispatcher->addListener('raow.component.register', [$this, '_onRegisterComponent']);
        }

        if (method_exists($this, 'afterRegister')) {
            $this->app->container->call([$this, 'afterRegister']);
        }
    }

    public function _onRegisterComponent($event)
    {
        $componentClass = $event->getComponentClass();

        if(!$componentClass::supports('pagebuilder')) return null;

        $this->onRegisterComponent($event);
    }
}