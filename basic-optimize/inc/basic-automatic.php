<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 WP自动别名组件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.3
 **/

use Jxlwqq\ChineseTypesetting\ChineseTypesetting;
use Overtrue\Pinyin\Pinyin;

class AYA_Plugin_Automatic
{
    public $automatic_options;

    public function __construct($args)
    {
        $this->automatic_options = $args;

        $options = $this->automatic_options;

        if ($options['the_post_auto_insert_bool'] == true) {
            add_filter('default_content', array($this, 'aya_theme_post_insert_pre_content'));
        }
        //文章保存时循环一次
        add_action('save_post', array($this, 'aya_theme_save_post_auto_formatting'));

        //自动别名
        add_filter('wp_insert_term_data',  array($this, 'aya_insert_term_data_slug'), 10, 3);
        add_filter('wp_update_term_data',  array($this, 'aya_update_term_data_slug'), 10, 4);
        add_filter('wp_insert_post_data',  array($this, 'aya_insert_post_data_slug'), 10, 2);
        add_filter('wp_unique_post_slug',  array($this, 'aya_auto_unique_post_slug'), 10, 6);

        //批量刷新工具函数
        //Tips: 用于批量刷新文章别名，取消下面这行的注释并打开任意页面一次，然后重新注释
        // add_action('init', array($this,'aya_post_all_slug_rewrite_update'));
    }

    public function __destruct() {}

    //应用中文格式化实例
    public function aya_autoload_chs_type_setting($content, $correct_array)
    {
        $typesetting = new ChineseTypesetting();

        //$formatted_content = $typesetting->correct($content, ['insertSpace', 'removeSpace', 'full2Half', 'fixPunctuation', 'properNoun']);

        $formatted_content = $typesetting->correct($content, $correct_array);

        //返回格式化后的内容
        return $formatted_content;
    }

    //应用拼音转换 SLUG 实例
    public function aya_autoload_pinyin_permalink($slug, $abbr = false)
    {
        //传入如果不是字符串
        $slug = strval($slug);
        //设置最大词长
        $length = 60;
        //设置字符
        $divider = '-'; //可用参数 '_', '-', '.', ''

        $pinyin = new Pinyin();

        //是否使用索引模式
        if ($abbr === true) {
            $slug = $pinyin->permalink($slug, $divider);
        } else {
            $slug = $pinyin->abbr($slug);
        }
        //截取最大长度
        $slug = aya_trim_slug($slug, $length, $divider);
        //返回格式化后的内容 //格式为：'带着希望去旅行' -> 'dai-zhe-xi-wang-qu-lyu-xing'
        return $slug;
    }

    //应用通用拼音转换实例
    public function aya_autoload_pinyin_setting($content, $tone = true)
    {
        //传入如果不是字符串
        $content = strval($content);

        $pinyin = new Pinyin();

        //是否添加声调
        $tone = ($tone) ? 'none' : '';

        //返回格式化后的内容
        return $pinyin->sentence($content, $tone);
    }

    //编辑器默认内容
    public function aya_theme_post_insert_pre_content()
    {
        $options = $this->automatic_options;

        $content = trim($options['the_post_auto_insert_content']);

        if (!empty($content) && !is_feed() && !is_home()) {

            return $content;
        }
    }

    //文本预处理组件

    //发布文章前对文章内容进行预处理
    public function aya_theme_save_post_auto_formatting($post_id)
    {
        $options = $this->automatic_options;

        //如果是新文章就先跳过
        if (empty($post_id)) {
            return;
        }
        //检查用户权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        //检查是否为自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        //防止进入递归，先注销钩子
        remove_action('save_post', array($this, 'aya_theme_save_post_auto_formatting'));

        //获取文章标题
        $post_title = get_post_field('post_title', $post_id);
        //获取文章内容
        $post_content = get_post_field('post_content', $post_id);

        //文章保存时自动触发动作添加标签
        if (AYF::get_post_action('auto_strpos_tags', 'post_automatic')) {
            $tags = get_tags(array('hide_empty' => false));

            if ($tags && !is_wp_error($tags)) {
                foreach ($tags as $tag) {
                    if (strpos($post_content, $tag->name) !== false) {
                        wp_set_post_tags($post_id, $tag->name, true);
                    }
                }
            }
        }
        //格式清理预处理
        if (AYF::get_post_action('auto_insert_html_filter', 'post_automatic')) {
            //合并方法
            $insert_content = self::light_insert_data_re_fullwidth_space($post_content);
            $insert_content = self::light_insert_data_re_div_tags($insert_content);
            $insert_content = self::light_insert_data_re_del_overlap($insert_content);
            $insert_content = self::light_insert_data_re_del_useless($insert_content);
            $formatted_content = trim($insert_content);
        }
        //中文排版
        else if (AYF::get_post_action('auto_chs_compose_filter', 'post_automatic')) {
            //过滤一些禁止的参数
            $correct_array = $options['the_post_auto_chs_compose_type'];
            //对文章内容进行格式化
            $formatted_content = self::aya_autoload_chs_type_setting($post_content, $correct_array);
            //对文章标题进行格式化
            $formatted_title = self::aya_autoload_chs_type_setting($post_title, $correct_array);
        }
        //是否刷新文章日期
        if (AYF::get_post_action('reset_post_datetime', 'post_automatic')) {
            // 重置发布日期为当前时间
            $reset_date_time = current_time('mysql');
        }
        //开始更新文章内容
        $post_array = array();

        $post_array['ID'] = $post_id;

        if (!empty($formatted_content)) {
            $post_array['post_content'] = $formatted_content;
        }
        if (!empty($formatted_title)) {
            $post_array['post_title'] = $formatted_title;
        }
        if (!empty($reset_date_time)) {
            $post_array['post_date'] = $reset_date_time;
            $post_array['post_date_gmt'] = get_gmt_from_date($reset_date_time);
        }

        //更新文章
        wp_update_post($post_array);

        //恢复钩子
        add_action('save_post', array($this, 'aya_theme_save_post_auto_formatting'));
    }

    //HTML标签去除全角空格*(　) 
    private function light_insert_data_re_fullwidth_space($data)
    {
        $data = preg_replace('/(　)*/', '', $data['post_content_filtered']);
        $data = preg_replace('/&nbsp;/', '', $data['post_content_filtered']);

        return $data;
    }

    //HTML标签替换为P
    private function light_insert_data_re_div_tags($data)
    {
        foreach (array('div', 'center') as $val) {
            $data = preg_replace('#<' . $val . '[^>]*>(.*?)</' . $val . '>#is', '<p>$1</p>', $data); //替换为p	

        }

        return $data;
    }

    //HTML标签去除重叠
    private function light_insert_data_re_del_overlap($data)
    {
        foreach (array('strong', 'b') as $val) {
            $data = preg_replace('#<' . $val . '><' . $val . '>(.*?)</' . $val . '></' . $val . '>#is', '<' . $val . '>$1</' . $val . '>', $data);
            $data = preg_replace('#</' . $val . '><' . $val . '>#is', '', $data);
            $data = preg_replace('#<' . $val . '></' . $val . '>#is', '', $data);
        }

        return $data;
    }

    //HTML标签删除
    private function light_insert_data_re_del_useless($data)
    {
        foreach (array('span', 'section') as $val) {
            $data = preg_replace('/<(\/?' . $val . '.*?)>/si', '', $data); //过滤标签

        }

        return $data;
    }

    //自动别名

    //添加分类时替换分类slug为拼音
    public function aya_insert_term_data_slug($data, $taxonomy, $term_arr)
    {
        $options = $this->automatic_options;

        if ($options['the_term_auto_pinyin_slug_bool'] == true) {
            //已存在，跳过
            if (!empty($term_arr['slug'])) {
                return $data;
            }

            $pinyin_slug = sanitize_title(self::aya_autoload_pinyin_permalink($data['name'], true));

            $data['slug'] = wp_unique_term_slug($pinyin_slug, (object) $term_arr);
        }

        return $data;
    }

    //更新分类时替换分类slug为拼音
    public function aya_update_term_data_slug($data, $term_id, $taxonomy, $term_arr)
    {
        $options = $this->automatic_options;

        if ($options['the_term_auto_pinyin_slug_bool'] == true) {
            //已存在，跳过
            if (!empty($term_arr['slug'])) {
                return $data;
            }

            $pinyin_slug = sanitize_title(self::aya_autoload_pinyin_permalink($data['name'], true));

            $data['slug'] = wp_unique_term_slug($pinyin_slug, (object) $term_arr);
        }

        return $data;
    }

    //保存文章时替换文章slug自定义格式
    public function aya_insert_post_data_slug($data, $post_arr)
    {
        $options = $this->automatic_options;

        //跳过自动草稿
        if ('auto-draft' === $post_arr['post_status']) {
            return $data;
        }

        //处理拼音化别名
        if ($options['the_post_auto_pinyin_slug_bool'] == true) {
            //已存在，跳过
            if (!empty($post_arr['post_name'])) {
                return $data;
            }
            //检查标题是否为空
            if (empty($post_arr['post_title'])) {
                return $data;
            }

            //使用拼音生成别名
            $formatted_sulg = sanitize_title(self::aya_autoload_pinyin_permalink($post_arr['post_title'], true));
            //替换数据
            $data['post_name'] = wp_unique_term_slug($formatted_sulg, (object) $post_arr);
        }

        return $data;
    }

    //强制文章别名
    public function aya_auto_unique_post_slug($slug, $post_id, $post_status, $post_type, $post_parent)
    {
        $options = $this->automatic_options;

        //只针对文章类型
        if (in_array($post_type, array('post', 'tweet'))) {
            //添加文章时防止循环
            $num_id = absint($post_id);

            if ($num_id === 0) {
                return $slug;
            }

            //获取设置
            $slug_type = $options['the_post_auto_slug_type'];

            if ($slug_type !== 'off') {

                $formatted_slug = self::aya_auto_post_slug_format($post_id);

                if ($formatted_slug !== false) {
                    return $formatted_slug;
                }
            }
        }

        return $slug;
    }

    //使用ID生成别名
    function aya_auto_post_slug_format($post_id)
    {
        $options = $this->automatic_options;

        //等于0时
        if ($post_id <= 0) {
            return false;
        }

        //获取设置
        $slug_type = $options['the_post_auto_slug_type'];
        //别名前缀
        $prefix = $options['site_post_auto_slug_prefix'];
        //处理输入只允许字母数字和结构无关的特殊字符
        $prefix = preg_replace('/[^a-zA-Z0-9\-._~:\/?#[\]@!$&\'()*+,;=]/u', '', $prefix);

        //低仿AV号
        if ($slug_type === 'id_av') {

            return $prefix . str_pad($post_id, 8, '0', STR_PAD_LEFT);
        }
        //低仿BV号
        else if ($slug_type === 'id_bv') {
            //调用了主题方法，防止报错
            if (function_exists('aya_token_encode')) {
                return $prefix . aya_token_encode($post_id, 8);
            } else {
                return $prefix . str_pad($post_id, 8, '0', STR_PAD_LEFT);
            }
        }

        //未知参数
        return false;
    }

    //批量刷新文章别名
    function aya_post_all_slug_rewrite_update()
    {
        //获取所有文章
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        $posts = get_posts($args);

        $count = 0;
        $message = 'Applying posts slug batch update...' . PHP_EOL;

        foreach ($posts as $post) {
            //使用指定格式或默认格式生成新别名
            $new_slug = self::aya_auto_post_slug_format($post->ID);

            //只有当返回值不是false时才更新别名
            if ($new_slug !== false) {
                //确保别名唯一性
                $new_slug = wp_unique_post_slug($new_slug, $post->ID, $post->post_status, $post->post_type, $post->post_parent);

                //更新文章别名
                $result = wp_update_post(array(
                    'ID' => $post->ID,
                    'post_name' => $new_slug
                ));

                if ($result) {
                    $count++;
                    $message .= 'POST_ID(' . $post->ID . ') -> ' . $new_slug . PHP_EOL;
                }
            }
        }

        $message .= 'done.';

        print_r($message);
        return;
    }
}
