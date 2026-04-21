<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS Theme Options Framework 创建Metabox组件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 2.0
 **/

if (!class_exists('AYA_Framework_Post_Meta')) {
    class AYA_Framework_Post_Meta
    {
        private $options;
        private $meta_inst;

        private $render_unfined_field;
        private $save_unfined_field;

        function __construct($options, $meta_inst)
        {
            $this->options = $options;
            $this->meta_inst = $meta_inst;

            //定义禁用项
            $this->render_unfined_field = array('group', 'group_mult', 'code_editor', 'tinymce', 'content', 'message', 'success', 'dismiss', 'warning');
            $this->save_unfined_field = array_merge($this->render_unfined_field, array('action_checkbox'));

            add_action('admin_menu', array(&$this, 'init_metaboxes'));

            //只在 save_post 执行，并放到队列最后，避免被其他组件二次写入覆盖
            add_action('save_post', array(&$this, 'save_podefaultata'), 999);
        }

        private function get_metabox_meta_key()
        {
            return 'aya_box_' . $this->meta_inst['id'];
        }

        private function get_metabox_values($post_id)
        {
            $meta_value = get_post_meta($post_id, $this->get_metabox_meta_key(), true);
            return is_array($meta_value) ? $meta_value : array();
        }

        public function init_metaboxes()
        {
            $meta_box_areas = $this->meta_inst['add_box_in'];

            if (function_exists('add_meta_box') && is_array($meta_box_areas)) {
                foreach ($meta_box_areas as $meta_area) {
                    //检查模板参数，兼容页面、指定页面
                    if (isset($this->meta_inst['template']) && $meta_area == 'page') {
                        if (isset($_GET['post'])) {
                            $post_id = $_GET['post'];
                        } else {
                            $post_id = 0;
                        }

                        $page_template = get_post_meta($post_id, '_wp_page_template', true);

                        if ($this->meta_inst['template'] == $page_template) {
                            add_meta_box(
                                $this->meta_inst['id'],
                                $this->meta_inst['title'],
                                array(&$this, 'create_meta_box'),
                                $meta_area,
                                $this->meta_inst['context'],
                                $this->meta_inst['priority']
                            );
                        }
                    } else {
                        add_meta_box(
                            $this->meta_inst['id'],
                            $this->meta_inst['title'],
                            array(&$this, 'create_meta_box'),
                            $meta_area,
                            $this->meta_inst['context'],
                            $this->meta_inst['priority']
                        );
                    }
                }
            }
        }

        public function create_meta_box()
        {
            if (isset($_GET['post']))
                $post_id = $_GET['post'];
            else
                $post_id = 0;

            $metabox_values = $this->get_metabox_values($post_id);

            echo '<div class="tab-content framework-section">';

            if (!empty($this->meta_inst['desc'])) {
                echo '<p class="form-field field-message">' . $this->meta_inst['desc'] . '</p>';
            }

            wp_nonce_field($this->meta_inst['id'] . '_meta_box_action', $this->meta_inst['id'] . '_meta_box_nonce');

            foreach ($this->options as $option) {
                //跳过空的数组定义
                if (empty($option)) {
                    continue;
                }
                //排除无ID的组件
                if (empty($option['id'])) {
                    continue;
                }
                //排除不支持的组件
                if (in_array($option['type'], $this->render_unfined_field)) {
                    continue;
                }
                //从单条 metabox 记录中获取字段数据
                if (isset($metabox_values[$option['id']]) && $metabox_values[$option['id']] !== '') {
                    $option['default'] = $metabox_values[$option['id']];
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

            //仅处理来自本框架 metabox 的提交，避免其他 save_post 触发误删
            $nonce_field = $this->meta_inst['id'] . '_meta_box_nonce';
            $nonce_action = $this->meta_inst['id'] . '_meta_box_action';
            if (!isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
                return;
            }

            if (isset($_POST['post_type']) && in_array($_POST['post_type'], $this->meta_inst['add_box_in'])) {

                //用户权限检查
                if ('page' == $_POST['post_type']) {
                    if (!current_user_can('edit_page', $post_id))
                        return false;
                } else {
                    if (!current_user_can('edit_post', $post_id))
                        return false;
                }

                $metabox_data = array();

                foreach ($this->options as $option) {

                    //跳过空的数组定义
                    if (empty($option)) {
                        continue;
                    }
                    //没有ID则直接跳过
                    if (!isset($option['id'])) {
                        continue;
                    }
                    //排除不支持的组件
                    if (in_array($option['type'], $this->save_unfined_field)) {
                        continue;
                    }

                    $data = empty($_POST[$option['id']]) ? '' : wp_unslash($_POST[$option['id']]);

                    //如果是数组
                    if ($option['type'] == 'array') {
                        $data = explode(',', $data);
                        $data = array_filter($data);
                    }
                    //其他
                    else {
                        $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
                    }

                    //空值时不写入当前字段
                    if ($data === '' || $data === array()) {
                        continue;
                    }

                    $metabox_data[$option['id']] = $data;
                }

                if (empty($metabox_data)) {
                    delete_post_meta($post_id, $this->get_metabox_meta_key());
                } else {
                    update_post_meta($post_id, $this->get_metabox_meta_key(), $metabox_data);
                }
            }
        }
    }
}
