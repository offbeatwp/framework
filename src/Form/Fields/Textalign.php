<?php
namespace OffbeatWP\Form\Fields;

class Textalign extends Select {
    public function getOptions () {
        return [
            ''              => __('Default', 'offbeatwp'),
            'left'          => __('Left', 'offbeatwp'),
            'center'        => __('Center', 'offbeatwp'),
            'right'         => __('Right', 'offbeatwp'),
        ];
    }
}