<?php
namespace OffbeatWP\Components;

use OffbeatWP\AcfCore\FieldsMapper as AcfFieldsMapper;
use OffbeatWP\Components\ComponentInterfaceTrait;
use OffbeatWP\Fields\Helper as FieldsHelper;


class GenericWidget extends \WP_Widget
{
    use ComponentInterfaceTrait;

    public $widgetId = null;
    public $settings = null;
    public $componentClass = null;

    public function __construct($settings, $componentClass)
    {
        $this->settings =       $settings;
        $this->componentClass = $componentClass;

        $options = (isset($settings['options'])) ? : [];

        parent::__construct(
            $settings['id_base'],
            $settings['name'],
            $options
        );

        $componentSettings = $componentClass::settings();

        add_action('admin_init', [$this, 'registerForm']);
    }

    public function widget($args, $instance)
    {
        $this->widgetId = "widget_" . $args["widget_id"];

        if (method_exists($this, 'setWidgetSettings')) {
            $this->setWidgetSettings();
        }

        $this->initWidget($args, $instance);
    }

    // TODO Quick implementation, needs to be improved
    public function initWidget($args, $instance)
    {
        echo $args['before_widget'];

        $form = $this->componentClass::getForm();
        $defaultAtts = FieldsHelper::getDefaults($form);

        echo $this->render($this->getFieldValues(array_keys($defaultAtts)));

        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['widget_exists'] = 1;

        return $instance;
    }

    public function form($instance)
    {
        echo "<br>";
    }

    public function the_field($key)
    {
        echo $this->get_field($key);
    }

    public function get_field($key)
    {
        return get_field($key, $this->widgetId);
    }

    public function getFieldValues($keys)
    {
        $settings = (object)[];

        foreach ($keys as $key) {
            $settings->{$key} = $this->get_field($key);
        }

        return $settings;
    }

    public function registerForm () {
        if( ! function_exists('acf_add_local_field_group') ) return null;

        $form = $this->componentClass::getForm();

        $fieldsMapper = new AcfFieldsMapper($form);

        acf_add_local_field_group(array (
            'key' => 'group_widget_' . $this->settings['id_base'],
            'title' => 'Widget settings - ' . $this->settings['name'],
            'fields' => $fieldsMapper->map(),
            'location' => array (
                array (
                    array (
                        'param' => 'widget',
                        'operator' => '==',
                        'value' => $this->settings['id_base'],
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ));
    }
}