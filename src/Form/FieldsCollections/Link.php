<?php
namespace OffbeatWP\Form\FieldsCollections;

class Link extends AbstractFieldsCollection {
    public function __construct()
    {
        $this->addField(new \OffbeatWP\Form\Fields\Text('link_label', __('Link label', 'offbeatwp')));
        $this->addField(new \OffbeatWP\Form\Fields\Text('link_url', __('Link url', 'offbeatwp')));

        $linkTargetField = new \OffbeatWP\Form\Fields\Select('link_target', __('Link target', 'offbeatwp'));
        $linkTargetField
            ->options([
                '_self' => __('Self', 'offbeatwp'),
                '_blank' => __('Blank', 'offbeatwp'),
                '_parent' => __('Parent', 'offbeatwp'),
                '_top' => __('Top', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($linkTargetField);

        $this->addField(new \OffbeatWP\Form\Fields\Text('link_data_target', __('Link data target', 'offbeatwp')));
    }
}