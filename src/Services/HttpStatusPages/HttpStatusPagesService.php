<?php
namespace OffbeatWP\Services\HttpStatusPages;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\SiteSettings;

class HttpStatusPagesService extends AbstractService
{
    protected $settings;

    public function register(SiteSettings $settings)
    {
        $this->settings = $settings;

        $settings->addPage(HttpStatusPagesSettings::class);

        add_filter('offbeatwp/http_status', [$this, 'renderHttpStatusPage'], 20, 2);
    }

    public function renderHttpStatusPage($return, $code) {
        $pageId = setting("http-status-page-{$code}");

        if (!$pageId || !is_numeric($pageId)) {
            return $return;
        }

        query_posts(['page_id' => $pageId]);
        the_post();

        $route = offbeat('routes')->findMatch();

        return offbeat()->runRoute($route);
    }
}