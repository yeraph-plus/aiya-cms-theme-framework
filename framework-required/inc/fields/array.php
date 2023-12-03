<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_array extends AYA_Field_Action
{
    public function action($field)
    {
        $field['desc'] = $field['desc'] . '通过<code>,</code>分隔多个值';
        echo parent::before_tags($field) . self::array($field) . parent::after_tags($field);
    }

    public function array($field)
    {
        //检查数据
        if (!empty($field['default']) && is_array($field['default'])) {
            $this_array = implode(',', $field['default']);
        } else {
            $this_array = '';
        }

        $field['default'] = $this_array;

        //加载方法
        return parent::field_mult('text', $field);
    }
}
