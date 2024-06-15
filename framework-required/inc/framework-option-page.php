<?php
if (!defined('ABSPATH')) exit;


/**
 * AIYA-CMS Theme Options Framework 创建设置页面
 * 
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Options_Page')) {
    class AYA_Framework_Options_Page
    {
        private $option_info;
        private $options;

        private $menu_slug;
        private $menu_icon;
        private $menu_parent;
        private $page_title;
        private $menu_title;

        private $in_multisite;

        private $old_value;
        private $saved_value;

        private $unfined_saved;

        private $saved_message;

        public function __construct($option_conf, $option_info)
        {
            //验证用户是否为管理员
            if (!current_user_can('manage_options')) return;

            $this->option_info = $option_info;
            $this->options = $option_conf;

            $this->menu_slug = (!empty($option_info['slug'])) ? $option_info['slug'] : 'default';
            $this->menu_icon = (!empty($option_info['icon'])) ? $option_info['icon'] : '';
            $this->page_title = (key_exists('page_title', $option_info) && !empty($option_info['page_title'])) ? $option_info['page_title'] : $option_info['title'];
            $this->menu_title = (!empty($option_info['title'])) ? $option_info['title'] : 'Default';
            //判断子菜单
            $this->menu_parent = (key_exists('parent', $option_info) && !empty($option_info['parent'])) ? $option_info['parent'] : '';
            //定义保存键名
            $this->saved_value = 'aya_opt_' . $option_info['slug'];
            //定义保存按钮排除
            $this->unfined_saved = array('callback', 'content', 'message', 'success', 'error', 'title_h1', 'title_h2', 'title_h3');
            //检查多站点
            $this->in_multisite = $this->in_multisite($option_info);

            //多站点兼容
            add_action($this->in_multisite ? 'network_admin_menu' : 'admin_menu', array(&$this, 'add_admin_menu_page'));

            //定位页面
            if (isset($_GET['page']) && ($_GET['page'] == $this->menu_slug)) {
                //加载
                add_action('admin_enqueue_scripts', array(&$this, 'enqueue_ajax_script'));
            }
        }
        //创建页面
        public function add_admin_menu_page()
        {
            $menu_slug = $this->menu_slug;
            $menu_icon = $this->menu_icon;
            $parent_slug = $this->menu_parent;
            $menu_title = $this->menu_title;
            $page_title = $this->page_title;
            /**
             * WP默认的菜单位置顺序如下：
             * 2 Dashboard
             * 4 Separator
             * 5 Posts
             * 10 Media
             * 15 Links
             * 20 Pages
             * 25 Comments
             * 59 Separator
             * 60 Appearance
             * 65 Plugins
             * 70 Users
             * 75 Tools
             * 80 Settings
             * 99 Separator
             */

            if ($parent_slug == '') {
                add_menu_page($page_title, $menu_title,  'manage_options', $menu_slug, array(&$this, 'init_page'), $menu_icon, 99);
            } else {
                add_submenu_page($parent_slug, $page_title,  $menu_title, 'manage_options', $menu_slug, array(&$this, 'init_page'), 99);
            }
        }
        //加载样式
        public function enqueue_ajax_script()
        {
            //Ajax
            wp_localize_script('aya-framework-ajax', 'aya_framework', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'base_url' => includes_url(),
                'cdn_url' => 'https://cdn.staticfile.net/',
            ));
        }
        //定义执行顺序
        public function init_page()
        {
            self::save_options_data();

            self::data_options_available();

            self::display_html();
        }
        //检查多站点设置
        private function in_multisite($info)
        {
            if (is_multisite()) {
                //检查设置表单
                if (isset($info['in_multisite']) && $info['in_multisite'] == true) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        //循环 htmlspecialchars 方法处理多层数组
        public function deep_htmlspecialchars($mixed, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
        {
            if (is_array($mixed)) {
                foreach ($mixed as $key => $value) {
                    $mixed[$key] = $this->deep_htmlspecialchars($value, $quote_style, $charset);
                }
            } elseif (is_string($mixed)) {
                $mixed = htmlspecialchars_decode($mixed, $quote_style);
            }
            return $mixed;
        }
        //提取数据
        public function data_options_available()
        {
            //多站点兼容
            if ($this->in_multisite) {
                $this->old_value[$this->option_info['slug']] = get_site_option($this->saved_value);
            } else {
                $this->old_value[$this->option_info['slug']] = get_option($this->saved_value);
            }
            //Fix
            $this->old_value = $this->deep_htmlspecialchars($this->old_value, ENT_QUOTES, 'UTF-8');

            foreach ($this->options as $key => $option) {
                //选项不存在ID则跳过
                if (isset($option['id']) && isset($this->old_value[$this->option_info['slug']][$option['id']])) {
                    //格式化数据
                    $this->options[$key]['default'] = $this->old_value[$this->option_info['slug']][$option['id']];
                }
            }
        }
        //保存数据
        public function save_options_data()
        {
            //验证用户是否为管理员
            if (!current_user_can('manage_options')) return;

            //获取设置数据
            $new_value  = $this->old_value;

            //检查From表单
            if (isset($_REQUEST['aya_option_field']) && check_admin_referer('aya_option_action', 'aya_option_field')) {
                //清除旧数据
                if (!empty($_POST['aya_option_reset'])) {
                    //多站点兼容
                    if ($this->in_multisite) {
                        delete_site_option($this->saved_value);
                    } else {
                        delete_option($this->saved_value);
                    }
                    //提示
                    $this->saved_message = __('Options reseted.');
                }
                //存入新数据
                if (!empty($_POST['aya_option_submit'])) {
                    //array('option_name' => 'option_value');
                    $new_value = array();
                    //处理数据
                    foreach ($this->options as $option) {
                        //没有ID则跳过循环
                        if (in_array($option['type'], $this->unfined_saved) || !isset($option['id'])) {
                            continue;
                        }

                        $value = (empty($_POST[$option['id']])) ? '' : $_POST[$option['id']];

                        //如果是输入框
                        if ($option['type'] == 'text' || $option['type'] == 'textarea') {
                            //$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                            $value = stripslashes($value);
                        }
                        //如果是复选框
                        elseif ($option['type'] == 'checkbox') {
                            $value = ($value == '') ? [] : $value;
                        }
                        //如果是数组
                        elseif ($option['type'] == 'array') {
                            $value = explode(',', $value);
                            $value = array_filter($value);
                        }
                        //如果是编辑器
                        elseif ($option['type'] == 'tinymce') {
                            $value = wp_unslash($value);
                        }
                        //如果是代码框
                        elseif ($option['type'] == 'code_editor') {
                            //$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                            $value = $value;
                        }
                        //如果是设置组
                        elseif ($option['type'] == 'group' || $option['type'] == 'group_mult') {
                            $value = ($value == '') ? [] : $value;
                            $value = array_filter($value);
                        }
                        //其他
                        else {
                            $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                        }

                        $new_value[$option['id']] = $value;
                    }
                    //防止重复提交
                    if ($this->old_value != $new_value) {
                        //多站点兼容
                        if ($this->in_multisite) {
                            update_site_option($this->saved_value, $new_value);
                        } else {
                            update_option($this->saved_value, $new_value);
                        }
                        //提示
                        $this->saved_message = __('Options saved.');
                    } else {
                        //提示
                        $this->saved_message = __('Options has already been saved.');
                    }
                }
            }
        }
        //HTML结构
        public function display_html()
        {
            $before_html = '';
            $after_html = '';

            $before_html .= '<div class="wrap" id="framework-page">';

            $before_html .= '<div class="container framework-title">';
            $before_html .= '<h1>' . esc_html($this->option_info['title']) . '</h1>';
            if (!empty($this->option_info['desc'])) {
                $before_html .= '<p>' . esc_html($this->option_info['desc']) . '</p>';
            }
            $before_html .= '</div>';

            //保存提示
            if (!empty($this->saved_message)) {
                $before_html .= '<div class="container framework-content framework-saved-success">';
                $before_html .= '<p>' . esc_html($this->saved_message) . '</p>';
                $before_html .= '</div>';
            }

            $before_html .= '<div class="container framework-content framework-wrap">';

            //表单结构
            $before_html .= '<form method="post" id="from-wrap" action="#saved">';

            echo $before_html;

            //保存按钮
            $saved_button = false;
            //循环
            foreach ($this->options as $option) {
                //如果是文本框则转换一次数据
                if (in_array($option['type'], array('text', 'textarea'))) {
                    $option['default'] = htmlspecialchars($option['default'], ENT_COMPAT, 'UTF-8');
                }
                //组件方法
                AYA_Field_Action::field($option);

                //放置保存按钮
                if (!in_array($option['type'], $this->unfined_saved)) {
                    $saved_button = true;
                }
            }
            //保存按钮
            if ($saved_button) {
                $button_html = '<div class="field-saved-button">';
                //Fix：检索表单的nonce隐藏字段
                wp_nonce_field('aya_option_action', 'aya_option_field');

                $button_html .= '<input type="submit" name="aya_option_submit" class="button-primary autowidth" value="' . esc_html__('Save Changes') . '" />';
                $button_html .= '<input type="submit" name="aya_option_reset" class="button-secondary autowidth" value="' . esc_html__('Clear') . '" />';

                $button_html .= '</div>';

                echo $button_html;
            }

            $after_html = '</form>';

            $after_html .= '</div>';

            echo $after_html;
        }
    }
}
