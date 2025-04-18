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
    public $filter_options;

    public function __construct($args)
    {
        $this->filter_options = $args;
    }

    public function __destruct()
    {
        $options = $this->filter_options;

        if (is_user_logged_in() && $options['site_comment_ignore_logged_users'] == true) {

            return;
        }

        if ($options['site_comment_check_wp_blacklist'] == true) {
            add_filter('preprocess_comment', array($this, 'aya_theme_comments_filter_from_wp_blacklist'));
        }

        if ($options['site_comment_remove_url_field'] == true) {
            add_filter('comment_form_default_fields', array($this, 'aya_theme_comment_remove_field'));
        }

        //评论过滤器（完整）
        add_filter('preprocess_comment', array($this, 'aya_theme_comments_filter_comment'));
    }
    //修改评论表单
    public function aya_theme_comment_remove_field($fields)
    {
        if (isset($fields['url'])) {
            unset($fields['url']);
        }

        return $fields;
    }
    //禁止垃圾评论提交到数据库
    public function aya_theme_comments_filter_from_wp_blacklist($comment_data)
    {
        //获取禁止关键字列表
        $disallowed_keys = get_option('disallowed_keys');

        if (empty($disallowed_keys)) {
            return $comment_data;
        }

        //将关键字转换为数组
        $disallowed_array = explode("\n", $disallowed_keys);

        //拼接所有字段方便检查
        $content_to_check = implode(' ', [
            $comment_data['comment_author'],
            $comment_data['comment_author_email'],
            $comment_data['comment_author_url'],
            $comment_data['comment_content'],
            $comment_data['comment_author_IP'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        //转换为小写
        $content_to_check = strtolower($content_to_check);

        wp_die($content_to_check);
        //循环关键字
        foreach ($disallowed_array as $word) {

            $word = trim(strtolower($word));

            if (!empty($word) && strpos($content_to_check, $word) !== false) {

                $message = 'Your comment contains disallowed characters and cannot be submitted.';

                return $this->aya_theme_comment_error_message($message);
            }
        }

        return $comment_data;
    }

    //评论过滤器
    public function aya_theme_comments_filter_comment($comment_data)
    {

        $options = $this->filter_options;

        $comment_content = $comment_data['comment_content'];

        //排除所有外语评论
        if ($options['site_comment_all_foreign_lang'] == true) {

            //$pattern = "/^[" . chr(0xa1) . "-" . chr(0xff) . "+$/"; //GB2312
            $pattern = '/[\x{4e00}-\x{9fa5}]/u'; //UTF-8

            if (!preg_match($pattern, $comment_content)) {

                $message = 'Your comment contains unsupported characters and cannot be submitted.';

                return $this->aya_theme_comment_error_message($message);
            }
        }

        //评论最小字数限制
        if ($options['site_comment_min_word_strlen'] == true) {

            $count = intval($options['site_comment_min_word_strlen_num']);

            if (mb_strlen($comment_content, 'UTF-8') < $count) {

                $message = 'Your comment is too short and cannot be submitted. Please enter at least ' . $count . ' characters.';

                return $this->aya_theme_comment_error_message($message);
            }
        }

        //评论链接限制
        if ($options['site_comment_count_link_limit'] == true) {

            $max_links = intval($options['site_comment_count_link_limit_num']);

            if (preg_match_all('/http(s)?:\/\/\S+/i', $comment_content, $matches) > $max_links) {

                $message = 'Your comment contains links and cannot be submitted.';

                return $this->aya_theme_comment_error_message($message);
            }
        }


        //自定义规则
        if ($options['site_comment_filter_custom_regular'] == true) {

            //按换行符拆分
            $spam_patterns = explode("\n", $options['site_comment_filter_custom_str_list']);

            //遍历规则检测
            foreach ($spam_patterns as $pattern) {

                if (preg_match($pattern, $comment_content)) {

                    $message = 'Your comment contains features of spam comments.';

                    return $this->aya_theme_comment_error_message($message);
                }
            }
        }


        return $comment_data;
    }
    //报错
    public function aya_theme_comment_error_message($message)
    {
        $title = __('Comment rejected.');
        $args = array(
            'response' => 403,
            'back_link' => true,
        );

        wp_die($message, $title, $args);

        return false;
    }
}
