<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 创建Taxonomy额外字段
 * 
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Term_Meta')) exit;

class AYA_Framework_Term_Meta extends AYA_Field_Action
{
    private $options;
    private $option_array;

    private $unfined_field;

    function __construct($option_conf, $option_array)
    {
        $this->options = $option_conf;
        $this->option_array = $option_array;

        //定义禁用项
        $this->unfined_field = array('group', 'group_mult', 'code_editor', 'tinymce');

        //如果传入是数组
        if (is_array($option_array)) {
            //循环执行
            foreach ($this->option_array as $taxonomy) {
                add_action($taxonomy . '_add_form_fields', array(&$this, 'add_taxonomy_field'), 10, 2);
                add_action($taxonomy . '_edit_form_fields', array(&$this, 'edit_taxonomy_field'), 10, 2);

                add_action('created_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                add_action('edited_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                add_action('delete_' . $taxonomy, array(&$this, 'delete_taxonomy_field_data'), 10, 1);
            }
        } else {
            add_action($option_array . '_add_form_fields', array(&$this, 'add_taxonomy_field'), 10, 2);
            add_action($option_array . '_edit_form_fields', array(&$this, 'edit_taxonomy_field'), 10, 2);

            add_action('created_' . $option_array, array(&$this, 'save_taxonomy_field'), 10, 1);
            add_action('edited_' . $option_array, array(&$this, 'save_taxonomy_field'), 10, 1);
            add_action('delete_' . $option_array, array(&$this, 'delete_taxonomy_field'), 10, 1);
        }
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_script'));
    }
    //加载样式
    public function enqueue_script()
    {
        //加载JS文件
        wp_enqueue_style('aya-framework', AYF_URI . '/css/framework-style.css');
        wp_enqueue_script('aya-framework', AYF_URI . '/js/framework-main.js', '', '', true);
    }
    //创建字段
    function add_taxonomy_field()
    {
        echo '<div class="form-field framework-wrap">';

        foreach ($this->options as $option) {
            //排除不支持的组件
            if (in_array($option['type'], $this->unfined_field)) {
                continue;
            }

            parent::field($option);
        }

        echo '</div>';
    }
    //编辑字段
    function edit_taxonomy_field($tag)
    {
        foreach ($this->options as $option) {
            //排除不支持的组件
            if (in_array($option['type'], $this->unfined_field)) {
                continue;
            }
            //获取字段数据
            $feild_value = get_term_meta($tag->term_id, $option['id'], true);

            if ($feild_value != '') {
                $option['default'] = $feild_value;
            }
            //重排组件结构
            $add_class = '';

            if ($option['type'] == 'color') $add_class = ' framework-color-picker';
            if ($option['type'] == 'switch') $add_class = ' framework-switcher';
            if ($option['type'] == 'upload') $add_class = ' framework-upload';

            $html = '<div class="form-field framework-wrap">';
            $html .= '<tr class="section-' . $option['type'] . $add_class . '"><th scope="row">';
            $html .= '<label for="' . $option['id'] . '">' . $option['title'] . '</label>';
            $html .= '</th><td>';
            $html .= parent::field_mult($option);
            $html .= '<p class="description">' . $option['desc'] . '</p>';
            $html .= '</td></tr></div>';

            echo $html;
        }
    }
    //保存数据
    function save_taxonomy_field($term_id)
    {
        //用户权限检查
        if (!current_user_can('manage_categories')) return;

        foreach ($this->options as $option) {

            //$old_data = get_term_meta($term_id, $option['id'], true);

            //没有ID则直接跳过
            if (!isset($option['id'])) {
                continue;
            }
            $data = empty($_POST[$option['id']]) ? '' : $_POST[$option['id']];
            //如果是数组
            if ($option['type'] == 'array') {
                $data = explode(',', $data);
                $data = array_filter($data);
            }
            //其他
            else {
                //$data = wp_unslash($data);
                $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
            }

            if ($data == '') {
                delete_term_meta($term_id, $option['id'], $data);
            } else {
                update_term_meta($term_id, $option['id'], $data);
            }
        }
    }
    //删除数据
    function delete_taxonomy_field($term_id)
    {
        foreach ($this->options as $options) {
            if (isset($options['id'])) {
                delete_term_meta($term_id, $options['id']);
            }
        }
    }
}
