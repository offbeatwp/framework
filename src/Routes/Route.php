<?php
namespace OffbeatWP\Routes;

use Closure;
use Symfony\Component\Routing\Route as RoutingRoute;

class Route extends RoutingRoute
{
    private $type;
    private $actionCallback;
    private $isCallbackRoute = false;
    private $matchCallback;

    /**
     * @var string|Callable $target
     */
    public function __construct($target, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '') {
        $this->setActionCallback($actionCallback);

        if (!is_string($target)) {
            $this->isCallbackRoute = true;
        }

        $this->setTarget($target);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        $this->setCondition($condition);
    }

    public function setTarget ($target) {
        if (is_string($target)) {
            $this->setPath($target);
            $this->setType('path');
        } else {
            $this->isCallbackRoute = true;
            $this->matchCallback = $target;
            $this->setType('callback');
        }
    }

    public function setActionCallback($actionCallback) {
        $this->actionCallback = $actionCallback;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType():string
    {
        return $this->type;   
    }

    public function doMatchCallback():bool
    {
        if ($this->isCallbackRoute) {
            $matchCallback = $this->matchCallback;

            return $matchCallback();
        }

        return false;
    }

    public function getActionCallback()
    {
        return $this->actionCallback;
    }

    public function doActionCallback():bool
    {
        $actionCallback = $this->getActionCallback();

        return $actionCallback();
    }

    public function hasValidActionCallback():bool
    {
        if (is_callable($this->actionCallback)) {
            return true;
        }
        
        return false;
    }

    public function getParameters()
    {
        $parameters = $this->getDefaults();

        if (isset($parameters['_parameterCallback']) && $parameters['_parameterCallback'] instanceof Closure) {
            return $parameters['_parameterCallback']();
        }

        return $parameters;
    }
}
