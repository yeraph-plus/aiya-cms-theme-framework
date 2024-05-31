<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_code_editor extends AYA_Field_Action
{
    public function action($field)
    {
        if (empty($field['settings']) || !is_array($field['settings'])) {
            $field['settings'] = array(
                'lineNumbers'   => true, //显示行号
                'tabSize'       => 2,
                'theme'         => 'monokai', //主题
                'mode'          => 'htmlmixed', //HMTL混合模式
            );
        }

        //调用codemirror
        wp_enqueue_style('codemirror', 'https://cdn.staticfile.net/codemirror/5.65.16/codemirror.min.css');
        wp_enqueue_script('codemirror', 'https://cdn.staticfile.net/codemirror/5.65.16/codemirror.min.js', '', '', false);

        wp_enqueue_script('codemirror-mode', 'https://cdn.staticfile.net/codemirror/6.65.7/mode/' . $field['settings']['mode'] . '/' . $field['settings']['mode'] . '.min.js');
        wp_enqueue_style('codemirror-theme', 'https://cdn.staticfile.net/codemirror/6.65.7/theme/' . $field['settings']['theme'] . '.min.css');

        $field['class'] = 'codemirror-editor';

        return parent::before_tags($field) . self::code_editor($field) . parent::after_tags($field);
    }

    function code_editor($field)
    {
        //定义格式
        $format = '<textarea class="autowidth" id="%s" name="%s" data-editor="%s">%s</textarea>';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            esc_attr(json_encode($field['settings'])),
            $field['default'],
        );

        return $html;
    }
}
