<?php

namespace OffbeatWP\Tools\Twig;

use OffbeatWP\Contracts\View;

class TwigView implements View
{
    protected $viewGlobals = [];

    protected $templatePaths = [];

    public function __construct () {
        $this->addTemplatePath(get_template_directory() . '/resources/views/');
    }

    public function render($template, $data = [])
    {
        $twig = $this->getTwig();

        $renderResult = $twig->render($template . '.twig', $data);

        return $renderResult;
    }

    public function getTwig()
    {
        $loader = new \Twig_Loader_Filesystem(self::getTemplatePaths());

        $settings = [];

        if (defined('WP_ENV') && WP_ENV === 'production') {
            $settings['cache'] = self::cacheDir();
        }

        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $settings['debug'] = true;
        }

        $twig = new \Twig_Environment($loader, $settings);

        $twig->addGlobal('wp', offbeat()->container->make(\OffbeatWP\Views\Wordpress::class));

        if (!empty($this->viewGlobals)) foreach ($this->viewGlobals as $globalNamespace => $globalValue) {
            $twig->addGlobal($globalNamespace, $globalValue);
        }

        $twig->addExtension(new RaowTwigExtension());

        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }

        return $twig;
    }

    public function cacheDir()
    {
        $cacheDirPath = WP_CONTENT_DIR . '/cache/twig';

        if (!is_dir($cacheDirPath)) {
            mkdir($cacheDirPath);
        }

        return $cacheDirPath;
    }

    public function registerGlobal($namespace, $value)
    {
        $this->viewGlobals[$namespace] = $value;
    }

    public function addTemplatePath($path) {
        array_unshift($this->templatePaths, $path);
    }

    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }
}