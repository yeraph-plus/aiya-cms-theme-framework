<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 预置方法
 * ------------------------------------------------------------------------------
 */

//验证MD5的方法
function ayf_md5_file_source($filenamesource, $filenamedest)
{
    $sourcefile = md5_file($filenamesource);
    $destfile = md5_file($filenamedest);
    if ($sourcefile == $destfile) {
        return true;
    } else {
        return false;
    }
}

//查询所有短代码
function query_shortcode_items()
{
    $items = [];

    //循环获取所有短代码和回调函数
    foreach ($GLOBALS['shortcode_tags'] as $tag => $callback) {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $callback = '<p>' . get_class($callback[0]) . '->' . (string) $callback[1] . '</p>';
            } else {
                $callback = '<p>' . $callback[0] . '->' . (string) $callback[1] . '</p>';
            }
        } else if (is_object($callback)) {
            $callback = '<pre>' . print_r($callback, true) . '</pre>';
        } else {
            $callback = wpautop($callback);
        }
        //简码+回调函数
        $items[] = ['tag' => wpautop($tag), 'callback' => $callback];
    }

    //print_r($items);
    //return $items;

    //将获得的数组转换为html表格
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>' . __('短代码', 'aiya-framework') . '</th><th>' . __('回调函数', 'aiya-framework') . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['tag'] . '</td><td>' . $item['callback'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

//查询所有路由
function query_rewrite_rules_items($args)
{
    $items = [];
    $rewrite_id = 0;
    //获取WP设置
    $rewrite_rules = get_option('rewrite_rules') ?: [];
    //循环设置
    foreach ($rewrite_rules as $regex => $query) {
        $rewrite_id++;
        $items[] = compact('rewrite_id', 'regex', 'query');
    }

    //print_r($items);
    //return $items;

    //将获得的数组转换为html表格
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>' . __('ID', 'aiya-framework') . '</th><th>' . __('正则', 'aiya-framework') . '</th><th>' . __('查询方法', 'aiya-framework') . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['rewrite_id'] . '</td><td>' . $item['regex'] . '</td><td>' . $item['query'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

//生成robots.txt内容
function ayf_get_default_robots_text()
{
    //兼容站点地址设置
    $site_url = parse_url(site_url());
    $path = !empty($site_url['path']) ? $site_url['path'] : '';
    //生成
    $output = "User-agent: *\n";
    $output .= "Disallow: *?*\n";
    $output .= "Disallow: $path/wp-admin/\n";
    $output .= "Disallow: $path/wp-includes/\n";
    $output .= "Disallow: $path/wp-content/themes/*\n";
    $output .= "Disallow: $path/wp-content/plugins/*\n";
    $output .= "Disallow: $path/wp-content/cache\n";
    $output .= "Disallow: $path/trackback\n";
    $output .= "Disallow: $path/feed\n";
    $output .= "Disallow: $path/comments\n";
    $output .= "Disallow: $path/search\n";
    $output .= "Disallow: $path/go/\n";
    $output .= "Disallow: $path/link/\n";
    $output .= "\n";
    $output .= "Allow: /wp-admin/admin-ajax.php\n";
    $output .= "\n";
    //$output .= "Sitemap: $site_url/wp-sitemap.xml";

    return $output;
}

/*
 * ------------------------------------------------------------------------------
 * 父级设置页面
 * ------------------------------------------------------------------------------
 */

//创建父级设置页面和内容
if (AYF::get_checked('all_plugin_off', 'plugin') === false) {
    AYF::new_opt([
        'title' => __('AIYA-Optimize', 'aiya-framework'),
        'page_tittle' => __('首选项', 'aiya-framework'),
        'slug' => 'plugin',
        'desc' => __('AIYA-CMS 主题，全局功能组件', 'aiya-framework'),
        'fields' => [
            [
                'desc' => __('禁用拓展', 'aiya-framework'),
                'type' => 'title_2',
            ],
            [
                'title' => __('全局禁用', 'aiya-framework'),
                'desc' => __('全局禁用所有后台功能和插件，以使用其他插件代替', 'aiya-framework'),
                'id' => 'all_plugin_off',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'desc' => __('次要组件', 'aiya-framework'),
                'type' => 'title_2',
            ],
            [
                'title' => __('外部功能加速', 'aiya-framework'),
                'desc' => __('将 Gravatar 头像服务、谷歌字体服务替换为国内 CDN 加速', 'aiya-framework'),
                'id' => 'plugin_add_avatar_speed',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('简单 SEO 组件', 'aiya-framework'),
                'desc' => __('替代页面标题配置器并支持一些基础的 SEO 功能', 'aiya-framework'),
                'id' => 'plugin_add_seo_stk',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('简单 WAF 防护', 'aiya-framework', 'aiya-framework'),
                'desc' => __('基于用户UA、IP、特定参数检测的防火墙功能', 'aiya-framework'),
                'id' => 'plugin_add_ua_firewall',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('垃圾评论过滤', 'aiya-framework'),
                'desc' => __('基于语法规则的垃圾评论过滤，无需 Akismet 接口', 'aiya-framework'),
                'id' => 'plugin_add_comment_filter',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('额外代码', 'aiya-framework'),
                'desc' => __('为站点增加额外 JS/CSS 代码，支持最小化添加百度统计和谷歌统计', 'aiya-framework'),
                'id' => 'plugin_add_site_statistics',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('STMP 通知', 'aiya-framework'),
                'desc' => __('通过 STMP 发送站点通知', 'aiya-framework'),
                'id' => 'plugin_add_stmp_mail',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('本地化头像', 'aiya-framework'),
                'desc' => __('本地化头像功能，允许作者及以上权限的用户上传头像到站点（在后台个人资料页面上传）', 'aiya-framework'),
                'id' => 'plugin_local_avatar_upload',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'desc' => __('此功能完全重建了分类页面的页面的路由方法，请自行测试你的主题 / 插件是否兼容', 'aiya-framework'),
                'type' => 'message',
            ],
            [
                'title' => __('分类 URL 重建', 'aiya-framework'),
                'desc' => __('移除分类URL中 [code]/category/[/code] 层级，启用此项功能后，需要在 [url=options-permalink.php]固定链接[/url] 设置中重新保存一次', 'aiya-framework'),
                'id' => 'plugin_no_category_url',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'desc' => __('开发者功能', 'aiya-framework'),
                'type' => 'title_2',
            ],
            [
                'title' => __('服务器状态信息', 'aiya-framework'),
                'desc' => __('在仪表盘中显示服务器状态信息组件（仅在打开仪表盘时读取一次，无监控功能）', 'aiya-framework'),
                'id' => 'dashboard_server_monitor',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('DEBUG模式', 'aiya-framework'),
                'desc' => __('在 wp_footer() 中输出 SQL 和 include 等调试信息', 'aiya-framework'),
                'id' => 'debug_mode',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('简码列表', 'aiya-framework'),
                'desc' => __('列出 WP 当前的全部固定链接（ Rewrite 规则）和查询方法', 'aiya-framework'),
                'id' => 'debug_shortcode_items',
                'type' => 'switch',
                'default' => false,
            ],
            [
                'title' => __('路由列表', 'aiya-framework'),
                'desc' => __('列出 WP 当前的全部简码功能（ Shortcode 字段）并列出回调函数', 'aiya-framework'),
                'id' => 'debug_rules_items',
                'type' => 'switch',
                'default' => false,
            ],
        ],
    ]);
} else {
    AYF::new_opt([
        'title' => __('AIYA-Optimize', 'aiya-framework'),
        'slug' => 'plugin',
        'page_tittle' => __('首选项', 'aiya-framework'),
        'desc' => __('AIYA-CMS 主题，全局功能组件', 'aiya-framework'),
        'fields' => [
            [
                'desc' => __('禁用拓展', 'aiya-framework'),
                'type' => 'title_2',
            ],
            [
                'title' => __('全局禁用', 'aiya-framework'),
                'desc' => __('全局禁用所有后台功能和插件，以使用其他插件代替', 'aiya-framework'),
                'id' => 'all_plugin_off',
                'type' => 'switch',
                'default' => true,
            ],
        ],
    ]);

    //退出当前脚本
    return;
}
