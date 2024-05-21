<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 创建Metabox组件
 * 
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Post_Meta')) exit;

class AYA_Framework_Post_Meta
{
    private $options;
    private $meta_info;

    private $unfined_field;

    function __construct($options, $meta_info)
    {
        $this->options = $options;
        $this->meta_info = $meta_info;

        //定义禁用项
        $this->unfined_field = array('group', 'group_mult', 'code_editor', 'tinymce');

        add_action('admin_menu', array(&$this, 'init_metaboxes'));

        add_action('post_updated', array(&$this, 'save_podefaultata'), 9);
        add_action('save_post', array(&$this, 'save_podefaultata'));

        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_script'));
    }
    //加载样式
    public function enqueue_script()
    {
        //加载JS文件
        wp_enqueue_style('aya-framework', AYF_URI . '/css/framework-style.css');
        wp_enqueue_script('aya-framework', AYF_URI . '/js/framework-main.js', '', '', true);
    }
    public function init_metaboxes()
    {
        $areas = $this->meta_info['add_box_in'];
        if (function_exists('add_meta_box') && is_array($areas)) {
            foreach ($this->meta_info['add_box_in'] as $area) {
                //检查模板参数
                if (isset($this->meta_info['template']) && $area == 'page') {
                    if (isset($_GET['post'])) {
                        $post_id = $_GET['post'];
                    } else {
                        $post_id = 0;
                    }

                    $page_template = get_post_meta($post_id, '_wp_page_template', true);

                    if ($this->meta_info['template'] == $page_template) {
                        add_meta_box(
                            $this->meta_info['id'],
                            $this->meta_info['title'],
                            array(&$this, 'create_metabox'),
                            $area,
                            $this->meta_info['context'],
                            $this->meta_info['priority']
                        );
                    }
                } else {
                    add_meta_box(
                        $this->meta_info['id'],
                        $this->meta_info['title'],
                        array(&$this, 'create_metabox'),
                        $area,
                        $this->meta_info['context'],
                        $this->meta_info['priority']
                    );
                }
            }
        }
    }

    public function create_metabox()
    {
        if (isset($_GET['post']))
            $post_id = $_GET['post'];
        else
            $post_id = 0;

        echo '<div class="tab-content framework-wrap">';

        foreach ($this->options as $option) {
            //排除不支持的组件
            if (in_array($option['type'], $this->unfined_field)) {
                continue;
            }
            //获取字段数据
            $meta_value = get_post_meta($post_id, $option['id'], true);

            if ($meta_value != '') {
                $option['default'] = $meta_value;
            }
            AYA_Field_Action::field($option);
        }

        echo '</div>';
    }

    public function save_podefaultata($post_id)
    {
        //跳过自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['post_type']) && in_array($_POST['post_type'], $this->meta_info['add_box_in'])) {

            //用户权限检查
            if ('page' == $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id))
                    return false;
            } else {
                if (!current_user_can('edit_post', $post_id))
                    return false;
            }

            foreach ($this->options as $option) {

                //$old_data = get_post_meta($post_id, $option['id']);

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
                    delete_post_meta($post_id, $option['id'], $data);
                } else {
                    update_post_meta($post_id, $option['id'], $data);
                }
            }
        }
    }
}
