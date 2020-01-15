<?php
namespace OffbeatWP\SiteSettings;

class SettingsScripts
{
    const ID = 'scripts';
    const PRIORITY = 90;

    public function title()
    {
        return __('Scripts', 'raow');
    }

    public function form()
    {
        $form = new \OffbeatWP\Form\Form();

        $form->addField(\OffbeatWP\Form\Fields\Textarea::make('scripts_head', 'Head')->attribute('new_lines', 0));
        $form->addField(\OffbeatWP\Form\Fields\Textarea::make('scripts_open_body', 'Body open')->attribute('new_lines', 0));
        $form->addField(\OffbeatWP\Form\Fields\Textarea::make('scripts_footer', 'Footer')->attribute('new_lines', 0));

        return $form;
    }
}
