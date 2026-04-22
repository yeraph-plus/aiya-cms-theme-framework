<?php

//防止错位加载
if (!defined('ABSPATH') || !class_exists('AYA_Field_Action')) {
    exit;
}

/**
 * 一次性动作复选框
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 2.0
 */
class AYA_Option_Fired_action_checkbox extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = empty($field['class'])
            ? 'framework-action-checkbox'
            : $field['class'] . ' framework-action-checkbox';

        return parent::before_tags($field) . $this->action_checkbox($field) . parent::after_tags($field);
    }

    public function action_checkbox($field)
    {
        $label = !empty($field['label'])
            ? parent::preg_desc($field['label'])
            : __('执行本次操作', 'aiya-framework');
        $checked = !empty($field['default']);

        $html = '<label class="quick-checkbox autowidth" for="' . esc_attr($field['id']) . '">';
        $html .= '<input type="checkbox" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="1" ' . checked($checked, true, false) . ' />';
        $html .= $label;
        $html .= '</label>';

        return $html;
    }
}
