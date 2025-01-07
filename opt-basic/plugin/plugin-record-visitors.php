<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 浏览量计数器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Record_Visitors
{
    public function __destruct()
    {
        add_action('wp_head', array($this, 'record_visitors'));
        add_action('the_post', array($this, 'add_view_count_to_post_object'));
    }
    //浏览量计数器
    public function record_visitors()
    {
        if (is_singular()) {
            global $post;

            $count = get_post_meta($post->ID, 'view_count', true);

            if ($count) {
                $count = intval($count) + 1;
                update_post_meta($post->ID, 'view_count', $count);
            } else {
                update_post_meta($post->ID, 'view_count', 1);
            }
        }
    }

    public function add_view_count_to_post_object($post)
    {
        if (is_object($post) && property_exists($post, 'ID')) {

            $the_views = get_post_meta($post->ID, 'view_count', true);

            $post->view_count = intval($the_views);
        }

        return $post;
    }
}
