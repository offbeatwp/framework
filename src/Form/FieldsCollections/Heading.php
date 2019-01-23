<?php
namespace OffbeatWP\Form\FieldsCollections;

class Heading extends AbstractFieldsCollection {
    public function __construct($defaultHeading = 'h3', $includeLead = false)
    {
        $this->addField(new \OffbeatWP\Form\Fields\Text('heading_title', __('Title', 'offbeatwp')));

        $headingTypeField = new \OffbeatWP\Form\Fields\Select('heading_type', __('Type', 'offbeatwp'));
        $headingTypeField
            ->options([
                'h1' => __('h1', 'offbeatwp'),
                'h2' => __('h2', 'offbeatwp'),
                'h3' => __('h3', 'offbeatwp'),
                'h4' => __('h4', 'offbeatwp'),
                'h5' => __('h5', 'offbeatwp'),
                'h6' => __('h6', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($headingTypeField);

        $headingStyleField = new \OffbeatWP\Form\Fields\Select('heading_style', __('Style', 'offbeatwp'));
        $headingStyleField
            ->options([
                'h1' => __('h1', 'offbeatwp'),
                'h2' => __('h2', 'offbeatwp'),
                'h3' => __('h3', 'offbeatwp'),
                'h4' => __('h4', 'offbeatwp'),
                'h5' => __('h5', 'offbeatwp'),
                'h6' => __('h6', 'offbeatwp'),
            ])
            ->description(__('The heading style is used to override the default styling of the header type', 'offbeatwp'));

        $this->addField($headingStyleField);
    }
}