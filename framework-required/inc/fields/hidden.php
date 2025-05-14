<?php

//防止错位加载
if (!defined('ABSPATH') || !class_exists('AYA_Field_Action')) {
    exit;
}

/**
 * 隐写输入框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_hidden extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::text($field) . parent::after_tags($field);
    }

    function text($field)
    {
        //定义格式
        $format = '<input type="hidden" id="%s" name="%s" value="%s" />';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}