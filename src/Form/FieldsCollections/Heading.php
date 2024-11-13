<?php

namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Form\Fields\Text;

class Heading extends AbstractFieldsCollection
{
    /** @param string $defaultHeading */
    public function __construct($defaultHeading = 'h3')
    {
        parent::__construct();
        $this->addField(Text::make('heading_title', __('Title', 'pinowp')));

        $headingTypeField = Select::make('heading_type', __('Type', 'pinowp'));
        $headingTypeField
            ->addOptions([
                'h1' => __('h1', 'pinowp'),
                'h2' => __('h2', 'pinowp'),
                'h3' => __('h3', 'pinowp'),
                'h4' => __('h4', 'pinowp'),
                'h5' => __('h5', 'pinowp'),
                'h6' => __('h6', 'pinowp'),
                'div' => __('div', 'pinowp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'pinowp'))
            ->default($defaultHeading);

        $this->addField($headingTypeField);

        $headingStyleField = Select::make('heading_style', __('Style', 'pinowp'));
        $headingStyleField
            ->addOptions([
                '' => __('Default', 'pinowp'),
                'h1' => __('h1', 'pinowp'),
                'h2' => __('h2', 'pinowp'),
                'h3' => __('h3', 'pinowp'),
                'h4' => __('h4', 'pinowp'),
                'h5' => __('h5', 'pinowp'),
                'h6' => __('h6', 'pinowp'),
            ])
            ->description(__('The heading style is used to override the default styling of the header type', 'pinowp'));

        $this->addField($headingStyleField);
    }
}
