<?php
namespace OffbeatWP\Routes\Routes;

use Closure;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    protected $name;
    private $actionCallback;

    public function __construct(string $name, string $path, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->setName($name);
        $this->setActionCallback($actionCallback);
        parent::__construct($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    public function setActionCallback($actionCallback)
    {
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

    public function hasValidActionCallback(): bool
    {
        return is_callable($this->actionCallback);
    }

    public function getParameters()
    {
        $parameters = $this->getDefaults();

        if (isset($parameters['_parameterCallback']) && $parameters['_parameterCallback'] instanceof Closure) {
            return $parameters['_parameterCallback']();
        }

        return $parameters;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }
}
