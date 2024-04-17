<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\SelectField;
use OffbeatWP\Form\Fields\TextField;

final class Link extends AbstractFieldsCollection
{
    public function __construct()
    {
        $this->addField(TextField::make('link_label', __('Link label', 'offbeatwp')));
        $this->addField(TextField::make('link_url', __('Link url', 'offbeatwp')));

        $linkTargetField = SelectField::make('link_target', __('Link target', 'offbeatwp'));
        $linkTargetField
            ->setOptions([
                '_self' => __('Self', 'offbeatwp'),
                '_blank' => __('Blank', 'offbeatwp'),
                '_parent' => __('Parent', 'offbeatwp'),
                '_top' => __('Top', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($linkTargetField);
    }
}