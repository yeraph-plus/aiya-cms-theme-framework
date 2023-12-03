<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_select extends AYA_Field_Action
{
    public function action($field)
    {
        echo parent::before_tags($field) . self::select($field) . parent::after_tags($field);
    }

    public function select($field)
    {
        //检查并返回数据
        $entries = parent::entry_select($field);

        $html = '<select class="quick-select autowidth" id="' . $field['id'] . '" name="' . $field['id'] . '"> ';

        $html .= '<option value="">Select...</option>';

        foreach ($entries as $id => $title) {

            $selected = ($field['default'] == $id) ? 'selected="selected"' : '';

            $html .= '<option ' . $selected . ' value="' . $id . '">' . $title . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}
