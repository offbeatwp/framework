<?php
namespace OffbeatWP\Routes\Routes;

use Closure;

class CallbackRoute extends Route
{
    /** @var callable(): bool */
    private $matchCallback;

    /**
     * @param string $name
     * @param Closure $matchCallback
     * @param callable $actionCallback
     * @param mixed[] $defaults
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @param string|null $host
     * @param string[] $schemes
     * @param string[] $methods
     * @param string|null $condition
     */
    public function __construct(string $name, Closure $matchCallback, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '') {
        $this->setName($name);
        $this->setActionCallback($actionCallback);

        $this->setMatchCallback($matchCallback);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        $this->setCondition($condition);
    }

    /**
     * @param callable(): bool $matchCallback
     * @return void
     */
    public function setMatchCallback($matchCallback) {
            $this->matchCallback = $matchCallback;
    }

    public function doMatchCallback(): bool
    {
        $matchCallback = $this->matchCallback;
        return $matchCallback();
    }
}
