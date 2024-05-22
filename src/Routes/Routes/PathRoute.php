<?php
namespace OffbeatWP\Routes\Routes;

final class PathRoute extends Route
{
    public function __construct(string $name, string $path, $actionCallback, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', string|array $schemes = [], string|array $methods = [], ?string $condition = '') {
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
}
