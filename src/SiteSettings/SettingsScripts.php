<?php
namespace OffbeatWP\SiteSettings;

use OffbeatWP\Form\Fields\TextareaField;
use OffbeatWP\Form\Form;

class SettingsScripts
{
    public const ID = 'scripts';
    public const PRIORITY = 90;

    public function title(): string
    {
        return __('Scripts', 'offbeatwp');
    }

    public function form(): Form
    {
        $form = new Form();

        $form->addField(TextareaField::make('scripts_head', 'Head')->attribute('new_lines', 0));
        $form->addField(TextareaField::make('scripts_open_body', 'Body open')->attribute('new_lines', 0));
        $form->addField(TextareaField::make('scripts_footer', 'Footer')->attribute('new_lines', 0));

        return $form;
    }
}
