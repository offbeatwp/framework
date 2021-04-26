<?php
namespace OffbeatWP\Routes\Routes;

use Closure;

class CallbackRoute extends Route
{
    private $matchCallback;

    /**
     * @var Closure $matchCallback
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

    public function setMatchCallback ($matchCallback) {
            $this->matchCallback = $matchCallback;
    }

    public function doMatchCallback():bool
    {
        $matchCallback = $this->matchCallback;

        return $matchCallback();
    }
}
