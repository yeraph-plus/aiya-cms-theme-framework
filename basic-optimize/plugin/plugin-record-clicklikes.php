<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 文章点赞计数器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Record_ClickLikes
{
    public function __destruct()
    {
        //前端action事件名：click_likes
        add_action('wp_ajax_click_likes', array($this, 'set_post_click_likes'));
        add_action('wp_ajax_nopriv_click_likes', array($this, 'set_post_click_likes'));

        add_action('the_post', array($this, 'add_like_count_to_post_object'));
    }
    //点赞计数器
    public function set_post_click_likes()
    {
        //验证请求
        if (!isset($_POST['post_id'])) {
            $response = array('status' => 'error');

            wp_send_json($response);
        } else {
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

            $count = get_post_meta($post_id, 'like_count', true);

            if ($count) {
                $count = intval($count) + 1;
                update_post_meta($post_id, 'like_count', $count);
            } else {
                update_post_meta($post_id, 'like_count', 1);
            }

            $response = array('status' => 'done');

            wp_send_json($response);
        }
    }

    public function add_like_count_to_post_object($post)
    {
        if (is_object($post) && property_exists($post, 'ID')) {

            $the_likes = get_post_meta($post->ID, 'like_count', true);

            $post->like_count = intval($the_likes);
        }

        return $post;
    }
}
