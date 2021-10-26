<?php
namespace OffbeatWP\Routes\Routes;

use Closure;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    /** @var string */
    protected $name;
    private $actionCallback;

    public function __construct(string $name, string $path, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '') {
        $this->setName($name);
        $this->setActionCallback($actionCallback);

        parent::__construct($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    public function setActionCallback($actionCallback): void
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
