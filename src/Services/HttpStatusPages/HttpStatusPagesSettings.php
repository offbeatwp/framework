<?php
namespace OffbeatWP\Services\HttpStatusPages;

class HttpStatusPagesSettings
{
    const ID = 'http-status-pages';
    const PRIORITY = 90;

    public function title()
    {
        return __('Http status pages', 'raow');
    }

    public function form()
    {
        $form = new \OffbeatWP\Form\Form();

        if (!($httpStatusPagesCodes = config('app.http_status_pages_codes'))) {
            $httpStatusPagesCodes = collect('404');
        }

        $httpStatusPagesCodes->each(function($statusCode) use ($form) {
            $form->addField(\OffbeatWP\Form\Fields\Post::make('http-status-page-' . $statusCode, 'Page for ' . $statusCode)->fromPostTypes(['page']));
        });

        return $form;
    }
}
