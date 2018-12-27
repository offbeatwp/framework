<?php

namespace OffbeatWP\Components;

use Symfony\Component\EventDispatcher\Event;

class EventRegisterComponent extends Event {
    const NAME = 'raow.component.register';

    protected $name;
    protected $componentClass;

    public function __construct($name, $componentClass)
    {
        $this->name             = $name;
        $this->componentClass   = '\\' . $componentClass;
    }

    public function getName() {
        return $this->name;   
    }

    public function getComponentClass() {
        return $this->componentClass;   
    }
}