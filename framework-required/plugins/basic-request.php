<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Request extends AYA_Theme_Setup
{
    public $query_options;

    public function __construct($args)
    {
        $this->query_options = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_serach_on_init');
        parent::add_action('template_redirect', 'aya_theme_serach_template_redirect');
        parent::add_filter('pre_get_posts', 'aya_theme_pre_get_posts');
        parent::add_filter('request', 'aya_theme_serach_pre_request');
        //高级搜索拓展
        parent::add_filter('posts_clauses', 'aya_theme_serach_add_filter', 10, 2);
    }
    //增加一些验证阻止非法访问
    public function aya_theme_serach_on_init()
    {
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
    //计算搜索关键词长度
    public function aya_theme_search_length($search_vars = '')
    {
        $options = $this->query_options;

        $keyword_length = mb_strwidth(preg_replace('/[\x00-\x7F]/', '', $search_vars), 'utf-8') + str_word_count($search_vars) * 2;

        //验证设置项
        $length = (empty($options['serach_scope_length'])) ? 255 : intval($options['serach_scope_length']);

        if ($keyword_length > $length) {
            return false;
        }
        return true;
    }
    //根据IP计数搜索次数
    public function aya_theme_serach_limit($cache_key = '')
    {
        $options = $this->query_options;

        //获取IP
        $cache_key = $cache_key ?: $_SERVER['REMOTE_ADDR'] ?? '';

        $cache_group = 'aya_serach_limit';

        $times = wp_cache_get($cache_key, $cache_group);

        //验证设置项
        $max = (empty($options['serach_scope_limit'])) ? 10 : intval($options['serach_scope_limit']);

        if ($times > $max) {
            return false;
        } else {
            $times = $times ?: 1;
            $cache_time = $times == $max ? MINUTE_IN_SECONDS * 10 : MINUTE_IN_SECONDS;

            wp_cache_set($cache_key, $times + 1, $cache_group, $cache_time);

            return true;
        }
    }
    //操作pre_get_posts钩子
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
            $self_post_type = (is_array($aya_post_type)) ? $aya_post_type : array();
            $post_type = array_merge($post_type, $self_post_type);
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
            $query->set('ignore_sticky_posts',  $sticky_type);
            $query->set('cat', $rule_home_cat);
            $query->set('post__not_in', $rule_home_post);
        }
        //搜索结果
        if (is_search()) {
            //添加页面
            if ($options['search_page_type'] == true) {
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
        //如果是后台
        if (is_admin()) return $query;

        //获取搜索词
        $search_vars = isset($query_vars['s']) ? $query_vars['s'] : '';

        //如果不是搜索
        if ($search_vars == '') return $query;

        $options = $this->query_options;

        //管理员用户执行跳过
        if ($options['serach_scope_enable'] == true && !current_user_can('manage_options')) {
            //验证设置项
            $if_user_check = (empty($options['serach_scope_user_check'])) ? 'all' : $options['serach_scope_user_check'];
            //关闭搜索
            if ($if_user_check == 'disabled') {
                $message = __('The search function of this site has been disabled.');
                $title = 'Search is disabled.';
                $args = array(
                    'response' => 403,
                    'back_link' => true,
                );

                wp_die($message, $title, $args);
            }
            //仅限登录用户
            else if ($if_user_check == 'logged' && !is_user_logged_in()) {
                $message = __('Unable to use the search function, please log in first.');
                $title = 'Search is disabled.';
                $args = array(
                    'response' => 403,
                    'back_link' => true,
                );

                wp_die($message, $title, $args);
            }
            //搜索词长度验证
            if (self::aya_theme_search_length($search_vars) === false) {
                $message = __('Search keyword is too long. Please shorten the search keyword and try again.');
                $title = 'Search is disabled.';
                $args = array(
                    'response' => 403,
                    'back_link' => true,
                );

                wp_die($message, $title, $args);
            }
            //搜索频率验证
            if (self::aya_theme_serach_limit() === false) {
                $message = __('Searches too many times. Please try again in 10 minutes!');
                $title = 'Search is disabled.';
                $args = array(
                    'response' => 403,
                    'back_link' => true,
                );

                wp_die($message, $title, $args);
            }
        }
        //搜索词直接匹配分类
        if ($options['search_redirect_term_search'] == true) {
            $term_id = term_exists($search_vars);
            $term = $term_id ? get_term($term_id) : null;

            if ($term && taxonomy_exists($term->taxonomy)) {
                $query_vars['search_type']    = $term->taxonomy;
                $query_vars['search_term']    = array_pull($query_vars, 's', null);;

                $query_vars['tax_query']    = $query_vars['tax_query'] ?? [];
                $query_vars['tax_query'][]    = [
                    'taxonomy'    => $term->taxonomy,
                    'terms'        => [$term_id],
                    'field'        => 'term_id',
                ];

                add_filter('get_search_query', function ($query) {
                    if (empty($query)) return get_query_var('search_term');
                });
            }
        }
        //返回查询
        return $query;
    }
    //操作重定向钩子
    public function aya_theme_serach_template_redirect()
    {
        $options = $this->query_options;

        global $wp_query;

        if (is_search() && get_query_var('module') == '') {
            //当搜索关键词为空返回首页
            if ($options['search_redirect_intend'] == true && empty($wp_query->query['s'])) {
                wp_redirect(home_url());
            }
            //当搜索结果只有一篇时直接跳转到文章页面
            if ($options['search_redirect_one_post'] == true && $wp_query->post_count == 1 && get_query_var('paged') <= 1) {
                wp_redirect(get_permalink($wp_query->posts['0']->ID));
            }
        }
    }
    //高级搜索组件
    public function aya_theme_serach_add_filter($clauses, $wp_query)
    {
        $options = $this->query_options;

        if ($wp_query->is_search()) {
            $posts_table = $GLOBALS['wpdb']->posts;

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

        return $clauses;
    }
}
