## AIYA-CMS 主题设置框架组件

![截图](https://github.com/yeraph-plus/aiya-cms-theme-core/blob/master/screenshot/%E5%B1%8F%E5%B9%95%E6%88%AA%E5%9B%BE%202023-12-03%20112645.png)

---

一个用于为 WordPress 主题创建设置页面、分类 Meta 字段和文章 MetaBox 组件的选项框架。

---

该项目是 AIYA-CMS 主题的其中一部分，写的很糙，之后会视我闲的程度继续更新。

#### 如何使用：

参考 `add-functions.php` 的内容复制到主题的 `functions.php` 即可，其中包含了框架使用的常量名和引入文件。

其中`sample-config.php` 是一个示例配置文件，项目额外提供了封装好的函数可供调用，参考内置文档。

#### 注意事项：

1、这个框架兼容多站点模式，但不是很确定是不是真的兼容，WP 官方的文档对于一些钩子的说明也不是很明确，或者也有可能是我太菜了没看懂。

2、理论上这个框架可以当作插件加载，但不建议，因为写的时候完全没考虑过插件兼容。

如果一定要用作插件的话请修改常量定义中的 `get_template_directory()` 和 `get_template_directory_uri()` 为 `plugin_dir_path()` 和 `plugins_url()` 。

#### 已完成：

- 创建设置组表单、嵌套以及多重创建
- 文本框、文本域、单选框、复选框、下拉框
- 设置开关（布尔型）
- 组件调用 WP 内置功能（上传文件、颜色选择器、TinyMCE）
- CodeMirror 编辑器
- 多语言兼容

#### 待完成：

- 置列表生成目录树，或者做 tab 分页
- 重写样式表
