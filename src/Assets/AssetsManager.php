<?php

namespace OffbeatWP\Assets;

use RuntimeException;
use stdClass;

final class AssetsManager
{
    public stdClass|null $manifest = null;
    public stdClass|null $entrypoints = null;

    public function getUrl(string $filename): string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            return $this->getAssetsUrl($path);
        }

        throw new RuntimeException('Could not retrieve url from asset manifest: ' . $filename);
    }

    public function getPath(string $filename): string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            return $this->getAssetsPath($path);
        }

        throw new RuntimeException('Could not retrieve path from asset manifest: ' . $filename);
    }

    public function getEntryFromAssetsManifest(string $filename): ?string
    {
        return $this->getAssetsManifest()->$filename ?? null;
    }

    /** @throws \JsonException */
    public function getAssetsManifest(): stdClass
    {
        if ($this->manifest === null) {
            $path = $this->getAssetsPath('manifest.json', true);

            if (file_exists($path)) {
                $this->manifest = json_decode(file_get_contents($path), false, 512, JSON_THROW_ON_ERROR);
            }
        }

        return $this->manifest;
    }

    /** @throws \JsonException */
    public function getAssetsEntryPoints(): stdClass
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $this->entrypoints = json_decode(file_get_contents($path), false, 512, JSON_THROW_ON_ERROR);
            }
        }

        return $this->entrypoints;
    }

    /**
     * @return string[]
     * @throws \JsonException
     */
    public function getAssetsByEntryPoint(string $entry, string $key): array
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints->entrypoints->$entry->$key)) {
            throw new RuntimeException('Entry ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.');
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

        $basepath = config('app.assets.path');
        if ($basepath) {
            return $basepath . $path;
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

    /**
     * @param string[] $dependencies
     * @throws \JsonException
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
                    if (did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'login_enqueue_scripts'])) {
                        wp_enqueue_style($handle, $this->getAssetsUrl($asset), $dependencies);
                    } else {
                        add_action('wp_enqueue_scripts', function () use ($handle, $asset, $dependencies) {
                            wp_enqueue_style($handle, $this->getAssetsUrl($asset), $dependencies);
                        });
                    }
                }
            }

            return;
        }

        if (did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'login_enqueue_scripts'])) {
            wp_enqueue_style('theme-style' . $entry, $this->getUrl($entry . '.css'), $dependencies);
        } else {
            add_action('wp_enqueue_scripts', function () use ($entry, $dependencies) {
                wp_enqueue_style('theme-style' . $entry, $this->getUrl($entry . '.css'), $dependencies);
            });
        }
    }

    /**
     * @param string[] $dependencies
     * @throws \JsonException
     */
    public function enqueueScripts(string $entry, array $dependencies = []): WpScriptAsset
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        if (apply_filters('offbeatwp/assets/include_jquery_by_default', true)) {
            $dependencies[] = 'jquery';
        }

        $dependencies = array_unique($dependencies);
        $enqueueNow = false;
        $handle = '';

        if ($assets) {
            foreach ($assets as $asset) {
                $asset = ltrim($asset, './');
                $baseName = basename($asset);
                $handle = substr($baseName, 0, strpos($baseName, '.'));
                $handle = 'owp-' . $handle;

                if (!wp_script_is($handle)) {
                    $enqueueNow = did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'login_enqueue_scripts']);

                    if ($enqueueNow) {
                        wp_enqueue_script($handle, $this->getAssetsUrl($asset), $dependencies, false, true);
                    } else {
                        add_action('wp_enqueue_scripts', function () use ($handle, $asset, $dependencies) {
                            wp_enqueue_script($handle, $this->getAssetsUrl($asset), $dependencies, false, true);
                        });
                    }
                }
            }
        } else {
            $handle = 'theme-script-' . $entry;
            $enqueueNow = did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'login_enqueue_scripts']);

            if ($enqueueNow) {
                wp_enqueue_script($handle, $this->getUrl($entry . '.js'), $dependencies, false, true);
            } else {
                add_action('wp_enqueue_scripts', function () use ($handle, $entry, $dependencies) {
                    wp_enqueue_script($handle, $this->getUrl($entry . '.js'), $dependencies, false, true);
                });
            }
        }

        return new WpScriptAsset($handle, $enqueueNow);
    }
}
