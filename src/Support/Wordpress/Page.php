<?php
namespace OffbeatWP\Support\Wordpress;

final class Page
{
    /** @var string[] */
    protected static array $pageTemplates = [];

    public static function registerTemplate(string $label, string $template): void
    {
        self::$pageTemplates[$template] = $label;
    }

    /** @return string[] */
    public static function getPageTemplates(): array
    {
        return self::$pageTemplates;
    }
}
