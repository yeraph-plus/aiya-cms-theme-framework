<?php
if (!defined('ABSPATH')) exit;

//功能配置
class AYA_Shortcode_Pic_Bed
{
    private static $instance;
    //实例化
    public static function instance()
    {
        if (is_null(self::$instance)) new self();
    }
    public static $uplod_path = 'upload-pics';
    //初始化
    public function __construct()
    {
        //加载菜单
        add_action('admin_menu', array(&$this, 'add_pic_bed_menu'));
    }
    //后台临时图床菜单
    public function add_pic_bed_menu()
    {
        add_menu_page('上传图片', '简码图床',  'manage_options', 'shortcode-pic-bed', array(&$this, 'upload_page'), 'dashicons-format-image', 81);
        add_submenu_page('shortcode-pic-bed', '查看上传', '图片列表',  'manage_options', 'shortcode-pic-view', array(&$this, 'upload_list_view'), 99);
    }
    //上传页面
    public function upload_page()
    {
        //加载css
        wp_enqueue_style('shortcode-pic-bed', PIC_PLUGIN_URI . '/assets/bed-style.css');

        include_once (__DIR__) . '/upload/pic-bed-up.php';
    }
    //查看页面
    public function upload_list_view()
    {
        //加载css
        wp_enqueue_style('shortcode-pic-bed', PIC_PLUGIN_URI . '/assets/bed-style.css');

        include_once (__DIR__) . '/upload/pic-bed-list.php';
    }
    //创建本地文件夹
    public static function local_mkdir()
    {
        $dir_sub_name = self::$uplod_path . '/' . date('Y') . '/' . date('m') . '/';

        //在 wp-content 下创建
        $local_dir = trailingslashit(WP_CONTENT_DIR) . '/' . $dir_sub_name;
        //判断文件夹是否存在
        if (!is_dir($local_dir)) {
            //创建文件夹
            wp_mkdir_p($local_dir);
        }
        //返回拼接的路径
        return $local_dir;
    }
    //上传控件
    public static function handle_image_upload()
    {
        //防止意外上传
        if (isset($_POST['handle_image_upload_nonce']) || isset($_FILES['image_upload'])) {
            //设置允许上传的类型
            $default_mime = array(
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/gif' => '.gif',
                'image/webp' => '.webp',
            );
            //获取文件信息
            $file = $_FILES['image_upload'];
            //异常处理模式
            try {
                if (!wp_verify_nonce($_POST['handle_image_upload_nonce'], 'handle_image_upload_action')) {
                    throw new Exception('非法操作!');
                }

                //验证格式
                $file_mime = strtolower($file['type']);

                if (!isset($default_mime[$file_mime])) {
                    throw new Exception('文件格式错误！');
                }
                if ($file['size'] > 10485760) {
                    throw new Exception('文件大小超过10MB！');
                }
                if ($file['error'] > 0) {
                    throw new Exception('文件错误！错误码：' . $file['error'] . '');
                }
                //保存文件
                $local_dir = self::local_mkdir();
                $file_tmp_name = $file['tmp_name'];
                $file_title = $file['name'];
                $file_name = date('d') . '-' . time() . $default_mime[$file_mime];

                $file_move = move_uploaded_file($file_tmp_name, $local_dir . $file_name);
                if ($file_move === false) {
                    throw new Exception('文件保存失败！');
                }
                //进行图片处理
                $trans_file = self::handle_image_trans_in($local_dir . $file_name);
                if (strpos($trans_file, 'ERROR') !== false) {
                    throw new Exception('处理器错误！' . $trans_file);
                }
                //输出插件的图片信息格式
                echo self::upload_done_info($trans_file, $file_title);
            } catch (Exception $e) {
                //抛出报错
                echo $e->getMessage();
                return;
            }
        } else {
            echo '<p>选择图片文件，支持jpg/png/gif/webp。</p>';
        }
    }
    //读取控件
    public static function handle_image_view()
    {
        $root_dir = trailingslashit(WP_CONTENT_DIR) . self::$uplod_path;

        //内部方法
        function trans_url($path)
        {
            return WP_CONTENT_URL . str_replace(WP_CONTENT_DIR, '', $path);
        }
        function entry_dir($dir, $title = '')
        {
            if (!is_dir($dir)) return;

            $files = scandir($dir);

            foreach ($files as $file) {
                if ($file != "." && $file != "..") {

                    $file_path = $dir . '/' . $file;

                    if (is_file($file_path)) {
                        echo '<img src="' . trans_url($file_path) . '" title="' . $file . '" />';
                    }
                    if (is_dir($file_path)) {
                        echo '<hr />';
                        echo '<p> ' . $title . ' "' . trans_url($file_path) . '/" </p>';
                        //递归
                        entry_dir($file_path, '位置');
                    }
                }
            }
        }
        entry_dir($root_dir, '根目录');
        //print_r($handle);
        return;
    }
    //文件处理
    public static function handle_image_trans_in($image_file)
    {
        //选择处理器
        //$generate_array = 'convert';
        //$generate_type = 'auto_scale';
        //$generate_type = 'watermark';

        $img = new AYA_Imagine_Trans();
        $image_file = $img->image_generate($image_file, 'convert', true);
        $image_file = $img->image_generate($image_file, 'auto_scale');
        $image_file = $img->image_generate($image_file, 'watermark');

        return $image_file;
    }
    //生成图片信息
    public static function upload_done_info($image_path,  $image_title)
    {
        //提取图片宽高
        $image_size = getimagesize($image_path);
        $width = $image_size[0];
        $height = $image_size[1];
        $mime = $image_size['mime'];
        //根据WP上传目录截取本地路径
        $path_file = str_replace(trailingslashit(WP_CONTENT_DIR), '', $image_path);
        //拼接为URL
        $image_url = set_url_scheme(WP_CONTENT_URL) . $path_file;

        //批量输出
        $info_array = array(
            'URL地址' => $image_url,
            '相对路径' => './wp-content' . $path_file,
            '简码格式' => '[pic_bed src="' . $path_file . '" width="' . $width . '" height="' . $height . '" title="' . $image_title . '" alt="' . $mime . '" /]',
            'HTML格式' => '<img src="' . $image_url . '" width="' . $width . '" height="' . $height . '" title="' . $image_title . '" alt="' . $mime . '" />',
            'Markdown格式' => '![' . $mime . '](' . $image_url . ' "' . $image_title . '")',
        );

        $html = '<img src="' . $image_url . '" title="' . $image_title . '" alt="' . $mime . '" />';

        foreach ($info_array as $key => $value) {
            $html .= '<h5>' . $key .   '</h5>';
            $html .= '<pre>' . htmlspecialchars($value) .   '</pre>';
        }

        return $html;
    }
}
