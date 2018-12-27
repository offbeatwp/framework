<?php
namespace OffbeatWP\Tools\Twig;

use Twig_Extension;
use Twig_Function;

class RaowTwigExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new Twig_Function('__', array($this, '__')),
            new Twig_Function('config', array($this, 'getConfig')),
            new Twig_Function('assetUrl', array($this, 'getAssetUrl')),
            new Twig_Function('component', array($this, 'getComponent')),
        );
    }

    public function __($string, $textdomain)
    {
        return __($string, $textdomain);
    }

    public function getConfig($key)
    {
        return config($key);
    }

    public function getAssetUrl($file)
    {
        return assetUrl($file);
    }

    public function getComponent($name, $args = [])
    {
        echo container('components')->render($name, $args);
    }
}
