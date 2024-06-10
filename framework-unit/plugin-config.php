<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 预置方法
 * ------------------------------------------------------------------------------
 */

$AYF = new AYF();

if (AYF::get_checked('all_plugin_off', 'plugin')) {
    //退出当前脚本
    return;
}

$PLUGIN_SETUP = new AYA_Theme_Setup();

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
 * 优化功能组件
 * ------------------------------------------------------------------------------
 */

//优化设置
$AYF_OPTIMIZE_FIELDS = array(
    array(
        'desc' => '站点图标',
        'type' => 'title_2',
    ),
    array(
        'desc' => '推荐使用 WP 设置生成站点图标，兼容性更好，如果你的主题不支持，可以使用此设置',
        'type' => 'message',
    ),
    array(
        'title' => '配置 favicon.ico ',
        'desc' => '上传站点 favicon 图标，可以为任意图片格式，不需要时留空',
        'id' => 'site_favicon_url',
        'type' => 'upload',
        'default' => '',
    ),
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
        'desc' => 'Jetpack插件和一些客户端依赖XML-RPC接口与站点通信，需要使用相关功能时，请使用<code>Disable XML-RPC</code>等插件替代此项',
        'type' => 'message',
    ),
    array(
        'title' => '禁用XML-RPC',
        'desc' => '此选项通过替换动作函数使XML-RPC无法工作，并不能彻底禁用此功能<br/>*如需彻底禁用XML-RPC，应当在服务器中通过WAF策略等方式阻止外部对<code>/xmlrpc.php</code>文件的访问',
        'id' => 'disable_xmlrpc',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '阻止 PingBack ',
        'desc' => '启用后阻止所有 PingBack 动作，关闭后仅阻止 PingBack 自己<br/>*PingBack、Enclosures和Trackbacks是XML-RPC的功能，禁用XML-RPC后此选项不会生效',
        'id' => 'disable_pingback',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用 REST-API ',
        'desc' => '禁用 REST-API 接口，启用后访问此接口会返回报错',
        'id' => 'disable_rest_api',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '验证 REST-API 请求来源',
        'desc' => '不禁用 REST-API 接口，但在 REST-API 内部添加来源验证',
        'id' => 'add_rest_api_referer_verify',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用 Feed ',
        'desc' => '禁用 Feed 功能 （RSS），启用后访问此接口会返回报错',
        'id' => 'disable_feed',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '禁用字体和语言包组件',
        'desc' => '禁用 WP 内置的翻译组件，并禁用语言包文件加载',
        'id' => 'disable_locale_rtl',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '禁用嵌入功能',
        'desc' => '禁用 WP 内置的嵌入功能（oEmbed），移除 <code>&lt;head&gt;</code> 标签中嵌入的功能组件',
        'id' => 'disable_head_oembed',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用自动链接解析',
        'desc' => '禁用 WP 内置的自动链接解析（Auto-Embed），阻止Youtube等外部网站输入时自动加载为 <code>&lt;iframe&gt;</code> ',
        'id' => 'disable_autoembed',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用自动字符转换',
        'desc' => '禁用 WP 内置的自动标点符号转换功能，阻止英文引号转义为中文引号和标签自动校正',
        'id' => 'disable_texturize',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用 s.w.org 标记',
        'desc' => '禁用 WP 内置的DNS预解析功能（dns-prefetch）',
        'id' => 'disable_sworg',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'desc' => '页面优化',
        'type' => 'title_2',
    ),
    array(
        'title' => '精简&lt;head&gt;结构',
        'desc' => '精简 <code>&lt;head&gt;</code> 中的日志链接、短链接、RSD接口等无用标签',
        'id' => 'remove_head_redundant',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '移除emoji&#x27;s',
        'desc' => '禁止 WP 加载 Emoji&#x27;s 组件和相关样式',
        'id' => 'remove_wp_emojicons',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '移除古腾堡样式',
        'desc' => '禁用 Gutenberg 引入的样式<br/>*会导致前台通过Gutenberg自定义的外观失效，注意检查',
        'id' => 'remove_gutenberg_styles',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用谷歌字体',
        'desc' => '禁止 WP 加载谷歌字体，并移除样式',
        'id' => 'remove_open_sans',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用静态文件版本号',
        'desc' => '移除前台静态文件加载时引入的版本号<br/>*可能会导致用户浏览器缓存的静态文件和服务器不一致，谨慎使用',
        'id' => 'remove_css_js_ver',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '易用性调整',
        'type' => 'title_2',
    ),
    array(
        'title' => '启用链接管理器',
        'desc' => '启用 WP 内置的链接管理器功能',
        'id' => 'add_link_manager',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '禁用修订版本记录',
        'desc' => '禁用 WP 编辑器的修订版本记录功能',
        'id' => 'remove_revisions',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用自动保存',
        'desc' => '禁用 WP 编辑器的自动保存功能',
        'id' => 'remove_editor_autosave',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => ' Sitemap 去除用户列表',
        'desc' => '禁止站点的<code>/wp-sitemap.xml</code>中生成Users列表',
        'id' => 'remove_sitemaps_users_provider',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '后台页面设置为中文',
        'desc' => '适配一些外贸站点和国内站点，将后台页面语言强制替换为<code>zh_CN</code><br/>*此选项不是翻译功能，只是为了去除浏览器的翻译页面提示',
        'id' => 'admin_page_locale_cn',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '允许WEBP图片上传',
        'desc' => '去除 WP 上传WEBP图片时产生的报错',
        'id' => 'add_upload_webp',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '附件自动重命名',
        'desc' => '由于 WP 实际上并不具有完整的文件管理器功能，此功能可避免重复文件名的文件过多造成大量SQL查询',
        'id' => 'auto_upload_rename',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用图片自动缩放',
        'desc' => '禁止 WP 自动生成略缩图和图片缩放<br/>*此选项通过将 WP 图片大小默认值设置为<code>0</code>来生效，可被 <a href="options-media.php">媒体设置</a> 覆盖',
        'id' => 'remove_image_thumbnails',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '禁用超大图片自动缩放',
        'desc' => '禁止 WP 对大于5000px*7000px的图像自动缩放',
        'id' => 'remove_image_threshold',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'desc' => '地区相关调整',
        'type' => 'title_2',
    ),
    array(
        'title' => '移除隐私政策页面',
        'desc' => '移除 WP 为欧洲通用数据保护条例（GDPR）生成的页面',
        'id' => 'remove_privacy_policy',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '中文安装包优化',
        'desc' => '移除 <code>cn.wordpress.org</code> 下载的 WP 安装包中的一些无用代码',
        'id' => 'zh_cn_option_cleanup',
        'type' => 'switch',
        'default' => false,
    ),
);

//查询设置
$AYF_REQUEST_FIELDS = array(
    array(
        'desc' => 'SQL查询优化',
        'type' => 'title_2',
    ),
    array(
        'desc' => '使用 <code>EXPLAIN SELECT</code> 语句替代 WP 主查询中的 <code>$wp_query->found_posts</code> 方法，大幅降低SQL开销',
        'type' => 'message',
    ),
    array(
        'desc' => '*该项会同时禁用<code>SQL_CALC_FOUND_ROWS</code>属性，会导致一些文章列表插件无法正常工作，请自行测试',
        'type' => 'message',
    ),
    array(
        'title' => '跳过SQL计数',
        'desc' => '主循环中跳过计算SQL匹配总数，提高查询速度，这对文章数量比较多的站点非常有用',
        'id' => 'query_no_found_rows',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '查询过滤器',
        'type' => 'title_2',
    ),
    array(
        'title' => '取消文章置顶',
        'desc' => '禁用文章置顶，按默认的文章排序输出',
        'id' => 'query_ignore_sticky',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '显示自定文章类型',
        'desc' => '将自定义的文章类型加入到主查询<br/>*此项仅对本插件创建的文章类型有效',
        'id' => 'query_post_type_var',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '首页中排除分类',
        'desc' => '填写首页排除分类的ID，通过<code>,</code>分隔',
        'id' => 'query_ignore_category',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => '首页中排除文章',
        'desc' => '填写首页排除文章的ID，通过<code>,</code>分隔',
        'id' => 'query_ignore_post',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => '搜索结果包含页面',
        'desc' => '搜索时同时搜索页面和文章添加到搜索结果',
        'id' => 'search_ignore_page_type',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '搜索结果匹配分类',
        'desc' => '当搜索关键词与分类/标签/自定义分类相同时，返回分类中的文章',
        'id' => 'search_request_term_exists',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '搜索中排除分类',
        'desc' => '填写搜索时排除分类的ID，通过<code>,</code>分隔',
        'id' => 'serach_ignore_category',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => '搜索中排除文章',
        'desc' => '填写搜索时排除文章的ID，通过<code>,</code>分隔',
        'id' => 'serach_ignore_post',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => '登录用户显示全部文章',
        'desc' => '对管理员或登录用户自己，显示全部状态的文章（包含草稿、待发布、已删除等）',
        'id' => 'query_author_current',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'desc' => '搜索结果设置',
        'type' => 'title_2',
    ),
    array(
        'title' => '搜索页重定向',
        'desc' => '强制<code>?s=</code>参数跳转到<code>search/</code>页面，使搜索页面静态化',
        'id' => 'search_redirect_search_page',
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
        'desc' => '搜索功能限制',
        'type' => 'title_2',
    ),
    array(
        'title' => '启用搜索限制',
        'desc' => '根据IP或用户角色限制搜索功能，防止滥用',
        'id' => 'serach_redirect_scope_enable',
        'type' => 'switch',
        'default' => false,
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
        'title' => '最大搜索关键词长度',
        'desc' => '计算单位为字节，限制最大长度255字节（一个汉字为3个字节，一个英文字母为1个字节）',
        'id' => 'serach_scope_length',
        'type' => 'text',
        'default' => '255',
    ),
    array(
        'title' => '每分钟搜索限制',
        'desc' => '每分钟最大搜索次数，达到上限之后屏蔽10分钟',
        'id' => 'serach_scope_limit',
        'type' => 'text',
        'default' => '10',
    ),
    array(
        'desc' => '自定义搜索（高级）',
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
);

//安全性
$AYF_SECURITY_FIELDS = array(
    array(
        'desc' => '后台入口权限设置',
        'type' => 'title_2',
    ),
    array(
        'title' => '验证权限级别',
        'desc' => '根据用户角色判断，禁止权限不足的用户访问后台并重定向回首页',
        'id' => 'admin_backend_verify',
        'type' => 'radio',
        'sub'  => array(
            'false' => '无限制',
            //'administrator' => '管理员',
            //'editor' => '编辑',
            'author' => '作者',
            'contributor' => '贡献者',
            'subscriber' => '订阅者',
        ),
        'default' => 'false',
    ),
    array(
        'desc' => '登录页防护',
        'type' => 'title_2',
    ),
    array(
        'title' => '简单登录页防护',
        'desc' => '为登录页面附加访问参数，隐藏登录表单防止脚本暴力破解',
        'id' => 'login_page_param_verify',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '登录页自动跳转',
        'desc' => '通过JavaScript方式，等待5秒后自动跳转到带有访问参数的地址<br/>*如果禁用自动跳转，请牢记设置的认证参数',
        'id' => 'login_page_auto_jump_times',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '登录页认证参数',
        'desc' => '接续上一项设置，登录页面的URL格式为 <code>/wp-login.php?auth=path_login</code>',
        'id' => 'login_page_param_args',
        'type' => 'text',
        'default' => 'path_login',
    ),
    array(
        'desc' => '用户验证',
        'type' => 'title_2',
    ),
    array(
        'desc' => '*注意！开启此选项后如果忘记密码将只能通过SSH等其他方式删除或禁用此插件来解除限制',
        'type' => 'message',
    ),
    array(
        'title' => '禁止管理员找回密码',
        'desc' => '禁止权限为管理员的用户发起密码找回',
        'id' => 'admin_disallow_password_reset',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '用户名验证',
        'desc' => '登录/注册时对用户信息进行验证，避免不安全的用户名',
        'id' => 'logged_sanitize_user_enable',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '登录时禁止用户名',
        'desc' => '接续上一项设置，指定禁止使用的用户名，通过<code>,</code>分隔<br/>*执行全词匹配，区分大小写',
        'id' => 'logged_prevent_user_name',
        'type' => 'text',
        'default' => 'admin,administrator,root',
    ),
    array(
        'title' => '注册时清理用户名',
        'desc' => '接续上一项设置，用户注册时去除不安全用户名和不安全的字符，通过<code>,</code>分隔<br/>*执行半匹配，不区分大小写',
        'id' => 'logged_register_user_name',
        'type' => 'text',
        'default' => 'admin,root',
    ),
    array(
        'desc' => ' robots.txt 规则设置',
        'type' => 'title_2',
    ),
    array(
        'title' => '启用 Robots 规则',
        'desc' => '自定义站点 <code>/robots.txt</code> 的内容，禁用则引用站点默认设置',
        'id' => 'robots_custom_switch',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '自定义 robots.txt ',
        'desc' => '自定义robots.txt的内容，语法参考： <a href="https://www.robotstxt.org/robotstxt.html" target="_blank">robotstxt.org</a>',
        'id' => 'robots_custom_txt',
        'type' => 'textarea',
        'default' => ayf_get_default_robots_text(),
    ),
    array(
        'desc' => '简单 WAF 防护',
        'type' => 'title_2',
    ),
    array(
        'title' => '启用 URL 参数验证',
        'desc' => '屏蔽一些和站点无关的参数访问，也可以用于防止百度统计刷数据',
        'id' => 'waf_reject_argument_switch',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '屏蔽参数关键字',
        'desc' => '接续上一项设置，填写需要屏蔽的 Url 参数关键字，通过<code>,</code>分隔',
        'id' => 'waf_reject_argument_list',
        'type' => 'text',
        'default' => 'wd,str',
    ),
    array(
        'title' => '启用 UA 验证',
        'desc' => '屏蔽一些无用的搜索引擎蜘蛛对网站的页面爬取和防御采集器，节约服务器CPU、内存、带宽的开销',
        'id' => 'waf_reject_useragent_switch',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '验证 UA 是否为空',
        'desc' => '禁止空 USER AGENT 访问，大部分采集程序都是空 UA ，部分SQL注入工具也是空 UA ',
        'id' => 'waf_reject_useragent_empty',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => '屏蔽 UA 列表',
        'desc' => '接续上一项设置，填写需要屏蔽的 UA 列表，通过<code>,</code>分隔，不区分大小写',
        'id' => 'waf_reject_useragent_list',
        'type' => 'textarea',
        'default' => 'BOT/0.1 (BOT for JCE),HttpClient,WinHttp,Python-urllib,Java,oBot,MJ12bot,Microsoft URL Control,YYSpider,UniversalFeedParser,FeedDemon,CrawlDaddy,Feedly,ApacheBench,Swiftbot,ZmEu,Indy Library,jaunty,AhrefsBot,jikeSpider,EasouSpider,jaunty,lightDeckReports Bot',
    ),
);

AYF::new_opt(
    array(
        'title' => '优化设置',
        'slug' => 'optimize',
        'parent' => 'plugin',
        'desc' => '禁用或调整一些WordPress内置功能，以优化网站性能',
        'fields' => $AYF_OPTIMIZE_FIELDS,
    )
);

AYF::new_opt(
    array(
        'title' => '查询设置',
        'slug' => 'request',
        'parent' => 'plugin',
        'desc' => '自定义调整WordPress首页和搜索的查询参数',
        'fields' => $AYF_REQUEST_FIELDS,
    )
);

AYF::new_opt(
    array(
        'title' => '安全性设置',
        'slug' => 'security',
        'parent' => 'plugin',
        'desc' => '禁用或调整一些WordPress内置功能，增加登录和后台访问验证',
        'fields' => $AYF_SECURITY_FIELDS,
    )
);

$PLUGIN_SETUP->action(
    array(
        'Optimize' => ayf_plugin_action($AYF_OPTIMIZE_FIELDS, 'optimize'),
        'Request' => ayf_plugin_action($AYF_REQUEST_FIELDS, 'request'),
        'Security' => ayf_plugin_action($AYF_SECURITY_FIELDS, 'security'),
    )
);

/*
 * ------------------------------------------------------------------------------
 * 拓展功能组件
 * ------------------------------------------------------------------------------
 */

//加速组件
if (AYF::get_checked('plugin_add_avatar_speed', 'plugin')) {
    //设置项
    $AYF_AVATAR_SPEED_FIELDS = array(
        array(
            'desc' => '自定义默认头像',
            'type' => 'title_2',
        ),
        array(
            'title' => '上传默认头像',
            'desc' => '此功能创建了一个新的头像标志，需要在WP的 <a href="options-discussion.php">讨论设置</a> 中，将默认头像设置切换为此选项<br/>Tips: 如果使用头像加速时可能会失效',
            'id' => 'site_default_avatar',
            'type' => 'upload',
            'default' => get_template_directory_uri() . '/framework-required/assects/img/default_avatar.png',
        ),
        array(
            'desc' => 'WeAvatar头像服务',
            'type' => 'title_2',
        ),
        array(
            'title' => '使用WeAvatar',
            'desc' => '使用 weavatar.com 头像服务替代Gravatar',
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
    );

    AYF::new_opt(array(
        'title' => '头像&字体加速',
        'slug' => 'avatar',
        'parent' => 'plugin',
        'desc' => '配置头像、字体通过第三方CDN加速',
        'fields' => $AYF_AVATAR_SPEED_FIELDS,
    ));

    $PLUGIN_SETUP->action(
        array(
            'CDN_Speed' => ayf_plugin_action($AYF_AVATAR_SPEED_FIELDS, 'avatar'),
        )
    );
}
//SEO组件
if (AYF::get_checked('plugin_add_seo_stk', 'plugin')) {
    //设置项
    $AYF_SEO_TDK_FIELDS = array(
        array(
            'desc' => '标题选择器',
            'type' => 'title_2',
        ),
        array(
            'desc' => '*标题选择器只影响head标签内部，即浏览器窗口显示的标题',
            'type' => 'message',
        ),
        array(
            'title' => 'SEO标题',
            'desc' => '留空则默认引用站点设置',
            'id' => 'site_title',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => 'SEO副标题',
            'desc' => '留空则默认引用站点描述设置',
            'id' => 'site_title_sub',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '显示站点副标题',
            'desc' => '是否显示副标题（站点描述）',
            'id' => 'site_title_sub_true',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '标题分隔符',
            'desc' => '设置标题文本的分隔符，默认添加空格补正',
            'id' => 'site_title_sep',
            'type' => 'radio',
            'sub'  => array(
                'nbsp' => '空格 <code> &nbsp; </code> ',
                'hyphen' => '连字符 <code> - </code> ',
                'y-line' => '分隔符 <code> | </code> ',
                'u-line' => '下划线 <code> _ </code> ',
            ),
            'default' => 'hyphen',
        ),
        array(
            'desc' => 'SEO设置',
            'type' => 'title_2',
        ),
        array(
            'title' => '启用SEO组件',
            'desc' => '同时启用文章和分类SEO组件，如果使用其他SEO插件则需要禁用此项',
            'id' => 'site_seo_action',
            'type' => 'switch',
            'default' => true,
        ),
        array(
            'title' => '首页SEO关键词',
            'desc' => '添加到首页关键词，仅影响首页',
            'id' => 'site_seo_keywords',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '首页SEO描述',
            'desc' => '添加到首页描述，仅影响首页',
            'id' => 'site_seo_description',
            'type' => 'textarea',
            'default' => '',
        ),
        array(
            'desc' => '文本关键词自动替换',
            'type' => 'title_2',
        ),
        array(
            'title' => '关键词列表',
            'desc' => '添加文本替换列表，一行一个，不需要时留空<br/>*格式举例： <code> 站点首页|&lt;a href="' . home_url() . '"&gt;站点首页&lt;/a&gt;</code> ',
            'id' => 'site_replace_text_wps',
            'type' => 'textarea',
            'default' => '',
        ),
        array(
            'desc' => '文本关键词自动内链',
            'type' => 'title_2',
        ),
        array(
            'desc' => '*这是一项SEO站点的常用功能，如果你的站点内容类型非常单一，可以使用此功能',
            'type' => 'message',
        ),
        array(
            'title' => '文章自动检索标签',
            'desc' => '检索全部正文，添加已存在的标签到文章（*该动作仅在文章保存时触发）<br/>*说明：内部使用 <code>strpos()</code> 方法，匹配不一定准确',
            'id' => 'site_seo_auto_add_tags',
            'type' => 'switch',
            'default' => false,
        ),
        array(
            'title' => '文内标签自动链接',
            'desc' => '自动为正文内匹配到的标签添加超链接<br/>*说明：规则为匹配到标签在文中出现 2 次自动添加链接，只添加 1 次',
            'id' => 'site_seo_auto_tag_link',
            'type' => 'switch',
            'default' => false,
        ),
    );

    AYF::new_opt(array(
        'title' => 'SEO-TDK',
        'slug' => 'seo',
        'parent' => 'plugin',
        'desc' => '简单SEO组件，用于自定义页面标题、内链功能、关键词和描述',
        'fields' => $AYF_SEO_TDK_FIELDS,
    ));
    AYF::new_tex(
        array(
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
        ),
        'category'
    );
    AYF::new_box(
        array(
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
        ),
        array(
            'title' => '自定义SEO',
            'id' => 'seo_box',
            'context' => 'normal',
            'priority' => 'low',
            'add_box_in' => array('post'),
        )
    );

    $PLUGIN_SETUP->action(
        array(
            'Head_SEO' => ayf_plugin_action($AYF_SEO_TDK_FIELDS, 'seo'),
        )
    );
}
//外部统计组件
if (AYF::get_checked('plugin_add_site_statistics', 'plugin')) {
    //设置项
    $AYF_ADD_EXTRA_FIELDS = array(
        array(
            'desc' => '统计代码',
            'type' => 'title_2',
        ),
        array(
            'title' => 'Google Analytics（分析）',
            'desc' => '填写谷歌统计的衡量ID（通常为<code>UA-</code>或<code>G-</code>开头，非数据流ID）<br/>*仅需填写统计ID，代码自动补全',
            'id' => 'site_google_analytics',
            'type' => 'text',
            'default' => '',
        ),
        array(
            'title' => '百度统计',
            'desc' => '填写百度统计的跟踪ID（位于<code>/hm.js?</code>之后的那段参数）',
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
            'desc' => '配置站点额外JavaScript代码，该项已包含<code>script</code>标签',
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
            'desc' => '配置站点额外CSS，该项已包含<code>style</code>标签',
            'id' => 'site_extra_css',
            'type' => 'code_editor',
            'default' => '',
        ),
    );

    AYF::new_opt(array(
        'title' => '额外代码',
        'slug' => 'extra',
        'parent' => 'plugin',
        'desc' => '为网站前台添加图标，额外样式和统计代码',
        'fields' => $AYF_ADD_EXTRA_FIELDS,
    ));

    $PLUGIN_SETUP->action(
        array(
            'Head_Extra' => ayf_plugin_action($AYF_ADD_EXTRA_FIELDS, 'extra'),
        )
    );
}
//STMP组件
if (AYF::get_checked('plugin_add_stmp_mail', 'plugin')) {
    //设置项
    $AYF_STMP_MAIL_FIELDS = array(
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
            'default' => false,
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
            'type' => 'radio',
            'sub'  => array(
                'yes' => '启用SSL',
                'no' => '禁用SSL',
            ),
            'default' => 'yes',
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
            'title' => '用户认证',
            'desc' => '',
            'id' => 'smtp_auth',
            'type' => 'radio',
            'sub'  => array(
                'yes' => '启用认证',
                'no' => '禁用认证',
            ),
            'default' => 'yes',
        ),
    );

    AYF::new_opt(array(
        'title' => 'SMTP发信',
        'slug' => 'stmpmail',
        'parent' => 'plugin',
        'desc' => '使用SMTP发送邮件',
        'fields' => $AYF_STMP_MAIL_FIELDS,
    ));

    $PLUGIN_SETUP->action(
        array(
            'Mail_Sender' => ayf_plugin_action($AYF_STMP_MAIL_FIELDS, 'stmpmail'),
        )
    );
}
//TinyMCE增强组件，移除分类URL中Category
if (AYF::get_checked('plugin_tinymce_add_modify', 'plugin')) {
    //无需设置
    $PLUGIN_SETUP->action(
        array(
            'Modify_TinyMCE' => array(
                //按钮重排
                'tinymce_filter_buttons' => true,
                //本地粘贴图片自动上传（用户在编辑器中粘贴的图片自动上传媒体库）
                'tinymce_upload_image' => false,
            ),
        )
    );
}
//分类URL重建组件，移除分类URL中Category
if (AYF::get_checked('plugin_no_category_url', 'plugin')) {
    //无需设置
    $PLUGIN_SETUP->action(
        array(
            'No_Category_URL' => true,
        )
    );
}

/*
 * ------------------------------------------------------------------------------
 * 开发用的小功能
 * ------------------------------------------------------------------------------
 */

//服务器状态小组件
if (AYF::get_checked('dashboard_server_monitor', 'plugin')) {
    $PLUGIN_SETUP->action(
        array(
            'Dashboard_Server_Status' => true,
        )
    );
}
//运行DEBUG查询
if (AYF::get_checked('debug_mode', 'plugin')) {
    $PLUGIN_SETUP->action(
        array(
            'Debug_Mode' => true,
        )
    );
}
//简码列表
if (AYF::get_checked('debug_shortcode_items', 'plugin')) {
    AYF::new_opt(array(
        'title' => '简码列表',
        'slug' => 'shortcode_items',
        'desc' => '列出当前主题支持的全部简码功能（ Shortcode 字段），并列出回调函数',
        'fields' => array(
            array(
                'function' => 'query_shortcode_items',
                'type' => 'callback',
            ),
        )
    ));
}
//路由列表
if (AYF::get_checked('debug_rules_items', 'plugin')) {
    AYF::new_opt(array(
        'title' => '路由列表',
        'slug' => 'rules_items',
        'desc' => '列出当前主题支持的全部固定链接（ Rewrite 规则）和查询方法',
        'fields' => array(
            array(
                'function' => 'query_rewrite_rules_items',
                'type' => 'callback',
            ),
        )
    ));
}
