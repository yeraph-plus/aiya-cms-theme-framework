<?php

if (!defined('ABSPATH')) {
    exit;
}

if (AYF::get_checked('all_plugin_off', 'plugin')) {
    //退出当前脚本
    return;
}

/*
 * ------------------------------------------------------------------------------
 * 优化功能组件
 * ------------------------------------------------------------------------------
 */

//优化设置
$AYF_OPTIMIZE_FIELDS = [
    [
        'desc' => __('站点图标', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'desc' => __('推荐使用 WP 设置生成站点图标，兼容性更好，如果你的主题不支持，可以使用此设置', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'title' => __('配置 favicon.ico ', 'aiya-framework'),
        'desc' => __('上传站点 favicon 图标，可以为任意图片格式，不需要时留空', 'aiya-framework'),
        'id' => 'site_favicon_url',
        'type' => 'upload',
        'default' => '',
    ],
    [
        'desc' => __('禁用功能', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('禁用自动更新（不建议）', 'aiya-framework'),
        'desc' => __('禁用 WP 自动更新，以解决WP无法连接到wordpress.org时产生报错', 'aiya-framework'),
        'id' => 'disable_wp_auto_update',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁用管理员邮箱确认', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的管理员用户定期提示邮箱确认功能', 'aiya-framework'),
        'id' => 'disable_admin_email_check',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('Jetpack插件和一些客户端依赖 XML-RPC 接口与站点通信，需要使用相关功能时，请使用“Disable XML-RPC”等插件替代此项', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'title' => __('禁用 XML-RPC ', 'aiya-framework'),
        'desc' => __('此选项通过替换动作函数使 XML-RPC 无法工作，并不能彻底禁用此功能[br/]*如需彻底禁用XML-RPC，应当在服务器中通过WAF策略等方式阻止外部对[code]/xmlrpc.php[/code]文件的访问', 'aiya-framework'),
        'id' => 'disable_xmlrpc',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('阻止 PingBack ', 'aiya-framework'),
        'desc' => __('启用后阻止所有 PingBack 动作，关闭后仅阻止 PingBack 自己[br/]*PingBack、Enclosures和Trackbacks是XML-RPC的功能，禁用XML-RPC后此选项不会生效', 'aiya-framework'),
        'id' => 'disable_pingback',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用 REST-API ', 'aiya-framework'),
        'desc' => __('禁用 REST-API 接口，启用后访问此接口会返回报错', 'aiya-framework'),
        'id' => 'disable_rest_api',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁用 Feed ', 'aiya-framework'),
        'desc' => __('禁用 Feed 功能 （RSS），启用后访问此接口会返回报错', 'aiya-framework'),
        'id' => 'disable_feed',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁用字体和语言包组件', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的翻译组件，并禁用语言包文件加载', 'aiya-framework'),
        'id' => 'disable_locale_rtl',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁用嵌入功能', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的嵌入功能（oEmbed），移除 [code]<head>[/code] 标签中嵌入的功能组件', 'aiya-framework'),
        'id' => 'disable_head_oembed',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用自动链接解析', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的自动链接解析（Auto-Embed），阻止Youtube等外部网站输入时自动加载为 [code]<iframe>[/code] ', 'aiya-framework'),
        'id' => 'disable_autoembed',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用自动字符转换', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的自动标点符号转换功能，阻止英文引号转义为中文引号和标签自动校正', 'aiya-framework'),
        'id' => 'disable_texturize',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用 s.w.org 标记', 'aiya-framework'),
        'desc' => __('禁用 WP 内置的DNS预解析功能（dns-prefetch）', 'aiya-framework'),
        'id' => 'disable_sworg',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('页面优化', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('精简页面 head 结构', 'aiya-framework'),
        'desc' => __('精简 [code]head[/code] 中的日志链接、短链接、RSD接口等无用标签', 'aiya-framework'),
        'id' => 'remove_head_redundant',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('移除 emoji 样式', 'aiya-framework'),
        'desc' => __('禁止 WP 加载并移除 emoji`s 组件和相关样式', 'aiya-framework'),
        'id' => 'remove_wp_emojicons',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('移除古腾堡样式', 'aiya-framework'),
        'desc' => __('禁用 Gutenberg 引入的样式[br/]*会导致前台通过Gutenberg自定义的外观失效，注意检查', 'aiya-framework'),
        'id' => 'remove_gutenberg_styles',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用谷歌字体', 'aiya-framework'),
        'desc' => __('禁止 WP 加载谷歌字体并移除样式', 'aiya-framework'),
        'id' => 'remove_open_sans',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用静态文件版本号', 'aiya-framework'),
        'desc' => __('移除前台静态文件加载时引入的版本号[br/]*可能会导致用户浏览器缓存的静态文件和服务器不一致，谨慎使用', 'aiya-framework'),
        'id' => 'remove_css_js_ver',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'desc' => __('易用性调整', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('启用链接管理器', 'aiya-framework'),
        'desc' => __('启用 WP 内置的链接管理器功能', 'aiya-framework'),
        'id' => 'add_link_manager',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁用修订版本记录', 'aiya-framework'),
        'desc' => __('禁用 WP 编辑器的修订版本记录功能', 'aiya-framework'),
        'id' => 'remove_revisions',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用自动保存', 'aiya-framework'),
        'desc' => __('禁用 WP 编辑器的自动保存功能', 'aiya-framework'),
        'id' => 'remove_editor_autosave',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('后台页面设置为中文', 'aiya-framework'),
        'desc' => __('适配一些外贸站点和国内站点，将后台页面语言强制替换为 [code]zh_CN[/code][br/]*此选项不是翻译功能，只是为了去除浏览器的翻译页面提示', 'aiya-framework'),
        'id' => 'admin_page_locale_cn',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('允许WEBP图片上传', 'aiya-framework'),
        'desc' => __('去除 WP 上传WEBP图片时产生的报错', 'aiya-framework'),
        'id' => 'add_upload_webp',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用图片自动缩放', 'aiya-framework'),
        'desc' => __('禁止 WP 自动生成略缩图和图片缩放[br/]*此选项通过将 WP 图片大小默认值设置为 [code]0[/code] 来生效，可被 [url=' . admin_url('options-media.php') . ']媒体设置[/url] 覆盖', 'aiya-framework'),
        'id' => 'remove_image_thumbnails',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用超大图片自动缩放', 'aiya-framework'),
        'desc' => __('禁止 WP 对大于 5000px*7000px 的图像自动缩放', 'aiya-framework'),
        'id' => 'remove_image_threshold',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('禁用 PDF 文件自动生成预览', 'aiya-framework'),
        'desc' => __('禁止 WP 自动生成 PDF 文件的缩略图，可以解决一些文件管理类插件的冲突', 'aiya-framework'),
        'id' => 'remove_pdf_preview',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('地区相关调整', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('移除隐私政策页面', 'aiya-framework'),
        'desc' => __('移除 WP 为欧洲通用数据保护条例（GDPR）生成的页面', 'aiya-framework'),
        'id' => 'remove_privacy_policy',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('中文安装包优化', 'aiya-framework'),
        'desc' => __('移除 [code]cn.wordpress.org[/code] 下载的 WP 安装包中的一些无用代码', 'aiya-framework'),
        'id' => 'zh_cn_option_cleanup',
        'type' => 'switch',
        'default' => false,
    ],
];

//查询设置
$AYF_REQUEST_FIELDS = [
    [
        'desc' => __('SQL查询优化', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'desc' => __('主循环中跳过计算 SQL 匹配总数以提高查询速度，这对文章数量比较多的站点非常有用', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'title' => __('跳过 SQL 计数', 'aiya-framework'),
        'desc' => __('替代 WP 主查询中的 [code]$wp_query->found_posts[/code] 方法为 [code]EXPLAIN SELECT[/code] 语句，大幅降低SQL开销[br/]*该项会同时禁用 [code]SQL_CALC_FOUND_ROWS[/code] 属性，会导致一些文章列表插件无法正常工作，请自行测试', 'aiya-framework'),
        'id' => 'query_no_found_rows',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('预加载元数据', 'aiya-framework'),
        'desc' => __('使 WP 全局预加载文章的元数据（post meta）和分类数据（post term）以提高查询速度', 'aiya-framework'),
        'id' => 'query_update_post_cache',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('查询过滤器', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('取消文章置顶', 'aiya-framework'),
        'desc' => __('禁用文章置顶，按默认的文章排序输出', 'aiya-framework'),
        'id' => 'query_ignore_sticky',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('显示自定义文章类型', 'aiya-framework'),
        'desc' => __('将自定义的文章类型加入到主查询[br/]*此项仅对本插件创建的文章类型有效', 'aiya-framework'),
        'id' => 'query_post_type_var',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('首页中排除分类', 'aiya-framework'),
        'desc' => __('填写首页排除分类的ID，通过 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'query_ignore_category',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('首页中排除文章', 'aiya-framework'),
        'desc' => __('填写首页排除文章的ID，通过 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'query_ignore_post',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('搜索结果包含页面', 'aiya-framework'),
        'desc' => __('搜索时同时搜索页面和文章添加到搜索结果', 'aiya-framework'),
        'id' => 'search_ignore_page_type',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('搜索结果匹配分类', 'aiya-framework'),
        'desc' => __('当搜索关键词与分类/标签/自定义分类相同时，返回分类中的文章', 'aiya-framework'),
        'id' => 'search_request_term_exists',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('搜索中排除分类', 'aiya-framework'),
        'desc' => __('填写搜索时排除分类的ID，通过 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'serach_ignore_category',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('搜索中排除文章', 'aiya-framework'),
        'desc' => __('填写搜索时排除文章的ID，通过 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'serach_ignore_post',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('登录用户显示全部文章', 'aiya-framework'),
        'desc' => __('对管理员或登录用户自己，显示全部状态的文章（包含草稿、待发布、已删除等）', 'aiya-framework'),
        'id' => 'query_author_current',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('搜索结果设置', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('搜索页重定向', 'aiya-framework'),
        'desc' => __('强制 [code]?s=[/code] 参数跳转到 [code]search/[/code] 页面，使搜索页面静态化', 'aiya-framework'),
        'id' => 'search_redirect_search_page',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('搜索结果是否为空', 'aiya-framework'),
        'desc' => __('当搜索关键词输入为空时，重定向到首页', 'aiya-framework'),
        'id' => 'search_redirect_intend',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('搜索结果跳过', 'aiya-framework'),
        'desc' => __('当搜索结果有且只有一篇文章时，直接转到文章页', 'aiya-framework'),
        'id' => 'search_redirect_one_post',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('搜索功能限制', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('启用搜索限制', 'aiya-framework'),
        'desc' => __('根据IP或用户角色限制搜索功能，防止滥用', 'aiya-framework'),
        'id' => 'serach_redirect_scope_enable',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('搜索用户验证', 'aiya-framework'),
        'desc' => __('仅允许已登录的用户使用搜索功能，或关闭搜索', 'aiya-framework'),
        'id' => 'serach_scope_user_check',
        'type' => 'radio',
        'sub' => [
            'all' => __('无限制', 'aiya-framework'),
            'logged' => __('仅限登录用户', 'aiya-framework'),
            'disabled' => __('关闭站点搜索', 'aiya-framework')
        ],
        'default' => 'all',
    ],
    [
        'title' => __('最大搜索关键词长度', 'aiya-framework'),
        'desc' => __('计算单位为字节，限制最大长度 255 字节（一个汉字为 3 字节，一个英文字母为 1 字节）', 'aiya-framework'),
        'id' => 'serach_scope_length',
        'type' => 'text',
        'default' => '255',
    ],
    [
        'title' => __('每分钟搜索限制', 'aiya-framework'),
        'desc' => __('每分钟最大搜索次数，达到上限之后屏蔽10分钟', 'aiya-framework'),
        'id' => 'serach_scope_limit',
        'type' => 'text',
        'default' => '10',
    ],
    [
        'desc' => __('自定义搜索（高级）', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('只搜索文章标题', 'aiya-framework'),
        'desc' => __('不搜索文章内容和摘要，提高搜索响应速度', 'aiya-framework'),
        'id' => 'search_clause_only_title',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('允许搜索文章ID', 'aiya-framework'),
        'desc' => __('允许搜索ID查找文章，多个ID时支持 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'search_clause_type_id',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('允许搜索自定义字段', 'aiya-framework'),
        'desc' => __('允许搜索自定义字段（postmeta），会大幅影响查询速度，请谨慎使用', 'aiya-framework'),
        'id' => 'search_clause_type_meta',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('指定搜索自定义字段', 'aiya-framework'),
        'desc' => __('接续上一项设置，设置接受搜索的自定义字段，请输入支持的 meta_key', 'aiya-framework'),
        'id' => 'search_clause_meta_key',
        'type' => 'text',
        'default' => '',
    ],
];

//安全性
$AYF_SECURITY_FIELDS = [
    [
        'desc' => __('阻止外部查询用户信息', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('去除 Sitemap 用户列表', 'aiya-framework'),
        'desc' => __('禁止站点的 [code]/wp-sitemap.xml[/code] 中生成Users列表', 'aiya-framework'),
        'id' => 'remove_sitemaps_users_provider',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('去除 REST-API 用户端点', 'aiya-framework'),
        'desc' => __('禁止站点的 [code]/wp-json/wp/v2/users[/code] 接口端点', 'aiya-framework'),
        'id' => 'remove_restapi_users_endpoint',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('去除 REST-API 文章端点', 'aiya-framework'),
        'desc' => __('禁止站点的 [code]/wp-json/wp/v2/posts[/code] 接口端点', 'aiya-framework'),
        'id' => 'remove_restapi_posts_endpoint',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => __('后台入口权限调整', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('调整访问后台用户级别', 'aiya-framework'),
        'desc' => __('根据用户角色判断，禁止权限不足的用户访问后台并重定向回首页', 'aiya-framework'),
        'id' => 'admin_backend_verify',
        'type' => 'radio',
        'sub' => [
            'false' => __('无限制', 'aiya-framework'),
            'editor' => __('编辑', 'aiya-framework'),
            'author' => __('作者', 'aiya-framework'),
            'contributor' => __('贡献者', 'aiya-framework'),
            'subscriber' => __('订阅者', 'aiya-framework'),
        ],
        'default' => 'false',
    ],
    [
        'desc' => __('登录页防护', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('简单登录页防护', 'aiya-framework'),
        'desc' => __('为登录页面附加访问参数，隐藏登录表单防止脚本暴力破解', 'aiya-framework'),
        'id' => 'login_page_param_verify',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('登录页自动跳转', 'aiya-framework'),
        'desc' => __('通过 JavaScript 方式，等待5秒后自动跳转到带有访问参数的地址[br/]*如果禁用自动跳转，请牢记设置的认证参数', 'aiya-framework'),
        'id' => 'login_page_auto_jump_times',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => __('登录页认证参数', 'aiya-framework'),
        'desc' => __('接续上一项设置，登录页面的URL格式为 [code]/wp-login.php?auth=path_login[/code]', 'aiya-framework'),
        'id' => 'login_page_param_args',
        'type' => 'text',
        'default' => 'path_login',
    ],
    [
        'desc' => __('登录验证逻辑调整', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('强制使用邮箱登录', 'aiya-framework'),
        'desc' => __('修改登录方式仅允许邮箱登录', 'aiya-framework'),
        'id' => 'admin_allow_email_login',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('禁止管理员找回密码', 'aiya-framework'),
        'desc' => __('禁止权限为管理员的用户发起密码找回[br/]*注意！开启此选项后如果忘记密码将只能通过 SSH 等其他方式删除或禁用此插件来解除限制', 'aiya-framework'),
        'id' => 'admin_disallow_password_reset',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('用户名验证', 'aiya-framework'),
        'desc' => __('登录/注册时对用户信息进行验证，避免不安全的用户名', 'aiya-framework'),
        'id' => 'logged_sanitize_user_enable',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('登录时禁止用户名', 'aiya-framework'),
        'desc' => __('接续上一项设置，指定禁止使用的用户名，通过 [code],[/code] 分隔[br/]*执行全词匹配，区分大小写', 'aiya-framework'),
        'id' => 'logged_prevent_user_name',
        'type' => 'text',
        'default' => 'admin,administrator,root',
    ],
    [
        'title' => __('注册时清理用户名', 'aiya-framework'),
        'desc' => __('接续上一项设置，用户注册时去除不安全用户名和不安全的字符，通过 [code],[/code] 分隔[br/]*执行半匹配，不区分大小写', 'aiya-framework'),
        'id' => 'logged_register_user_name',
        'type' => 'text',
        'default' => 'admin,root',
    ],
];

//自动功能
$AYF_AUTOMATIC_FIELDS = [
    [
        'desc' => __('文章默认模板', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('进入编辑器时插入默认文本', 'aiya-framework'),
        'desc' => __('将一些标准的格式化的文章内容直接插入到编辑器中', 'aiya-framework'),
        'id' => 'the_post_auto_insert_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('添加编辑器默认内容', 'aiya-framework'),
        'desc' => __('添加默认内容之后，会在每次创建文章时提前插入编辑器', 'aiya-framework'),
        'id' => 'the_post_auto_insert_content',
        'type' => 'textarea',
        'default' => '',
    ],
    [
        'desc' => __('文章内容预处理', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'desc' => __('此模块会在文章编辑器页面创建“数据更新”组件，如果没有此组件，请在顶部显示选项中勾选此组件。', 'aiya-framework'),
        'type' => 'content',
    ],
    [
        'desc' => __('警告：相关预处理功能在文章保存/更新时触发，会直接操作文章数据，投入使用前请先备份站点数据并仔细测试。', 'aiya-framework'),
        'type' => 'warning',
    ],
    [
        'desc' => __('*此功能使用 [code]jxlwqq/chinese-typesetting[/code] 项目：[url=https://github.com/jxlwqq/chinese-typesetting]查看文档[/url]', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'title' => __('启用的中文排版纠正方法', 'aiya-framework'),
        'desc' => __('选择需要启用的格式过滤器，详细说明请查阅相关项目文档', 'aiya-framework'),
        'id' => 'the_post_auto_chs_compose_type',
        'type' => 'checkbox',
        'sub' => [
            'insertSpace' => __('中英文空格补正', 'aiya-framework'),
            'removeSpace' => __('清除全角标点空格', 'aiya-framework'),
            'full2Half' => __('全角转半角', 'aiya-framework'),
            'fixPunctuation' => __('修复错误的标点符号', 'aiya-framework'),
            'properNoun' => __('专有名词大小写', 'aiya-framework'),
            'removeClass' => __('清除标签 Class 属性', 'aiya-framework'),
            'removeId' => __('清除标签 ID 属性', 'aiya-framework'),
            'removeStyle' => __('清除标签 Style 属性', 'aiya-framework'),
            'removeEmptyParagraph' => __('清除空的段落标签', 'aiya-framework'),
            'removeEmptyTag' => __('清除所有空的标签', 'aiya-framework'),
            'removeIndent' => __('清除段首缩进', 'aiya-framework'),
        ],
        'default' => ['insertSpace', 'removeSpace', 'full2Half'],
    ],
    [
        'desc' => __('自动别名', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'desc' => __('*注意：如需文章别名在 URL 结构中生效，请先设置 [url=options-permalink.php]固定链接[/url]', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'desc' => __('*此功能使用 [code]overtrue/pinyin[/code] 项目', 'aiya-framework'),
        'type' => 'message',
    ],
    [
        'title' => __('使用拼音生成分类、标签别名', 'aiya-framework'),
        'desc' => __('在分类法创建/更新时触发，如果未设置别名（留空时）自动生成'),
        'id' => 'the_term_auto_pinyin_slug_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('使用拼音生成文章别名', 'aiya-framework'),
        'desc' => __('在文章保存/更新时触发，如果未设置别名（为空时），则自动生成'),
        'id' => 'the_post_auto_pinyin_slug_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => __('根据文章 ID 生成别名', 'aiya-framework'),
        'desc' => __('会覆盖上一项设置，强制使用 ID 生成文章的别名，不会检查是否已设置别名
            [br/]仿写格式1 [code]av00000001[/code] 使用文章 ID 数据进行字符补全
            [br/]仿写格式2 [code]bvxxxxxxxx[/code] 使用文章 ID 数据进行Base58编码', 'aiya-framework'),
        'id' => 'the_post_auto_slug_type',
        'type' => 'radio',
        'sub' => [
            'off' => __('关闭', 'aiya-framework'),
            'id_av' => __('低仿AV号', 'aiya-framework'),
            'id_bv' => __('低仿BV号', 'aiya-framework'),
        ],
        'default' => 'off',
    ],
    [
        'title' => __('文章别名附加前缀', 'aiya-framework'),
        'desc' => __('在使用 ID 生成别名时设置的别名前缀，请勿包含 [code]<>{}|^[][?#:[/code] 等控制字符以及空格', 'aiya-framework'),
        'id' => 'site_post_auto_slug_prefix',
        'type' => 'text',
        'default' => 'PV',
    ],
];

AYF::new_opt([
    'title' => __('优化设置', 'aiya-framework'),
    'desc' => __('禁用或调整一些 WordPress 内置功能，以优化网站性能', 'aiya-framework'),
    'slug' => 'optimize',
    'parent' => 'plugin',
    'fields' => $AYF_OPTIMIZE_FIELDS,
]);

AYP::action('Optimize', $AYF_OPTIMIZE_FIELDS, 'optimize');

AYF::new_opt([
    'title' => __('查询和搜索', 'aiya-framework'),
    'desc' => __('自定义和调整 WordPress 首页和搜索的查询参数', 'aiya-framework'),
    'slug' => 'request',
    'parent' => 'plugin',
    'fields' => $AYF_REQUEST_FIELDS,
]);

AYP::action('Request', $AYF_REQUEST_FIELDS, 'request');

AYF::new_opt([
    'title' => __('登录保护', 'aiya-framework'),
    'desc' => __('调整 WordPress 登录页面，增加登录和后台访问验证', 'aiya-framework'),
    'slug' => 'security',
    'parent' => 'plugin',
    'fields' => $AYF_SECURITY_FIELDS,
]);

AYP::action('Security', $AYF_SECURITY_FIELDS, 'security');

AYF::new_opt([
    'title' => __('自动功能', 'aiya-framework'),
    'desc' => __('调整 WordPress 文章内容过滤器组件设置', 'aiya-framework'),
    'slug' => 'automatic',
    'parent' => 'plugin',
    'fields' => $AYF_AUTOMATIC_FIELDS,
]);

AYF::new_box([
    'title' => __('数据更新', 'aiya-framework'),
    'desc' => __('本组件全部功能仅在勾选时生效', 'aiya-framework'),
    'id' => 'post_automatic',
    'context' => 'normal',
    'priority' => 'low',
    'add_box_in' => ['post'],
    'fields' => [
        [
            'title' => __('重置发布日期', 'aiya-framework'),
            'label' => __('刷新文章发布日期到当前时间', 'aiya-framework'),
            'id' => 'reset_post_datetime',
            'type' => 'action_checkbox',
            'default' => false,
        ],
        [
            'title' => __('自动检索标签', 'aiya-framework'),
            'label' => __('添加已存在的标签到文章', 'aiya-framework'),
            'desc' => __('*说明：内部使用 [code]strpos()[/code] 方法检索全部正文，匹配不一定准确', 'aiya-framework'),
            'id' => 'auto_strpos_tags',
            'type' => 'action_checkbox',
            'default' => false,
        ],
        [
            'title' => __('格式清理', 'aiya-framework'),
            'label' => __('清理多余 HTML 标签', 'aiya-framework'),
            'desc' => __('*说明：清理复制粘贴时带来的 [code]div[/code] 、 [code]span[/code] 等标签和重叠的标签、去除全角空格等', 'aiya-framework'),
            'id' => 'auto_insert_html_filter',
            'type' => 'action_checkbox',
            'default' => false,
        ],
        [
            'title' => __('中文排版纠正', 'aiya-framework'),
            'label' => __('执行复杂排版纠正', 'aiya-framework'),
            'desc' => __('*说明：此处理器行为在[url=/wp-admin/admin.php?page=aya-options-automatic]设置页面[/url]中控制，此项与格式清理选项互斥', 'aiya-framework'),
            'id' => 'auto_chs_compose_filter',
            'type' => 'action_checkbox',
            'default' => false,
        ],
    ],
]);

AYP::action('Automatic', $AYF_AUTOMATIC_FIELDS, 'automatic');

/*
 * ------------------------------------------------------------------------------
 * 拓展功能组件
 * ------------------------------------------------------------------------------
 */

//加速组件
if (AYF::get_checked('plugin_add_avatar_speed', 'plugin')) {
    //设置项
    $AYF_AVATAR_SPEED_FIELDS = [
        [
            'desc' => __('自定义默认头像', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('上传默认头像', 'aiya-framework'),
            'desc' => __('此功能创建了一个新的头像标志，需要在 WP 的 [url=' . admin_url('options-discussion.php') . ']讨论设置[/url] 中，将默认头像设置切换为此选项[br/]Tips: 如果使用头像加速时可能会失效', 'aiya-framework'),
            'id' => 'site_default_avatar',
            'type' => 'upload',
            'default' => AYF::get_base_url() . '/assects/img/default_avatar.png',
        ],
        [
            'desc' => __('WeAvatar 头像服务', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('使用 WeAvatar 头像', 'aiya-framework'),
            'desc' => __('使用 weavatar.com 提供的头像服务替代 Gravatar', 'aiya-framework'),
            'id' => 'use_speed_weavatar',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => __('Gravatar 头像加速', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('Gravatar 加速', 'aiya-framework'),
            'desc' => __('替换 gravatar.com 头像服务的地址到镜像源，和上一项互斥', 'aiya-framework'),
            'id' => 'use_speed_gravatar',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('Gravatar 镜像源', 'aiya-framework'),
            'desc' => __('使用 Gravatar 头像服务的镜像源', 'aiya-framework'),
            'id' => 'avatar_cdn_type',
            'type' => 'radio',
            'sub' => [
                'cn' => __('Gravatar （官方CN源）', 'aiya-framework'),
                'qiniu' => __('七牛 CDN', 'aiya-framework'),
                'v2ex' => __('V2EX CDN', 'aiya-framework'),
                'geekzu' => __('极客族 CDN', 'aiya-framework'),
                'loli' => __('LOLI 图床', 'aiya-framework'),
            ],
            'default' => 'cn',
        ],
        [
            'title' => __('自定义镜像', 'aiya-framework'),
            'desc' => __('使用自定义 Gravatar 头像服务的镜像源', 'aiya-framework'),
            'id' => 'avatar_cdn_custom',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('强制 HTTPS', 'aiya-framework'),
            'desc' => __('强制头像服务通过 HTTPS 加载', 'aiya-framework'),
            'id' => 'avatar_ssl',

            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => __('字体加速', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('Google Fonts 字体加速', 'aiya-framework'),
            'desc' => __('使用 Google 字体镜像源', 'aiya-framework'),
            'id' => 'use_speed_google_fonts',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('Google Fonts 字体镜像源', 'aiya-framework'),
            'desc' => __('使用 Google 字体的镜像源，加速主题加载', 'aiya-framework'),
            'id' => 'google_fonts_cdn_type',
            'type' => 'radio',
            'sub' => [
                'geekzu' => __('极客族 CDN', 'aiya-framework'),
                'loli' => __('LOLI 图床', 'aiya-framework'),
                'ustc' => __('中科大 CDN', 'aiya-framework'),
                'custom' => __('自定义', 'aiya-framework'),
            ],
            'default' => 'ustc',
        ],
        [
            'title' => __('自定义镜像地址', 'aiya-framework'),
            'desc' => __('使用自定义 Google 字体的镜像加速服务地址', 'aiya-framework'),
            'id' => 'google_fonts_cdn_custom',
            'type' => 'group',
            'sub_type' => [
                [
                    'title' => 'googleapis_fonts',
                    'id' => 'fonts_cdn',
                    'type' => 'text',
                    'default' => '//fonts.googleapis.com',
                ],
                [
                    'title' => 'googleapis_ajax',
                    'id' => 'fonts_ajax',
                    'type' => 'text',
                    'default' => '//ajax.googleapis.com',
                ],
                [
                    'title' => 'googleusercontent_themes',
                    'id' => 'fonts_themes',
                    'type' => 'text',
                    'default' => '//themes.googleusercontent.com',
                ],
                [
                    'title' => 'gstatic_fonts',
                    'id' => 'fonts_gstatic',
                    'type' => 'text',
                    'default' => '//fonts.gstatic.com',
                ],
            ],
        ],
    ];

    AYF::new_opt([
        'title' => __('头像&字体加速', 'aiya-framework'),
        'desc' => __('配置头像、字体通过第三方CDN加速', 'aiya-framework'),
        'slug' => 'avatar',
        'parent' => 'plugin',
        'fields' => $AYF_AVATAR_SPEED_FIELDS,
    ]);

    AYP::action('CDN_Speed', $AYF_AVATAR_SPEED_FIELDS, 'avatar');
}
//SEO组件
if (AYF::get_checked('plugin_add_seo_stk', 'plugin')) {
    //设置项
    $AYF_SEO_TDK_FIELDS = [
        [
            'desc' => __('标题选择器', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'desc' => __('*标题选择器只影响head标签内部，即浏览器窗口显示的标题', 'aiya-framework'),
            'type' => 'message',
        ],
        [
            'title' => __('SEO标题', 'aiya-framework'),
            'desc' => __('留空则默认引用站点设置', 'aiya-framework'),
            'id' => 'site_title',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('SEO副标题', 'aiya-framework'),
            'desc' => __('留空则默认引用站点描述设置', 'aiya-framework'),
            'id' => 'site_title_sub',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('显示站点副标题', 'aiya-framework'),
            'desc' => __('是否显示副标题（站点描述）', 'aiya-framework'),
            'id' => 'site_title_sub_true',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => __('标题分隔符', 'aiya-framework'),
            'desc' => __('设置标题文本的分隔符，默认添加空格补正', 'aiya-framework'),
            'id' => 'site_title_sep',
            'type' => 'radio',
            'sub' => [
                'nbsp' => __('空格" &nbsp; "', 'aiya-framework'),
                'hyphen' => __('连字符" - "', 'aiya-framework'),
                'y-line' => __('分隔符" | "', 'aiya-framework'),
                'u-line' => __('下划线"  _ "', 'aiya-framework'),
            ],
            'default' => 'hyphen',
        ],
        [
            'desc' => __('SEO设置', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('启用SEO组件', 'aiya-framework'),
            'desc' => __('同时启用文章和分类SEO组件，如果使用其他SEO插件则需要禁用此项', 'aiya-framework'),
            'id' => 'site_seo_action',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => __('首页SEO关键词', 'aiya-framework'),
            'desc' => __('添加到首页关键词，仅影响首页', 'aiya-framework'),
            'id' => 'site_seo_keywords',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('首页SEO描述', 'aiya-framework'),
            'desc' => __('添加到首页描述，仅影响首页', 'aiya-framework'),
            'id' => 'site_seo_description',
            'type' => 'textarea',
            'default' => '',
        ],
        [
            'desc' => __('robots.txt 规则设置', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('启用 Robots 规则', 'aiya-framework'),
            'desc' => __('自定义站点 [code]/robots.txt[/code] 的内容，禁用则引用站点默认设置', 'aiya-framework'),
            'id' => 'site_seo_robots_switch',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => __('自定义 robots.txt', 'aiya-framework'),
            'desc' => __('自定义robots.txt的内容，语法参考：[url=https://www.robotstxt.org/robotstxt.html]robotstxt.org[/url]', 'aiya-framework'),
            'id' => 'site_seo_robots_txt',
            'type' => 'textarea',
            'default' => ayf_get_default_robots_text(),
        ],
        [
            'desc' => __('文本关键词自动替换', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('启用自动替换', 'aiya-framework'),
            'desc' => __('用于在文内替换关键词或添加链接，请见下方格式说明[br/]*说明：内部使用 [code]str_replace()[/code] 方法，匹配不一定准确', 'aiya-framework'),
            'id' => 'site_seo_auto_replace',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('关键词列表', 'aiya-framework'),
            'desc' => __('添加文本替换列表，一行一个，关键词使用 [code]|[/code] 分隔[br/]*格式举例： [code] 站点首页|<a href="' . home_url() . '">站点首页</a>[/code] ', 'aiya-framework'),
            'id' => 'site_replace_text_wps',
            'type' => 'textarea',
            'default' => '',
        ],
        [
            'desc' => __('文本关键词自动内链', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'desc' => __('*这是一项SEO站点的常用功能，如果你的站点内容类型非常单一，可以使用此功能', 'aiya-framework'),
            'type' => 'message',
        ],
        [
            'title' => __('文内标签自动链接', 'aiya-framework'),
            'desc' => __('自动为正文内匹配到的标签添加超链接[br/]*说明：规则为匹配到标签在文中出现 2 次自动添加链接，只添加 1 次', 'aiya-framework'),
            'id' => 'site_seo_auto_tag_link',
            'type' => 'switch',
            'default' => false,
        ],
    ];

    AYF::new_opt([
        'title' => 'SEO-TDK',
        'slug' => 'seo',
        'parent' => 'plugin',
        'desc' => __('简单SEO组件，用于自定义页面标题、关键词和描述，以及简单内链功能', 'aiya-framework'),
        'fields' => $AYF_SEO_TDK_FIELDS,
    ]);

    AYF::new_tex([
        'add_meta_in' => 'category',
        'fields' => [
            [
                'title' => __('SEO关键词', 'aiya-framework'),
                'desc' => __('多个关键词之间使用 [code], [/code] 分隔，默认显示该分类名称', 'aiya-framework'),
                'id' => 'seo_cat_keywords',
                'type' => 'text',
                'default' => '',
            ],
            [
                'title' => __('SEO描述', 'aiya-framework'),
                'desc' => __('默认显示该分类说明文本', 'aiya-framework'),
                'id' => 'seo_cat_desc',
                'type' => 'textarea',
                'default' => '',
            ],
        ],
    ]);

    AYF::new_box([
        'title' => __('自定义SEO', 'aiya-framework'),
        'id' => 'post_seo',
        'context' => 'normal',
        'priority' => 'low',
        'add_box_in' => ['post'],
        'desc' => __('为文章添加自定义的SEO描述', 'aiya-framework'),
        'fields' => [
            [
                'title' => __('SEO关键词', 'aiya-framework'),
                'desc' => __('多个关键词之间使用 [code], [/code] 分隔，留空则默认设置为文章的标签', 'aiya-framework'),
                'id' => 'seo_keywords',
                'type' => 'text',
                'default' => '',
            ],
            [
                'title' => __('SEO描述', 'aiya-framework'),
                'desc' => __('文章页面默认提取全文为前150个字符（描述文本推荐不超过150个字符）', 'aiya-framework'),
                'id' => 'seo_desc',
                'type' => 'textarea',
                'default' => '',
            ],
        ],
    ]);

    AYP::action('Head_SEO', $AYF_SEO_TDK_FIELDS, 'seo');
}

//WAF组件
if (AYF::get_checked('plugin_add_ua_firewall', 'plugin')) {
    //设置项
    $AYF_FIREWALL_FIELDS = [
        [
            'desc' => __('简单 WAF 防护', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('启用 URL 参数验证', 'aiya-framework'),
            'desc' => __('屏蔽一些和站点无关的参数访问，也可以用于防止百度统计刷数据', 'aiya-framework'),
            'id' => 'waf_reject_argument_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('屏蔽参数关键字', 'aiya-framework'),
            'desc' => __('接续上一项设置，填写需要屏蔽的 Url 参数关键字，通过 [code],[/code] 分隔', 'aiya-framework'),
            'id' => 'waf_reject_argument_list',
            'type' => 'text',
            'default' => 'wd,str',
        ],
        [
            'title' => __('启用 UA 验证', 'aiya-framework'),
            'desc' => __('屏蔽一些无用的搜索引擎蜘蛛对网站的页面爬取和防御采集器，节约服务器CPU、内存、带宽的开销', 'aiya-framework'),
            'id' => 'waf_reject_useragent_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('屏蔽空 UA', 'aiya-framework'),
            'desc' => __('禁止空 USER AGENT 访问，[br/]*大部分采集程序都是空 UA ，部分 SQL 注入工具也是空 UA ', 'aiya-framework'),
            'id' => 'waf_reject_useragent_empty',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => __('屏蔽 UA 列表', 'aiya-framework'),
            'desc' => __('接续上一项设置，填写需要屏蔽的 UA 列表，通过 [code],[/code] 分隔，不区分大小写', 'aiya-framework'),
            'id' => 'waf_reject_useragent_list',
            'type' => 'textarea',
            'default' => 'BOT/0.1 (BOT for JCE),HttpClient,WinHttp,Python-urllib,Java,oBot,MJ12bot,Microsoft URL Control,YYSpider,UniversalFeedParser,FeedDemon,CrawlDaddy,Feedly,ApacheBench,Swiftbot,ZmEu,Indy Library,jaunty,AhrefsBot,jikeSpider,EasouSpider,jaunty,lightDeckReports Bot',
        ],
        [
            'title' => __('启用 IP 验证', 'aiya-framework'),
            'desc' => __('禁止某些 IP 访问，使用黑名单模式', 'aiya-framework'),
            'id' => 'waf_reject_ips_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('屏蔽 IP 列表', 'aiya-framework'),
            'desc' => __('接续上一项设置，填写需要屏蔽的 IP 列表，每行一条（支持 IP 段）', 'aiya-framework'),
            'id' => 'waf_reject_ip_list',
            'type' => 'textarea',
            'default' => '127.0.0.1' . "\n" . '192.168.1.1',
        ],
    ];

    AYF::new_opt([
        'title' => __('WAF模块', 'aiya-framework'),
        'slug' => 'firewall',
        'parent' => 'plugin',
        'desc' => __('基于正则匹配方式的评论过滤组件，阻止垃圾评论', 'aiya-framework'),
        'fields' => $AYF_FIREWALL_FIELDS,
    ]);

    AYP::action('UA_Firewall', $AYF_FIREWALL_FIELDS, 'firewall');
}

//评论过滤器组件
if (AYF::get_checked('plugin_add_comment_filter', 'plugin')) {
    //设置项
    $AYF_COMMENT_FILTER_FIELDS = [
        [
            'desc' => __('过滤器设置', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'desc' => __('与 WP 自身的评论过滤器不同，此过滤器工作在站点的评论表单提交时，垃圾评论不会被保存到数据库', 'aiya-framework'),
            'type' => 'message',
        ],
        /*
        [
            'title' => __('启用 Akismet 插件兼容', 'aiya-framework'),
            'desc' => __('如果评论用户已登录，则忽略此过滤器', 'aiya-framework'),
            'id' => 'site_comment_akismet_compatibility',
            'type' => 'switch',
            'default' => true,
        ],
        */
        [
            'title' => __('忽略已登录用户的评论', 'aiya-framework'),
            'desc' => __('如果评论用户已登录，则忽略此过滤器', 'aiya-framework'),
            'id' => 'site_comment_ignore_logged_users',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => __('禁止垃圾评论提交到数据库', 'aiya-framework'),
            'desc' => __('使 [url=' . admin_url('options-discussion.php') . '] 讨论设置 [/url] 中的 “禁止使用的评论关键字” 列表在当前插件中生效', 'aiya-framework'),
            'id' => 'site_comment_check_wp_blacklist',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('排除评论表单网址字段', 'aiya-framework'),
            'desc' => __('使站点评论表单的 url （网址）字段失效，即使模板网址字段可被填写，但不会被提交到数据库', 'aiya-framework'),
            'id' => 'site_comment_remove_url_field',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => __('评论内容过滤', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('排除所有非中文评论', 'aiya-framework'),
            'desc' => __('基于 UTF-8 编码检测，排除所有非中文评论', 'aiya-framework'),
            'id' => 'site_comment_all_foreign_lang',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('评论内容字数限制', 'aiya-framework'),
            'desc' => __('评论内容检测，限制评论内容最少字数', 'aiya-framework'),
            'id' => 'site_comment_min_word_strlen',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('最少字数限制参数', 'aiya-framework'),
            'desc' => __('计算评论内容字数，默认最少需要 [code]10[/code] 字', 'aiya-framework'),
            'id' => 'site_comment_min_word_strlen_num',
            'type' => 'text',
            'default' => '10',
        ],
        [
            'title' => __('评论链接限制', 'aiya-framework'),
            'desc' => __('评论内容检测，限制评论内容最多出现几个链接', 'aiya-framework'),
            'id' => 'site_comment_count_link_limit',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('链接限制计数参数', 'aiya-framework'),
            'desc' => __('计算评论内容链接数量，默认最多允许出现 [code]2[/code] 个链接', 'aiya-framework'),
            'id' => 'site_comment_count_link_limit_num',
            'type' => 'text',
            'default' => '2',
        ],
        [
            'desc' => __('自定义过滤器', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('启用自定义正则', 'aiya-framework'),
            'desc' => __('使用正则表达式过滤评论列表，匹配成功时自动过滤，[b]如果不了解正则语法，请勿使用[/b]', 'aiya-framework'),
            'id' => 'site_comment_filter_custom_regular',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('表达式列表', 'aiya-framework'),
            'desc' => __('一行一个，顺序执行。常用语法：
            [br/][code]/\d{10,}/[/code] 匹配 10 位以上数字（常见电话号码）
            [br/][code]/[A-Z]{10,}/[/code] 匹配 10 位以上大写字母
            [br/][code]/[!@#$%^&*]{5,}/[/code] 匹配 5 个以上连续特殊符号
            [br/][code]/\p{So}{5,}/u[/code] 匹配特殊符号（如 emoji）
            [br/][code]/代刷|文凭|菠菜|办理|代理|棋牌|包过/iu[/code] 匹配中文关键词
            [br/][code]/\b(viagra|casino|loan|sex)\b/i[/code] 匹配英文关键词
            [br/][code]/\p{Han}{3}\d{3}/u[/code] 匹配类似 "关键词123" 的引流类关键词', 'aiya-framework'),
            'id' => 'site_comment_filter_custom_str_list',
            'type' => 'textarea',
            'default' => '/代刷|文凭|菠菜|办理|代理|棋牌|包过/iu' . "\n" . '\b(viagra|casino|loan|sex)\b/i',
        ],

    ];

    AYF::new_opt([
        'title' => __('评论过滤', 'aiya-framework'),
        'desc' => __('基于正则匹配方式的评论过滤组件，阻止垃圾评论', 'aiya-framework'),
        'slug' => 'comment',
        'parent' => 'plugin',
        'fields' => $AYF_COMMENT_FILTER_FIELDS,
    ]);

    AYP::action('Comment_Filter', $AYF_COMMENT_FILTER_FIELDS, 'comment');
}

//外部统计组件
if (AYF::get_checked('plugin_add_site_statistics', 'plugin')) {
    //设置项
    $AYF_ADD_EXTRA_FIELDS = [
        [
            'desc' => __('统计代码', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('Google Analytics（分析）', 'aiya-framework'),
            'desc' => __('填写谷歌统计的衡量ID（通常为 [code]UA-[/code] 或 [code]G-[/code] 开头，非数据流ID）[br/]*仅需填写统计ID，代码自动补全'),
            'id' => 'site_google_analytics',
            'type' => 'text',
            'default' => '',
        ],
        [
            'desc' => __('插入代码', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('额外JS', 'aiya-framework'),
            'desc' => __('配置站点额外JavaScript代码，该项已包含 [code]script[/code] 标签'),
            'id' => 'site_extra_script',
            'type' => 'code_editor',
            'settings' => [
                'lineNumbers' => true,
                'tabSize' => 2,
                'theme' => 'monokai',
                'mode' => 'javascript',
            ],
            'default' => '',
        ],
        [
            'desc' => __('插入样式', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('额外CSS', 'aiya-framework'),
            'desc' => __('配置站点额外CSS，该项已包含 [code]style[/code] 标签'),
            'id' => 'site_extra_css',
            'type' => 'code_editor',
            'settings' => [
                'lineNumbers' => true,
                'tabSize' => 2,
                'theme' => 'monokai',
                'mode' => 'css',
            ],
            'default' => '',
        ],
    ];

    AYF::new_opt([
        'title' => __('额外代码', 'aiya-framework'),
        'slug' => 'extra',
        'parent' => 'plugin',
        'desc' => __('为网站前台添加图标，额外样式和统计代码', 'aiya-framework'),
        'fields' => $AYF_ADD_EXTRA_FIELDS,
    ]);

    AYP::action('Head_Extra', $AYF_ADD_EXTRA_FIELDS, 'extra');
}

//STMP组件
if (AYF::get_checked('plugin_add_stmp_mail', 'plugin')) {
    //设置项
    $AYF_STMP_MAIL_FIELDS = [
        [
            'desc' => __('新用户注册通知', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('禁止通知站长', 'aiya-framework'),
            'desc' => __('禁用新用户注册通知站长的邮件'),
            'id' => 'disable_new_user_email_admin',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('禁止通知用户', 'aiya-framework'),
            'desc' => __('禁用新用户注册通知用户的邮件'),
            'id' => 'disable_new_user_email_user',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => __('SMTP 服务器', 'aiya-framework'),
            'type' => 'title_2',
        ],
        [
            'title' => __('发件邮箱', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_from',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('发件人', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_from_name',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('SMTP 服务器', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_host',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('SMTP 端口', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_port',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('加密方式', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_secure',
            'type' => 'radio',
            'sub' => [
                'ssl' => __('SSL认证', 'aiya-framework'),
                'tls' => __('TLS认证', 'aiya-framework'),
                'fil' => __('禁用', 'aiya-framework'),
            ],
            'default' => 'fil',
        ],
        [
            'title' => __('用户认证', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_auth',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => __('用户名', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_username',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('密码', 'aiya-framework'),
            'desc' => '',
            'id' => 'smtp_password',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => __('禁用SSL证书验证', 'aiya-framework'),
            'desc' => __('使用此选项以禁用PHP默认的 SSL 证书配置验证，如果可选，你应该让你的主机修复 SSL 配置而不是绕过它', 'aiya-framework'),
            'id' => 'smtp_disable_ssl_verification',
            'type' => 'switch',
            'default' => false,
        ],
    ];

    AYF::new_opt([
        'title' => __('SMTP 邮件发信', 'aiya-framework'),
        'slug' => 'stmpmail',
        'parent' => 'plugin',
        'desc' => __('使用SMTP发送邮件', 'aiya-framework'),
        'fields' => $AYF_STMP_MAIL_FIELDS,
    ]);

    AYP::action('Mail_Sender', $AYF_STMP_MAIL_FIELDS, 'stmpmail');
}

//本地化头像
if (AYF::get_checked('plugin_local_avatar_upload', 'plugin')) {
    //无需设置
    AYP::action('Local_Avatars', true);
}

//分类URL重建组件，移除分类URL中Category
if (AYF::get_checked('plugin_no_category_url', 'plugin')) {
    //无需设置
    AYP::action('No_Category_URL', true);
}

/*
 * ------------------------------------------------------------------------------
 * 开发用的小功能
 * ------------------------------------------------------------------------------
 */

//服务器状态仪表盘小组件
if (AYF::get_checked('dashboard_server_monitor', 'plugin')) {
    //无需设置
    AYP::action('Dashboard_Server_Status', true);
}

//运行DEBUG查询
if (AYF::get_checked('debug_mode', 'plugin')) {
    //无需设置
    AYP::action('Debug_Mode', true);
}

//简码列表
if (AYF::get_checked('debug_shortcode_items', 'plugin')) {
    AYF::new_opt([
        'title' => __('短代码列表', 'aiya-framework'),
        'slug' => 'shortcode_items',
        'parent' => 'plugin',
        'desc' => __('列出当前主题支持的全部短代码功能（ Shortcode 字段），并列出回调函数', 'aiya-framework'),
        'fields' => [
            [
                'function' => 'query_shortcode_items',
                'type' => 'callback',
            ],
        ],
    ]);
}

//路由列表
if (AYF::get_checked('debug_rules_items', 'plugin')) {
    AYF::new_opt([
        'title' => __('固定链接列表', 'aiya-framework'),
        'slug' => 'rules_items',
        'parent' => 'plugin',
        'desc' => __('列出当前主题支持的全部固定链接（ Rewrite 规则）和查询方法', 'aiya-framework'),
        'fields' => [
            [
                'function' => 'query_rewrite_rules_items',
                'type' => 'callback',
            ],
        ],
    ]);
}
