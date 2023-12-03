<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_switch extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'framework-switcher';
        echo parent::before_tags($field) . self::switch($field) . parent::after_tags($field);
    }

    public function switch($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = 0;
        }

        $active = ($field['default'] == true) ? 'active' : '';

        //定义格式
        $html = '<label class="quick-switch autowidth" for="' . $field['id'] . '">';
        $html .= '<input type="hidden" "id="' . $field['id'] . '" name="' . $field['id'] . '" value="' . $field['default'] . '" />';
        $html .= '<div class="slider ' . $active . '"></div>';
        $html .= '</label>';

        return $html;
    }
}
