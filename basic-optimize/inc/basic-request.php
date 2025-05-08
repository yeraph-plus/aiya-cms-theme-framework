<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 WP搜索和主查询SQL查询优化插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.5
 **/

class AYA_Plugin_Request
{
    public $query_options;

    public function __construct($args)
    {
        $this->query_options = $args;
    }

    public function __destruct()
    {
        add_action('init', array($this, 'aya_theme_serach_on_init'));
        add_action('template_redirect', array($this, 'aya_theme_serach_template_redirect'));
        add_action('template_redirect', array($this, 'aya_theme_serach_clause_redirect'));

        add_filter('request', array($this, 'aya_theme_serach_pre_request'));
        add_filter('pre_get_posts', array($this, 'aya_theme_pre_get_posts'));

        //SQL优化拓展
        add_filter('posts_clauses', array($this, 'aya_theme_sql_set_filter'), 10, 2);
    }
    //增加一些验证阻止非法访问
    public function aya_theme_serach_on_init()
    {
        //管理员用户跳过
        if (is_user_logged_in() && current_user_can('manage_options')) return;

        if (
            strlen($_SERVER['REQUEST_URI']) > 255 ||
            strpos($_SERVER['REQUEST_URI'], "eval(") ||
            strpos($_SERVER['REQUEST_URI'], "base64") ||
            strpos($_SERVER['REQUEST_URI'], "/**/")
        ) {
            @header("HTTP/1.1 414 Request-URI Too Long");
            @header("Status: 414 Request-URI Too Long");
            @header("Connection: Close");

            exit;
        }
    }
    //配置重定向
    public function aya_theme_serach_template_redirect()
    {
        $options = $this->query_options;

        //不是搜索则跳过
        if (is_admin() || !is_search()) return;

        //配置重定向
        if ($options['search_redirect_search_page'] === true) {

            if (!empty($_GET['s'])) {

                wp_redirect(home_url('search') . '/' . urlencode($_GET['s']));

                exit;
            }
        }
    }
    //搜索条件重定向
    public function aya_theme_serach_clause_redirect()
    {
        $options = $this->query_options;

        //不是搜索则跳过
        if (is_admin() || !is_search()) return;

        $search_vars = get_query_var('s');

        //检查搜索关键词为空
        if ($options['search_redirect_intend'] == true) {
            //返回首页
            if (empty($search_vars)) {
                wp_redirect(home_url());

                exit;
            }
        }
        //用户搜索权限验证
        if ($options['serach_redirect_scope_enable'] == true) {

            self::aya_theme_search_permission();
            self::aya_theme_search_length($search_vars);
            self::aya_theme_serach_limit();
        }
        //检查搜索关键词是否只有一个结果
        if ($options['search_redirect_one_post'] == true) {
            //当搜索结果只有1页时，重新读取wp_query验证public数据
            if (get_query_var('paged') <= 1) {
                global $wp_query;

                if ($wp_query->post_count == 1) {
                    //跳转到文章页面
                    wp_redirect(get_permalink($wp_query->posts['0']->ID));

                    exit;
                }
            }
        }
    }
    //操作pre_get_posts钩子
    public function aya_theme_pre_get_posts(\WP_Query $query)
    {
        //如果是后台
        if (is_admin()) return $query;

        //如果不是主查询
        if (!$query->is_main_query()) return $query;

        //获取设置
        $options = $this->query_options;

        //开始生成查询参数

        //添加'no_found_rows'属性
        if ($options['query_no_found_rows'] === true) {
            $query->set('no_found_rows', true);
        }

        //默认包含帖子类型
        $post_type = array('post');
        //默认文章状态
        $post_status = array('publish');

        //定义转换方法
        function ignore_map($vaule)
        {
            return -$vaule;
        }

        //添加自定义类型
        if ($options['query_post_type_var'] == true) {
            //合并数组
            if (isset($GLOBALS['aya_post_type']) && is_array($GLOBALS['aya_post_type'])) {
                $post_type = array_merge($post_type, $GLOBALS['aya_post_type']);
            }
        }
        //首页循环
        if (is_home()) {
            //排除内容
            $rule_home_post = array();
            $rule_home_cat = array();
            //排除内容
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

            //默认置顶
            $query->set('ignore_sticky_posts', ($options['query_ignore_sticky'] === true) ? true : false);
            $query->set('cat', $rule_home_cat);
            $query->set('post__not_in', $rule_home_post);
        }
        //搜索结果
        if (is_search()) {
            //添加页面
            if ($options['search_ignore_page_type'] == true) {
                $post_type[] = 'page';
            }
            //排除内容
            $rule_serach_cat = array();
            $rule_serach_post = array();
            //排除内容
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
        if (is_author()) {
            //验证登录
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
    public function aya_theme_serach_pre_request($query)
    {
        $options = $this->query_options;

        //匹配分类
        if ($options['search_request_term_exists'] == true) {
            //检查是否与分类或标签匹配
            $term_id = term_exists(get_query_var('s'));
            $term = $term_id ? get_term($term_id) : null;

            if (!is_wp_error($term) && $term && taxonomy_exists($term->taxonomy)) {
                //附加查询参数
                $tax_query = $query->get('tax_query');
                if (!is_array($tax_query)) {
                    $tax_query = array();
                }

                $tax_query[] = array(
                    'taxonomy' => $term->taxonomy,
                    'terms' => [$term_id],
                    'field' => 'term_id',
                );

                $query->set('tax_query', $tax_query);
            }
        }
        //返回查询
        return $query;
    }
    //验证搜索权限
    public function aya_theme_search_permission()
    {
        $options = $this->query_options;
        //检查设置
        $scope_user_type = (empty($options['serach_scope_user_check'])) ? 'all' : $options['serach_scope_user_check'];

        //不限制
        if ($scope_user_type == 'all') {
            return;
        }
        //关闭搜索
        if ($scope_user_type == 'disabled') {
            return self::aya_theme_error_search_clause_off();
        }
        //仅限登录用户
        if ($scope_user_type == 'logged') {
            //检查登录状态
            if (!is_user_logged_in()) {
                return self::aya_theme_error_search_clause_login();
            }
            return;
        }
    }
    //计算搜索关键词长度
    public function aya_theme_search_length($search_vars = '')
    {
        $options = $this->query_options;

        $keyword_length = mb_strwidth(preg_replace('/[\x00-\x7F]/', '', $search_vars), 'utf-8') + str_word_count($search_vars) * 2;

        //验证设置项
        $length = (empty($options['serach_scope_length'])) ? 255 : intval($options['serach_scope_length']);

        if ($keyword_length > $length) {
            return self::aya_theme_error_search_clause_too_long();
        }
        return true;
    }
    //根据IP计数搜索次数
    public function aya_theme_serach_limit()
    {
        $options = $this->query_options;

        //获取IP
        $cache_key = $_SERVER['REMOTE_ADDR'];

        $cache_group = 'aya_serach_limit';

        $times = wp_cache_get($cache_key, $cache_group);

        //验证设置项
        $max = (empty($options['serach_scope_limit'])) ? 10 : intval($options['serach_scope_limit']);

        if ($times > $max) {
            return self::aya_theme_error_search_clause_too_fast();
        } else {
            $times = $times ?: 1;
            $cache_time = $times == $max ? MINUTE_IN_SECONDS * 10 : MINUTE_IN_SECONDS;

            wp_cache_set($cache_key, $times + 1, $cache_group, $cache_time);

            return true;
        }
    }
    //SQL优化&自定义搜索拓展
    public function aya_theme_sql_set_filter($clauses, $wp_query)
    {
        $options = $this->query_options;

        global $wpdb, $wp_query;

        //跳过文内页
        if ($wp_query->is_singular()) {
            return $clauses;
        }
        //高级搜索参数
        if ($wp_query->is_search()) {

            $posts_table = $wpdb->posts;

            //仅查询标题
            if ($options['search_clause_only_title'] == true) {
                $clauses['where'] = preg_replace('/OR \(' . $posts_table . '\.(post_content|post_excerpt) LIKE (.*?)\)/', '', $clauses['where']);
            }
            //查询ID
            if ($options['search_clause_type_id'] == true) {
                $search_term = $wp_query->query['s'];

                if (is_numeric($search_term)) {
                    $id_where = '(' . $posts_table . '.ID = ' . $search_term . ')';
                } elseif (preg_match("/^(\d+)(,\s*\d+)*\$/", $search_term)) {
                    $id_where = '(' . $posts_table . '.ID in (' . $search_term . '))';
                } else {
                    $id_where = '';
                }

                if ($id_where) {
                    $clauses['where'] = str_replace('(' . $posts_table . '.post_title LIKE', $id_where . ' OR (' . $posts_table . '.post_title LIKE', $clauses['where']);
                }
            }
            //查询Meta
            if ($options['search_clause_type_meta'] == true) {
                if ($search_metas = $wp_query->get('search_metas')) {
                    $search_metas = wp_parse_list($search_metas);
                } else {
                    $search_metas = $options['search_clause_meta_key'];
                }
                //参数不为空
                if ($search_metas) {
                    $clauses['where'] = preg_replace_callback('/\(' . $posts_table . '.post_title (LIKE|NOT LIKE) (.*?)\)/', function ($matches) use ($search_metas) {
                        $search_metas = "'" . implode("', '", $search_metas) . "'";
                        $posts_table = $GLOBALS['wpdb']->posts;
                        $postmeta_table = $GLOBALS['wpdb']->postmeta;

                        return "EXISTS (SELECT * FROM {$postmeta_table} WHERE {$postmeta_table}.post_id={$posts_table}.ID AND meta_key IN ({$search_metas}) AND meta_value " . $matches[1] . " " . $matches[2] . ") OR " . $matches[0];
                    }, $clauses['where']);
                }
            }
        }

        //增加 EXPLAIN 语句重构found_posts
        if ($options['query_no_found_rows'] == true) {
            //检查SQL参数
            $where = isset($clauses['where']) ? $clauses['where'] : '';
            $join = isset($clauses['join']) ? $clauses['join'] : '';
            $distinct = isset($clauses['distinct']) ? $clauses['distinct'] : '';

            //get_row()方法添加语句
            $wp_query->found_posts = (int)$wpdb->get_row("EXPLAIN SELECT $distinct * FROM {$wpdb->posts} $join WHERE 1=1 $where")->rows;

            //验证分页
            $posts_per_page = (!empty($wp_query->query_vars['posts_per_page']) ? absint($wp_query->query_vars['posts_per_page']) : absint(get_option('posts_per_page')));
            //PHP方法计算分页
            $wp_query->max_num_pages = ceil($wp_query->found_posts / $posts_per_page);
        }

        //返回
        return $clauses;
    }
    //返回搜索关闭报错
    public function aya_theme_error_search_clause_off()
    {
        $message = __('The search function of this site has been disabled.');
        $title = __('Search is disabled.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        exit;
    }
    //返回搜索登录报错
    public function aya_theme_error_search_clause_login()
    {
        $message = __('Unable to use the search function, please log in first.');
        $title = __('Search is disabled.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        exit;
    }
    //返回搜索词过长报错
    public function aya_theme_error_search_clause_too_long()
    {
        $message = __('Search keyword is too long. Please shorten the search keyword and try again.');
        $title = __('Search is disabled.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        exit;
    }
    //返回搜索频率过多报错
    public function aya_theme_error_search_clause_too_fast()
    {
        $message = __('Searches too many times. Please try again in 10 minutes!');
        $title = __('Search is disabled.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        exit;
    }
}
