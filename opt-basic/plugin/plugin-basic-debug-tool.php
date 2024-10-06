<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 DEBUG用的小功能
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Debug_Mode extends AYA_Theme_Setup
{

    public function __construct()
    {
        //添加WP常量
        @define('SAVEQUERIES', true);
        @define('WP_DEBUG', true);
        @define('SCRIPT_DEBUG',  true);
        //@define('WP_DEBUG_LOG', true);
        //@define('WP_DEBUG_DISPLAY', false);
        //@ini_set('display_errors',0);
    }

    public function __destruct()
    {
        parent::add_action('wp_footer', 'aya_theme_queries_out_footer');
    }

    public function aya_theme_queries_out_footer()
    {
        global $wpdb;

        echo get_num_queries();
        echo ' queries in ';
        timer_stop(1);
        echo ' seconds.';

        echo '<br>';
        echo '<br>';

        echo '执行顺序：';
        echo '<br>';
        echo '--------------------';
        echo '<br>';

        echo '<pre>';
        var_dump($wpdb->queries);
        echo '</pre>';

        echo '耗时：';
        echo '<br>';
        echo '--------------------';
        echo '<br>';

        echo '<pre>';
        $qs = array();
        foreach ($wpdb->queries as $q) {
            $qs['' . $q[1] . ''] = $q;
        }
        krsort($qs);
        print_r($qs);
        echo '</pre>';
    }
}
