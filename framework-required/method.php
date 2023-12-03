<?php
if (!defined('ABSPATH')) exit;

require_once AYA_CORE_PATH . '/page.php';

//启动框架
$AYF = new AYF();

//定义一些全局变量
global $aya_post_type, $aya_tax_type;

//创建父级设置页面内容
$opt_self = array(
    'title' => 'AIYA-CMS',
    'slug' => '',
    'desc' => 'AIYA-CMS 主题 & 设置框架',
    'fields' => array(
        array(
            'function' => 'about_page',
            'type' => 'callback',
        ),
    )
);

//创建功能设置
$opt_field = array(
    'title' => '优化设置',
    'slug' => 'optimize',
    'desc' => 'AIYA-CMS 主题，优化、安全性功能设置',
    'fields' => array(
        array(
            'desc' => '优化设置',
            'type' => 'title_h2',
        ),
        array(
            'desc' => '头像加速',
            'type' => 'title_h3',
        ),
        array(
            'title' => '使用Cravatar',
            'desc' => '使用国内的 cravatar.cn 头像服务替代Gravatar。',
            'id'   => 'default_cravatar',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '加速Gravatar',
            'desc' => '替换 gravatar.com 头像服务的地址到镜像源，和上一项互斥。',
            'id'   => 'default_gravatar',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => 'Gravatar镜像源',
            'desc' => '使用Gravatar头像服务的镜像源。',
            'id'   => 'avatar_cdn_type',
            'type' => 'radio',
            'sub'  => array(
                'qiniu' => '七牛 CDN',
                'v2ex' => 'V2EX CDN',
                'loli' => 'LOLI 图床',
                'cn' => 'GravatarCN源',
            ),
            'default' => 'qiniu',
        ),
        array(
            'title' => '自定义镜像',
            'desc' => '使用自定义Gravatar头像服务的镜像源。',
            'id'   => 'avatar_cdn_custom',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '强制HTTPS',
            'desc' => '启用头像服务通过HTTPS加载',
            'id'   => 'avatar_ssl',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => '前台设置',
            'type' => 'title_h3',
        ),
        array(
            'title' => '去除URL中category',
            'desc' => '去除前台分类页面URL中的<code>/category/</code>层级。',
            'id'   => 'no_category_base',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '精简&lt;head&gt;结构',
            'desc' => '精简&lt;head&gt;中的无用标签。',
            'id'   => 'remove_head_redundant',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '移除谷歌字体',
            'desc' => '禁止WP加载谷歌字体，并移除样式。',
            'id'   => 'remove_open_sans',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '移除emoji&#x27;s',
            'desc' => '禁止WP加载Emoji&#x27;s组件的样式。',
            'id'   => 'remove_wp_emojicons',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '移除默认页面标题',
            'desc' => '使用SEO插件时，移除&lt;head&gt;中由WP生成的标题。',
            'id'   => 'remove_head_title',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '移除古腾堡样式',
            'desc' => '禁用 Gutenberg 引入的样式（会导致前台通过Gutenberg自定义的外观失效）。',
            'id'   => 'remove_gutenberg_styles',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '移除WP嵌入功能',
            'desc' => '移除&lt;head&gt;标签中的嵌入功能组件（embed）',
            'id'   => 'remove_head_oembed',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '同时禁用WP链接解析',
            'desc' => '禁用WP内置的自动链接解析，例如Youtube等外部网站输入时自动加载的&lt;iframe&gt;，主要是防止WP自动引入&lt;head&gt;中被移除的链接。',
            'id'   => 'remove_autoembed',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用s.w.org标记',
            'desc' => '禁用 dns-prefetch （dns预解析）功能。',
            'id'   => 'remove_sworg',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用静态文件版本号',
            'desc' => '移除前台静态文件加载时引入的版本号（可能会导致用户浏览器缓存的静态文件和服务器不一致）。',
            'id'   => 'remove_css_js_ver',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁止WP自动生成缩略图',
            'desc' => '通过将WP默认缩略图尺寸设置为0来禁止自动生成缩略图。',
            'id'   => 'remove_wp_thumbnails',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁止超大图片自动缩放',
            'desc' => '禁止WP对大于5000px*7000px的图像自动缩放。',
            'id'   => 'remove_image_threshold',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => '后台设置',
            'type' => 'title_h3',
        ),
        array(
            'title' => '启用WEBP图像支持',
            'desc' => '允许WEBP图像上传，并启用WP内置的WEBP图像支持。',
            'id'   => 'add_upload_webp',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '启用链接管理器',
            'desc' => '启用WP内置的链接管理器功能。',
            'id'   => 'add_link_manager',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用修订版本记录',
            'desc' => '禁用编辑器修订版本记录功能。',
            'id'   => 'remove_revisions',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用自动保存',
            'desc' => '禁用编辑器自动保存功能。',
            'id'   => 'remove_editor_autosave',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => '安全性设置',
            'type' => 'title_h2',
        ),
        array(
            'desc' => '禁用功能',
            'type' => 'title_h3',
        ),
        array(
            'title' => '禁用XML-RPC',
            'desc' => '禁用XML-RPC功能，启用后访问此接口会返回空页面。',
            'id'   => 'disable_xmlrpc',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用PingBack',
            'desc' => '禁用PingBack功能，或阻止PingBack自己。',
            'id'   => 'disable_pingback',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用REST-API',
            'desc' => '禁用REST-API，启用后访问此接口会返回WP报错。',
            'id'   => 'disable_rest_api',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '添加REST-API登录验证',
            'desc' => '不禁用REST-API，在REST-API内部添加用户登录验证。',
            'id'   => 'add_rest_api_logged_verify',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用Feed（RSS）',
            'desc' => '禁用Feed功能，启用后访问此接口会返回WP报错。',
            'id'   => 'disable_feed',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '屏蔽特定参数访问',
            'desc' => '大部分时候不需要，主要是用来防止百度统计刷数据的。',
            'id'   => 'add_access_reject',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => ' - ',
            'desc' => '需要屏蔽的Url关键字参数，通过<code>,</code>分隔。',
            'id'   => 'access_reject_list',
            'type' => 'text',
            'default' => 'wd,str',
        ),
        array(
            'desc' => '登录验证',
            'type' => 'title_h3',
        ),
        array(
            'title' => '登录页面保护',
            'desc' => '启用后需要使用 <code>' . home_url() . '/wp-login.php?login=admin</code> 来登录后台。',
            'id'   => 'add_admin_login_protection',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => ' - ',
            'desc' => '登录页面的访问参数名',
            'id'   => 'admin_login_args',
            'type' => 'text',
            'default' => 'login',
        ),
        array(
            'title' => ' - ',
            'desc' => '登录页面的访问参数值',
            'id'   => 'admin_login_args_val',
            'type' => 'text',
            'default' => 'admin',
        ),
        array(
            'title' => '限制后台访问权限',
            'desc' => '禁止作者以下权限的用户访问后台。',
            'id'   => 'add_admin_logged_verify',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用登录错误提示',
            'desc' => '登录用户名或密码不正确时不会返回错误提示。',
            'id'   => 'disable_login_errors',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用找回密码',
            'desc' => '禁用找回密码功能。',
            'id'   => 'disable_allow_password_reset',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁用管理员邮箱确认',
            'desc' => '禁用WP内置的管理员用户定期提示邮箱确认功能。',
            'id'   => 'disable_admin_email_check',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => '全站查询设置',
            'type' => 'title_h2',
        ),
        array(
            'desc' => '首页',
            'type' => 'title_h3',
        ),
        array(
            'title' => '首页显示自定文章类型',
            'desc' => '将自定义的文章类型加入到主查询。',
            'id'   => 'query_post_type_var',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '首页取消文章置顶',
            'desc' => '取消置顶，按默认的文章排序方式输出。',
            'id'   => 'query_ignore_sticky',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '首页排除分类',
            'desc' => '填写首页排除分类的ID，通过<code>,</code>分隔',
            'id'   => 'query_ignore_category',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '首页排除文章',
            'desc' => '填写首页排除文章的ID，通过<code>,</code>分隔',
            'id'   => 'query_ignore_post',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'desc' => '搜索页',
            'type' => 'title_h3',
        ),
        array(
            'title' => '检查关键词是否为空',
            'desc' => '当搜索关键词输入为空时，不跳转到搜索结果页。',
            'id'   => 'search_redirect_intend',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '搜索结果跳过',
            'desc' => '当搜索关键词结果有且只有一篇文章时，直接转到文章页。',
            'id'   => 'search_redirect_request',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '同时搜索页面内容',
            'desc' => '搜索时同时搜索页面，将页面作为文章添加到搜索结果。',
            'id'   => 'search_page_type',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '搜索时排除分类',
            'desc' => '填写搜索时排除分类的ID，通过<code>,</code>分隔',
            'id'   => 'serach_ignore_category',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '搜索时排除文章',
            'desc' => '填写搜索时排除文章的ID，通过<code>,</code>分隔',
            'id'   => 'serach_ignore_post',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'desc' => '用户页',
            'type' => 'title_h3',
        ),
        array(
            'title' => '查询全部文章',
            'desc' => '对管理员或登录用户自己，显示全部状态的文章（包含草稿、待发布、已删除等）。',
            'id'   => 'query_author_current',
            'type' => 'switch',
            'default' => true,
        ),
    )
);

//插件功能设置
$plugin_seo_field = array(
    'title' => 'SEO设置',
    'slug' => 'head',
    'desc' => 'AIYA-CMS 主题，SEO组件设置',
    'fields' => array(
        array(
            'desc' => '额外代码',
            'type' => 'title_h3',
        ),
        array(
            'title' => '配置favicon.ico',
            'desc' => '配置站点的favicon.ico图标。',
            'id'   => 'site_favicon_url',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '统计代码',
            'desc' => '配置站点统计代码。',
            'id'   => 'site_analytics_script',
            'type' => 'textarea',
            'default' => '',
        ),
        array(
            'title' => '额外CSS',
            'desc' => '配置站点额外CSS。',
            'id'   => 'site_extra_css',
            'type' => 'textarea',
            'default' => '',
        ),
        array(
            'desc' => '标题设置',
            'type' => 'title_h3',
        ),
        array(
            'title' => '站点标题',
            'desc' => '只影响head标签内部，留空则默认引用站点设置。',
            'id'   => 'site_title',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '站点副标题',
            'desc' => '只影响head标签内部，留空则默认引用站点描述设置。',
            'id'   => 'site_subtitle',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '显示副标题',
            'desc' => '只影响head标签内部，是否显示副标题。',
            'id'   => 'subtitle_true',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '标题分隔符',
            'desc' => '只影响head标签内部，留空则引用默认设置。',
            'id'   => 'title_sep',
            'type' => 'text',
            'default' => '-',
        ),
        array(
            'title' => '分隔符空格补正',
            'desc' => '只影响head标签内部，对分隔符两端添加空格补正。',
            'id'   => 'title_sep_space',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => 'SEO功能',
            'type' => 'title_h3',
        ),
        array(
            'title' => '启用SEO组件',
            'desc' => '同时启用文章和分类SEO组件，如果使用其他SEO插件则需要禁用此项。',
            'id'   => 'seo_action',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '首页SEO关键词',
            'desc' => '添加到首页描述。',
            'id'   => 'home_seo_keywords',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '首页SEO描述',
            'desc' => '添加到首页描述。',
            'id'   => 'home_seo_description',
            'type' => 'textarea',
            'default' => '',
        ),
        array(
            'title' => '自定义robots.txt',
            'desc' => '自定义robots.txt的内容，禁用则默认引用站点设置。',
            'id'   => 'custom_robots_true',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => 'robots.txt',
            'desc' => '自定义robots.txt的内容。',
            'id'   => 'custom_robots_txt',
            'type' => 'textarea',
            'default' => '' //get_default_robots_txt(),
        ),
    )
);
$plugin_stmp_field = array(
    'title' => '邮件设置',
    'slug' => 'stmpmail',
    'desc' => '邮件通知和STMP送信功能设置',
    'fields' => array(
        array(
            'desc' => '新用户注册通知',
            'type' => 'title_h3',
        ),
        array(
            'title' => '禁止通知站长',
            'desc' => '禁用新用户注册通知站长的邮件。',
            'id'   => 'disable_new_user_email_admin',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '禁止通知用户',
            'desc' => '禁用新用户注册通知用户的邮件。',
            'id'   => 'disable_new_user_email_user',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'desc' => 'STMP送信',
            'type' => 'title_h3',
        ),
        array(
            'title' => '启用SMTP',
            'desc' => '启用SMTP邮件发送功能。',
            'id'   => 'stmp_action',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '发件邮箱',
            'desc' => '',
            'id'   => 'smtp_from',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '发件人',
            'desc' => '',
            'id'   => 'smtp_from_name',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => 'SMTP服务器',
            'desc' => '',
            'id'   => 'smtp_host',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => 'SMTP端口',
            'desc' => '',
            'id'   => 'smtp_port',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => 'SSL',
            'desc' => '',
            'id'   => 'smtp_ssl',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '用户名',
            'desc' => '',
            'id'   => 'smtp_user',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '密码',
            'desc' => '',
            'id'   => 'smtp_pass',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '启用验证',
            'desc' => '',
            'id'   => 'smtp_auth',
            'type' => 'switch',
            'default' => true,
        ),
    )
);

$AYF->new_opt($opt_self);
$AYF->new_opt($plugin_stmp_field);
$AYF->new_opt($plugin_seo_field);
$AYF->new_opt($opt_field);

//创建功能
$opt_action = array(
    'Optimize' => array(
        'default_cravatar' => $AYF->get_checked('default_cravatar', 'optimize'),
        'default_gravatar' => $AYF->get_checked('default_gravatar', 'optimize'),
        'grvatar_speed' => array(
            'avatar_cdn_type' => $AYF->get_opt('avatar_cdn_type', 'optimize'),
            'avatar_cdn_custom' => $AYF->get_opt('avatar_cdn_custom', 'optimize'),
            'avatar_ssl' => $AYF->get_checked('avatar_ssl', 'optimize'),
        ),
        'no_category_base' => $AYF->get_checked('no_category_base', 'optimize'),
        'remove_head_redundant' => $AYF->get_checked('remove_head_redundant', ''),
        'remove_head_title' => $AYF->get_checked('remove_head_title', 'optimize'),
        'remove_open_sans' => $AYF->get_checked('remove_open_sans', 'optimize'),
        'remove_gutenberg_styles' => $AYF->get_checked('remove_gutenberg_styles', 'optimize'),
        'remove_head_oembed' => $AYF->get_checked('remove_head_oembed', 'optimize'),
        'remove_autoembed' => $AYF->get_checked('remove_autoembed', 'optimize'),
        'remove_wp_emojicons' => $AYF->get_checked('remove_wp_emojicons', 'optimize'),
        'remove_sworg' => $AYF->get_checked('remove_sworg', 'optimize'),
        'remove_wp_thumbnails' => $AYF->get_checked('remove_wp_thumbnails', 'optimize'),
        'remove_image_threshold' => $AYF->get_checked('remove_image_threshold', 'optimize'),
        'remove_css_js_ver' => $AYF->get_checked('remove_css_js_ver', 'optimize'),
        'remove_revisions' => $AYF->get_checked('remove_revisions', 'optimize'),
        'remove_editor_autosave' => $AYF->get_checked('remove_editor_autosave', 'optimize'),
        'add_link_manager' => $AYF->get_checked('add_link_manager', 'optimize'),
        'add_upload_webp' => $AYF->get_checked('add_upload_webp', 'optimize'),
    ),
    'Security' => array(
        'disable_xmlrpc' => $AYF->get_checked('disable_xmlrpc', 'optimize'),
        'disable_pingback' => $AYF->get_checked('disable_pingback', 'optimize'),
        'disable_rest_api' => $AYF->get_checked('disable_rest_api', 'optimize'),
        'add_rest_api_logged_verify' => $AYF->get_checked('add_rest_api_logged_verify', 'optimize'),
        'disable_feed' => $AYF->get_checked('disable_feed', 'optimize'),
        'add_access_reject' => $AYF->get_checked('add_access_reject', 'optimize'),
        'access_reject_list' => $AYF->get_opt('access_reject_list', 'optimize'),
        'add_admin_login_protection' => $AYF->get_checked('add_admin_login_protection', 'optimize'),
        'admin_login_args' => $AYF->get_opt('admin_login_args', 'optimize'),
        'admin_login_args_val' => $AYF->get_opt('admin_login_args_val', 'optimize'),
        'add_admin_logged_verify' => $AYF->get_checked('add_admin_logged_verify', 'optimize'),
        'disable_login_errors' => $AYF->get_checked('disable_login_errors', 'optimize'),
        'disable_allow_password_reset' => $AYF->get_checked('disable_allow_password_reset', 'optimize'),
        'disable_admin_email_check' => $AYF->get_checked('disable_admin_email_check', 'optimize'),
    ),
    'Request' => array(
        'query_post_type_var' => $AYF->get_checked('query_post_type_var', 'optimize'),
        'query_ignore_sticky' => $AYF->get_checked('query_ignore_sticky', 'optimize'),
        'query_ignore_category' => $AYF->get_opt('query_ignore_category', 'optimize'),
        'query_ignore_post' => $AYF->get_opt('query_ignore_post', 'optimize'),
        'search_redirect_intend' => $AYF->get_checked('search_redirect_intend', 'optimize'),
        'search_redirect_request' => $AYF->get_checked('search_redirect_request', 'optimize'),
        'search_page_type' => $AYF->get_checked('search_page_type', 'optimize'),
        'serach_ignore_category' => $AYF->get_opt('serach_ignore_category', 'optimize'),
        'serach_ignore_post' => $AYF->get_opt('serach_ignore_post', 'optimize'),
        'query_author_current' => $AYF->get_checked('query_author_current', 'optimize'),
    ),
);
/*
//功能参数备份
$opt_action_backup = array(
    //查询设置
    'Request' => array(
        //将自定文章类型添加主查询
        'query_post_type_var' => array('tweet'),
        //首页取消文章置顶
        'query_ignore_sticky' => true,
        //首页排除指定分类ID，通过<code>,</code>分隔
        'query_ignore_category' => '1',
        //首页排除指定文章ID，通过<code>,</code>分隔
        'query_ignore_post' => '1',
        //将页面添加到搜索结果
        'search_page_type' => true,
        //搜索时排除指定分类ID，通过<code>,</code>分隔
        'serach_ignore_category' => '1',
        //搜索时排除指定文章ID，通过<code>,</code>分隔
        'serach_ignore_post' => '1',
        //用户登录后查询全部文章状态
        'query_author_current' => true,
        //当搜索关键词为空时返回首页
        'search_redirect_intend' => true,
        //当搜索关键词只有一篇文章时返回文章页
        'search_redirect_request' => true,
    ),
    //优化和安全性功能设置
    'Optimize' => array(
        //启用Cravatar
        'default_cravatar' => false,
        //加速Gravatar
        'default_gravatar' => true,
        'grvatar_speed' => array(
            //切换头像CDN
            'avatar_cdn_type' => 'qiniu', //'loli','v2ex','null'
            //自定义头像CDN地址
            'avatar_cdn_custom' => '',
            //强制使用HTTPS
            'avatar_ssl' => true,
        ),
        //启用去除URL中的category层级
        'no_category_base' => true,
        //移除head标签中的冗余信息
        'remove_head_redundant' => true,
        //移除head标签中的title
        'remove_head_title' => true,
        //移除谷歌字体
        'remove_open_sans' => true,
        //移除gutenberg编辑器引入的样式
        'remove_gutenberg_styles' => true,
        //移除head标签中的嵌入功能
        'remove_head_oembed' => true,
        //同时禁用wp内置的链接解析功能
        //主要是防止wp自动引入head中被移除的链接，以及youtube等外部网站自动加载的iframe
        'remove_autoembed' => true,
        //移除wp原生emoji's
        'remove_wp_emojicons' => true,
        //禁用sworg标记（dns-prefetch功能）
        'remove_sworg' => true,
        //设置禁用wp自动生成缩略图
        'remove_wp_thumbnails' => true,
        ///禁用超大图片自动缩放
        'remove_image_threshold' => true,
        //移除wp静态文件加载时引入的版本号
        'remove_css_js_ver' => false,
        //禁用修订版本记录
        'remove_revisions' => true,
        //禁用编辑器自动保存
        'remove_editor_autosave' => true,
        //添加链接管理器功能
        'add_link_manager' => true,
        //添加WEBP图像的支持
        'add_upload_webp' => true,
    ),
    //安全性设置
    'Security' => array(
        //禁用xmlrpc功能
        'disable_xmlrpc' => true,
        //禁用pingback功能
        'disable_pingback' => false,
        //禁用rest-api
        'disable_rest_api' => false,
        //添加rest-api登录用户验证
        'add_rest_api_logged_verify' => true,
        //禁用feed功能
        'disable_feed' => true,
        //屏蔽Url中特定参数访问
        //大部分时候不需要，主要是用来防止百度统计刷数据的 
        'add_access_reject' => false,
        //需要屏蔽的Url关键字参数，通过<code>,</code>分隔
        'access_reject_list' => 'wd,str',
        //添加wp-admin页面登录保护
        //启用后则需要使用/wp-login.php?login=admin
        'add_admin_login_protection' => true,
        //登录页面的访问参数名称
        'admin_login_args' => 'login',
        //登录页面的访问参数值
        'admin_login_args_val' => 'admin',
        //设置wp-admin页面禁止作者以下权限访问
        'add_admin_logged_verify' => true,
        //禁用登陆面板错误提示
        'disable_login_errors' => false,
        //禁用找回密码
        'disable_allow_password_reset' => false,
        //禁用管理员邮箱确认
        'disable_admin_email_check' => false,
    ),
);
*/

//插件功能
$plugin_action = array(
    'Mail_Sender' => array(
        'disable_new_user_email_admin' => $AYF->get_checked('disable_new_user_email_admin', 'stmpmail'),
        'disable_new_user_email_user' => $AYF->get_checked('disable_new_user_email_user', 'stmpmail'),
        'stmp_action' => $AYF->get_checked('stmp_action', 'stmpmail'),
        'smtp_from' => $AYF->get_opt('smtp_from', 'stmpmail'),
        'smtp_from_name' => $AYF->get_opt('smtp_from_name', 'stmpmail'),
        'smtp_host' => $AYF->get_opt('smtp_host', 'stmpmail'),
        'smtp_port' => $AYF->get_opt('smtp_port', 'stmpmail'),
        'smtp_ssl' => $AYF->get_checked('smtp_ssl', 'stmpmail'),
        'smtp_user' => $AYF->get_opt('smtp_user', 'stmpmail'),
        'smtp_pass' => $AYF->get_opt('smtp_pass', 'stmpmail'),
        'smtp_auth' => $AYF->get_checked('smtp_auth', 'stmpmail'),
    ),
    'Head_Label_Action' => array(
        'site_favicon_url' => $AYF->get_opt('avatar_cdn_custom', 'head'),
        'site_analytics_script' => $AYF->get_opt('site_analytics_script', 'head'),
        'site_extra_css' => $AYF->get_opt('site_extra_css', 'head'),
        'site_title' => $AYF->get_opt('site_title', 'head'),
        'site_subtitle' => $AYF->get_opt('site_subtitle', 'head'),
        'subtitle_true' => $AYF->get_checked('subtitle_true', 'head'),
        'title_sep' => $AYF->get_opt('title_sep', 'head'),
        'title_sep_space' => $AYF->get_checked('title_sep_space', 'head'),
        'seo_action' => $AYF->get_checked('seo_action', 'head'),
        'home_seo_keywords' => $AYF->get_opt('home_seo_keywords', 'head'),
        'home_seo_description' => $AYF->get_opt('home_seo_description', 'head'),
        'custom_robots_true' => $AYF->get_checked('custom_robots_true', 'head'),
        'custom_robots_txt' => $AYF->get_opt('custom_robots_txt', 'head'),
    ),
);

//创建主题功能
$theme_action = array(
    //运行环境检查
    'EnvCheck' => array(
        //PHP最低版本
        'php_last' => '7.4',
        //PHP扩展
        'php_ext' => array('gd', 'session', 'curl'),
        //WP最低版本
        'wp_last' => '6.1',
        //检查经典编辑器插件
        'check_classic_editor' => false,
    ),
    //后台自定义
    'Admin_Backstage' => array(
        //禁用前台顶部工具栏
        'remove_admin_bar' => true,
        //替换后台标题格式
        'admin_title_format' => true,
        //替换后台页脚信息
        'admin_footer_replace' => '感谢使用 <b>AIYA-CMS</b> ，欢迎访问 <a href="https://www.yeraph.com" target="_blank" style="text-decoration:none">Yeraph Studio</a> 了解更多。',
        //后台仪表盘添加组件
        'admin_add_dashboard_widgets' => true,
        //隐藏后台导航栏WordPress标志
        'remove_bar_wplogo' => true,
        //隐藏后台仪表盘WordPress新闻
        'remove_bar_wpnews' => true,
    ),

    /**
     * Tips：以下是一些简化方法，内部定义了路由和HTML结构，有需要时请自行修改
     **/

    //运行after_setup_theme()
    'Register_Theme' => true,
    //注册导航菜单
    'Register_Menu' => array(
        //'菜单ID' => '菜单名',
        'header-menu' => __('顶部菜单', 'AIYA'),
        'footer-menu' => __('底部菜单', 'AIYA'),
        'widget-menu' => __('小工具菜单', 'AIYA'),
    ),
    //注册小工具栏位
    'Register_Sidebar' => array(
        //'边栏ID' => '边栏名',
        'index-sitebar' => __('首页', 'AIYA'),
        'page-sitebar' => __('页面', 'AIYA'),
        'single-sitebar' => __('文内页', 'AIYA'),
        'author-sitebar' => __('用户页', 'AIYA'),
    ),
    //注册自定义文章类型
    'Register_Post_Type' => array(
        //'文章类型' => array('name' => '文章类型名','slug' => '别名','icon' => '使用的图标'),
        'tweet' => array(
            'name' => __('推文', 'AIYA'),
            'slug' => 'tweet',
            'icon' => 'dashicons-format-quote',
        ),
    ),
    //注册自定义分类法
    'Register_Tax_Type' => array(
        //'分类法' => array('name' => '分类法名','slug' => '别名','post_type' => array('此分类法适用的文章类型',)),
        'collect' => array(
            'name' => __('专题', 'AIYA'),
            'slug' => 'collect',
            'post_type' => array('post', 'page', 'tweet'),
        ),
    ),
    //注册自定义页面模板
    //Tips：这是一个为主题创建功能性页面的方法，无需用户手动新建页面选择页面模板，减少用户操作
    'Template_New_Page' => array(
        //'模板名' => '是否静态化',
        'go' => false,
        'link' => true,
        'error' => true,
    ),
    //注册小工具 Tips：请确保此时要注册的小工具类已被require_once()
    'Widget_Load' => array(
        //'小工具Class名',
        '',
    ),
    //解除 WP 自带的小工具
    'Widget_Unload' => array(
        //'需要注销的小工具Class名',
        'WP_Widget_Archives',        //年份文章归档
        'WP_Widget_Calendar',        //日历
        //'WP_Widget_Categories',      //分类列表
        //'WP_Widget_Links',           //链接
        'WP_Widget_Media_Audio',     //音乐
        'WP_Widget_Media_Video',     //视频
        'WP_Widget_Media_Gallery',   //相册
        //'WP_Widget_Custom_HTML',     //html
        'WP_Widget_Media_Image',     //图片
        //'WP_Widget_Text',            //文本
        //'WP_Widget_Meta',            //默认工具链接
        'WP_Widget_Pages',           //页面
        //'WP_Widget_Recent_Comments', //评论
        //'WP_Widget_Recent_Posts',    //文章列表
        //'WP_Widget_RSS',             //RSS订阅
        //'WP_Widget_Search',          //搜索
        //'WP_Widget_Tag_Cloud',       //标签云
        //'WP_Nav_Menu_Widget',        //菜单
        'WP_Widget_Block',           //区块
    ),
    //启用小工具缓存插件
    'Widget_Cache_Mode' => true,
);

//$AYF->new_act($theme_action);
$AYF->new_act($opt_action);
$AYF->new_act($plugin_action);

require_once AYA_CORE_PATH . '/fix.php';
