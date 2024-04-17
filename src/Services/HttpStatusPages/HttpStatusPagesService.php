<?php
namespace OffbeatWP\Services\HttpStatusPages;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\SiteSettings;

class HttpStatusPagesService extends AbstractService
{
    protected ?SiteSettings $settings;

    public function register(SiteSettings $settings): void
    {
        $this->settings = $settings;

        $settings->addPage(HttpStatusPagesSettings::class);

        add_filter('offbeatwp/http_status', [$this, 'renderHttpStatusPage'], 20, 2);
    }

    public function renderHttpStatusPage(mixed $return, int $code): mixed
    {
        global $wp_query, $wp_the_query;

        $pageId = setting("http-status-page-{$code}");

        if (!$pageId || !is_numeric($pageId)) {
            return $return;
        }

        query_posts(['page_id' => $pageId]);
        the_post();

        $wp_the_query = $wp_query;

        $route = offbeat('routes')->findCallbackRoute();

        return offbeat()->runRoute($route);
    }
}