<?php

if (!defined('ABSPATH'))
    exit;

/**
 * AIYA-Framework 拓展 评论过滤器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

class AYA_Plugin_Comment_Filter extends AYA_Theme_Setup
{
    public $filter_action;

    public function __construct($args)
    {
        $this->filter_action = $args;
    }

    public function __destruct()
    {
        //添加钩子 强制排除评论表单站点字段
        parent::add_filter('comment_form_default_fields', 'aya_theme_comment_remove_field');
    }
    //合并方法
    public function aya_theme_comment_filter()
    {

    }
    //修改评论表单
    public function aya_theme_comment_remove_field($fields)
    {
        if (isset($fields['url'])) {
            unset($fields['url']);
        }
    
        return $fields;
    }
    //
    public function aya_theme_error_($message)
    {
        $message = __('The current browser userAgent is disabled by the site administrator.');
        $title = __('Access was denied.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        exit;
    }

    function filter_chinese_comments($comment_data) {
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $comment_data['comment_content'])) {
            wp_die('您的评论包含不支持的字符，无法提交。');
            return false;
        }
        return $comment_data;
    }
    //add_filter('preprocess_comment', 'filter_chinese_comments');
    function filter_short_comments($comment_data) {
        if (strlen($comment_data['comment_content']) < 10) { // 假设10个字符是阈值
            wp_die('您的评论过短，无法提交。');
            return false;
        }
        return $comment_data;
    }
    //add_filter('preprocess_comment', 'filter_short_comments');
    function filter_no_links_in_comments($comment_data) {
        if (!preg_match('/<a href/i', $comment_data['comment_content'])) {
            wp_die('您的评论不包含链接，无法提交。');
            return false;
        }
        return $comment_data;
    }
    //add_filter('preprocess_comment', 'filter_no_links_in_comments');
}