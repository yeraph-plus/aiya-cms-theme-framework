<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_editor extends AYA_Field_Action
{
    public function action($field)
    {
        self::editor($field);
    }

    public function editor($field)
    {


        $settings = array('tinymce' => 1, 'editor_height' => 300);

        if (isset($field['style']) && $field['style'] != '') {
            $settings['tinymce'] = array('content_css' => $field['style']);
        }

        if (isset($field['media']) && !$field['media']) {
            $settings['media_buttons'] = 0;
        } else {
            $settings['media_buttons'] = 1;
        }

        if (!empty($field['textarea_name'])) {
            $settings['textarea_name'] = $field['textarea_name'];
        }

        echo parent::before_tags($field);

        wp_editor($field['default'], $field['id'], $settings);

        echo parent::after_tags($field);
    }
}
