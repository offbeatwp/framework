<?php
namespace OffbeatWP\Assets;

class AssetsManager
{
    public $actions = [];
    public $manifest = null;
    public $entrypoints = null;

    /**
     * @param string $filename
     * @return false|string
     */
    public function getUrl($filename)
    {
        if ($this->getEntryFromAssetsManifest($filename) !== false) {
            $path = $this->getEntryFromAssetsManifest($filename);

            if (strpos($path, 'http') === 0) {
                return $path;
            }

            return $this->getAssetsUrl($path);
        }

        return false;
    }

    /**
     * @param string $filename
     * @return false|string
     */
    public function getPath($filename)
    {
        if ($this->getEntryFromAssetsManifest($filename) !== false) {
            return $this->getAssetsPath($this->getEntryFromAssetsManifest($filename));
        }

        return false;
    }

    /**
     * @param non-empty-string $filename
     * @return string|false
     */
    public function getEntryFromAssetsManifest($filename)
    {
        return $this->getAssetsManifest()->$filename ?? false;
    }

    /** @return object|bool|null */
    public function getAssetsManifest() {
        if ($this->manifest === null && file_exists($this->getAssetsPath('manifest.json', true))) {
            $this->manifest = json_decode(file_get_contents($this->getAssetsPath('manifest.json', true)));
        }

        return $this->manifest;
    }

    /** @return object|bool|null */
    public function getAssetsEntryPoints() {
        if ($this->entrypoints === null && file_exists($this->getAssetsPath('entrypoints.json', true))) {
            $this->entrypoints = json_decode(file_get_contents($this->getAssetsPath('entrypoints.json', true)));
        }

        return $this->entrypoints;
    }

    /**
     * @param string $entry
     * @param string $key
     * @return mixed|false
     */
    public function getAssetsByEntryPoint($entry, $key)
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints->entrypoints->$entry->$key)) {
            return false;
        }

        return $entrypoints->entrypoints->$entry->$key;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAssetsPath($path = '', $forceAssetsPath = false)
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

        if ($basepath = config('app.assets.path'))  {
            return $basepath . $path;
        }

        return get_template_directory() . '/assets' . $path; 
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAssetsUrl($path = '')
    {
        if (strpos($path, 'http') === 0) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        if (config('app.assets.from_site_root')) {
            $url = get_site_url();

            return $url . $path;
        }


        if ($url = config('app.assets.url'))  {
            return $url . $path;
        }

        return get_template_directory_uri() . '/assets' . $path; 
    }

    /** @param string $entry */
    public function enqueueStyles($entry): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'css');

        if ($assets) {
            foreach ($assets as $key => $asset) {
                $asset = ltrim($asset, './');

                $handle = basename($asset);
                $handle = substr(basename($asset), 0, strpos($handle, '.'));
                $handle = 'owp-' . $handle;
                
                if (!wp_style_is($handle)) {
                    wp_enqueue_style($handle, $this->getAssetsUrl($asset), [], false, false);
                }
            }

            return;
        }

        wp_enqueue_style('theme-style' . $entry, $this->getUrl($entry . '.css'), [], false);
    }

    /** @param string $entry */
    public function enqueueScripts($entry): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');

        if ($assets) {
            foreach ($assets as $key => $asset) {
                $asset = ltrim($asset, './');

                $handle = basename($asset);
                $handle = substr(basename($asset), 0, strpos($handle, '.'));
                $handle = 'owp-' . $handle;

                if (!wp_script_is($handle)) {
                    wp_enqueue_script($handle, $this->getAssetsUrl($asset), ['jquery'], false, true);
                }
            }

            return;
        }

        wp_enqueue_script('theme-script-' . $entry, $this->getUrl($entry . '.js'), ['jquery'], false, true);
    }
}
