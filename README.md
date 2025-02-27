## AIYA-Framework / AIYA-Optimize-Plugin

### 说明

这个项目是为了将 AIYA-CMS 主题的开发工作分支出来而专门设计的，你可以认为这是一个不包含前台页面组件的 AIYA-CMS 主题。

所以简单介绍一下这个项目，这是一个用于 WordPress 主题的设置选项框架 + 一整套的预置功能，可以为其他人制作WP主题提供一个良好的快速开始选项。

框架的设计和提供的功能类似于Codestar-Framework，但轻（jian）量（lou）的多。目前已支持为 WordPress 主题创建设置页面、创建分类 Meta 字段、创建文章 Meta 字段，另外对 WprdPress 小工具提供了一个用于简化创建的构造器。

预置功能分两部分，一部分是用于制作主题时添加功能的简化函数（注册主题页面路由、菜单、自定义文章类型等），另一部分是一套完整的“WordPress 优化插件”，包含大部分常见的优化功能（WP功能禁用、WP主查询修改、WP安全性设置、以及SEO拓展、头像加速、STMP等）。

---

![截图](https://github.com/yeraph-plus/aiya-wordpress-theme-core/blob/main/screenshot/2024-06-01%20001416.png)

A WordPress theme framework, can be use to create settings option pages, category Meta fields, and article MetaBox components, also provides some quick launch features.

There is a short demo of building this framework:

1. Copy the entire `framework-required` directory copy in the theme's directory.
2. Add require following to your theme `functions.php`:

```
//Require framework file
require_once get_template_directory() . '/framework-required/setup.php';
//The sample-config
require_once get_template_directory() . '/framework-required/sample-config.php';
```
