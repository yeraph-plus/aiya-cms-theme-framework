<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 重定义的文章Meta数据模板
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Data_Template_Of_Post_Meta
{
    //替代一些WP原来的The_方法

    //定位文章
    public function aya_get_post($post_id = 0)
    {
        //检查当前是否在have_posts()内，是则返回
        if (empty($post_id) && isset($GLOBALS['post'])) {
            $post = $GLOBALS['post'];
        }
        //尝试获取WP_Post
        else {
            $post = get_post($post_id);
        }

        return (!empty($post)) ? $post : false;
    }
    //定位文章URL
    public function aya_get_post_url($post = null)
    {
        return get_permalink($post);
    }

    //获取文章ID
    public function aya_get_post_id($post = null)
    {
        if (!is_object($post)) {
            $post = get_post();
        }

        return !empty($post) ? $post->ID : false;
    }

    //获取文章标题检查是否为空
    public function aya_get_post_title($post = NULL, $attribute = false)
    {
        if (!is_object($post)) {
            $the_title = get_the_title($post);
        } else {
            $the_title = $post->post_title;
        }

        //检查文章标题
        if (strlen($the_title) === 0) {
            $the_title = __('无标题', 'AIYA');
        }
        //是否转义
        if ($attribute == true) {
            $the_title = esc_attr(strip_tags($the_title));
        }
        //返回标题
        return $the_title;
    }

    //获取文章状态
    public function aya_get_post_status($post = NULL)
    {
        if (!is_object($post)) {
            $the_status = get_post_status($post);
        } else {
            $the_status = $post->post_status;
        }

        //返回文本
        switch ($the_status) {
            case 'publish':
                //return __('已发布', 'AIYA');
                return '';
            case 'pending':
                return __('待审', 'AIYA');
            case 'future':
                return __('定时发布', 'AIYA');
            case 'private':
                return __('私密文章', 'AIYA');
            case 'draft':
                return __('草稿', 'AIYA');
            case 'auto-draft':
                return __('自动保存的草稿', 'AIYA');
            case 'inherit':
                return __('修订版本', 'AIYA');
            case 'trash':
                return __('已删除', 'AIYA');
            default:
                return '';
        }
    }

    //获取文章访问量
    public function aya_get_post_views($post = NULL)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }

        $the_views = $post->view_count;

        //计算为千位
        if ($the_views >= 1000) {
            return round($the_views / 1000, 1) . 'K';
        } else {
            return $the_views;
        }
    }

    //获取文章点赞数
    function aya_get_post_likes($post = NULL)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }

        $the_likes = $post->like_count;

        if ($the_likes > 0) {
            return $the_likes;
        } else {
            return '0';
        }
    }

    //获取文章用户摘要
    public function aya_get_post_excerpt($post = NULL)
    {
        if (!is_object($post)) {
            $the_excerpt = get_the_excerpt($post);
        } else {
            $the_excerpt = $post->post_excerpt;
        }
        return $the_excerpt;
    }

    //获取文章摘要
    public function aya_get_post_preview($post = NULL, $size = 225)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        //如果文章加密
        if (post_password_required($post)) {
            return __('这篇文章受密码保护，输入密码才能阅读。', 'AIYA');
        }
        //如果用户设置了摘要，则直接输出摘要内容
        if (has_excerpt($post)) {
            $the_preview = has_excerpt($post);
        }
        //没有摘要，截取正文内容
        else {

            //如果内容为空，则返回空
            $the_content = $post->post_content;
            $the_content = aya_clear_text(strip_shortcodes($the_content));
            if ($the_content === null) {
                $the_preview = '';
            } else {
                //$the_preview = wp_trim_words($the_content, $size);
                //DEBUG：WP原生的摘要函数好像完全没法判断中文长度，改用PHP判断
                $the_preview = mb_strimwidth($the_content, 0, $size, '...');
            }
        }
        //再次检查摘要是否为空
        if ($the_preview != '') {
            return $the_preview;
        } else {
            return __('这篇文章没有摘要内容。', 'AIYA');
        }
    }

    //获取文章作者
    public function aya_get_post_author($post = NULL, $modified = false)
    {
        if (!is_object($post)) {
            $post = get_post($post);
            $the_author = get_post_field('post_author', $post);
        } else {
            $the_author = $post->post_author;
        }

        $author_name = get_the_author_meta('display_name', $the_author);

        if ($modified == true) {
            $author_status = (user_can($the_author, 'publish_posts')) ? __(' 发布', 'AIYA') : __(' 投稿', 'AIYA');
            return  __('由 ', 'AIYA') . $author_name . $author_status;
        } else {
            return $author_name;
        }
    }

    //获取作者头像
    public function aya_get_post_author_avatar($post = NULL, $size = 32)
    {
        if (!is_object($post)) {
            $post = get_post($post);
            $the_author = get_post_field('post_author', $post);
        } else {
            $the_author = $post->post_author;
        }

        return get_avatar_url($the_author, $size);
    }

    //计算已发布时间
    public function aya_diff_timeago($time)
    {
        //更新：使用WordPress内置方法
        return human_time_diff($time, current_time('timestamp')) . __('前', 'AIYA');
    }

    //获取文章发布时间
    public function aya_get_post_date($post = NULL, $modified = 'short')
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        $publish_time = get_post_time('U', false, $post, true);

        //获取WP时间格式设置
        $date_format = get_option('date_format');

        switch ($modified) {
            case 'full':
                $modified_time = get_post_modified_time('U', false, $post, true);
                return  __('发布时间 ', 'AIYA') . date($date_format, $publish_time) . __(' [ 上次更新于 ', 'AIYA') . self::aya_diff_timeago($modified_time) . __(' ]', 'AIYA');
            case 'short':
                return date($date_format, $publish_time);
            case 'timeago':
                return self::aya_diff_timeago($publish_time);
            default:
                return date($date_format, $publish_time);
        }
    }

    //检查文章是否已过时
    function aya_get_post_is_outdated($post = NULL, $out_day = 30)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        $publish_time = get_post_time('U', false, $post, true);
        $modified_time = get_post_modified_time('U', false, $post, true);

        //判断更新时间取最近
        $last_time = ($modified_time > $publish_time) ? $modified_time : $publish_time;

        //时间30天
        if (time() > $last_time + 86400 * $out_day) {
            return true;
        }
        return false;
    }


    //获取文章评论数
    public function aya_get_post_comments($post = NULL, $modified = false)
    {
        if (!is_object($post)) {
            $the_comment_count = get_comments_number($post);
        } else {
            $the_comment_count = $post->comment_count;
        }

        if ($modified == true) {
            if ($the_comment_count > 0) {
                return $the_comment_count . __('条评论', 'AIYA');
            } else {
                return __('无人评论', 'AIYA');
            }
        } else {
            return empty($the_comment_count) ? 0 : $the_comment_count;
        }
    }

    //获取文章正文
    public function aya_get_post_content($post = NULL)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }

        $the_content = get_the_content(null, false, $post);

        //执行 the_content 的过滤器
        $the_content = apply_filters('the_content', $the_content);
        $the_content = str_replace(']]>', ']]&gt;', $the_content);
        //返回
        return $the_content;
    }

    //获取文章附件
    public function aya_get_post_media($post = NULL, $media = '')
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        //可以获取 image video audio text application 等
        $the_attachments = get_attached_media($media, $post);
        //返回查询到的Object
        return $the_attachments;
    }

    //获取文章特色图片
    public function aya_get_post_thumbnail($post = NULL)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        //如果存在特色图片
        if (has_post_thumbnail($post)) {
            return get_the_post_thumbnail_url($post);
        }
        //返回空
        return false;
    }

    //获取文章缩略图
    public function aya_get_post_thumb($post = NULL)
    {
        if (!is_object($post)) {
            $post = get_post($post);
        }

        $post_id = $post->ID;

        //如果存在特色图片
        if (has_post_thumbnail($post_id)) {
            //返回
            $img_url = get_the_post_thumbnail_url($post_id);
        }
        //获取文章中的第一张图片
        else {
            //查询附件
            $media = self::aya_get_post_media($post_id, 'image');
            //弹出数组中的第一个元素
            $media = array_shift($media);

            //只取URL，忽略width和height
            $img_url = (empty($media)) ? NULL : wp_get_attachment_image_src($media->ID, '', false)[0];
        }

        //如果存在图片
        return ($img_url !== NULL) ? $img_url : false;
    }
}

class AYA_Post_Meta extends AYA_Plugin_Data_Template_Of_Post_Meta
{
    public $id, $url, $title, $attr_title, $status, $views, $likes, $date, $author, $author_avatar, $comments, $thumb_url, $preview;

    public function __construct($post_id = 0, $date_mod = 'short', $avatar_size = 32, $preview_size = 255)
    {
        //获取原始 POST 对象
        $post = parent::aya_get_post($post_id);

        //报错时直接退出
        if (!$post) return NULL;

        //获取数据存入当前对象
        $this->id = parent::aya_get_post_id($post);
        $this->url = parent::aya_get_post_url($post);
        $this->title = parent::aya_get_post_title($post, false);
        $this->attr_title = parent::aya_get_post_title($post, true);
        $this->status = parent::aya_get_post_status($post);
        $this->views = parent::aya_get_post_views($post);
        $this->likes = parent::aya_get_post_likes($post);
        $this->date = parent::aya_get_post_date($post, $date_mod);
        $this->author = parent::aya_get_post_author($post, false);
        $this->author_avatar = parent::aya_get_post_author_avatar($post, $avatar_size);
        $this->comments = parent::aya_get_post_comments($post);
        $this->thumb_url = parent::aya_get_post_thumb($post);
        $this->preview = parent::aya_get_post_preview($post, $preview_size);

        return (object) $this;
    }
}

class AYA_Post_Content extends AYA_Plugin_Data_Template_Of_Post_Meta
{
    public $id, $title, $status, $author, $author_avatar, $views, $likes, $date, $is_outdated, $comments, $excerpt, $thumbnail, $content;

    public function __construct($post_id = 0, $avatar_size = 128, $days_outdate = 30)
    {
        //获取原始 POST 对象
        $post = parent::aya_get_post($post_id);

        //报错时直接退出
        if (!$post) return NULL;

        //获取数据存入当前对象
        $this->id = parent::aya_get_post_id($post);
        $this->title = parent::aya_get_post_title($post, false);
        $this->status = parent::aya_get_post_status($post);
        $this->author = parent::aya_get_post_author($post, true);
        $this->author_avatar = parent::aya_get_post_author_avatar($post, $avatar_size);
        $this->views = parent::aya_get_post_views($post);
        $this->likes = parent::aya_get_post_likes($post);
        $this->date = parent::aya_get_post_date($post, 'full');
        $this->is_outdated = parent::aya_get_post_is_outdated($post, $days_outdate);
        $this->comments = parent::aya_get_post_comments($post);
        $this->excerpt = parent::aya_get_post_excerpt($post);
        $this->thumbnail = parent::aya_get_post_thumbnail($post);
        $this->content = parent::aya_get_post_content($post);

        return (object) $this;
    }
}
