<?php
namespace OffbeatWP\Routes\Routes;

use Closure;
use Symfony\Component\Routing\Route as RoutingRoute;

class Route extends RoutingRoute
{
    protected $name;
    private $actionCallback;

    /**
     * @var string|Callable $target
     */
    public function __construct(string $name, string $path, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '') {
        $this->setName($name);
        $this->setActionCallback($actionCallback);

        $this->setPath($path);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        $this->setCondition($condition);
    }

    public function setActionCallback($actionCallback) {
        $this->actionCallback = $actionCallback;
    }

    public function getActionCallback()
    {
        $actionCallback = $this->actionCallback;

        if ($actionCallback instanceof Closure) {
            $actionCallback = $actionCallback();
        }

        return $actionCallback;
    }

    public function doActionCallback()
    {
        $actionCallback = $this->getActionCallback();

        if ($actionCallback instanceof Closure) {
            $actionCallback = $actionCallback();
        }

        return container()->call($actionCallback, $this->getParameters());
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

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
