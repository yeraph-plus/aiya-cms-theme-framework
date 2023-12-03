<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Request extends AYA_Theme_Setup
{
    var $query_options;

    public function __construct($args)
    {
        $this->query_options = $args;
    }

    public function __destruct()
    {
        parent::add_filter('pre_get_posts', 'aya_theme_pre_get_posts');
        parent::add_filter('request', 'aya_theme_pre_request');
    }

    public function aya_theme_pre_get_posts($query)
    {
        //如果是后台
        if (is_admin()) return $query;

        //如果不是主查询
        if (!$query->is_main_query()) return $query;

        //开始生成设置参数
        $options = $this->query_options;

        //默认包含帖子类型
        $post_type = array('post');
        //默认文章状态
        $post_status = array('publish');
        //默认置顶
        $sticky_type = ($options['query_ignore_sticky'] == true) ? 1 : 0;

        //定义转换方法
        function ignore_map($vaule)
        {
            return -$vaule;
        }

        global $aya_post_type;
        //添加自定义类型
        if ($options['query_post_type_var'] == true) {
            //合并数组
            $self_post_type = is_array($aya_post_type) ? $aya_post_type : array();
            $post_type = array_merge($post_type, $self_post_type);
        }
        //首页循环
        if ($query->is_home()) {
            //排除内容
            $rule_home_post = array();
            $rule_home_cat = array();

            if ($options['query_ignore_category'] != '') {
                //重建数组
                $self_home_cat = explode(',', $options['query_ignore_category']);
                $self_home_cat = array_map('ignore_map', $self_home_cat);
                $rule_home_cat = is_array($self_home_cat) ? $self_home_cat : array();
            }
            if ($options['query_ignore_post'] != '') {
                //重建数组
                $self_home_post = explode(',', $options['query_ignore_post']);
                $rule_home_post = is_array($self_home_post) ? $self_home_post : array();
            }

            //创建Query参数
            $query->set('post_type', $post_type);
            $query->set('ignore_sticky_posts',  $sticky_type);
            $query->set('cat', $rule_home_cat);
            $query->set('post__not_in', $rule_home_post);
        }
        //搜索结果
        if ($query->is_search()) {
            //添加页面
            if ($options['search_page_type'] == true) {
                $post_type[] = 'page';
            }
            //排除内容
            $rule_serach_post = array();
            $rule_serach_cat = array();

            if ($options['serach_ignore_category'] != '') {
                //重建数组
                $self_serach_cat = explode(',', $options['serach_ignore_category']);
                $rule_serach_cat = is_array($self_serach_cat) ? $self_serach_cat : array();
                $rule_serach_cat = array_map('ignore_map', $rule_serach_cat);
            }
            if ($options['serach_ignore_post'] != '') {
                //重建数组
                $self_serach_post = explode(',', $options['serach_ignore_post']);
                $rule_serach_post = is_array($self_serach_post) ? $self_serach_post : array();
            }

            //创建Query参数
            $query->set('post_type', $post_type);
            $query->set('cat', $rule_serach_cat);
            $query->set('post__not_in', $rule_serach_post);
        }
        //用户页面
        if ($query->is_author()) {

            if ($options['query_author_current'] == true && is_user_logged_in()) {
                //获取作者和登录者身份
                $current_user_can = current_user_can('publish_pages');
                $current_user_id = get_current_user_id();
                $user_id =  get_query_var('author');
                //判断是否为本人
                if ($user_id == $current_user_id || $current_user_can) {
                    //输出时包含文章状态
                    $post_status = array('publish', 'draft', 'pending', 'future', 'private', 'trash');
                }
            }
            //创建Query参数
            $query->set('post_type', $post_type);
            $query->set('post_status', $post_status);
        }
        //返回
        return $query;
    }
    //操作request钩子
    function aya_theme_pre_request($query)
    {
        $options = $this->query_options;

        //获取搜索词，当搜索关键词为空返回首页
        if (isset($_GET['s']) && !is_admin() && $options['search_redirect_intend'] == true) {
            if (empty($_GET['s']) || ctype_space($_GET['s'])) {
                wp_redirect(home_url());
                //退出
                exit;
            }
        }
        //当搜索结果只有一篇时直接跳转到文章页面
        if (is_search() && $options['search_redirect_request'] == true) {
            global $wp_query;
            if ($wp_query->post_count == 1) {

                wp_redirect(get_permalink($wp_query->posts['0']->ID));
                //退出
                exit;
            }
        }
        return $query;
    }
}
