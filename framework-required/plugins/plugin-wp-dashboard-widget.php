<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: WP 仪表盘拓展小工具包
 * Version: 1.2.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Dashboard_Server_Status
{
    public function __construct()
    {
        if (!is_admin()) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        //注册仪表盘自定义小工具组件
        add_action('wp_dashboard_setup', array($this, 'add_server_status_dashboard_widgets'), 0);
    }
    //注册新的控制台Widget
    public function add_server_status_dashboard_widgets()
    {
        $add_widget = array();

        $add_widget[] = ['server_version_widget', '应用程序版本'];
        //PHP信息
        $add_widget[] = ['php_config_widget', 'PHP 运行信息 '];
        $add_widget[] = ['php_extens_widget', 'PHP 扩展 '];
        //Apache信息
        if ($GLOBALS['is_apache'] && function_exists('apache_get_modules')) {
            $add_widget[] = ['php_apache_widget', 'Apache 信息 '];
        }
        //服务器信息
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $add_widget[] = ['server_info_widget', '服务器信息'];
            $add_widget[] = ['server_status_widget', '服务器状态'];
        }
        //OPCache信息
        if (function_exists('opcache_get_status')) {
            $add_widget[] = ['opcache_usage_widget', 'OPCache 使用率 '];
            $add_widget[] = ['opcache_status_widget', 'OPCache 状态 '];
            $add_widget[] = ['opcache_config_widget', 'OPCache 配置信息 '];
        }
        //遍历注册
        foreach ($add_widget as $widget_args) {
            $widget_func = $widget_args[0];
            $widget_name = $widget_args[1];

            wp_add_dashboard_widget('dashboard_' . $widget_func, $widget_name, function () use ($widget_func) {
                $widget = new AYA_DashboardServerWidget();
                $widget->{$widget_func}();
            }, null, null, 'column3', 'high');
        }
    }
}

class AYA_DashboardServerWidget
{
    //定义一下输出结构
    public function output($items)
    {
        $output_html = '';
        $output_html .= '<table class="widefat striped" style="border:none;"><tbody>';

        foreach ($items as $item) {
            $output_html .= '<tr>';

            if ($item['title'] != '') {
                $output_html .= '<th>' . $item['title'] . '</th>';
                $output_html .= '<td>' . $item['value'] . '</td>';
            } else {
                $output_html .= '<td colspan="2">' . $item['value'] . '</td>';
            }

            $output_html .= '</tr>';
        }

        $output_html .= '</tbody></table>';

        echo $output_html;
    }
    //程序版本
    public function server_version_widget()
    {
        global $wpdb, $required_mysql_version, $required_php_version, $wp_version, $wp_db_version, $tinymce_version;

        $http_server = explode('/', $_SERVER['SERVER_SOFTWARE'])[0];

        $items = array();

        $items[] = ['title' => $http_server, 'value' => $_SERVER['SERVER_SOFTWARE']];
        $items[] = ['title' => 'MySQL',  'value' => $wpdb->db_version() . ' ( Supported: ' . $required_mysql_version . ' )'];
        $items[] = ['title' => 'PHP',  'value' => phpversion() . ' ( Supported: ' . $required_php_version . ' )'];
        $items[] = ['title' => 'Zend',  'value' => Zend_Version()];
        $items[] = ['title' => 'WordPress', 'value' => $wp_version . ' ( ' . $wp_db_version . ' )'];
        $items[] = ['title' => 'TinyMCE', 'value' => $tinymce_version];

        return self::output($items);
    }
    //Apache
    public function php_apache_widget()
    {
        $items = array(
            ['title' => '', 'value' => implode(', ', apache_get_modules())]
        );

        return self::output($items);
    }
    //PHP配置
    public function php_config_widget()
    {
        $items = array();

        $items[] = ['title' => 'ROOT DOCUMENT ', 'value' => $_SERVER['DOCUMENT_ROOT']];
        $items[] = ['title' => 'PHP API', 'value' => php_sapi_name()];
        $items[] = ['title' => 'PHP Runtime Version', 'value' => phpversion()];
        $items[] = ['title' => 'PHP Runtime Profile', 'value' => php_ini_loaded_file()];
        /*
        $items[] = ['title' => '(php.ini) display_errors', 'value' => ini_get('display_errors')];
        $items[] = ['title' => '(php.ini) register_globals', 'value' => ini_get('register_globals')];
        $items[] = ['title' => '(php.ini) memory_limit', 'value' => ini_get('memory_limit')];
        $items[] = ['title' => '(php.ini) max_execution_time', 'value' => ini_get('max_execution_time')];
        $items[] = ['title' => '(php.ini) max_input_vars', 'value' => ini_get('max_input_vars')];
        $items[] = ['title' => '(php.ini) max_file_uploads', 'value' => ini_get('max_file_uploads')];
        $items[] = ['title' => '(php.ini) max_execution_time', 'value' => ini_get('max_execution_time')];
        $items[] = ['title' => '(php.ini) upload_max_filesize', 'value' => ini_get('upload_max_filesize')];
        $items[] = ['title' => '(php.ini) post_max_size', 'value' => ini_get('post_max_size')];
        $items[] = ['title' => '(php.ini) post_max_size+1', 'value' => ini_get('post_max_size')+1];
        $items[] = ['title' => '(php.ini) magic_quotes_gpc', 'value' => ini_get('magic_quotes_gpc')];
        */

        return self::output($items);
    }
    //PHP扩展
    public function php_extens_widget()
    {
        $items = array(
            ['title' => '', 'value' => implode(', ', get_loaded_extensions())]
        );
        return self::output($items);
    }
    //服务器信息
    public function server_info_widget()
    {
        $items  = array();

        $items[] = ['title' => 'HOST', 'value' => gethostname() . '(' . $_SERVER['HTTP_HOST'] . ')'];
        $items[] = ['title' => 'OS', 'value' => php_uname('s') . ' ' . php_uname('r')];
        $items[] = ['title' => 'ARCHITECTURE', 'value' => php_uname('m')];
        $items[] = ['title' => 'TIME', 'value' => date('Y-m-d H:i:s')];
        $items[] = ['title' => 'TIMEZONE', 'value' => date_default_timezone_get()];
        $items[] = ['title' => 'IP', 'value' => '(Internal: ' . gethostbyname(gethostname()) . ')'];

        return self::output($items);
    }
    //服务器状态
    public function server_status_widget()
    {
        //内置方法
        function trim_exec($exec = '', $str = '', $key = 1)
        {
            if (empty($exec)) {
                $exec = 'NULL';
                $str = false;
            } else {
                //使用exec方法运行Linux命令
                $exec = exec($exec);
            }

            $trim = trim($exec);

            //字符串切割方法
            if ($str !== '' && $str !== false) {
                //搜索字符串换行分割
                $explode = explode($str, $trim);

                $trim = (isset($explode[$key])) ? $explode[$key] : $trim;
            }
            //去除不需要的字符
            $trim = rtrim($trim, ' kB');

            //验证字符串是否为数字
            if (is_numeric($trim)) {
                $trim = intval($trim);
            }

            return $trim;
        }
        function time_seconds($seconds)
        {
            $d = floor($seconds / (3600 * 24));
            $h = floor(($seconds % (3600 * 24)) / 3600);
            $m = floor((($seconds % (3600 * 24)) % 3600) / 60);

            if ($d > '0') {
                return $d . ' D ' . $h . ' H ' . $m . ' M ';
            } else {
                if ($h > '0') {
                    return $h . ' H ' . $m . ' M ';
                } else {
                    return $m . ' M ';
                }
            }
        }


        //CPU名
        $cpu_name = trim_exec('cat /proc/cpuinfo | grep "model name" | uniq', ':', 1);
        //CPU核心
        $cpu_core = trim_exec('cat /proc/cpuinfo | grep "processor" | wc -l', '', 0);
        //内存总量
        $mem_total = trim_exec('cat /proc/meminfo | grep "MemTotal" | uniq', ':', 1);
        //内存闲置
        $mem_free = trim_exec('cat /proc/meminfo | grep "MemFree" | uniq', ':', 1);
        //SWAP总量
        $swap_total = trim_exec('cat /proc/meminfo | grep "SwapTotal" | uniq', ':', 1);
        //SWAP闲置
        $swap_free = trim_exec('cat /proc/meminfo | grep "SwapFree" | uniq', ':', 1);
        //运行时间
        $boot_time = trim_exec('cat /proc/uptime', ' ', 0);
        //闲置时间
        $idle_time = trim_exec('cat /proc/uptime', ' ', 1);
        //系统负载
        //$loadavg_in_1m = trim_exec('cat /proc/loadavg', ' ', 0);
        //$loadavg_in_5m = trim_exec('cat /proc/loadavg', ' ', 1);
        //$loadavg_in_15m = trim_exec('cat /proc/loadavg', ' ', 2);

        //计算本机内存
        $now_mem_total = round($mem_total / 1024 / 1024);
        $now_swap_total = round($swap_total / 1024 / 1024);
        //计算内存使用率（全部内存-可用内存/全部内存）
        $now_mem_rate = round(($mem_total - $mem_free) / $mem_total * 100, 2);
        $now_swap_rate = round(($swap_total - $swap_free) / $swap_total * 100, 2);
        //计算开机时间
        $now_boot_time = time_seconds($boot_time);
        //计算闲置时间（时间/线程总数）
        $now_idle_time = time_seconds(round($idle_time / $cpu_core, 0));
        //计算CPU闲置率（闲置时间/启动时间）
        $now_idle_rate = round((($idle_time / $cpu_core) / $boot_time) * 100, 2);

        $items = [];

        $items[] = ['title' => 'CPU', 'value' => $cpu_name];
        $items[] = ['title' => 'Processor', 'value' => $cpu_core];
        $items[] = ['title' => 'MemTotal', 'value' => $now_mem_total . ' G ( Usage rate ' . $now_mem_rate . ' % )'];
        $items[] = ['title' => 'SwapTotal', 'value' => $now_swap_total . ' G ( Usage rate ' . $now_swap_rate . ' % )'];
        $items[] = ['title' => 'Uptime', 'value' => $now_boot_time];
        $items[] = ['title' => 'Idle', 'value' => $now_idle_time . ' ( Idle rate ' . $now_idle_rate . ' % )'];
        $items[] = ['title' => 'LOADAVG', 'value' => '<b>' . implode(' / ', sys_getloadavg()) . '</b>'];

        return self::output($items);
    }
    //Opcache使用率
    public function opcache_usage_widget()
    {
        /*
        $capability = is_multisite() ? 'manage_site' : 'manage_options';

        if (is_admin() && current_user_can($capability)) {
            $action = $_GET['action'] ?? '';

            $msg = '';

            if ($action == 'opcache-flush') {

                check_admin_referer('opcache-flush');

                $reset = opcache_reset() ? true : false;
                if ($reset) {
                    $msg = 'ERROR';
                } else {
                    wp_die('ERROR');
                }
            }
            echo '<p><a href="' . admin_url('admin.php&action=opcache-flush') . '" class="button-primary">Refresh cache</a>' . $msg . '</p>';
        }
        */
        $status = opcache_get_status();

        //计算内存
        $mem_used = round($status['memory_usage']['used_memory'] / (1024 * 1024), 2);
        $mem_free = round($status['memory_usage']['free_memory'] / (1024 * 1024), 2);
        $mem_wasted = round($status['memory_usage']['wasted_memory'] / (1024 * 1024), 2);
        //计算命中率
        $stat_hits = $status['opcache_statistics']['hits'];
        $stat_misses = $status['opcache_statistics']['misses'];
        $stat_hit_rate = round(($stat_hits / ($stat_hits + $stat_misses)) * 100, 2);
        //计算使用率
        $num_keys = $status['opcache_statistics']['num_cached_keys'];
        $max_keys = $status['opcache_statistics']['max_cached_keys'];
        $oth_keys = $max_keys - $num_keys;
        $stat_usage_rate = round(($num_keys / $max_keys) * 100, 2);
        //计算字符串存储
        $interned_used = round($status['interned_strings_usage']['used_memory'] / (1024 * 1024), 2);
        $interned_free = round($status['interned_strings_usage']['free_memory'] / (1024 * 1024), 2);
        $interned_buffer = round($status['interned_strings_usage']['buffer_size'] / (1024 * 1024), 2);

        $items = [];
        $items[] = ['title' => 'Memory', 'value' => ' Usage ' . $mem_used . ' / Unused ' . $mem_free . ' / Wasted ' . $mem_wasted];

        $items[] = ['title' => 'Hits', 'value' => ' Hit ' . $stat_hits . ' / Miss ' . $stat_misses . ' / Hit Rate ' . $stat_hit_rate . '%'];

        $items[] = ['title' => 'Keys', 'value' => ' Usage ' . $num_keys . ' / Balance ' . $oth_keys . ' / Usage Rate ' . $stat_usage_rate . '%'];

        $items[] = ['title' => 'Interned Strings Usage', 'value' => ' Buffer ' . $interned_buffer . ' / Usage ' . $interned_used . ' /  Unused ' . $interned_free];

        return self::output($items);
    }
    //Opcache状态
    public function opcache_status_widget()
    {
        $items  = array();

        $status = opcache_get_status();

        foreach ($status['opcache_statistics'] as $key => $value) {
            $items[] = ['title' => $key, 'value' => $value];
        }

        return self::output($items);
    }
    //Opcache配置
    public function opcache_config_widget()
    {
        $items  = array();

        $configuration = opcache_get_configuration();

        foreach ($configuration['version'] as $key => $value) {
            $items[] = ['title' => $key, 'value' => $value];
        }

        foreach ($configuration['directives'] as $key => $value) {
            $items[] = ['title' => str_replace('opcache.', '', $key), 'value' => $value];
        }

        return self::output($items);
    }
}
