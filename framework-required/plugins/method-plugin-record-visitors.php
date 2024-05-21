<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Record_Visitors extends AYA_Theme_Setup
{
    public function __destruct()
    {
        parent::add_action('wp_head', 'record_visitors');
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
