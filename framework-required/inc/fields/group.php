<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_group extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'clearfix';

        echo parent::before_tags($field) . self::group($field) . parent::after_tags($field);
    }


    public function group($field)
    {
        if (empty($field['default'])) {
            $field['default'] = array();
        }

        //检查是否为数组
        if (empty($field['sub_type']) || !is_array($field['sub_type'])) {
            $field['sub_type'] = array();
        }

        $html = '';
        //循环
        foreach ($field['sub_type'] as $field) {
            //加载方法
            $html .= parent::before_tags($field);
            $html .= parent::field_mult($field['type'], $field);
            $html .= parent::after_tags($field);
        }
        return $html;
    }
}
