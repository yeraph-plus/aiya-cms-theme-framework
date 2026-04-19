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
        'desc' => '站点图标',
        'type' => 'title_2',
    ],
    [
        'desc' => '推荐使用 WP 设置生成站点图标，兼容性更好，如果你的主题不支持，可以使用此设置',
        'type' => 'message',
    ],
    [
        'title' => '配置 favicon.ico ',
        'desc' => '上传站点 favicon 图标，可以为任意图片格式，不需要时留空',
        'id' => 'site_favicon_url',
        'type' => 'upload',
        'default' => '',
    ],
    [
        'desc' => '禁用功能',
        'type' => 'title_2',
    ],
    [
        'title' => '禁用自动更新（不建议）',
        'desc' => '禁用 WP 自动更新，以解决WP无法连接到wordpress.org时产生报错',
        'id' => 'disable_wp_auto_update',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁用管理员邮箱确认',
        'desc' => '禁用 WP 内置的管理员用户定期提示邮箱确认功能',
        'id' => 'disable_admin_email_check',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => 'Jetpack插件和一些客户端依赖 XML-RPC 接口与站点通信，需要使用相关功能时，请使用“Disable XML-RPC”等插件替代此项',
        'type' => 'message',
    ],
    [
        'title' => '禁用 XML-RPC ',
        'desc' => '此选项通过替换动作函数使 XML-RPC 无法工作，并不能彻底禁用此功能[br/]*如需彻底禁用XML-RPC，应当在服务器中通过WAF策略等方式阻止外部对[code]/xmlrpc.php[/code]文件的访问',
        'id' => 'disable_xmlrpc',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '阻止 PingBack ',
        'desc' => '启用后阻止所有 PingBack 动作，关闭后仅阻止 PingBack 自己[br/]*PingBack、Enclosures和Trackbacks是XML-RPC的功能，禁用XML-RPC后此选项不会生效',
        'id' => 'disable_pingback',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用 REST-API ',
        'desc' => '禁用 REST-API 接口，启用后访问此接口会返回报错',
        'id' => 'disable_rest_api',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁用 Feed ',
        'desc' => '禁用 Feed 功能 （RSS），启用后访问此接口会返回报错',
        'id' => 'disable_feed',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁用字体和语言包组件',
        'desc' => '禁用 WP 内置的翻译组件，并禁用语言包文件加载',
        'id' => 'disable_locale_rtl',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁用嵌入功能',
        'desc' => '禁用 WP 内置的嵌入功能（oEmbed），移除 [code]<head>[/code] 标签中嵌入的功能组件',
        'id' => 'disable_head_oembed',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用自动链接解析',
        'desc' => '禁用 WP 内置的自动链接解析（Auto-Embed），阻止Youtube等外部网站输入时自动加载为 [code]<iframe>[/code] ',
        'id' => 'disable_autoembed',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用自动字符转换',
        'desc' => '禁用 WP 内置的自动标点符号转换功能，阻止英文引号转义为中文引号和标签自动校正',
        'id' => 'disable_texturize',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用 s.w.org 标记',
        'desc' => '禁用 WP 内置的DNS预解析功能（dns-prefetch）',
        'id' => 'disable_sworg',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '页面优化',
        'type' => 'title_2',
    ],
    [
        'title' => '精简页面 head 结构',
        'desc' => '精简 [code]head[/code] 中的日志链接、短链接、RSD接口等无用标签',
        'id' => 'remove_head_redundant',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '移除 emoji 样式',
        'desc' => '禁止 WP 加载并移除 emoji`s 组件和相关样式',
        'id' => 'remove_wp_emojicons',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '移除古腾堡样式',
        'desc' => '禁用 Gutenberg 引入的样式[br/]*会导致前台通过Gutenberg自定义的外观失效，注意检查',
        'id' => 'remove_gutenberg_styles',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用谷歌字体',
        'desc' => '禁止 WP 加载谷歌字体并移除样式',
        'id' => 'remove_open_sans',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用静态文件版本号',
        'desc' => '移除前台静态文件加载时引入的版本号[br/]*可能会导致用户浏览器缓存的静态文件和服务器不一致，谨慎使用',
        'id' => 'remove_css_js_ver',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'desc' => '易用性调整',
        'type' => 'title_2',
    ],
    [
        'title' => '启用链接管理器',
        'desc' => '启用 WP 内置的链接管理器功能',
        'id' => 'add_link_manager',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁用修订版本记录',
        'desc' => '禁用 WP 编辑器的修订版本记录功能',
        'id' => 'remove_revisions',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用自动保存',
        'desc' => '禁用 WP 编辑器的自动保存功能',
        'id' => 'remove_editor_autosave',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '后台页面设置为中文',
        'desc' => '适配一些外贸站点和国内站点，将后台页面语言强制替换为 [code]zh_CN[/code][br/]*此选项不是翻译功能，只是为了去除浏览器的翻译页面提示',
        'id' => 'admin_page_locale_cn',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '允许WEBP图片上传',
        'desc' => '去除 WP 上传WEBP图片时产生的报错',
        'id' => 'add_upload_webp',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用图片自动缩放',
        'desc' => '禁止 WP 自动生成略缩图和图片缩放[br/]*此选项通过将 WP 图片大小默认值设置为 [code]0[/code] 来生效，可被 [url=' . admin_url('options-media.php') . ']媒体设置[/url] 覆盖',
        'id' => 'remove_image_thumbnails',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用超大图片自动缩放',
        'desc' => '禁止 WP 对大于 5000px*7000px 的图像自动缩放',
        'id' => 'remove_image_threshold',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '禁用 PDF 文件自动生成预览',
        'desc' => '禁止 WP 自动生成 PDF 文件的缩略图，可以解决一些文件管理类插件的冲突',
        'id' => 'remove_pdf_preview',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '地区相关调整',
        'type' => 'title_2',
    ],
    [
        'title' => '移除隐私政策页面',
        'desc' => '移除 WP 为欧洲通用数据保护条例（GDPR）生成的页面',
        'id' => 'remove_privacy_policy',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '中文安装包优化',
        'desc' => '移除 [code]cn.wordpress.org[/code] 下载的 WP 安装包中的一些无用代码',
        'id' => 'zh_cn_option_cleanup',
        'type' => 'switch',
        'default' => false,
    ],
];

//查询设置
$AYF_REQUEST_FIELDS = [
    [
        'desc' => 'SQL查询优化',
        'type' => 'title_2',
    ],
    [
        'desc' => '主循环中跳过计算 SQL 匹配总数以提高查询速度，这对文章数量比较多的站点非常有用',
        'type' => 'message',
    ],
    [
        'title' => '跳过 SQL 计数',
        'desc' => '替代 WP 主查询中的 [code]$wp_query->found_posts[/code] 方法为 [code]EXPLAIN SELECT[/code] 语句，大幅降低SQL开销[br/]*该项会同时禁用 [code]SQL_CALC_FOUND_ROWS[/code] 属性，会导致一些文章列表插件无法正常工作，请自行测试',
        'id' => 'query_no_found_rows',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '预加载元数据',
        'desc' => '使 WP 全局预加载文章的元数据（post meta）和分类数据（post term）以提高查询速度',
        'id' => 'query_update_post_cache',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '查询过滤器',
        'type' => 'title_2',
    ],
    [
        'title' => '取消文章置顶',
        'desc' => '禁用文章置顶，按默认的文章排序输出',
        'id' => 'query_ignore_sticky',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '显示自定文章类型',
        'desc' => '将自定义的文章类型加入到主查询[br/]*此项仅对本插件创建的文章类型有效',
        'id' => 'query_post_type_var',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '首页中排除分类',
        'desc' => '填写首页排除分类的ID，通过 [code],[/code] 分隔',
        'id' => 'query_ignore_category',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '首页中排除文章',
        'desc' => '填写首页排除文章的ID，通过 [code],[/code] 分隔',
        'id' => 'query_ignore_post',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '搜索结果包含页面',
        'desc' => '搜索时同时搜索页面和文章添加到搜索结果',
        'id' => 'search_ignore_page_type',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '搜索结果匹配分类',
        'desc' => '当搜索关键词与分类/标签/自定义分类相同时，返回分类中的文章',
        'id' => 'search_request_term_exists',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '搜索中排除分类',
        'desc' => '填写搜索时排除分类的ID，通过 [code],[/code] 分隔',
        'id' => 'serach_ignore_category',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '搜索中排除文章',
        'desc' => '填写搜索时排除文章的ID，通过 [code],[/code] 分隔',
        'id' => 'serach_ignore_post',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '登录用户显示全部文章',
        'desc' => '对管理员或登录用户自己，显示全部状态的文章（包含草稿、待发布、已删除等）',
        'id' => 'query_author_current',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '搜索结果设置',
        'type' => 'title_2',
    ],
    [
        'title' => '搜索页重定向',
        'desc' => '强制 [code]?s=[/code] 参数跳转到 [code]search/[/code] 页面，使搜索页面静态化',
        'id' => 'search_redirect_search_page',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '搜索结果是否为空',
        'desc' => '当搜索关键词输入为空时，重定向到首页',
        'id' => 'search_redirect_intend',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '搜索结果跳过',
        'desc' => '当搜索结果有且只有一篇文章时，直接转到文章页',
        'id' => 'search_redirect_one_post',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '搜索功能限制',
        'type' => 'title_2',
    ],
    [
        'title' => '启用搜索限制',
        'desc' => '根据IP或用户角色限制搜索功能，防止滥用',
        'id' => 'serach_redirect_scope_enable',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '搜索用户验证',
        'desc' => '仅允许已登录的用户使用搜索功能，或关闭搜索',
        'id' => 'serach_scope_user_check',
        'type' => 'radio',
        'sub' => [
            'all' => '无限制',
            'logged' => '仅限登录用户',
            'disabled' => '关闭站点搜索'
        ],
        'default' => 'all',
    ],
    [
        'title' => '最大搜索关键词长度',
        'desc' => '计算单位为字节，限制最大长度 255 字节（一个汉字为 3 字节，一个英文字母为 1 字节）',
        'id' => 'serach_scope_length',
        'type' => 'text',
        'default' => '255',
    ],
    [
        'title' => '每分钟搜索限制',
        'desc' => '每分钟最大搜索次数，达到上限之后屏蔽10分钟',
        'id' => 'serach_scope_limit',
        'type' => 'text',
        'default' => '10',
    ],
    [
        'desc' => '自定义搜索（高级）',
        'type' => 'title_2',
    ],
    [
        'title' => '只搜索文章标题',
        'desc' => '不搜索文章内容和摘要，提高搜索响应速度',
        'id' => 'search_clause_only_title',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '允许搜索文章ID',
        'desc' => '允许搜索ID查找文章，多个ID时支持 [code],[/code] 分隔',
        'id' => 'search_clause_type_id',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '允许搜索自定义字段',
        'desc' => '允许搜索自定义字段（postmeta），会大幅影响查询速度，请谨慎使用',
        'id' => 'search_clause_type_meta',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '指定搜索自定义字段',
        'desc' => '接续上一项设置，设置接受搜索的自定义字段，请输入支持的 meta_key',
        'id' => 'search_clause_meta_key',
        'type' => 'text',
        'default' => '',
    ],
];

//安全性
$AYF_SECURITY_FIELDS = [
    [
        'desc' => '阻止外部查询用户信息',
        'type' => 'title_2',
    ],
    [
        'title' => '去除 Sitemap 用户列表',
        'desc' => '禁止站点的 [code]/wp-sitemap.xml[/code] 中生成Users列表',
        'id' => 'remove_sitemaps_users_provider',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '去除 REST-API 用户端点',
        'desc' => '禁止站点的 [code]/wp-json/wp/v2/users[/code] 接口端点',
        'id' => 'remove_restapi_users_endpoint',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '去除 REST-API 文章端点',
        'desc' => '禁止站点的 [code]/wp-json/wp/v2/posts[/code] 接口端点',
        'id' => 'remove_restapi_posts_endpoint',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '后台入口权限调整',
        'type' => 'title_2',
    ],
    [
        'title' => '调整访问后台用户级别',
        'desc' => '根据用户角色判断，禁止权限不足的用户访问后台并重定向回首页',
        'id' => 'admin_backend_verify',
        'type' => 'radio',
        'sub' => [
            'false' => '无限制',
            'editor' => '编辑',
            'author' => '作者',
            'contributor' => '贡献者',
            'subscriber' => '订阅者',
        ],
        'default' => 'false',
    ],
    [
        'desc' => '登录页防护',
        'type' => 'title_2',
    ],
    [
        'title' => '简单登录页防护',
        'desc' => '为登录页面附加访问参数，隐藏登录表单防止脚本暴力破解',
        'id' => 'login_page_param_verify',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '登录页自动跳转',
        'desc' => '通过 JavaScript 方式，等待5秒后自动跳转到带有访问参数的地址[br/]*如果禁用自动跳转，请牢记设置的认证参数',
        'id' => 'login_page_auto_jump_times',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '登录页认证参数',
        'desc' => '接续上一项设置，登录页面的URL格式为 [code]/wp-login.php?auth=path_login[/code]',
        'id' => 'login_page_param_args',
        'type' => 'text',
        'default' => 'path_login',
    ],
    [
        'desc' => '登录验证逻辑调整',
        'type' => 'title_2',
    ],
    [
        'title' => '强制使用邮箱登录',
        'desc' => '修改登录方式仅允许邮箱登录',
        'id' => 'admin_allow_email_login',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '禁止管理员找回密码',
        'desc' => '禁止权限为管理员的用户发起密码找回[br/]*注意！开启此选项后如果忘记密码将只能通过 SSH 等其他方式删除或禁用此插件来解除限制',
        'id' => 'admin_disallow_password_reset',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '用户名验证',
        'desc' => '登录/注册时对用户信息进行验证，避免不安全的用户名',
        'id' => 'logged_sanitize_user_enable',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '登录时禁止用户名',
        'desc' => '接续上一项设置，指定禁止使用的用户名，通过 [code],[/code] 分隔[br/]*执行全词匹配，区分大小写',
        'id' => 'logged_prevent_user_name',
        'type' => 'text',
        'default' => 'admin,administrator,root',
    ],
    [
        'title' => '注册时清理用户名',
        'desc' => '接续上一项设置，用户注册时去除不安全用户名和不安全的字符，通过 [code],[/code] 分隔[br/]*执行半匹配，不区分大小写',
        'id' => 'logged_register_user_name',
        'type' => 'text',
        'default' => 'admin,root',
    ],
];

//自动功能
$AYF_AUTOMATIC_FIELDS = [
    [
        'desc' => '文章默认模板',
        'type' => 'title_2',
    ],
    [
        'title' => '进入编辑器时插入默认文本',
        'desc' => '将一些标准的格式化的文章内容直接插入到编辑器中',
        'id' => 'the_post_auto_insert_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '添加编辑器默认内容',
        'desc' => '添加默认内容之后，会在每次创建文章时提前插入编辑器',
        'id' => 'the_post_auto_insert_content',
        'type' => 'textarea',
        'default' => '',
    ],
    [
        'desc' => '文章内容预处理',
        'type' => 'title_2',
    ],
    [
        'desc' => '警告：以下功能在文章保存/更新时触发，会直接操作文章数据，投入使用前请先备份站点数据并仔细测试。',
        'type' => 'warning',
    ],
    [
        'title' => '自动检索标签',
        'desc' => '检索全部正文，添加已存在的标签到文章（*该动作仅在文章保存时触发）[br/]*说明：内部使用 [code]strpos()[/code] 方法，匹配不一定准确',
        'id' => 'the_post_auto_strpos_tags',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => 'HTML格式预处理',
        'desc' => '简单过滤器，自动去除 [code]div[/code] 、 [code]span[/code] 等标签和重叠的标签、去除全角空格等[br/]*说明：采集站/笔记站的实用功能，在文章保存前对文章内容进行预处理，用于清理复制粘贴时带来的多余 HTML 标签',
        'id' => 'the_post_auto_insert_html_filter',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'desc' => '*此功能使用 [code]jxlwqq/chinese-typesetting[/code] 项目：[url=https://github.com/jxlwqq/chinese-typesetting]查看文档[/url]',
        'type' => 'message',
    ],
    [
        'title' => '中文排版纠正',
        'desc' => '使用依赖库，执行复杂排版纠正',
        'id' => 'the_post_auto_chs_compose_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '启用的排版纠正方法',
        'desc' => '接续上一项设置，选择需要启用的格式过滤器，详细说明请查阅相关项目文档',
        'id' => 'the_post_auto_chs_compose_type',
        'type' => 'checkbox',
        'sub' => [
            'insertSpace' => '中英文空格补正',
            'removeSpace' => '清除全角标点空格',
            'full2Half' => '全角转半角',
            'fixPunctuation' => '修复错误的标点符号',
            'properNoun' => '专有名词大小写',
            'removeClass' => '清除标签 Class 属性',
            'removeId' => '清除标签 ID 属性',
            'removeStyle' => '清除标签 Style 属性',
            'removeEmptyParagraph' => '清除空的段落标签',
            'removeEmptyTag' => '清除所有空的标签',
            'removeIndent' => '清除段首缩进',
        ],
        'default' => ['insertSpace', 'removeSpace', 'full2Half'],
    ],
    [
        'desc' => '自动别名',
        'type' => 'title_2',
    ],
    [
        'desc' => '*注意：如需文章别名在 URL 结构中生效，请先设置 [url=options-permalink.php]固定链接[/url]',
        'type' => 'message',
    ],
    [
        'desc' => '*此功能使用 [code]overtrue/pinyin[/code] 项目',
        'type' => 'message',
    ],
    [
        'title' => '使用拼音生成分类、标签别名',
        'desc' => '在分类法创建/更新时触发，如果未设置别名（留空时）自动生成',
        'id' => 'the_term_auto_pinyin_slug_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '使用拼音生成文章别名',
        'desc' => '在文章保存/更新时触发，如果未设置别名（为空时），则自动生成',
        'id' => 'the_post_auto_pinyin_slug_bool',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '根据文章 ID 生成别名',
        'desc' => '会覆盖上一项设置，强制使用 ID 生成文章的别名，不会检查是否已设置别名
            [br/]仿写格式1 [code]av00000001[/code] 使用文章 ID 数据进行字符补全
            [br/]仿写格式2 [code]bvxxxxxxxx[/code] 使用文章 ID 数据进行Base58编码',
        'id' => 'the_post_auto_slug_type',
        'type' => 'radio',
        'sub' => [
            'off' => '关闭',
            'id_av' => '低仿AV号',
            'id_bv' => '低仿BV号',
        ],
        'default' => 'off',
    ],
    [
        'title' => '文章别名附加前缀',
        'desc' => '在使用 ID 生成别名时设置的别名前缀，请勿包含 [code]<>{}|^[][?#:[/code] 等控制字符以及空格',
        'id' => 'site_post_auto_slug_prefix',
        'type' => 'text',
        'default' => 'PV',
    ],
];

AYF::new_opt([
    'title' => '优化设置',
    'slug' => 'optimize',
    'parent' => 'plugin',
    'desc' => '禁用或调整一些 WordPress 内置功能，以优化网站性能',
    'fields' => $AYF_OPTIMIZE_FIELDS,
]);

AYP::action('Optimize', $AYF_OPTIMIZE_FIELDS, 'optimize');

AYF::new_opt([
    'title' => '查询和搜索',
    'slug' => 'request',
    'parent' => 'plugin',
    'desc' => '自定义和调整 WordPress 首页和搜索的查询参数',
    'fields' => $AYF_REQUEST_FIELDS,
]);

AYP::action('Request', $AYF_REQUEST_FIELDS, 'request');

AYF::new_opt([
    'title' => '登录保护',
    'slug' => 'security',
    'parent' => 'plugin',
    'desc' => '调整 WordPress 登录页面，增加登录和后台访问验证',
    'fields' => $AYF_SECURITY_FIELDS,
]);

AYP::action('Security', $AYF_SECURITY_FIELDS, 'security');

AYF::new_opt([
    'title' => '自动功能',
    'slug' => 'automatic',
    'parent' => 'plugin',
    'desc' => '调整 WordPress 文章内容过滤器组件设置',
    'fields' => $AYF_AUTOMATIC_FIELDS,
]);

AYF::new_box([
    'title' => '重新发布',
    'id' => 'reset_post_datetime_box',
    'context' => 'normal',
    'priority' => 'low',
    'add_box_in' => ['post'],
    'fields' => [
        [
            'title' => '',
            'desc' => '刷新文章发布日期到当前时间',
            'id' => 'reset_post_datetime',
            'type' => 'switch',
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
            'desc' => '自定义默认头像',
            'type' => 'title_2',
        ],
        [
            'title' => '上传默认头像',
            'desc' => '此功能创建了一个新的头像标志，需要在 WP 的 [url=' . admin_url('options-discussion.php') . ']讨论设置[/url] 中，将默认头像设置切换为此选项[br/]Tips: 如果使用头像加速时可能会失效',
            'id' => 'site_default_avatar',
            'type' => 'upload',
            'default' => AYF::get_base_url() . '/assects/img/default_avatar.png',
        ],
        [
            'desc' => ' WeAvatar 头像服务',
            'type' => 'title_2',
        ],
        [
            'title' => '使用 WeAvatar 头像',
            'desc' => '使用 weavatar.com 提供的头像服务替代 Gravatar',
            'id' => 'use_speed_weavatar',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => ' Gravatar 头像加速',
            'type' => 'title_2',
        ],
        [
            'title' => ' Gravatar 加速',
            'desc' => '替换 gravatar.com 头像服务的地址到镜像源，和上一项互斥',
            'id' => 'use_speed_gravatar',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => ' Gravatar 镜像源',
            'desc' => '使用 Gravatar 头像服务的镜像源',
            'id' => 'avatar_cdn_type',
            'type' => 'radio',
            'sub' => [
                'cn' => 'Gravatar （官方CN源）',
                'qiniu' => '七牛 CDN',
                'v2ex' => 'V2EX CDN',
                'geekzu' => '极客族 CDN',
                'loli' => 'LOLI 图床',
            ],
            'default' => 'cn',
        ],
        [
            'title' => '自定义镜像',
            'desc' => '使用自定义 Gravatar 头像服务的镜像源',
            'id' => 'avatar_cdn_custom',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '强制 HTTPS ',
            'desc' => '强制头像服务通过 HTTPS 加载',
            'id' => 'avatar_ssl',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => '字体加速',
            'type' => 'title_2',
        ],
        [
            'title' => ' Google 字体加速',
            'desc' => '使用 Google 字体镜像源',
            'id' => 'use_speed_google_fonts',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => ' Google 字体镜像源',
            'desc' => '使用 Google 字体的镜像源，加速主题加载',
            'id' => 'google_fonts_cdn_type',
            'type' => 'radio',
            'sub' => [
                'geekzu' => '极客族 CDN',
                'loli' => 'LOLI 图床',
                'ustc' => '中科大 CDN',
                'custom' => '自定义',
            ],
            'default' => 'ustc',
        ],
        [
            'title' => '自定义镜像地址',
            'desc' => '使用自定义 Google 字体的镜像加速服务地址',
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
        'title' => '头像&字体加速',
        'slug' => 'avatar',
        'parent' => 'plugin',
        'desc' => '配置头像、字体通过第三方CDN加速',
        'fields' => $AYF_AVATAR_SPEED_FIELDS,
    ]);

    AYP::action('CDN_Speed', $AYF_AVATAR_SPEED_FIELDS, 'avatar');
}
//SEO组件
if (AYF::get_checked('plugin_add_seo_stk', 'plugin')) {
    //设置项
    $AYF_SEO_TDK_FIELDS = [
        [
            'desc' => '标题选择器',
            'type' => 'title_2',
        ],
        [
            'desc' => '*标题选择器只影响head标签内部，即浏览器窗口显示的标题',
            'type' => 'message',
        ],
        [
            'title' => 'SEO标题',
            'desc' => '留空则默认引用站点设置',
            'id' => 'site_title',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => 'SEO副标题',
            'desc' => '留空则默认引用站点描述设置',
            'id' => 'site_title_sub',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '显示站点副标题',
            'desc' => '是否显示副标题（站点描述）',
            'id' => 'site_title_sub_true',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => '标题分隔符',
            'desc' => '设置标题文本的分隔符，默认添加空格补正',
            'id' => 'site_title_sep',
            'type' => 'radio',
            'sub' => [
                'nbsp' => '空格" &nbsp; "',
                'hyphen' => '连字符" - "',
                'y-line' => '分隔符" | "',
                'u-line' => '下划线"  _ "',
            ],
            'default' => 'hyphen',
        ],
        [
            'desc' => 'SEO设置',
            'type' => 'title_2',
        ],
        [
            'title' => '启用SEO组件',
            'desc' => '同时启用文章和分类SEO组件，如果使用其他SEO插件则需要禁用此项',
            'id' => 'site_seo_action',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => '首页SEO关键词',
            'desc' => '添加到首页关键词，仅影响首页',
            'id' => 'site_seo_keywords',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '首页SEO描述',
            'desc' => '添加到首页描述，仅影响首页',
            'id' => 'site_seo_description',
            'type' => 'textarea',
            'default' => '',
        ],
        [
            'desc' => ' robots.txt 规则设置',
            'type' => 'title_2',
        ],
        [
            'title' => '启用 Robots 规则',
            'desc' => '自定义站点 [code]/robots.txt[/code] 的内容，禁用则引用站点默认设置',
            'id' => 'site_seo_robots_switch',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => '自定义 robots.txt ',
            'desc' => '自定义robots.txt的内容，语法参考：[url=https://www.robotstxt.org/robotstxt.html]robotstxt.org[/url]',
            'id' => 'site_seo_robots_txt',
            'type' => 'textarea',
            'default' => ayf_get_default_robots_text(),
        ],
        [
            'desc' => '文本关键词自动替换',
            'type' => 'title_2',
        ],
        [
            'title' => '启用自动替换',
            'desc' => '用于在文内替换关键词或添加链接，请见下方格式说明[br/]*说明：内部使用 [code]str_replace()[/code] 方法，匹配不一定准确',
            'id' => 'site_seo_auto_replace',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '关键词列表',
            'desc' => '添加文本替换列表，一行一个，关键词使用 [code]|[/code] 分隔[br/]*格式举例： [code] 站点首页|<a href="' . home_url() . '">站点首页</a>[/code] ',
            'id' => 'site_replace_text_wps',
            'type' => 'textarea',
            'default' => '',
        ],
        [
            'desc' => '文本关键词自动内链',
            'type' => 'title_2',
        ],
        [
            'desc' => '*这是一项SEO站点的常用功能，如果你的站点内容类型非常单一，可以使用此功能',
            'type' => 'message',
        ],
        [
            'title' => '文内标签自动链接',
            'desc' => '自动为正文内匹配到的标签添加超链接[br/]*说明：规则为匹配到标签在文中出现 2 次自动添加链接，只添加 1 次',
            'id' => 'site_seo_auto_tag_link',
            'type' => 'switch',
            'default' => false,
        ],
    ];

    AYF::new_opt([
        'title' => 'SEO-TDK',
        'slug' => 'seo',
        'parent' => 'plugin',
        'desc' => '简单SEO组件，用于自定义页面标题、关键词和描述，以及简单内链功能',
        'fields' => $AYF_SEO_TDK_FIELDS,
    ]);

    AYF::new_tex([
        'add_meta_in' => 'category',
        'fields' => [
            [
                'title' => 'SEO关键词',
                'desc' => '多个关键词之间使用 [code], [/code] 分隔，默认显示该分类名称',
                'id' => 'seo_cat_keywords',
                'type' => 'text',
                'default' => '',
            ],
            [
                'title' => 'SEO描述',
                'desc' => '默认显示该分类说明文本',
                'id' => 'seo_cat_desc',
                'type' => 'textarea',
                'default' => '',
            ],
        ],
    ]);

    AYF::new_box([
        'title' => '自定义SEO',
        'id' => 'seo_box',
        'context' => 'normal',
        'priority' => 'low',
        'add_box_in' => ['post'],
        'fields' => [
            [
                'title' => 'SEO关键词',
                'desc' => '多个关键词之间使用 [code], [/code] 分隔，留空则默认设置为文章的标签',
                'id' => 'seo_keywords',
                'type' => 'text',
                'default' => '',
            ],
            [
                'title' => 'SEO描述',
                'desc' => '文章页面默认提取全文为前150个字符（描述文本推荐不超过150个字符）',
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
            'desc' => '简单 WAF 防护',
            'type' => 'title_2',
        ],
        [
            'title' => '启用 URL 参数验证',
            'desc' => '屏蔽一些和站点无关的参数访问，也可以用于防止百度统计刷数据',
            'id' => 'waf_reject_argument_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '屏蔽参数关键字',
            'desc' => '接续上一项设置，填写需要屏蔽的 Url 参数关键字，通过 [code],[/code] 分隔',
            'id' => 'waf_reject_argument_list',
            'type' => 'text',
            'default' => 'wd,str',
        ],
        [
            'title' => '启用 UA 验证',
            'desc' => '屏蔽一些无用的搜索引擎蜘蛛对网站的页面爬取和防御采集器，节约服务器CPU、内存、带宽的开销',
            'id' => 'waf_reject_useragent_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '屏蔽空 UA ',
            'desc' => '禁止空 USER AGENT 访问，[br/]*大部分采集程序都是空 UA ，部分 SQL 注入工具也是空 UA ',
            'id' => 'waf_reject_useragent_empty',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => '屏蔽 UA 列表',
            'desc' => '接续上一项设置，填写需要屏蔽的 UA 列表，通过 [code],[/code] 分隔，不区分大小写',
            'id' => 'waf_reject_useragent_list',
            'type' => 'textarea',
            'default' => 'BOT/0.1 (BOT for JCE),HttpClient,WinHttp,Python-urllib,Java,oBot,MJ12bot,Microsoft URL Control,YYSpider,UniversalFeedParser,FeedDemon,CrawlDaddy,Feedly,ApacheBench,Swiftbot,ZmEu,Indy Library,jaunty,AhrefsBot,jikeSpider,EasouSpider,jaunty,lightDeckReports Bot',
        ],
        [
            'title' => '启用 IP 验证',
            'desc' => '禁止某些 IP 访问，使用黑名单模式',
            'id' => 'waf_reject_ips_switch',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '屏蔽 IP 列表',
            'desc' => '接续上一项设置，填写需要屏蔽的 IP 列表，每行一条（支持 IP 段）',
            'id' => 'waf_reject_ip_list',
            'type' => 'textarea',
            'default' => '127.0.0.1' . "\n" . '192.168.1.1',
        ],
    ];

    AYF::new_opt([
        'title' => 'WAF模块',
        'slug' => 'firewall',
        'parent' => 'plugin',
        'desc' => '基于正则匹配方式的评论过滤组件，阻止垃圾评论',
        'fields' => $AYF_FIREWALL_FIELDS,
    ]);

    AYP::action('UA_Firewall', $AYF_FIREWALL_FIELDS, 'firewall');
}

//评论过滤器组件
if (AYF::get_checked('plugin_add_comment_filter', 'plugin')) {
    //设置项
    $AYF_COMMENT_FILTER_FIELDS = [
        [
            'desc' => '过滤器设置',
            'type' => 'title_2',
        ],
        [
            'desc' => '与 WP 自身的评论过滤器不同，此过滤器工作在站点的评论表单提交时，垃圾评论不会被保存到数据库',
            'type' => 'message',
        ],
        /*
        [
            'title' => '启用 Akismet 插件兼容',
            'desc' => '如果评论用户已登录，则忽略此过滤器',
            'id' => 'site_comment_akismet_compatibility',
            'type' => 'switch',
            'default' => true,
        ],
        */
        [
            'title' => '忽略已登录用户的评论',
            'desc' => '如果评论用户已登录，则忽略此过滤器',
            'id' => 'site_comment_ignore_logged_users',
            'type' => 'switch',
            'default' => true,
        ],
        [
            'title' => '禁止垃圾评论提交到数据库',
            'desc' => '使 [url=' . admin_url('options-discussion.php') . '] 讨论设置 [/url] 中的 “禁止使用的评论关键字” 列表在当前插件中生效',
            'id' => 'site_comment_check_wp_blacklist',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '排除评论表单网址字段',
            'desc' => '使站点评论表单的 url （网址）字段失效，即使模板网址字段可被填写，但不会被提交到数据库',
            'id' => 'site_comment_remove_url_field',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => '评论内容过滤',
            'type' => 'title_2',
        ],
        [
            'title' => '排除所有非中文评论',
            'desc' => '基于 UTF-8 编码检测，排除所有非中文评论',
            'id' => 'site_comment_all_foreign_lang',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '评论内容字数限制',
            'desc' => '评论内容检测，限制评论内容最少字数',
            'id' => 'site_comment_min_word_strlen',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '最少字数限制参数',
            'desc' => '计算评论内容字数，默认最少需要 [code]10[/code] 字',
            'id' => 'site_comment_min_word_strlen_num',
            'type' => 'text',
            'default' => '10',
        ],
        [
            'title' => '评论链接限制',
            'desc' => '评论内容检测，限制评论内容最多出现几个链接',
            'id' => 'site_comment_count_link_limit',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '链接限制计数参数',
            'desc' => '计算评论内容链接数量，默认最多允许出现 [code]2[/code] 个链接',
            'id' => 'site_comment_count_link_limit_num',
            'type' => 'text',
            'default' => '2',
        ],
        [
            'desc' => '自定义过滤器',
            'type' => 'title_2',
        ],
        [
            'title' => '启用自定义正则',
            'desc' => '使用正则表达式过滤评论列表，匹配成功时自动过滤，[b]如果不了解正则语法，请勿使用[/b]',
            'id' => 'site_comment_filter_custom_regular',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '表达式列表',
            'desc' => '一行一个，顺序执行。常用语法：
            [br/][code]/\d{10,}/[/code] 匹配 10 位以上数字（常见电话号码）
            [br/][code]/[A-Z]{10,}/[/code] 匹配 10 位以上大写字母
            [br/][code]/[!@#$%^&*]{5,}/[/code] 匹配 5 个以上连续特殊符号
            [br/][code]/\p{So}{5,}/u[/code] 匹配特殊符号（如 emoji）
            [br/][code]/代刷|文凭|菠菜|办理|代理|棋牌|包过/iu[/code] 匹配中文关键词
            [br/][code]/\b(viagra|casino|loan|sex)\b/i[/code] 匹配英文关键词
            [br/][code]/\p{Han}{3}\d{3}/u[/code] 匹配类似 "关键词123" 的引流类关键词',
            'id' => 'site_comment_filter_custom_str_list',
            'type' => 'textarea',
            'default' => '/代刷|文凭|菠菜|办理|代理|棋牌|包过/iu' . "\n" . '\b(viagra|casino|loan|sex)\b/i',
        ],

    ];

    AYF::new_opt([
        'title' => '评论过滤',
        'slug' => 'comment',
        'parent' => 'plugin',
        'desc' => '基于正则匹配方式的评论过滤组件，阻止垃圾评论',
        'fields' => $AYF_COMMENT_FILTER_FIELDS,
    ]);

    AYP::action('Comment_Filter', $AYF_COMMENT_FILTER_FIELDS, 'comment');
}

//外部统计组件
if (AYF::get_checked('plugin_add_site_statistics', 'plugin')) {
    //设置项
    $AYF_ADD_EXTRA_FIELDS = [
        [
            'desc' => '统计代码',
            'type' => 'title_2',
        ],
        [
            'title' => 'Google Analytics（分析）',
            'desc' => '填写谷歌统计的衡量ID（通常为 [code]UA-[/code] 或 [code]G-[/code] 开头，非数据流ID）[br/]*仅需填写统计ID，代码自动补全',
            'id' => 'site_google_analytics',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '百度统计',
            'desc' => '填写百度统计的跟踪ID（位于 [code]/hm.js?[/code] 之后的那段参数）',
            'id' => 'site_baidu_tongji',
            'type' => 'text',
            'default' => '',
        ],
        [
            'desc' => '插入代码',
            'type' => 'title_2',
        ],
        [
            'title' => '额外JS',
            'desc' => '配置站点额外JavaScript代码，该项已包含 [code]<script>[/code] 标签',
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
            'desc' => '插入样式',
            'type' => 'title_2',
        ],
        [
            'title' => '额外CSS',
            'desc' => '配置站点额外CSS，该项已包含 [code]<style>[/code] 标签',
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
        'title' => '额外代码',
        'slug' => 'extra',
        'parent' => 'plugin',
        'desc' => '为网站前台添加图标，额外样式和统计代码',
        'fields' => $AYF_ADD_EXTRA_FIELDS,
    ]);

    AYP::action('Head_Extra', $AYF_ADD_EXTRA_FIELDS, 'extra');
}

//STMP组件
if (AYF::get_checked('plugin_add_stmp_mail', 'plugin')) {
    //设置项
    $AYF_STMP_MAIL_FIELDS = [
        [
            'desc' => '新用户注册通知',
            'type' => 'title_2',
        ],
        [
            'title' => '禁止通知站长',
            'desc' => '禁用新用户注册通知站长的邮件',
            'id' => 'disable_new_user_email_admin',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '禁止通知用户',
            'desc' => '禁用新用户注册通知用户的邮件',
            'id' => 'disable_new_user_email_user',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'desc' => ' STMP 送信',
            'type' => 'title_2',
        ],
        [
            'title' => '发件邮箱',
            'desc' => '',
            'id' => 'smtp_from',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '发件人',
            'desc' => '',
            'id' => 'smtp_from_name',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => ' SMTP 服务器',
            'desc' => '',
            'id' => 'smtp_host',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => ' SMTP 端口',
            'desc' => '',
            'id' => 'smtp_port',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '加密方式',
            'desc' => '',
            'id' => 'smtp_secure',
            'type' => 'radio',
            'sub' => [
                'ssl' => 'SSL认证',
                'tls' => 'TLS认证',
                'fil' => '禁用',
            ],
            'default' => 'fil',
        ],
        [
            'title' => '用户认证',
            'desc' => '',
            'id' => 'smtp_auth',
            'type' => 'switch',
            'default' => false,
        ],
        [
            'title' => '用户名',
            'desc' => '',
            'id' => 'smtp_username',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '密码',
            'desc' => '',
            'id' => 'smtp_password',
            'type' => 'text',
            'default' => '',
        ],
        [
            'title' => '禁用SSL证书验证',
            'desc' => '使用此选项以禁用PHP默认的 SSL 证书配置验证，如果可选，你应该让你的主机修复 SSL 配置而不是绕过它',
            'id' => 'smtp_disable_ssl_verification',
            'type' => 'switch',
            'default' => false,
        ],
    ];

    AYF::new_opt([
        'title' => 'SMTP发信',
        'slug' => 'stmpmail',
        'parent' => 'plugin',
        'desc' => '使用SMTP发送邮件',
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
        'title' => '简码列表',
        'slug' => 'shortcode_items',
        'parent' => 'plugin',
        'desc' => '列出当前主题支持的全部简码功能（ Shortcode 字段），并列出回调函数',
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
        'title' => '路由列表',
        'slug' => 'rules_items',
        'parent' => 'plugin',
        'desc' => '列出当前主题支持的全部固定链接（ Rewrite 规则）和查询方法',
        'fields' => [
            [
                'function' => 'query_rewrite_rules_items',
                'type' => 'callback',
            ],
        ],
    ]);
}
