<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 直接提取WP的菜单数据结构到数组
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Menu_Object_In_Array
{
    public $menu;

    public function __construct($menu_name, $convet_json = false)
    {
        $menu_array = self::aya_get_menu_array($menu_name);

        if ($convet_json) {
            $this->menu = json_encode($menu_array);
        }

        $this->menu = $menu_array;

        //return (object) $this;
    }

    //将WP菜单对象构造为数组
    public function aya_get_menu_array($menu_name = '')
    {
        $locations = get_nav_menu_locations();
        $menu_array = array();

        //检查菜单是否存在
        if (($locations) && isset($locations[$menu_name])) {
            //提取对象
            $menu = wp_get_nav_menu_object($locations[$menu_name]);
            //查询
            $menu_items = wp_get_nav_menu_items($menu->term_id);

            //重新循环数组，将ID替换为键名用于递归
            $each_items = array();

            foreach ($menu_items as $item) {
                $each_items[$item->ID] = $item;
            }

            //递归函数
            $menu_array = self::aya_menu_array_build($each_items);
        }

        //将数组重新排序
        //$value_down = array_values($menu_array);
        return $menu_array;
    }

    //用来处理菜单层级的子方法
    public function aya_menu_array_build($items, $parent_id = 0)
    {
        $branch_menu = array();

        foreach ($items as $item) {
            //检查父级ID，如果相同则递归一次
            if ($item->menu_item_parent == $parent_id) {
                $child = self::aya_menu_array_build($items, $item->ID);
                //添加到子菜单
                if ($child) {
                    $item->child = $child;
                }

                $branch_menu[$item->ID] = array(
                    'label' => $item->title,
                    'url' => $item->url,
                    'child' => isset($item->child) ? $item->child : array()
                );
            }
        }

        return $branch_menu;
    }
}
