<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 侧边栏小工具缓存插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

/* 
 * Todo
 * 调整了菜单生成和一些Callback方法，但是主要缓存逻辑没有改动，之后可能需要重写成Memcached的
 */

class AYA_Plugin_Widget_Cache
{
    public function __construct()
    {
        //单例模式执行
        AYA_WidgetOutputCache::instance();
    }
}

class AYA_WidgetOutputCache
{
    public static function instance()
    {
        //当前实例
        static $instance;

        if (!$instance) new self();
        return $instance;
    }

    //存储要排除的小工具IDS
    private $widget_excluded = array();

    public function __destruct()
    {
        add_action('init', array($this, 'init_option'), 10);
        add_action('in_widget_form', array($this, 'widget_controls'), 10, 3);
        add_action('sidebar_admin_setup', array($this, 'save_widget_controls'));
        add_filter('widget_update_callback', array($this, 'cache_bump'));
        add_filter('widget_display_callback', array($this, 'widget_callback'), 10, 3);
    }
    //创建设置
    function init_option()
    {
        $this->widget_excluded = (array) get_option('cache-widgets-excluded', array());
    }
    function widget_controls($object, $return, $instance)
    {
        $is_excluded = in_array($object->id, $this->widget_excluded);
        printf(
            '<p>
				<label>
					<input type="checkbox" name="widget-cache-exclude" value="%s" %s />
					%s
				</label>
			</p>',
            esc_attr($object->id),
            checked($is_excluded, true, false),
            esc_html__('不缓存这个小工具')
        );
    }
    function save_widget_controls()
    {
        // current_user_can( 'edit_theme_options' ) is already being checked in widgets.php
        if (empty($_POST) || !isset($_POST['widget-id']))
            return;
        $widget_id = $_POST['widget-id'];
        $is_excluded = isset($_POST['widget-cache-exclude']);
        if (!isset($_POST['delete_widget']) && $is_excluded) {
            // Wiget is being saved and it is being excluded too
            $this->widget_excluded[] = $widget_id;
        } elseif (in_array($widget_id, $this->widget_excluded)) {
            // Widget is being removed, remove it from exclusions too
            $exclude_pos_key = array_search($widget_id, $this->widget_excluded);
            unset($this->widget_excluded[$exclude_pos_key]);
        }
        $this->widget_excluded = array_unique($this->widget_excluded);
        update_option('cache-widgets-excluded', $this->widget_excluded);
    }
    //在WP中更新缓存标记
    function cache_bump($instance)
    {
        update_option('cache-widgets-version', time());

        return $instance;
    }
    //缓存回调方法
    function widget_callback($instance, $widget_object, $args)
    {
        //检查设置
        if (false === $instance || !is_subclass_of($widget_object, 'WP_Widget')) return $instance;
        if (in_array($widget_object->id, $this->widget_excluded)) return $instance;
        //缓存定时
        $timer_start = microtime(true);
        //缓存标记
        $cache_key = sprintf(
            'cwdgt-%s',
            md5($widget_object->id . get_option('cache-widgets-version', 1))
        );

        $cached_widget = get_transient($cache_key);
        //检查缓存
        if (empty($cached_widget)) {
            //创建缓存
            ob_start();
            $widget_object->widget($args, $instance);
            $cached_widget = ob_get_contents();
            ob_end_clean();
            set_transient(
                $cache_key,
                $cached_widget,
                apply_filters('widget_output_cache_ttl', 60 * 12, $args)
            );
            //返回缓存信息
            printf(
                '%s <!-- Stored in widget cache in %s seconds (%s) -->',
                $cached_widget,
                round(microtime(true) - $timer_start, 4),
                $cache_key
            );
        }
        //如果缓存存在则
        else {
            //返回缓存信息
            printf(
                '%s <!-- From widget cache in %s seconds (%s) -->',
                $cached_widget,
                round(microtime(true) - $timer_start, 4),
                $cache_key
            );
        }
        return false;
    }
}
