<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_color extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'framework-color-picker';

        echo parent::before_tags($field) . self::color($field) . parent::after_tags($field);
    }

    public function color($field)
    {
        //加载方法
        return parent::field_mult('text', $field);
    }
}
