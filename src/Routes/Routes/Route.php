<?php
namespace OffbeatWP\Routes\Routes;

use Closure;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Routes\IMiddleware;
use OffbeatWP\Routes\WpRedirect;
use OffbeatWP\Routes\RouteRequest;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    /** @var string */
    protected $name;
    /** @var callable */
    private $actionCallback;
    /** @var class-string<IMiddleware>[] */
    private $middleware = [];

    /**
     * @param string $name
     * @param string $path
     * @param callable $actionCallback
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     * @param string|null $host
     * @param string[] $schemes
     * @param string[] $methods
     * @param string|null $condition
     */
    public function __construct(string $name, string $path, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->setName($name);
        $this->setActionCallback($actionCallback);
        parent::__construct($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    /**
     * @param callable $actionCallback
     * @return $this
     */
    public function setActionCallback($actionCallback)
    {
        $this->actionCallback = $actionCallback;
        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string[] $classNames
     * @return $this
     */
    public function middleware(array $classNames): self
    {
        $this->middleware = $classNames;
        return $this;
    }

    public function runMiddleware(): Route
    {
        $request = new RouteRequest($this);

        foreach ($this->middleware as $middleware) {
            /** @var IMiddleware $middlewareInstance */
            $middlewareInstance = new $middleware();
            $result = $middlewareInstance->handle($request);

            if ($result instanceof WpRedirect) {
                $result->execute();
            }

            if (!$result instanceof RouteRequest) {
                throw new InvalidRouteException('Unexpected Middleware return value received.');
            }
        }

        return $request->getRoute();
    }
}
