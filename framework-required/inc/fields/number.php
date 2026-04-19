<?php

//防止错位加载
if (!defined('ABSPATH') || !class_exists('AYA_Field_Action')) {
    exit;
}

/**
 * 数字输入框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_number extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::number($field) . parent::after_tags($field);
    }

    function number($field)
    {
        //定义格式
        $format = '<input class="quick-input" type="number" id="%s" name="%s" value="%s" style="width: 120px;" />';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}
