<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 预置方法
 * ------------------------------------------------------------------------------
 */

//插件功能转换参数
function ayf_plugin_action($field_array, $plugin_sulg)
{
    if (!is_array($field_array)) return;

    $action_array = array();

    //遍历
    foreach ($field_array as $field) {
        //跳过
        if (empty($field['id'])) continue;

        //验证选项布尔型
        if ($field['type'] === 'switch') {
            $action_array[$field['id']] = AYF::get_checked($field['id'], $plugin_sulg);
        } else {
            $action_array[$field['id']] = AYF::get_opt($field['id'], $plugin_sulg);
        }
    }

    //print_r($action_array);
    //ayf_plugin_action_print($field_array);
    //返回
    return $action_array;
}
//打印当前设置表单
function ayf_plugin_action_print($field_array)
{
    if (!is_array($field_array)) return;

    $setting_array = array();
    $i = 0;
    foreach ($field_array as $field) {
        $i++;
        if (empty($field['id'])) {
            continue;
        }
        $setting_array[$i] = $field['id'] . '/' . $field['title'] . '/' . $field['desc'];
    }

    print_r($setting_array);
}
//验证MD5的方法
function ayf_md5_file_source($filenamesource, $filenamedest)
{
    $sourcefile = md5_file($filenamesource);
    $destfile   = md5_file($filenamedest);
    if ($sourcefile == $destfile) {
        return  true;
    } else {
        return  false;
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
                $callback = '<p>' . get_class($callback[0]) . '->' . (string)$callback[1] . '</p>';
            } else {
                $callback = '<p>' . $callback[0] . '->' . (string)$callback[1] . '</p>';
            }
        } elseif (is_object($callback)) {
            $callback = '<pre>' . print_r($callback, true) . '</pre>';
        } else {
            $callback    = wpautop($callback);
        }
        //简码+回调函数
        $items[] = ['tag' => wpautop($tag), 'callback' => $callback];
    }

    //print_r($items);
    //return $items;

    //将获得的数组转换为html表格
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>简码</th><th>回调函数</th></tr></thead>';
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
    echo '<thead><tr><th>ID</th><th>正则</th><th>查询方法</th></tr></thead>';
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

//设置页面和内容
$AYF_PARENT_FIELDS = array(
    array(
        'desc' => '禁用拓展',
        'type' => 'title_2',
    ),
    array(
        'title' => '全局禁用',
        'desc' => '全局禁用所有后台功能和插件，以使用其他插件代替',
        'id' => 'all_plugin_off',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '插件',
        'type' => 'title_2',
    ),
    array(
        'title' => '外部功能加速',
        'desc' => '将 Gravatar 头像服务、谷歌字体服务 替换为国内CDN',
        'id' => 'plugin_add_avatar_speed',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '简单SEO组件',
        'desc' => '替代页面标题配置器并支持一些基础的SEO功能',
        'id' => 'plugin_add_seo_stk',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '额外代码',
        'desc' => '为站点增加额外 JS/CSS 代码，支持最小化添加百度统计和谷歌统计',
        'id' => 'plugin_add_site_statistics',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => ' STMP 送信',
        'desc' => '通过 STMP 发送站点通知',
        'id' => 'plugin_add_stmp_mail',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '经典编辑器拓展',
        'desc' => '编辑增强插件，按钮重排、支持表格、自动上传 Tips：仅支持TinyMCE（经典编辑器）',
        'id' => 'plugin_tinymce_add_modify',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '本地头像拓展',
        'desc' => '本地化头像插件，允许作者及以上权限的用户上传头像到站点（在后台个人资料页面上传）',
        'id' => 'plugin_local_avatar_upload',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '此功能完全重建了分类的页面的路由方法，请自行测试你的主题 / 插件是否兼容',
        'type' => 'message',
    ),
    array(
        'title' => '分类 URL 重建',
        'desc' => '移除分类URL中 <code>/category/</code> 层级，启用此项功能后，需要在 <a href="options-permalink.php">固定链接</a> 设置中重新保存一次',
        'id' => 'plugin_no_category_url',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '开发者功能',
        'type' => 'title_2',
    ),
    array(
        'title' => '服务器状态信息',
        'desc' => '在仪表盘中显示服务器状态信息组件（仅在打开仪表盘时读取一次，无监控功能）',
        'id' => 'dashboard_server_monitor',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => 'DEBUG模式',
        'desc' => '在wp_footer中输出SQL和include等调试信息',
        'id' => 'debug_mode',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '简码列表',
        'desc' => '列出WP当前的全部固定链接（ Rewrite 规则）和查询方法',
        'id' => 'debug_shortcode_items',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '路由列表',
        'desc' => '列出WP当前的全部简码功能（ Shortcode 字段），并列出回调函数',
        'id' => 'debug_rules_items',
        'type' => 'switch',
        'default' => false,
    ),
);
echo AYF::get_checked('all_plugin_off', 'plugin');
//创建父级设置页面和内容
if (AYF::get_checked('all_plugin_off', 'plugin')) {
    AYF::new_opt(
        array(
            'title' => 'AIYA-Optimize',
            'slug' => 'plugin',
            'desc' => 'AIYA-CMS 主题，全局功能组件',
            'fields' => array(
                array(
                    'desc' => '禁用拓展',
                    'type' => 'title_2',
                ),
                array(
                    'title' => '全局禁用',
                    'desc' => '全局禁用所有后台功能和插件，以使用其他插件代替',
                    'id' => 'all_plugin_off',
                    'type' => 'switch',
                    'default' => true,
                ),
            ),
        )
    );

    //退出当前脚本
    return;
} else {
    AYF::new_opt(
        array(
            'title' => 'AIYA-Optimize',
            'slug' => 'plugin',
            'desc' => 'AIYA-CMS 主题，全局功能组件',
            'fields' => $AYF_PARENT_FIELDS,
        )
    );
}
