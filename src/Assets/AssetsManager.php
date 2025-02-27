<?php

namespace OffbeatWP\Assets;

use Generator;
use InvalidArgumentException;
use stdClass;

final class AssetsManager
{
    public stdClass|null|false $manifest = null;
    public stdClass|null|false $entrypoints = null;

    public function getUrl(string $filename): false|string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== false) {
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            return $this->getAssetsUrl($path);
        }

        trigger_error('Could not retrieve url from asset manifest: ' . $filename, E_USER_WARNING);
        return false;
    }

    public function getPath(string $filename): false|string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== false) {
            return $this->getAssetsPath($path);
        }

        trigger_error('Could not retrieve path from asset manifest: ' . $filename, E_USER_WARNING);
        return false;
    }

    public function getEntryFromAssetsManifest(string $filename): false|string
    {
        return $this->getAssetsManifest()->$filename ?? false;
    }

    public function getAssetsManifest(): stdClass|false|null
    {
        if ($this->manifest === null) {
            $path = $this->getAssetsPath('manifest.json', true);

            if (file_exists($path)) {
                $this->manifest = json_decode(file_get_contents($path), false);

                if ($this->manifest === false) {
                    trigger_error('MANIFEST JSON ERROR - ' . json_last_error_msg(), E_USER_WARNING);
                }
            }
        }

        return $this->manifest;
    }

    public function getAssetsEntryPoints(): stdClass|false|null
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $this->entrypoints = json_decode(file_get_contents($path), false);

                if ($this->entrypoints === false) {
                    trigger_error('ENTRYPOINT JSON ERROR - ' . json_last_error_msg(), E_USER_WARNING);
                }
            }
        }

        return $this->entrypoints;
    }

    /** @return string[]|false */
    public function getAssetsByEntryPoint(string $entry, string $key): array|false
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints->entrypoints->$entry->$key)) {
            if ($entrypoints !== null) {
                trigger_error('Entry ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.', E_USER_WARNING);
            }

            return false;
        }

        return $entrypoints->entrypoints->$entry->$key;
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

        if ($assetsPath) {
            return $assetsPath . $path;
        }

        return get_template_directory() . '/assets' . $path;
    }

    public function getAssetsUrl(string $path = ''): string
    {
        if (str_starts_with($path, 'http')) {
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

    /** @param string[] $dependencies */
    public function enqueueStyles(string $entry, array $dependencies = []): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'css');
        $dependencies = array_unique($dependencies);

        if ($assets) {
            foreach ($this->trimAssets($assets) as $asset) {
                $handle = $this->getHandleFromAsset($asset);

                if (!wp_style_is($handle)) {
                    $enqueueNow = $this->shouldEnqueueNow();
                    $url = $this->getAssetsUrl($asset);

                    if ($enqueueNow) {
                        wp_enqueue_style($handle, $url, $dependencies);
                    } else {
                        add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies) {
                            wp_enqueue_style($handle, $url, $dependencies);
                        });
                    }
                }
            }
        } else {
            $handle = 'theme-style' . $entry;
            $enqueueNow = $this->shouldEnqueueNow();
            $url = $this->getUrl($entry . '.css');

            if ($enqueueNow) {
                wp_enqueue_style($handle, $url, $dependencies);
            } else {
                add_action('wp_enqueue_scripts', function () use ($url, $dependencies, $handle) {
                    wp_enqueue_style($handle, $url, $dependencies);
                });
            }
        }
    }

    public function registerStyles(string $entry, array $dependencies = [])
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'css');
        if (!$assets) {
            trigger_error("Failed to register styles for entry: {$entry}", E_USER_WARNING);
            return;
        }

        $previousHandle = '';

        foreach ($this->trimAssets($assets) as $asset) {
            $handle = $this->getHandleFromAsset($asset);

            wp_register_style(
                $handle,
                $this->getAssetsUrl($asset),
                $previousHandle ? [...$dependencies, $previousHandle] : $dependencies
            );

            $previousHandle = $handle;
        }
    }

    /**
     * @param string[] $dependencies
     * @param array{in_footer?: bool, strategy?: 'defer'|'async'} $args
     */
    public function enqueueScripts(string $entry, array $dependencies = [], array $args = ['in_footer' => true]): WpScriptAsset
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        $autoIncludeJquery = apply_filters('offbeatwp/assets/include_jquery_by_default', true);

        if ($autoIncludeJquery === 'jquery-core') {
            $dependencies[] = 'jquery-core';
        } elseif ($autoIncludeJquery) {
            $dependencies[] = 'jquery';
        }

        $dependencies = array_unique($dependencies);
        $enqueueNow = false;
        $handle = '';

        if ($assets) {
            foreach ($this->trimAssets($assets) as $asset) {
                $handle = $this->getHandleFromAsset($asset);

                if (!wp_script_is($handle)) {
                    $enqueueNow = $this->shouldEnqueueNow();
                    $url = $this->getAssetsUrl($asset);

                    if ($enqueueNow) {
                        wp_enqueue_script($handle, $url, $dependencies, false, $args);
                    } else {
                        add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies, $args) {
                            wp_enqueue_script($handle, $url, $dependencies, false, $args);
                        });
                    }
                }
            }
        } else {
            $handle = 'theme-script-' . $entry;
            $enqueueNow = $this->shouldEnqueueNow();
            $url = $this->getUrl($entry . '.js');

            if ($enqueueNow) {
                wp_enqueue_script($handle, $url, $dependencies, false, $args);
            } else {
                add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies, $args) {
                    wp_enqueue_script($handle, $url, $dependencies, false, $args);
                });
            }
        }

        return new WpScriptAsset($handle, $enqueueNow);
    }

    public function registerScripts(string $entry, array $dependencies = [])
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        if (!$assets) {
            trigger_error("Failed to register scripts for entry: {$entry}", E_USER_WARNING);
            return;
        }

        $previousHandle = '';

        foreach ($this->trimAssets($assets) as $asset) {
            $handle = $this->getHandleFromAsset($asset);

            wp_register_script(
                $handle,
                $this->getAssetsUrl($asset),
                $previousHandle ? [...$dependencies, $previousHandle] : $dependencies
            );

            $previousHandle = $handle;
        }
    }

    /**
     * Retrieves the <b>primary</b> asset url for the given handle.
     * @deprecated
     * @param string $handle The handle
     * @param string $assetType The type. Either <b>js</b> or <b>css</b>.
     * @return non-empty-string|null
     */
    public function getAssetUrlByHandle(string $handle, string $assetType): ?string
    {
        trigger_error('AssetsManager::getAssetUrlByHandle is deprecated.', E_USER_DEPRECATED);

        if ($assetType !== 'js' && $assetType !== 'css') {
            throw new InvalidArgumentException('Type parameter must be either "js" or "css"');
        }

        $assets = $this->getAssetsByEntryPoint($handle, $assetType);
        if ($assets) {
            return $this->getAssetsUrl(ltrim($assets[0], './'));
        }

        return $this->getUrl($handle . '.' . $assetType) ?: null;
    }

    private function shouldEnqueueNow(): bool
    {
        return did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'enqueue_block_assets', 'login_enqueue_scripts']);
    }

    private function getHandleFromAsset(string $asset): string
    {
        $baseName = basename($asset);
        $handle = substr($baseName, 0, strpos($baseName, '.'));

        return 'owp-' . $handle;
    }

    /**
     * @param string[] $assets
     * @return Generator<string>
     */
    private function trimAssets(array $assets): Generator
    {
        foreach ($assets as $asset) {
            yield ltrim($asset, './');
        }
    }
}
