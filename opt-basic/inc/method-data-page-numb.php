<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 生成分页链接
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Pagination_link_In_Array
{
    public $pagination;
    public function __construct($page_num_type = true, $range_num_page = 4)
    {
        $paged_array = self::aya_get_paged_nav_item($page_num_type, $range_num_page);

        $this->pagination = $paged_array;

        return (object) $this;
    }

    //分页方法
    public function aya_get_paged_nav_item($page_num_type = true, $range_num_page = 4)
    {
        global $wp_query;

        //判断是否是主查询
        if (!$wp_query->is_main_query()) return false;

        //当前页码
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        //检查值
        $max_num_page = $wp_query->max_num_pages;
        $total_posts = $wp_query->found_posts;
        $posts_per_page = $wp_query->query_vars['posts_per_page'];


        //生成统计信息
        if ($wp_query->is_front_page() || $wp_query->is_archive()) {
            $total_info = sprintf(__('第%1$s页，共%2$s页。', 'AIYA'), $paged, $max_num_page);
        }
        //搜索
        else if ($wp_query->is_search()) {
            //根据页码计算项目次序
            $show_item_from = ($paged - 1) * $posts_per_page + 1;
            $show_item_to = $paged * $posts_per_page;
            $total_info = sprintf(__('共有%1$s条结果，当前为第%2$s条到%3$s条。', 'AIYA'), $total_posts, $show_item_from, $show_item_to);
        } else {
            $total_info = '';
        }

        //生成分页数组
        $paged_array = array();

        //首页
        $paged_array[] = array(
            'type' => 'page_home',
            'link' => get_pagenum_link(1),
            'text' =>  __('Home', 'AIYA'),
            'active' => (1 == $paged) ? true : false,
        );
        //上页
        $paged_array[] = array(
            'type' => 'page_prev',
            'link' => get_pagenum_link($paged - 1),
            'text' => __('Prev', 'AIYA'),
            'active' => (1 == $paged) ? true : false,
        );
        //下页
        $paged_array[] = array(
            'type' => 'page_next',
            'link' => get_pagenum_link($paged + 1),
            'text' => __('Next', 'AIYA'),
            'active' => ($max_num_page == $paged) ? true : false,
        );

        //页码范围
        if ($page_num_type) {
            if ($max_num_page > $range_num_page) {
                //计算页码显示范围
                $range_round = floor($range_num_page / 2);
                $last_round = $max_num_page - $range_round;
                //当前页码小于显示范围时，从第一页开始循环
                if ($paged < $range_round) {
                    //循环
                    for ($i = 1; $i <= $range_num_page; $i++) {
                        $paged_array[] = array(
                            'type' => 'page_num',
                            'link' => get_pagenum_link($i),
                            'text' => $i,
                            'active' => ($i == $paged) ? true : false,
                        );
                    }
                    //省略号
                    $paged_array[] = array(
                        'type' => 'page_ellipsis',
                        'link' => '#',
                        'text' => '...',
                        'active' => false,
                    );
                    //最后一页
                    $paged_array[] = array(
                        'type' => 'page_num',
                        'link' => get_pagenum_link($max_num_page),
                        'text' => $max_num_page,
                        'active' => ($max_num_page == $paged) ? true : false,
                    );
                }
                //当前页码大于显示范围时，从最后一页开始循环
                else if ($paged > $last_round) {
                    //第一页
                    $paged_array[] = array(
                        'type' => 'page_num',
                        'link' => get_pagenum_link(1),
                        'text' => 1,
                        'active' => (1 == $paged) ? true : false,
                    );
                    //省略号
                    $paged_array[] = array(
                        'type' => 'page_ellipsis',
                        'link' => '#',
                        'text' => '...',
                        'active' => false,
                    );
                    //循环
                    for ($i = $last_round; $i <= $max_num_page; $i++) {
                        $paged_array[] = array(
                            'type' => 'page_num',
                            'link' => get_pagenum_link($i),
                            'text' => $i,
                            'active' => ($i == $paged) ? true : false,
                        );
                    }
                }
                //当前页码在显示范围中间时，显示当前页前后各一半的页数
                else if ($paged >= $range_round && $paged <= $last_round) {
                    //第一页
                    $paged_array[] = array(
                        'type' => 'page_num',
                        'link' => get_pagenum_link(1),
                        'text' => 1,
                        'active' => (1 == $paged) ? true : false,
                    );
                    //省略号
                    $paged_array[] = array(
                        'type' => 'page_ellipsis',
                        'link' => '#',
                        'text' => '...',
                        'active' => false,
                    );
                    //循环
                    for ($i = $paged - $range_round; $i <= $paged + $range_round; $i++) {
                        $paged_array[] = array(
                            'type' => 'page_num',
                            'link' => get_pagenum_link($i),
                            'text' => $i,
                            'active' => ($i == $paged) ? true : false
                        );
                    }
                    //省略号
                    $paged_array[] = array(
                        'type' => 'page_ellipsis',
                        'link' => '#',
                        'text' => '...',
                        'active' => false,
                    );
                    //最后一页
                    $paged_array[] = array(
                        'type' => 'page_num',
                        'link' => get_pagenum_link($max_num_page),
                        'text' => $max_num_page,
                        'active' => ($max_num_page == $paged) ? true : false,
                    );
                }
            } else {
                //页数不足时直接生成页码
                for ($i = 1; $i <= $max_num_page; $i++) {
                    $paged_array[] = array(
                        'type' => 'page_num',
                        'link' => get_pagenum_link($i),
                        'text' => $i,
                        'active' => ($i == $paged) ? true : false,
                    );
                }
            }
        }

        return array(
            'total_info' => $total_info,
            'paged_array' => $paged_array,
        );
    }
}
