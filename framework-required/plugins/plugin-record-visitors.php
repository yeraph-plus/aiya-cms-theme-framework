<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: 浏览量计数器
 * Version: 1.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Record_Visitors
{
    public function __destruct()
    {
        add_action('wp_head', array($this, 'record_visitors'));
    }
    //浏览量计数器
    public function record_visitors()
    {
        if (is_singular()) {
            global $post;

            if ($post->ID) {
                $post_views = (int)get_post_meta($post->ID, 'views', true);
                if (!update_post_meta($post->ID, 'views', ($post_views + 1))) {
                    add_post_meta($post->ID, 'views', 0, true);
                }
            }
        }
    }
}
