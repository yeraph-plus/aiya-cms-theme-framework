<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Admin_Backstage extends AYA_Theme_Setup
{
    var $admin_options;

    public function __construct($args)
    {
        $this->admin_options = $args;
    }

    public function __destruct()
    {
        $options = $this->admin_options;

        if ($options['admin_title_format'] == true) {
            parent::add_action('wp_dashboard_setup', 'aya_theme_remove_dashboard_meta_box');
        }
        if ($options['admin_footer_replace'] != NULL) {
            parent::add_action('wp_before_admin_bar_render', 'aya_theme_remove_admin_bar_logo', 0);
        }
        if ($options['remove_admin_bar'] == true) {
            parent::add_action('init', 'aya_theme_remove_admin_bar');
        }
        if ($options['admin_add_dashboard_widgets'] == true) {
            parent::add_action('wp_dashboard_setup', 'add_server_status_dashboard_widgets', 0);
        }
        if ($options['remove_bar_wplogo'] == true) {
            add_filter('admin_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
            add_filter('login_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
        }
        if ($options['remove_bar_wpnews'] == true) {
            add_filter('admin_footer_text', array($this, 'aya_theme_custom_admin_footer'));
        }
    }
    //替换后台标题
    public function aya_theme_custom_admin_title($admin_title, $title)
    {
        //站点名 - 页面
        return get_bloginfo('name') . ' - ' . $title;
    }
    //替换后台页脚信息
    public function aya_theme_custom_admin_footer()
    {
        echo '<span id="footer-thankyou">' . $this->admin_options['admin_footer_replace'] . '</span>';
    }
    public function aya_theme_remove_admin_bar()
    {
        //禁用前台顶部工具栏
        add_action('show_admin_bar', '__return_false');
    }
    //隐藏左上角WordPress标志
    public function aya_theme_remove_admin_bar_logo()
    {
        global $wp_admin_bar;
        //禁用 Menu Logo
        $wp_admin_bar->remove_menu('wp-logo');
    }
    //隐藏后台欢迎模块和WordPress新闻
    public function aya_theme_remove_dashboard_meta_box()
    {
        //删除 "欢迎" 模块
        remove_action('welcome_panel', 'wp_welcome_panel');
        //删除用户标记
        delete_user_meta(get_current_user_id(), 'show_welcome_panel');
        //删除 "站点健康" 模块
        //remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
        //删除 "概况" 模块
        //remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        //删除 "快速发布" 模块
        //remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        //删除 "引入链接" 模块
        //remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        //删除 "插件" 模块
        //remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        //删除 "动态" 模块
        //remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
        //删除 "近期评论" 模块
        //remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        //删除 "近期草稿" 模块
        //remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        //删除 "WordPress 开发日志" 模块
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        //删除 "WordPress 新闻" 模块
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    }
    //注册新的Widget
    public function add_server_status_dashboard_widgets()
    {
        //PHP信息
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            wp_add_dashboard_widget(
                'dashboard_server_widget',
                '服务器信息',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->server_widget();
                }
            );
        }
        //PHP版本
        wp_add_dashboard_widget(
            'dashboard_version_widget',
            '服务器版本',
            function () {
                $widget = new AYA_Plugin_Server_Status();
                echo $widget->version_widget();
            }
        );
        //PHP拓展
        wp_add_dashboard_widget(
            'dashboard_php_widget',
            'PHP扩展',
            function () {
                $widget = new AYA_Plugin_Server_Status();
                echo $widget->php_widget();
            }
        );
        //Apache信息
        if ($GLOBALS['is_apache'] && function_exists('apache_get_modules')) {
            wp_add_dashboard_widget(
                'dashboard_apache_widget',
                'Apache信息',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->apache_widget();
                }
            );
        }
        //OPCache信息
        if (function_exists('opcache_get_status')) {
            wp_add_dashboard_widget(
                'dashboard_opcache_usage_widget',
                'OPCache使用率',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->opcache_usage_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_opcache_status_widget',
                'OPCache状态',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->opcache_status_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_opcache_configuration_widget',
                'OPCache配置信息',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->opcache_configuration_widget();
                }
            );
        }
        //Memcached信息
        if (method_exists('WP_Object_Cache', 'get_mc')) {
            wp_add_dashboard_widget(
                'dashboard_memcached_status_widget',
                'Memcached状态',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->memcached_status_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_memcached_usage_widget',
                'Memcached使用率',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->memcached_usage_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_memcached_usage_efficiency_widget',
                'Memcached效率',
                function () {
                    $widget = new AYA_Plugin_Server_Status();
                    echo $widget->memcached_usage_efficiency_widget();
                }
            );
        }
    }
}

class AYA_Plugin_Server_Status
{
    //输出结构
    public static function output($items)
    {
        $output_html = '';
        $output_html .= '<table class="widefat striped" style="border:none;"><tbody>';

        foreach ($items as $item) {
            $output_html .= '<tr>';

            if (!empty($item['counts'])) {
                //内部继续循环
                $counts = $item['counts'];
                foreach ($counts as $count) {
                    if ($count['title']) {
                        $output_html .= '<th></th>';
                        $output_html .= '<td>' . $count['title'] . $count['value'] . '</td>';
                    }
                }
            }

            if ($item['title'] != '') {
                $output_html .= '<th>' . $item['title'] . '</th>';
                $output_html .= '<td>' . $item['value'] . '</td>';
            } else {
                $output_html .= '<td colspan="2">' . $item['value'] . '</td>';
            }

            $output_html .= '</tr>';
        }

        $output_html .= '</tbody></table>';

        return $output_html;
    }
    //服务器信息
    public static function server_widget()
    {
        $items  = array();

        $items[] = ['title' => '服务器', 'value' => gethostname() . '（' . $_SERVER['HTTP_HOST'] . '）'];
        $items[] = ['title' => '服务器IP', 'value' => '内网：' . gethostbyname(gethostname())];
        $items[] = ['title' => '系统',  'value' => php_uname('s')];

        if (strpos(ini_get('open_basedir'), ':/proc') !== false) {
            if (@is_readable('/proc/cpuinfo')) {
                $cpus = trim(file_get_contents('/proc/cpuinfo'));
                $cpus = explode("\n\n", $cpus);
                $cpu_count = count($cpus);
            } else {
                $cpu_count = 0;
            }

            if (@is_readable('/proc/meminfo')) {
                $mems = trim(file_get_contents('/proc/meminfo'));
                $mems = explode("\n", $mems);

                $mems_list = [];

                foreach ($mems as $mem) {
                    list($k, $v) = explode(':', $mem);
                    $mems_list[$k] = (int)$v;
                }

                $mem_total = $mems_list['MemTotal'];
            } else {
                $mem_total = 0;
            }

            $value = [];

            if ($cpu_count) {
                $value[] = $cpu_count . '核CPU';
            }

            if ($mem_total) {
                $value[] = round($mem_total / 1024 / 1024) . 'G内存';
            }

            if ($value) {
                $value = implode('&nbsp;&nbsp;/&nbsp;&nbsp;', $value);
            } else {
                $value = '无法获取信息';
            }

            $items[] = ['title' => '配置', 'value' => $value];

            if (@is_readable('/proc/meminfo')) {
                $uptime = trim(file_get_contents('/proc/uptime'));
                $uptime = explode(' ', $uptime);
                $value = human_time_diff(time() - $uptime[0]);
            } else {
                $value = '无法获取信息';
            }

            $items[] = ['title' => '运行时间', 'value' => $value];
            $items[] = ['title' => '空闲率', 'value' => round($uptime[1] * 100 / ($uptime[0] * $cpu_count), 2) . '%'];
            $items[] = ['title' => '系统负载', 'value' => '<strong>' . implode('&nbsp;&nbsp;', sys_getloadavg()) . '</strong>'];
        }

        $items[] = ['title' => '文档根目录', 'value' => $_SERVER['DOCUMENT_ROOT']];

        return self::output($items);
    }
    //PHP版本
    public static function php_widget()
    {
        $items = array(
            ['title' => '', 'value' => implode(', ', get_loaded_extensions())]
        );
        return self::output($items);
    }
    //PHP版本
    public static function version_widget()
    {
        global $wpdb, $required_mysql_version, $required_php_version, $wp_version, $wp_db_version, $tinymce_version;

        $http_server = explode('/', $_SERVER['SERVER_SOFTWARE'])[0];

        $items = array(
            ['title' => $http_server, 'value' => $_SERVER['SERVER_SOFTWARE']],
            ['title' => 'MySQL',  'value' => $wpdb->db_version() . '（最低要求：' . $required_mysql_version . '）'],
            ['title' => 'PHP',  'value' => phpversion() . '（最低要求：' . $required_php_version . '）'],
            ['title' => 'Zend',  'value' => Zend_Version()],
            ['title' => 'WordPress', 'value' => $wp_version . '（' . $wp_db_version . '）'],
            ['title' => 'TinyMCE', 'value' => $tinymce_version]
        );
        return self::output($items);
    }
    //Apache版本
    public static function apache_widget()
    {
        $items = array(
            ['title' => '', 'value' => implode(', ', apache_get_modules())]
        );
        return self::output($items);
    }
    //Opcache信息
    public static function opcache_status_widget()
    {
        $items = [];
        $status = opcache_get_status();

        foreach ($status['opcache_statistics'] as $key => $value) {
            $items[] = ['title' => $key, 'value' => $value];
        }
        return self::output($items);
    }
    public static function opcache_usage_widget()
    {
        global $current_admin_url;

        $capability = is_multisite() ? 'manage_site' : 'manage_options';

        if (current_user_can($capability)) {
            $action = $_GET['action'] ?? '';

            if ($action == 'flush') {

                check_admin_referer('flush-opcache');

                opcache_reset();

                $redirect_to = add_query_arg(['deleted' => 'true'], wpjam_get_referer());

                wp_redirect($redirect_to);
            }

            echo '<p><a href="' . esc_url(wp_nonce_url($current_admin_url . '&action=flush', 'flush-opcache')) . '" class="button-primary">刷新缓存</a></p>';
        }

        $status = opcache_get_status();

        $items = [];

        //计算内存
        $counts = [
            ['title' => '已用内存', 'value' => round($status['memory_usage']['used_memory'] / (1024 * 1024), 2)],
            ['title' => '剩余内存', 'value' => round($status['memory_usage']['free_memory'] / (1024 * 1024), 2)],
            ['title' => '无效内存', 'value' => round($status['memory_usage']['wasted_memory'] / (1024 * 1024), 2)]
        ];
        $total = round(($status['memory_usage']['used_memory'] + $status['memory_usage']['free_memory'] + $status['memory_usage']['wasted_memory']) / (1024 * 1024), 2);
        $items[] = ['title' => '内存使用', 'value' => $total];
        $items[] = ['title' => '', 'counts' => $counts];

        //计算命中率
        $counts  = [
            ['title' => '命中',  'count' => $status['opcache_statistics']['hits']],
            ['title' => '未命中',  'count' => $status['opcache_statistics']['misses']]
        ];

        $total = $status['opcache_statistics']['hits'] + $status['opcache_statistics']['misses'];

        $items[] = ['title' => '命中率', 'value' => $total];
        $items[] = ['title' => '', 'counts' => $counts];

        //计算使用率
        $counts = [
            ['title' => '已用Keys', 'count' => $status['opcache_statistics']['num_cached_keys']],
            ['title' => '剩余Keys', 'count' => $status['opcache_statistics']['max_cached_keys'] - $status['opcache_statistics']['num_cached_keys']]
        ];

        $total = $status['opcache_statistics']['max_cached_keys'];

        $items[] = ['title' => '存储Keys', 'value' => $total];
        $items[] = ['title' => '', 'counts' => $counts];

        //计算字符串存储
        $counts = [
            ['title' => '已用内存', 'count' => round($status['interned_strings_usage']['used_memory'] / (1024 * 1024), 2)],
            ['title' => '剩余内存', 'count' => round($status['interned_strings_usage']['free_memory'] / (1024 * 1024), 2)]
        ];

        $total = round($status['interned_strings_usage']['buffer_size'] / (1024 * 1024), 2);

        $items[] = ['title' => '临时字符串存储内存', 'value' => $total];
        $items[] = ['title' => '', 'counts' => $counts];


        return self::output($items);
    }
    public static function opcache_configuration_widget()
    {
        $items = [];

        $configuration = opcache_get_configuration();

        foreach ($configuration['version'] as $key => $value) {
            $items[] = ['title' => $key, 'value' => $value];
        }

        foreach ($configuration['directives'] as $key => $value) {
            $items[] = ['title' => str_replace('opcache.', '', $key), 'value' => $value];
        }

        return self::output($items);
    }
    //Memcached信息
    public static function memcached_status_widget()
    {
        global $wp_object_cache;

        $items = [];

        foreach ($wp_object_cache->get_mc()->getStats() as $key => $details) {
            // $items[]	= ['title'=>'Memcached进程ID',	'value'=>$details['pid']];
            $items[] = ['title' => 'Memcached地址',  'value' => $key];
            $items[] = ['title' => 'Memcached版本',  'value' => $details['version']];
            $items[] = ['title' => '启动时间',   'value' => wpjam_date('Y-m-d H:i:s', ($details['time'] - $details['uptime']))];
            $items[] = ['title' => '运行时间',   'value' => human_time_diff(0, $details['uptime'])];
            $items[] = ['title' => '已用/分配的内存',  'value' => size_format($details['bytes']) . ' / ' . size_format($details['limit_maxbytes'])];
            $items[] = ['title' => '启动后总数量',  'value' => $details['curr_items'] . ' / ' . $details['total_items']];
            $items[] = ['title' => '为获取内存踢除数量', 'value' => $details['evictions']];
            $items[] = ['title' => '当前/总打开连接数', 'value' => $details['curr_connections'] . ' / ' . $details['total_connections']];
            $items[] = ['title' => '命中次数',   'value' => $details['get_hits']];
            $items[] = ['title' => '未命中次数',   'value' => $details['get_misses']];
            $items[] = ['title' => '总获取请求次数',  'value' => $details['cmd_get']];
            $items[] = ['title' => '总设置请求次数',  'value' => $details['cmd_set']];
            $items[] = ['title' => 'Item平均大小',  'value' => size_format($details['bytes'] / $details['curr_items'])];
        }

        return self::output($items);
    }
    public static function memcached_usage_widget()
    {
        global $current_admin_url, $wp_object_cache;

        $capability = is_multisite() ? 'manage_site' : 'manage_options';

        if (current_user_can($capability)) {
            $action = $_GET['action'] ?? '';
            if ($action == 'flush') {
                check_admin_referer('flush-memcached');
                wp_cache_flush();

                wp_redirect(add_query_arg(['deleted' => 'true'], wpjam_get_referer()));
            }
            echo '<p><a href="' . esc_url(wp_nonce_url($current_admin_url . '&action=flush', 'flush-memcached')) . '" class="button-primary">刷新缓存</a></p>';
        }

        $items = [];

        foreach ($wp_object_cache->get_mc('defaul')->getStats() as $key => $details) {
            $counts = [
                ['label' => '命中次数', 'count' => $details['get_hits']],
                ['label' => '未命中次数', 'count' => $details['get_misses']]
            ];

            $total = $details['cmd_get'];

            wpjam_donut_chart($counts, ['title' => '命中率', 'total' => $total, 'chart_width' => 150, 'table_width' => 320]);

            $counts = [
                ['label' => '已用内存', 'count' => round($details['bytes'] / (1024 * 1024), 2)],
                ['label' => '剩余内存', 'count' => round(($details['limit_maxbytes'] - $details['bytes']) / (1024 * 1024), 2)]
            ];

            $total = round($details['limit_maxbytes'] / (1024 * 1024), 2);

            wpjam_donut_chart($counts, ['title' => '内存使用', 'total' => $total, 'chart_width' => 150, 'table_width' => 320]);
        }
    }
    public static function memcached_usage_efficiency_widget()
    {
        global $wp_object_cache;

        $items = [];

        foreach ($wp_object_cache->get_mc('defaul')->getStats() as $key => $details) {
            $items[] = ['title' => '每秒命中次数',  'value' => round($details['get_hits'] / $details['uptime'], 2)];
            $items[] = ['title' => '每秒未命中次数', 'value' => round($details['get_misses'] / $details['uptime'], 2)];
            $items[] = ['title' => '每秒获取请求次数', 'value' => round($details['cmd_get'] / $details['uptime'], 2)];
            $items[] = ['title' => '每秒设置请求次数', 'value' => round($details['cmd_set'] / $details['uptime'], 2)];
        }
        return self::output($items);
    }
}
