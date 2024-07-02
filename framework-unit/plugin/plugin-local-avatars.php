<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 本地化头像功能插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/

 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

class AYA_Plugin_Local_Avatars
{
    private $user_id_being_edited;

    public function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('show_user_profile', array($this, 'edit_user_profile'));
        add_action('edit_user_profile', array($this, 'edit_user_profile'));
        add_action('personal_options_update', array($this, 'edit_user_profile_update'));
        add_action('edit_user_profile_update', array($this, 'edit_user_profile_update'));
        add_action('bbp_user_edit_after_about', array($this, 'bbpress_user_profile'));

        add_filter('get_avatar_data', array($this, 'get_avatar_data'), 10, 2);
        add_filter('get_avatar', array($this, 'get_avatar'), 10, 6);
        add_filter('avatar_defaults', array($this, 'avatar_defaults'));
    }

    public function admin_init()
    {
        //注册功能开关设置项
        register_setting('discussion', 'basic_user_avatars_caps', array($this, 'sanitize_options'));
        //在讨论设置里增加了功能开关
        add_settings_field('basic-user-avatars-caps', __('Local Avatar Permissions'), array($this, 'avatar_settings_field'), 'discussion', 'avatars');
    }
    //设置数组中增加功能开关设置项
    public function sanitize_options($input)
    {
        $new_input['basic_user_avatars_caps'] = empty($input) ? 0 : 1;

        return $new_input;
    }
    //功能开关设置项表单
    public function avatar_settings_field($args)
    {
        $options = get_option('basic_user_avatars_caps');

        //验证选项，允许全部人上传或仅允许作者
        $basic_user_avatars_caps = !empty($options['basic_user_avatars_caps']) ? 1 : 0;
        //表单
        printf(
            '<label for="basic_user_avatars_caps">
					<input type="checkbox" name="basic_user_avatars_caps" id="basic_user_avatars_caps" value="%s" %s />
					%s
				</label>
			</p>',
            esc_attr($basic_user_avatars_caps),
            checked($basic_user_avatars_caps, true, false),
            esc_html__('Only allow users with file upload capabilities to upload local avatars (Authors and above)')
        );
    }
    //过滤头像数据
    public function get_avatar_data($args, $id_or_email)
    {
        if (!empty($args['force_default'])) return $args;

        global $wpdb;

        $return_args = $args;

        //验证数据，然后转换成$user_id
        if (is_numeric($id_or_email) && 0 < $id_or_email) {
            $user_id = (int) $id_or_email;
        } elseif (is_object($id_or_email) && isset($id_or_email->user_id) && 0 < $id_or_email->user_id) {
            $user_id = $id_or_email->user_id;
        } elseif (is_object($id_or_email) && isset($id_or_email->ID) && isset($id_or_email->user_login) && 0 < $id_or_email->ID) {
            $user_id = $id_or_email->ID;
        } elseif (is_string($id_or_email) && false !== strpos($id_or_email, '@')) {
            $_user = get_user_by('email', $id_or_email);

            if (!empty($_user)) {
                $user_id = $_user->ID;
            }
        }

        //没有此用户
        if (empty($user_id)) return $args;

        $user_avatar_url = null;

        //从usermeta获取本地头像
        $local_avatars = get_user_meta($user_id, 'basic_user_avatar', true);

        if (empty($local_avatars) || empty($local_avatars['full'])) {
            //从WP方法提取头像
            $wp_user_avatar_id = get_user_meta($user_id, $wpdb->get_blog_prefix() . 'user_avatar', true);

            if (!empty($wp_user_avatar_id)) {
                $wp_user_avatar_url = wp_get_attachment_url(intval($wp_user_avatar_id));
                $local_avatars = array('full' => $wp_user_avatar_url);
                update_user_meta($user_id, 'basic_user_avatar', $local_avatars);
            } else {
                //没有找到，参数返回给原方法
                return $args;
            }
        }

        //注册一个头像尺寸的过滤器
        $size = apply_filters('basic_user_avatars_default_size', (int) $args['size'], $args);

        //生成头像尺寸
        if (empty($local_avatars[$size])) {

            //上传路径
            $upload_path = wp_upload_dir();
            $avatar_full_path = str_replace($upload_path['baseurl'], $upload_path['basedir'], $local_avatars['full']);
            //WP的图片裁剪方法
            $image = wp_get_image_editor($avatar_full_path);
            $image_sized = null;

            //是否报错
            if (!is_wp_error($image)) {
                $image->resize($size, $size, true);
                $image_sized = $image->save();
            }

            //不能处理就返回原图
            if (empty($image_sized) || is_wp_error($image_sized)) {
                $local_avatars[$size] = $local_avatars['full'];
            } else {
                $local_avatars[$size] = str_replace($upload_path['basedir'], $upload_path['baseurl'], $image_sized['path']);
            }

            //保存到usermeta
            update_user_meta($user_id, 'basic_user_avatar', $local_avatars);
        }
        //验证HTTPS
        elseif (substr($local_avatars[$size], 0, 4) != 'http') {
            $local_avatars[$size] = home_url($local_avatars[$size]);
        }

        if (is_ssl()) {
            $local_avatars[$size] = str_replace('http:', 'https:', $local_avatars[$size]);
        }

        $user_avatar_url = $local_avatars[$size];

        //组装返回的参数
        if ($user_avatar_url) {
            $return_args['url'] = $user_avatar_url;
            $return_args['found_avatar'] = true;
        }

        //注册一个覆盖原始头像的过滤器
        return apply_filters('basic_user_avatar_data', $return_args);
    }
    //在原来的头像获取函数中注册自己的方法
    public function get_avatar($avatar, $id_or_email, $size = 96, $default = '', $alt = false, $args = array())
    {
        return apply_filters('basic_user_avatar', $avatar, $id_or_email);
    }
    //注销原来的 get_avatar 动作替换成自己的
    public function avatar_defaults($avatar_defaults)
    {
        remove_action('get_avatar', array($this, 'get_avatar'));

        return $avatar_defaults;
    }
    //删除头像方法
    public function avatar_delete($user_id)
    {
        $old_avatars = get_user_meta($user_id, 'basic_user_avatar', true);
        $upload_path = wp_upload_dir();

        if (is_array($old_avatars)) {
            foreach ($old_avatars as $old_avatar) {
                $old_avatar_path = str_replace($upload_path['baseurl'], $upload_path['basedir'], $old_avatar);
                @unlink($old_avatar_path);
            }
        }

        delete_user_meta($user_id, 'basic_user_avatar');
    }
    //文件重命名格式
    public function unique_filename_callback($dir, $name, $ext)
    {
        $user = get_user_by('id', (int) $this->user_id_being_edited);
        $name = $base_name = sanitize_file_name(strtolower($user->display_name) . '_avatar');

        $number = 1;

        while (file_exists($dir . "/$name$ext")) {
            $name = $base_name . '_' . $number;
            $number++;
        }

        return $name . $ext;
    }
    //文件上传操作
    public function edit_user_profile_update($user_id)
    {
        //WP安全验证和时间限制
        if (!isset($_POST['_basic_user_avatar_nonce']) || !wp_verify_nonce($_POST['_basic_user_avatar_nonce'], 'basic_user_avatar_nonce')) return;

        //创建文件
        if (!empty($_FILES['basic-user-avatar']['name'])) {
            //设置允许的图片和格式
            $mimes = array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
            );

            //验证upload函数存在不存在则加载（这里是为了兼容bbpress等前端加载）
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            //一点简单的防御，防止上传php文件
            if (strstr($_FILES['basic-user-avatar']['name'], '.php')) {
                wp_die('For security reasons, the extension ".php" cannot be in your file name.');
            }

            //先删除原来的头像
            $this->avatar_delete($this->user_id_being_edited);

            //把user_id传递给unique_filename_callback
            $this->user_id_being_edited = $user_id;
            //执行上传
            $avatar = wp_handle_upload($_FILES['basic-user-avatar'], array('mimes' => $mimes, 'test_form' => false, 'unique_filename_callback' => array($this, 'unique_filename_callback')));

            //Handle failures
            if (empty($avatar['file'])) {
                switch ($avatar['error']) {
                    case 'File type does not meet security guidelines. Try another.':
                        add_action('user_profile_update_errors', function ($error = 'avatar_error') {
                            esc_html__("Please upload a valid image file for the avatar.", "basic-user-avatars");
                        });
                        break;
                    default:
                        add_action('user_profile_update_errors', function ($error = 'avatar_error') {
                            // No error let's bail.
                            if (empty($avatar['error'])) {
                                return;
                            }

                            '<strong>' . esc_html__('There was an error uploading the avatar:') . '</strong> ' . esc_attr($avatar['error']);
                        });
                }
                return;
            }

            // Save user information (overwriting previous)
            update_user_meta($user_id, 'basic_user_avatar', array('full' => $avatar['url']));
        }
        //删除文件
        elseif (!empty($_POST['basic-user-avatar-erase'])) {
            //删除当前文件
            $this->avatar_delete($user_id);
        }
    }
    //用户资料页面的提交表单
    public function edit_user_profile($profileuser)
    {
        //跳过BBP
        if (function_exists('is_bbpress') && is_bbpress()) return;

        $options = get_option('basic_user_avatars_caps');

        $from_html = '';
        $from_html .= '<h2>' . __('Local Avatar') . '</h2>';
        $from_html .= '<table class="form-table"><tr>';
        $from_html .= '<th><label for="basic-user-avatar">' . __('Upload Avatar') . '</label></th>';
        $from_html .= '<td style="width: 50px;" valign="top">' . get_avatar($profileuser->ID) . '</td>';
        $from_html .= '<td>';
        //验证用户权限
        if (empty($options['basic_user_avatars_caps']) || current_user_can('upload_files')) {
            // Nonce security ftw
            wp_nonce_field('basic_user_avatar_nonce', '_basic_user_avatar_nonce', false);

            //上传选项表单
            $from_html .= '<input type="file" name="basic-user-avatar" id="basic-local-avatar" />';

            //提示文本
            if (empty($profileuser->basic_user_avatar)) {
                $from_html .= '<p class="description">' . __('No local avatar is set. Use the upload field to add a local avatar.') . '</p>';
            } else {
                //附加删除选项
                $from_html .= '<p><input type="checkbox" name="basic-user-avatar-erase" id="basic-user-avatar-erase" value="1" /><label for="basic-user-avatar-erase">' . __('Delete local avatar') . '</label></p>';
                $from_html .= '<p class="description">' . __('Replace the local avatar by uploading a new avatar, or erase the local avatar (falling back to a gravatar) by checking the delete option.') . '</p>';
            }
        }
        //权限不足时
        else {
            //提示文本
            if (empty($profileuser->basic_user_avatar)) {
                $from_html .= '<p class="description">' . __('No local avatar is set. Set up your avatar at Gravatar.com.') . '</p>';
            } else {
                $from_html .= '<p class="description">' . __('You do not have media management permissions. To change your local avatar, contact the site administrator.') . '</p>';
            }
        }

        $from_html .= '</td>';
        $from_html .= '</tr></table>';
        $from_html .= '<script type="text/javascript"> var form = document.getElementById("your-profile"); form.encoding = "multipart/form-data"; form.setAttribute("enctype", "multipart/form-data");</script>';

        echo $from_html;
    }
    //BBP页面的提交表单
    public function bbpress_user_profile()
    {
        if (!bbp_is_user_home_edit()) return;

        $user_id = get_current_user_id();
        $profileuser = get_userdata($user_id);

        $options = get_option('basic_user_avatars_caps');

        $from_html = '<div>';
        $from_html .= '<label for="basic-local-avatar">' . __('Avatar') . '</label>';
        $from_html .= '<fieldset class="bbp-form avatar">';
        $from_html .= get_avatar($profileuser->ID);

        //验证权限
        if (empty($options['basic_user_avatars_caps']) || current_user_can('upload_files')) {
            // Nonce security ftw
            wp_nonce_field('basic_user_avatar_nonce', '_basic_user_avatar_nonce', false);

            //上传选项表单
            $from_html .= '<br /><input type="file" name="basic-user-avatar" id="basic-local-avatar" /><br />';

            if (empty($profileuser->basic_user_avatar)) {
                $from_html .= '<span class="description" style="margin-left:0;">' . apply_filters('bu_avatars_no_avatar_set_text', __('No local avatar is set. Use the upload field to add a local avatar.'), $profileuser) . '</span>';
            } else {
                $from_html .= '<input type="checkbox" name="basic-user-avatar-erase" id="basic-user-avatar-erase" value="1" style="width:auto" /> <label for="basic-user-avatar-erase">' . apply_filters('bu_avatars_delete_avatar_text', __('Delete local avatar'), $profileuser) . '</label><br />';
                $from_html .= '<span class="description" style="margin-left:0;">' . apply_filters('', __('Replace the local avatar by uploading a new avatar, or erase the local avatar (falling back to a gravatar) by checking the delete option.'), $profileuser) . '</span>';
            }
        } else {

            if (empty($profileuser->basic_user_avatar)) {
                $from_html .= '<span class="description" style="margin-left:0;">' . apply_filters('bu_avatars_no_avatar_set_text', esc_html__('No local avatar is set. Set up your avatar at Gravatar.com.'), $profileuser) . '</span>';
            } else {
                $from_html .= '<span class="description" style="margin-left:0;">' . apply_filters('bu_avatars_permissions_text', esc_html__('You do not have media management permissions. To change your local avatar, contact the site administrator.'), $profileuser) . '</span>';
            }
        }

        $from_html .= '</fieldset>';
        $from_html .= '</div>';
        $from_html .= '<script type="text/javascript"> var form = document.getElementById("bbp-your-profile"); form.encoding = "multipart/form-data"; form.setAttribute("enctype", "multipart/form-data"); </script>';

        echo $from_html;
    }
}
