<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_code_editor extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'codemirror-editor';
        echo parent::before_tags($field) . self::code_editor($field) . parent::after_tags($field);
    }

    function code_editor($field)
    {
        $default_settings = array(
            'lineNumbers'   => true, //显示行号
            'tabSize'       => 2,
            'theme'         => 'monokai', //主题
            'mode'          => 'htmlmixed', //HMTL混合模式
        );

        $settings = (!empty($field['settings'])) ? $field['settings'] : array();
        $settings = wp_parse_args($settings, $default_settings);

        //定义格式
        $format = '<textarea class="autowidth" id="%s" name="%s" data-editor="%s">%s</textarea>';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            esc_attr(json_encode($settings)),
            $field['default'],
        );

        return $html;
    }
}
