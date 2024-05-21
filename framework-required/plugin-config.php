<?php
if (!defined('ABSPATH')) exit;

//启动框架
$AYF = new AYF();
$PLUGIN_SETUP = new AYA_Theme_Setup();

//创建父级设置页面和内容
AYF::new_opt(array(
    'title' => '后台功能',
    'slug' => 'plugin',
    'desc' => 'AIYA-CMS 主题，全局功能组件',
    'fields' => array(
        array(
            'desc' => '禁用拓展',
            'type' => 'title_2',
        ),
        array(
            'title' => '全局禁用',
            'desc' => '全局禁用所有后台功能插件，以使用其他插件代替',
            'id' => 'all_plugin_off',
            'type' => 'switch',
            'default' => false,
        ),
        array(
            'desc' => 'DEBUG',
            'type' => 'title_2',
        ),
        array(
            'title' => 'DEBUG模式',
            'desc' => '启用DEBUG模式，在wp_footer中输出SQL和include等调试信息',
            'id' => 'debug_mode',
            'type' => 'switch',
            'default' => false,
        ),
        array(
            'desc' => '短代码列表',
            'type' => 'title_2',
        ),
        array(
            'function' => 'query_shortcode_items',
            'type' => 'callback',
        ),
        array(
            'desc' => '路由列表',
            'type' => 'title_2',
        ),
        array(
            'function' => 'query_rewrite_rules_items',
            'type' => 'callback',
        ),
    )
));

if (AYF::get_checked('debug_mode', 'plugin')) {
    $PLUGIN_SETUP->action(array(
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
            'check_classic_widgets' => false,
        ),
        //运行DEBUG查询
        'Debug_Mode' => true,
    ));
}

if (AYF::get_checked('all_plugin_off', 'plugin') !== true) {
    //加载子级设置页面和内容
    AYF::new_opt(array(
        'title' => '优化设置',
        'slug' => 'optimize',
        'parent' => 'plugin',
        'desc' => '禁用或调整一些WordPress内置功能，以优化网站性能',
        'fields' => array(
            array(
                'desc' => '禁用功能',
                'type' => 'title_2',
            ),
            array(
                'title' => '禁用自动更新（不建议）',
                'desc' => '禁用WP自动更新，以解决WP无法连接到wordpress.org时产生报错',
                'id' => 'disable_wp_auto_update',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '禁用管理员邮箱确认',
                'desc' => '禁用WP内置的管理员用户定期提示邮箱确认功能',
                'id' => 'disable_admin_email_check',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用XML-RPC',
                'desc' => '禁用XML-RPC功能，启用后访问此接口会返回空页面',
                'id' => 'disable_xmlrpc',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用PingBack',
                'desc' => '关闭PingBack功能，或阻止PingBack自己（Trackbacks）',
                'id' => 'disable_pingback',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用Feed（RSS）',
                'desc' => '禁用Feed功能，启用后访问此接口会返回WP报错',
                'id' => 'disable_feed',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '禁用WP嵌入功能',
                'desc' => '移除&lt;head&gt;标签中的嵌入功能组件（Embed）',
                'id' => 'disable_head_oembed',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除WP自动链接解析',
                'desc' => '禁用WP内置的自动链接解析，例如Youtube等外部网站输入时自动加载的&lt;iframe&gt;，主要是防止WP自动引入&lt;head&gt;中被移除的组件（Auto-Embed）',
                'id' => 'remove_autoembed',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除WP隐私政策页面',
                'desc' => '移除WP为欧洲通用数据保护条例（GDPR）而生成的页面。',
                'id' => 'remove_privacy_page',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用WP自动字符转换',
                'desc' => '禁用编辑器自动标点符号转换功能',
                'id' => 'remove_wp_texturize',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用WP修订版本记录',
                'desc' => '禁用编辑器修订版本记录功能，否则限制最大修订版本数量为10个',
                'id' => 'remove_revisions',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用WP自动保存',
                'desc' => '禁用编辑器自动保存功能',
                'id' => 'remove_editor_autosave',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '启用WP链接管理器',
                'desc' => '启用WP内置的链接管理器功能',
                'id' => 'add_link_manager',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'desc' => '页面优化',
                'type' => 'title_2',
            ),
            array(
                'title' => '精简&lt;head&gt;结构',
                'desc' => '精简&lt;head&gt;中的无用标签',
                'id' => 'remove_head_redundant',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用s.w.org标记',
                'desc' => '禁用 dns-prefetch （dns预解析）功能',
                'id' => 'remove_sworg',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除谷歌字体',
                'desc' => '禁止WP加载谷歌字体，并移除样式',
                'id' => 'remove_open_sans',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除emoji&#x27;s',
                'desc' => '禁止WP加载Emoji&#x27;s组件的样式',
                'id' => 'remove_wp_emojicons',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除古腾堡样式',
                'desc' => '禁用 Gutenberg 引入的样式（会导致前台通过Gutenberg自定义的外观失效）',
                'id' => 'remove_gutenberg_styles',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '移除默认页面标题',
                'desc' => '使用SEO插件时，移除&lt;head&gt;中由WP生成的标题',
                'id' => 'remove_head_title',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁用静态文件版本号',
                'desc' => '移除前台静态文件加载时引入的版本号（可能会导致用户浏览器缓存的静态文件和服务器不一致）',
                'id' => 'remove_css_js_ver',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'desc' => '缩略图',
                'type' => 'title_2',
            ),
            array(
                'title' => '启用WEBP图像支持',
                'desc' => '允许WEBP图像上传，并启用WP内置的WEBP图像支持',
                'id' => 'add_upload_webp',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '启用附件自动重命名',
                'desc' => '由于WP实际上并不具有完整的文件管理器功能，此功能将自动为文件名附加时间戳，避免重复文件名的文件过多造成大量SQL查询',
                'id' => 'auto_upload_rename',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁止WP自动生成缩略图',
                'desc' => '通过将WP默认缩略图尺寸设置为0来禁止自动生成缩略图',
                'id' => 'remove_wp_thumbnails',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '禁止超大图片自动缩放',
                'desc' => '禁止WP对大于5000px*7000px的图像自动缩放',
                'id' => 'remove_image_threshold',
                'type' => 'switch',
                'default' => true,
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => '查询设置',
        'slug' => 'request',
        'parent' => 'plugin',
        'desc' => '自定义调整WordPress首页和搜索的查询参数',
        'fields' => array(
            array(
                'desc' => '查询过滤器',
                'type' => 'title_2',
            ),
            array(
                'title' => '主循环显示自定类型',
                'desc' => '将自定义的文章类型加入到主查询',
                'id' => 'query_post_type_var',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '主循环取消文章置顶',
                'desc' => '禁用文章置顶，按默认的文章排序输出',
                'id' => 'query_ignore_sticky',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '登录用户显示全部文章',
                'desc' => '对管理员或登录用户自己，显示全部状态的文章（包含草稿、待发布、已删除等）',
                'id' => 'query_author_current',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '首页排除分类',
                'desc' => '填写首页排除分类的ID，通过<code>,</code>分隔',
                'id' => 'query_ignore_category',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '首页排除文章',
                'desc' => '填写首页排除文章的ID，通过<code>,</code>分隔',
                'id' => 'query_ignore_post',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '搜索结果包含页面',
                'desc' => '搜索时同时搜索页面和文章添加到搜索结果',
                'id' => 'search_page_type',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '搜索排除分类',
                'desc' => '填写搜索时排除分类的ID，通过<code>,</code>分隔',
                'id' => 'serach_ignore_category',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '搜索排除文章',
                'desc' => '填写搜索时排除文章的ID，通过<code>,</code>分隔',
                'id' => 'serach_ignore_post',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'desc' => '搜索功能限制',
                'type' => 'title_2',
            ),
            array(
                'title' => '启用搜索限制器',
                'desc' => '根据IP或用户角色限制搜索功能，防止滥用',
                'id' => 'serach_scope_enable',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '搜索用户验证',
                'desc' => '仅允许已登录的用户使用搜索功能，或关闭搜索',
                'id' => 'serach_scope_user_check',
                'type' => 'radio',
                'sub'  => array(
                    'all' => '无限制',
                    'logged' => '仅限登录用户',
                    'disabled' => '关闭站点搜索'
                ),
                'default' => 'all',
            ),
            array(
                'title' => '每分钟搜索限制',
                'desc' => '每分钟最大搜索次数，达到上限之后屏蔽10分钟',
                'id' => 'serach_scope_limit',
                'type' => 'text',
                'default' => '10',
            ),
            array(
                'title' => '最大搜索关键词长度',
                'desc' => '计算单位为字节，限制最大长度255字节（一个汉字为3个字节，一个英文字母为1个字节）',
                'id' => 'serach_scope_length',
                'type' => 'text',
                'default' => '255',
            ),
            array(
                'desc' => '搜索增强',
                'type' => 'title_2',
            ),
            array(
                'title' => '搜索结果匹配分类',
                'desc' => '当搜索关键词与分类/标签/自定义分类相同时，直接转到归档页',
                'id' => 'search_redirect_term_search',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '搜索结果是否为空',
                'desc' => '当搜索关键词输入为空时，重定向到首页',
                'id' => 'search_redirect_intend',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '搜索结果跳过',
                'desc' => '当搜索结果有且只有一篇文章时，直接转到文章页',
                'id' => 'search_redirect_one_post',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'desc' => '搜索增强（高级）',
                'type' => 'title_2',
            ),
            array(
                'title' => '只搜索文章标题',
                'desc' => '不搜索文章内容和摘要，提高搜索响应速度',
                'id' => 'search_clause_only_title',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '允许搜索文章ID',
                'desc' => '允许搜索ID查找文章，多个ID时支持<code>,</code>分隔',
                'id' => 'search_clause_type_id',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '允许搜索自定义字段',
                'desc' => '允许搜索自定义字段（postmeta），会大幅影响查询速度，请谨慎使用',
                'id' => 'search_clause_type_meta',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '指定搜索自定义字段',
                'desc' => '接续上一项设置，设置接受搜索的自定义字段，请输入支持的 meta_key',
                'id' => 'search_clause_meta_key',
                'type' => 'text',
                'default' => '',
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => '安全性设置',
        'slug' => 'security',
        'parent' => 'plugin',
        'desc' => '禁用或调整一些WordPress内置功能，增加登录和后台访问验证',
        'fields' => array(
            array(
                'desc' => '后台访问',
                'type' => 'title_2',
            ),
            array(
                'title' => '限制后台访问权限',
                'desc' => '根据用户角色判断，禁止权限不足的用户访问后台，并重定向回首页',
                'id' => 'admin_backend_verify',
                'type' => 'radio',
                'sub'  => array(
                    'false' => '无限制',
                    //'administrator' => '管理员',
                    'editor' => '编辑',
                    'author' => '作者',
                    'contributor' => '贡献者',
                    'subscriber' => '订阅者',
                ),
                'default' => 'false',
            ),
            array(
                'desc' => '登录页',
                'type' => 'title_2',
            ),
            array(
                'title' => '禁止管理员找回密码',
                'desc' => '<b>注意！开启此选项后如果忘记密码将只能通过SSH等其他方式删除或禁用此插件来解除限制</b>',
                'id' => 'disable_admin_allow_password_reset',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '登录次数限制',
                'desc' => '根据IP限制最大尝试登录次数为5次，防止暴力破解',
                'id' => 'logged_scope_limit_enable',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '登录防守时间（分钟）',
                'desc' => '接续上一项设置，登录尝试超过最大次数后的屏蔽时间',
                'id' => 'logged_scope_limit_times',
                'type' => 'text',
                'default' => '15',
            ),
            array(
                'desc' => '屏蔽特定参数访问',
                'type' => 'title_2',
            ),
            array(
                'title' => '启用参数屏蔽',
                'desc' => '大部分时候不需要，主要是用来防止百度统计刷数据的',
                'id' => 'add_access_reject_switch',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '屏蔽关键字',
                'desc' => '接续上一项设置，需要屏蔽的Url关键字参数，通过<code>,</code>分隔',
                'id' => 'add_access_reject_list',
                'type' => 'text',
                'default' => 'wd,str',
            ),
            array(
                'desc' => 'REST-API验证',
                'type' => 'title_2',
            ),
            array(
                'title' => '禁用REST-API',
                'desc' => '禁用REST-API，启用后访问此接口会返回WP报错',
                'id' => 'disable_rest_api',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '添加REST-API登录验证',
                'desc' => '不禁用REST-API，但在REST-API内部添加用户登录验证',
                'id' => 'add_rest_api_logged_verify',
                'type' => 'switch',
                'default' => false,
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => '加速设置',
        'slug' => 'speed',
        'parent' => 'plugin',
        'desc' => '配置头像、字体通过第三方CDN加速',
        'fields' => array(
            array(
                'desc' => 'WeAvatar头像服务',
                'type' => 'title_2',
            ),
            array(
                'title' => '使用WeAvatar',
                'desc' => '使用国内的 weavatar.com 头像服务替代Gravatar',
                'id' => 'use_speed_weavatar',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'desc' => '头像加速',
                'type' => 'title_2',
            ),
            array(
                'title' => 'Gravatar加速',
                'desc' => '替换 gravatar.com 头像服务的地址到镜像源，和上一项互斥',
                'id' => 'use_speed_gravatar',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => 'Gravatar镜像源',
                'desc' => '使用Gravatar头像服务的镜像源',
                'id' => 'avatar_cdn_type',
                'type' => 'radio',
                'sub'  => array(
                    'cn' => 'Gravatar （官方CN源）',
                    'qiniu' => '七牛 CDN',
                    'v2ex' => 'V2EX CDN',
                    'geekzu' => '极客族 CDN',
                    'loli' => 'LOLI 图床',
                ),
                'default' => 'cn',
            ),
            array(
                'title' => '自定义镜像',
                'desc' => '使用自定义Gravatar头像服务的镜像源',
                'id' => 'avatar_cdn_custom',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '强制HTTPS',
                'desc' => '强制头像服务通过HTTPS加载',
                'id' => 'avatar_ssl',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'desc' => '字体加速',
                'type' => 'title_2',
            ),
            array(
                'title' => 'Google字体加速',
                'desc' => '使用Google字体镜像源',
                'id' => 'use_speed_google_fonts',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => 'Google字体镜像源',
                'desc' => '使用Google字体的镜像源，加速主题加载',
                'id' => 'google_fonts_cdn_type',
                'type' => 'radio',
                'sub'  => array(
                    'geekzu' => '极客族 CDN',
                    'loli' => 'LOLI 图床',
                    'ustc' => '中科大 CDN',
                    'custom' => '自定义',
                ),
                'default' => 'ustc',
            ),
            array(
                'title' => '自定义镜像地址',
                'desc' => '使用自定义Google字体的镜像加速服务地址',
                'id' => 'google_fonts_cdn_custom',
                'type' => 'group',
                'sub_type' => array(
                    array(
                        'title' => 'googleapis_fonts',
                        'id' => 'fonts_cdn',
                        'type' => 'text',
                        'default'  => '//fonts.googleapis.com',
                    ),
                    array(
                        'title' => 'googleapis_ajax',
                        'id' => 'fonts_ajax',
                        'type' => 'text',
                        'default'  => '//ajax.googleapis.com',
                    ),
                    array(
                        'title' => 'googleusercontent_themes',
                        'id' => 'fonts_themes',
                        'type' => 'text',
                        'default'  => '//themes.googleusercontent.com',
                    ),
                    array(
                        'title' => 'gstatic_fonts',
                        'id' => 'fonts_gstatic',
                        'type' => 'text',
                        'default'  => '//fonts.gstatic.com',
                    ),
                ),
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => 'SMTP发信',
        'slug' => 'stmpmail',
        'parent' => 'plugin',
        'desc' => '使用SMTP发送邮件',
        'fields' => array(
            array(
                'desc' => '新用户注册通知',
                'type' => 'title_2',
            ),
            array(
                'title' => '禁止通知站长',
                'desc' => '禁用新用户注册通知站长的邮件',
                'id' => 'disable_new_user_email_admin',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'title' => '禁止通知用户',
                'desc' => '禁用新用户注册通知用户的邮件',
                'id' => 'disable_new_user_email_user',
                'type' => 'switch',
                'default' => false,
            ),
            array(
                'desc' => 'STMP送信',
                'type' => 'title_2',
            ),
            array(
                'title' => '启用SMTP',
                'desc' => '启用SMTP邮件发送功能',
                'id' => 'stmp_action',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '发件邮箱',
                'desc' => '',
                'id' => 'smtp_from',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '发件人',
                'desc' => '',
                'id' => 'smtp_from_name',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => 'SMTP服务器',
                'desc' => '',
                'id' => 'smtp_host',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => 'SMTP端口',
                'desc' => '',
                'id' => 'smtp_port',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => 'SSL',
                'desc' => '',
                'id' => 'smtp_ssl',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '用户名',
                'desc' => '',
                'id' => 'smtp_user',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '密码',
                'desc' => '',
                'id' => 'smtp_pass',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '启用验证',
                'desc' => '',
                'id' => 'smtp_auth',
                'type' => 'switch',
                'default' => true,
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => 'SEO设置',
        'slug' => 'seo',
        'parent' => 'plugin',
        'desc' => '简单SEO组件，用于自定义页面标题、关键词和描述',
        'fields' => array(
            array(
                'desc' => '图标',
                'type' => 'title_2',
            ),
            array(
                'title' => '配置favicon.ico',
                'desc' => '配置站点的favicon.ico图标',
                'id' => 'site_favicon_url',
                'type' => 'upload',
                'default' => get_template_directory_uri() . '/framework-required/assects/img/wordpress_logo.png',
            ),
            array(
                'desc' => '标题设置',
                'type' => 'title_2',
            ),
            array(
                'title' => '显示副标题',
                'desc' => '只影响head标签内部，是否显示副标题',
                'id' => 'subtitle_true',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '标题分隔符',
                'desc' => '只影响head标签内部，留空则引用默认设置',
                'id' => 'title_sep',
                'type' => 'text',
                'default' => '-',
            ),
            array(
                'title' => '分隔符空格补正',
                'desc' => '只影响head标签内部，对分隔符两端添加空格补正',
                'id' => 'title_sep_space',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'desc' => 'SEO设置',
                'type' => 'title_2',
            ),
            array(
                'title' => 'SEO标题',
                'desc' => '只影响head标签内部，留空则默认引用站点设置',
                'id' => 'site_title',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => 'SEO副标题',
                'desc' => '只影响head标签内部，留空则默认引用站点描述设置',
                'id' => 'site_subtitle',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '启用SEO组件',
                'desc' => '同时启用文章和分类SEO组件，如果使用其他SEO插件则需要禁用此项',
                'id' => 'seo_action',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => '首页SEO关键词',
                'desc' => '添加到首页关键词，仅影响首页',
                'id' => 'home_seo_keywords',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '首页SEO描述',
                'desc' => '添加到首页描述，仅影响首页',
                'id' => 'home_seo_description',
                'type' => 'textarea',
                'default' => '',
            ),
            array(
                'desc' => 'Robots设置',
                'type' => 'title_2',
            ),
            array(
                'title' => '自定义robots.txt',
                'desc' => '自定义<code>/robots.txt</code>的内容，禁用则默认引用站点设置',
                'id' => 'custom_robots_true',
                'type' => 'switch',
                'default' => true,
            ),
            array(
                'title' => 'robots.txt',
                'desc' => '自定义robots.txt的内容',
                'id' => 'custom_robots_txt',
                'type' => 'textarea',
                'default' => get_default_robots_text(),
            ),
            array(
                'desc' => 'WP-Sitemap设置',
                'type' => 'title_2',
            ),
            array(
                'title' => '禁止输出用户列表',
                'desc' => '禁止站点的<code>/wp-sitemap.xml</code>中生成Users列表',
                'id' => 'sremove_sitemaps_provider',
                'type' => 'switch',
                'default' => true,
            ),
        )
    ));
    AYF::new_opt(array(
        'title' => '额外代码',
        'slug' => 'extra',
        'parent' => 'plugin',
        'desc' => '为网站前台添加图标，额外样式和统计代码',
        'fields' => array(
            array(
                'desc' => '统计代码',
                'type' => 'title_2',
            ),
            array(
                'desc' => 'TIPS：仅需填写统计ID，代码自动补全',
                'type' => 'message',
            ),
            array(
                'title' => 'Google Analytics（分析）',
                'desc' => '填写谷歌统计的衡量ID（非数据流ID，通常为<code>UA-</code>或<code>G-</code>开头）',
                'id' => 'site_google_analytics',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'title' => '百度统计',
                'desc' => '填写百度统计的百度跟踪ID（位于<code>hm.js?</code>之后的那段参数）',
                'id' => 'site_baidu_tongji',
                'type' => 'text',
                'default' => '',
            ),
            array(
                'desc' => '插入代码',
                'type' => 'title_2',
            ),
            array(
                'title' => '额外JS',
                'desc' => '配置站点额外JavaScript代码，需要包含<code>script</code>标签',
                'id' => 'site_extra_script',
                'type' => 'code_editor',
                'default' => '',
            ),
            array(
                'desc' => '插入样式',
                'type' => 'title_2',
            ),
            array(
                'title' => '额外CSS',
                'desc' => '配置站点额外CSS，无需<code>style</code>标签',
                'id' => 'site_extra_css',
                'type' => 'code_editor',
                'default' => '',
            ),
        )
    ));
    AYF::new_tex(array(
        array(
            'title' => 'SEO关键词',
            'desc' => '多个关键词之间使用<code>, </code>分隔，默认显示该分类名称',
            'id' => 'seo_cat_keywords',
            'type' => 'text',
            'default'  => '',
        ),
        array(
            'title' => 'SEO描述',
            'desc' => '默认显示该分类说明文本',
            'id' => 'seo_cat_desc',
            'type' => 'textarea',
            'default'  => '',
        ),
    ), 'category');
    AYF::new_box(array(
        array(
            'title' => 'SEO关键词',
            'desc' => '多个关键词之间使用<code>, </code>分隔，留空则默认设置为文章的标签',
            'id' => 'seo_keywords',
            'type' => 'text',
            'default'  => '',
        ),
        array(
            'title' => 'SEO描述',
            'desc' => '文章页面默认提取全文为前150个字符（描述文本推荐不超过150个字符）',
            'id' => 'seo_desc',
            'type' => 'textarea',
            'default'  => '',
        ),
    ), array(
        'title' => '自定义SEO',
        'id' => 'seo_box',
        'context' => 'normal',
        'priority' => 'low',
        'add_box_in' => array('post'),
    ));

    //运行插件
    $PLUGIN_SETUP->action(array(
        'Optimize' => array(
            'disable_wp_auto_update' => AYF::get_checked('disable_wp_auto_update', 'optimize'),
            'disable_admin_email_check' => AYF::get_checked('disable_admin_email_check', 'optimize'),
            'disable_xmlrpc' => AYF::get_checked('disable_xmlrpc', 'optimize'),
            'disable_pingback' => AYF::get_checked('disable_pingback', 'optimize'),
            'disable_feed' => AYF::get_checked('disable_feed', 'optimize'),
            'disable_head_oembed' => AYF::get_checked('disable_head_oembed', 'optimize'),
            'remove_autoembed' => AYF::get_checked('remove_autoembed', 'optimize'),
            'remove_privacy_page' => AYF::get_checked('remove_privacy_page', 'optimize'),
            'remove_wp_texturize' => AYF::get_checked('remove_wp_texturize', 'optimize'),
            'remove_revisions' => AYF::get_checked('remove_revisions', 'optimize'),
            'add_link_manager' => AYF::get_checked('add_link_manager', 'optimize'),
            'remove_head_redundant' => AYF::get_checked('remove_head_redundant', 'optimize'),
            'remove_sworg' => AYF::get_checked('remove_sworg', 'optimize'),
            'remove_wp_emojicons' => AYF::get_checked('remove_wp_emojicons', 'optimize'),
            'remove_head_title' => AYF::get_checked('remove_head_title', 'optimize'),
            'remove_open_sans' => AYF::get_checked('remove_open_sans', 'optimize'),
            'remove_editor_autosave' => AYF::get_checked('remove_editor_autosave', 'optimize'),
            'remove_gutenberg_styles' => AYF::get_checked('remove_gutenberg_styles', 'optimize'),
            'remove_css_js_ver' => AYF::get_checked('remove_css_js_ver', 'optimize'),
            'add_upload_webp' => AYF::get_checked('add_upload_webp', 'optimize'),
            'auto_upload_rename' => AYF::get_checked('auto_upload_rename', 'optimize'),
            'remove_wp_thumbnails' => AYF::get_checked('remove_wp_thumbnails', 'optimize'),
            'remove_image_threshold' => AYF::get_checked('remove_image_threshold', 'optimize'),
        ),
        'Request' => array(
            'query_post_type_var' => AYF::get_checked('query_post_type_var', 'request'),
            'query_ignore_sticky' => AYF::get_checked('query_ignore_sticky', 'request'),
            'query_ignore_category' => AYF::get_opt('query_ignore_category', 'request'),
            'query_ignore_post' => AYF::get_opt('query_ignore_post', 'request'),
            'query_author_current' => AYF::get_checked('query_author_current', 'request'),
            'search_page_type' => AYF::get_checked('search_page_type', 'request'),
            'serach_ignore_category' => AYF::get_opt('serach_ignore_category', 'request'),
            'serach_ignore_post' => AYF::get_opt('serach_ignore_post', 'request'),
            'serach_scope_enable' => AYF::get_checked('serach_scope_enable', 'request'),
            'serach_scope_user_check' => AYF::get_opt('serach_scope_user_check', 'request'),
            'serach_scope_limit' => AYF::get_opt('serach_scope_limit', 'request'),
            'serach_scope_length' => AYF::get_opt('serach_scope_length', 'request'),
            'search_redirect_term_search' => AYF::get_checked('search_redirect_term_search', 'request'),
            'search_redirect_intend' => AYF::get_checked('search_redirect_intend', 'request'),
            'search_redirect_one_post' => AYF::get_checked('search_redirect_one_post', 'request'),
            'search_clause_only_title' => AYF::get_checked('search_clause_only_title', 'request'),
            'search_clause_type_id' => AYF::get_checked('search_clause_type_id', 'request'),
            'search_clause_type_meta' => AYF::get_checked('search_clause_type_meta', 'request'),
            'search_clause_meta_key' => AYF::get_opt('search_clause_meta_key', 'request'),
        ),
        'Security' => array(
            'admin_backend_verify' => AYF::get_checked('admin_backend_verify', 'security'),
            'disable_admin_allow_password_reset' => AYF::get_checked('disable_admin_allow_password_reset', 'security'),
            'logged_scope_limit_enable' => AYF::get_checked('logged_scope_limit_enable', 'security'),
            'logged_scope_limit_times' => AYF::get_opt('logged_scope_limit_times', 'security'),
            'add_access_reject_switch' => AYF::get_checked('add_access_reject_switch', 'security'),
            'add_access_reject_list' => AYF::get_opt('add_access_reject_list', 'security'),
            'disable_rest_api' => AYF::get_checked('disable_rest_api', 'optimize'),
            'add_rest_api_logged_verify' => AYF::get_checked('add_rest_api_logged_verify', 'optimize'),
        ),
        'CDN_Speed' => array(
            'use_speed_weavatar' => AYF::get_checked('use_speed_weavatar', 'speed'),
            'use_speed_gravatar' => AYF::get_checked('use_speed_gravatar', 'speed'),
            'use_speed_google_fonts' => AYF::get_checked('use_speed_google_fonts', 'speed'),
            'avatar_cdn_type' => AYF::get_opt('avatar_cdn_type', 'speed'),
            'avatar_cdn_custom' => AYF::get_opt('avatar_cdn_custom', 'speed'),
            'avatar_ssl' => AYF::get_checked('avatar_ssl', 'speed'),
            'google_fonts_cdn_type' => AYF::get_opt('google_fonts_cdn_type', 'speed'),
            'google_fonts_cdn_custom' => AYF::get_opt('google_fonts_cdn_custom', 'speed'),
        ),
        'Mail_Sender' => array(
            'disable_new_user_email_admin' => AYF::get_checked('disable_new_user_email_admin', 'stmpmail'),
            'disable_new_user_email_user' => AYF::get_checked('disable_new_user_email_user', 'stmpmail'),
            'stmp_action' => AYF::get_checked('stmp_action', 'stmpmail'),
            'smtp_from' => AYF::get_opt('smtp_from', 'stmpmail'),
            'smtp_from_name' => AYF::get_opt('smtp_from_name', 'stmpmail'),
            'smtp_host' => AYF::get_opt('smtp_host', 'stmpmail'),
            'smtp_port' => AYF::get_opt('smtp_port', 'stmpmail'),
            'smtp_ssl' => AYF::get_checked('smtp_ssl', 'stmpmail'),
            'smtp_user' => AYF::get_opt('smtp_user', 'stmpmail'),
            'smtp_pass' => AYF::get_opt('smtp_pass', 'stmpmail'),
            'smtp_auth' => AYF::get_checked('smtp_auth', 'stmpmail'),
        ),
        'Head_SEO' => array(
            'site_favicon_url' => AYF::get_opt('site_favicon_url', 'seo'),
            'site_title' => AYF::get_opt('site_title', 'seo'),
            'site_subtitle' => AYF::get_opt('site_subtitle', 'seo'),
            'subtitle_true' => AYF::get_checked('subtitle_true', 'seo'),
            'title_sep' => AYF::get_opt('title_sep', 'seo'),
            'title_sep_space' => AYF::get_checked('title_sep_space', 'seo'),
            'seo_action' => AYF::get_checked('seo_action', 'seo'),
            'home_seo_keywords' => AYF::get_opt('home_seo_keywords', 'seo'),
            'home_seo_description' => AYF::get_opt('home_seo_description', 'seo'),
            'custom_robots_true' => AYF::get_checked('custom_robots_true', 'seo'),
            'custom_robots_txt' => AYF::get_opt('custom_robots_txt', 'seo'),
            'remove_sitemaps_provider' => AYF::get_checked('remove_sitemaps_provider', 'seo'),
        ),
        'Head_Extra' => array(
            'site_google_analytics' => AYF::get_opt('site_google_analytics', 'extra'),
            'site_baidu_tongji' => AYF::get_opt('site_baidu_tongji', 'extra'),
            'site_extra_script' => AYF::get_opt('site_extra_script', 'extra'),
            'site_extra_css' => AYF::get_opt('site_extra_css', 'extra'),
        ),
    ));
}
