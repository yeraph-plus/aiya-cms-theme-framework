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
