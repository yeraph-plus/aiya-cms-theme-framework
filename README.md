## AIYA-CMS 主题设置框架&组件

![截图](https://https://github.com/yeraph-plus/AIYA-CMS-THEME-CORE/blob/main/screenshot/2024-06-01%20001416.png)

---

这个项目是为了将 AIYA-CMS 主题的开发工作分支出来而专门设计的，你可以认为这是一个不包含前台页面组件的 AIYA-CMS 主题。

所以简单介绍一下这个项目，这是一个用于 WordPress 主题的设置选项框架外带一整套的预置功能，可以为其他人制作WP主题提供一个良好的快速开始选项。

框架的设计和提供的功能类似于Codestar-Framework，但轻（jian）量（lou）的多。目前已支持为 WordPress 主题创建设置页面、创建分类 Meta 字段、创建文章 Meta 字段，另外对 WprdPress 小工具提供了一个用于简化创建的构造器。

预置功能分两部分，一部分是用于制作主题时添加功能的简化函数（注册主题页面路由、菜单、自定义文章类型等），另一部分是一套完整的“WordPress 优化插件”，包含大部分常见的优化功能（WP功能禁用、WP主查询修改、WP安全性设置、以及SEO拓展、头像加速、STMP等）。

---

#### 如何使用：

需要PHP版本7及以上，WprdPress版本5.4及以上，建议能上最新上最新。

将`framework-required`整个目录复制到主题文件夹中，在主题的 `functions.php` 中添加：

`require_once(get_template_directory() . '/framework-required/setup.php');`


示例文件：

`require_once(get_template_directory() . '/framework-required/inc/sample-config.php');`

此 DEMO 包含了本框架支持的所有组件，调用方法未做封装，项目额外提供了封装好的函数可供使用。


加载预置功能组件：

`require_once(get_template_directory() . '/framework-required/plugin-config.php');`

如果你不需要这部分功能，可用不加载，框架本体仅需 `setup.php` 文件和 inc 目录下的组件，其他的部分可以删除。

全部的组件均位于 plugins 目录下，由于 `plugin-config.php` 中列出的只是一部分组件，其余的你不需要的组件也可以删除。

~~详细的功能列表请见 wiki 中的说明~~（还没写）。


一些使用和增加功能的参考：

和CSF框架一样，本框架最终也是通过WP的 `get_option()` 方法保存设置内容的。

位于 `setup.php` 中有此框架的封装好的创建/调用函数 `AYF::` ，但是这样调用可能不是最简化的，你可能需要二次封装或者干脆自己写一个新的。

项目的文件加载结构，位于 `inc/framework-steup-action.php` ，包含了框架的零件（fileds）和预置功能插件的加载方式，请见内部注释有详细说明。


注意事项：

所有的功能目前还没有完全测试，有问题请 issues 或者到我的博客留言。

框架生成的页面并不过滤HTML标签，这是为了方便在参数中使用<code>等标签调整文字样式，如果你要这样用的话应当注意检查标签封闭防止页面整段垮掉。

框架用作插件的话请修改常量定义中的 `get_template_directory()` 和 `get_template_directory_uri()` 为 `plugin_dir_path()` 和 `plugins_url()` 。

#### 计划/待办：

- 补全功能文档
- 重写一下回调，写成ajax的

#### 项目备注：

1、这个框架兼容多站点模式，但不是很确定是不是真的兼容，WP 官方的文档对于一些钩子的说明也不是很明确，或者也有可能是我太菜了没看懂。

2、多语言兼容并不完整，请待后续更新，你会发现有些地方是显示的是中文的然后有些地方是英文，这个是因为原来想全写成英文然后懒癌犯了，所以先这样吧。

3、预置功能的这部分中大部分是这些年我写的项目&帮别人改的项目中攒下来的代码，主打一个历史悠久来源广泛，有些方法会比较智障，还有一些方法可能已经因为历史太久已经失效了，另外很多地方写的写法确实不太严谨，老PHPer看了会发疯的那种，道理我都懂但反正不影响用。~~建议Fork本项目修改后推送回当前分支来教我做事~~（bushi）