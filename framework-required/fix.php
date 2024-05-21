<?php
if (!defined('ABSPATH')) exit;

/*

//$local_avatars = new AYA_Plugin_Local_Avatars;
function get_simple_local_avatar($id_or_email, $size = '96', $default = '', $alt = false)
{
    global $local_avatars;

    $avatar = $local_avatars->get_avatar('', $id_or_email, $size, $default, $alt);

    if (empty($avatar))
        $avatar = get_avatar($id_or_email, $size, $default, $alt);

    return $avatar;
}
*/

/**
 * 用于生成设置项内容的一些方法
 */

function framework_doc_about_page()
{
    $document_file = fopen(AYF_PATH . '/document.html', 'r') or die('Unable to open file!');
    echo fread($document_file, filesize(AYF_PATH . '/document.html'));
    fclose($document_file);
}

//生成robots.txt内容
function get_default_robots_text()
{
    //兼容站点路径设置
    $site_url = parse_url(site_url());
    $path = !empty($site_url['path']) ? $site_url['path'] : '';
    //生成
    return '
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php
Disallow: /wp-includes/
Disallow: /cgi-bin/

Disallow: ' . $path . '/wp-content/plugins/
Disallow: ' . $path . '/wp-content/themes/
Disallow: ' . $path . '/wp-content/cache/
Disallow: ' . $path . '/trackback/
Disallow: ' . $path . '/feed/
Disallow: ' . $path . '/comments/
Disallow: ' . $path . '/search/
Disallow: ' . $path . '/?s=
Disallow: ' . $path . '/go/
Disallow: ' . $path . '/link/

Sitemap: ' . site_url() . '/wp-sitemap.xml
';
}
//所有短代码
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
    echo '<table>';
    echo '<thead><tr><th>简码</th><th>回调函数</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['tag'] . '</td><td>' . $item['callback'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
//所有路由
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
    echo '<table>';
    echo '<thead><tr><th>ID</th><th>正则</th><th>查询方法</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['rewrite_id'] . '</td><td>' . $item['regex'] . '</td><td>' . $item['query'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
