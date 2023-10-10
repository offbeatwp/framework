<?php

namespace OffbeatWP\Assets;

final class AssetsManager
{
    public ?array $manifest = null;
    public ?array $entrypoints = null;

    public function getUrl(string $filename): ?string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            if (strncmp($path, 'http', 4) === 0) {
                return $path;
            }

            return $this->getAssetsUrl($path);
        }

        return null;
    }

    public function getPath(string $filename): ?string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            return $this->getAssetsPath($path);
        }

        return null;
    }

    public function getEntryFromAssetsManifest(string $filename): ?string
    {
        return $this->getAssetsManifest()[$filename] ?? null;
    }

    /** @return mixed[]|null */
    public function getAssetsManifest(): ?array
    {
        if ($this->manifest === null) {
            $path = $this->getAssetsPath('manifest.json', true);

            if (file_exists($path)) {
                $content = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

                if (is_array($content)) {
                    $this->manifest = $content;
                }
            }
        }

        return $this->manifest;
    }

    /** @return mixed[]|null */
    public function getAssetsEntryPoints(): ?array
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $content = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

                if (is_array($content)) {
                    $this->entrypoints = $content;
                }
            }
        }

        return $this->entrypoints;
    }

    /** @return mixed[]|null */
    public function getAssetsByEntryPoint(string $entry, string $key): ?array
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (!$entrypoints || empty($entrypoints['entrypoints'][$entry][$key])) {
            return null;
        }

        return $entrypoints['entrypoints'][$entry][$key];
    }

    public function getAssetsPath(string $path = '', bool $forceAssetsPath = false): string
    {
        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        $assetsPath = config('app.assets.path');

        if ($forceAssetsPath) {
            return $assetsPath . $path;
        }

        if (config('app.assets.from_site_root')) {
            if (config('app.assets.root_path')) {
                $basepath = config('app.assets.root_path');
            } else {
                $basepath = defined('ABSPATH') ? ABSPATH : null;
            }

            return $basepath . $path;
        }

        $basepath = config('app.assets.path');
        if ($basepath) {
            return $basepath . $path;
        }

        return get_template_directory() . '/assets' . $path;
    }

    public function getAssetsUrl(string $path = ''): string
    {
        if (strncmp($path, 'http', 4) === 0) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        if (config('app.assets.from_site_root')) {
            $url = get_site_url();

            return $url . $path;
        }


        $url = config('app.assets.url');
        if ($url) {
            return $url . $path;
        }

        return get_template_directory_uri() . '/assets' . $path;
    }

    /**
     * @param string $entry
     * @param string[] $dependencies
     * @return void
     */
    public function enqueueStyles(string $entry, array $dependencies = []): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'css');
        $dependencies = array_unique($dependencies);

        if ($assets) {
            foreach ($assets as $asset) {
                $asset = ltrim($asset, './');
                $baseName = basename($asset);
                $handle = substr($baseName, 0, strpos($baseName, '.'));
                $handle = 'owp-' . $handle;

                if (!wp_style_is($handle)) {
                    $this->_enqueueStyle($handle, $this->getAssetsUrl($asset), $dependencies);
                }
            }
        } else {
            $this->_enqueueStyle('theme-style' . $entry, $this->getUrl($entry . '.css'), $dependencies);
        }
    }

    /**
     * @param string $entry
     * @param string[] $dependencies
     * @return void
     */
    public function enqueueScripts(string $entry, array $dependencies = []): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        if (is_admin() || apply_filters('offbeatwp/assets/include_jquery_by_default', true)) {
            $dependencies[] = 'jquery';
        }

        $dependencies = array_unique($dependencies);

        if ($assets) {
            foreach ($assets as $asset) {
                $asset = ltrim($asset, './');
                $baseName = basename($asset);
                $handle = substr($baseName, 0, strpos($baseName, '.'));
                $handle = 'owp-' . $handle;

                if (!wp_script_is($handle)) {
                    $this->_enqueueScript($handle, $this->getAssetsUrl($asset), $dependencies);
                }
            }
        } else {
            $this->_enqueueScript('theme-script-' . $entry, $this->getUrl($entry . '.js'), $dependencies);
        }
    }

    /**
     * @param string $handle
     * @param string $src
     * @param string[] $deps
     * @return void
     */
    private function _enqueueScript(string $handle, string $src, array $deps): void
    {
        if (did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets'])) {
            wp_enqueue_script($handle, $src, $deps, false, true);
        } else {
            add_action('wp_enqueue_scripts', static function () use ($handle, $src, $deps) {
                wp_enqueue_script($handle, $src, $deps, false, true);
            });
        }
    }

    /**
     * @param string $handle
     * @param string $src
     * @param string[] $deps
     * @return void
     */
    private function _enqueueStyle(string $handle, string $src, array $deps): void
    {
        if (did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets'])) {
            wp_enqueue_style($handle, $src, $deps);
        } else {
            add_action('wp_enqueue_scripts', static function () use ($handle, $src, $deps) {
                wp_enqueue_style($handle, $src, $deps);
            });
        }
    }
}
