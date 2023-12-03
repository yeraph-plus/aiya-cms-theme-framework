<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_textarea extends AYA_Field_Action
{
    public function action($field)
    {
        echo parent::before_tags($field) . self::textarea($field) . parent::after_tags($field);
    }

    function textarea($field)
    {
        //定义格式
        $format = '<textarea class="quick-textarea autowidth" id="%s" name="%s" >%s</textarea>';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}
