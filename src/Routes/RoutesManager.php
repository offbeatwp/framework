<?php
namespace OffbeatWP\Routes;

class RoutesManager
{
    public $actions = [];

    public function callback($actionCallback, $checkCallback, $parameters = [])
    {
        $action = [[
            'actionCallback' => $actionCallback,
            'checkCallback'  => $checkCallback,
            'parameters'     => $parameters,
        ]];

        $this->actions = array_merge($action, $this->actions);
    }

    public function findMatch()
    {
        foreach ($this->actions as $action) {
            if ($action['checkCallback']()) {
                return $action;
            }

        }

        return false;
    }
}
