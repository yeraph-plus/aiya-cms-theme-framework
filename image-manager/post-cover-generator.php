<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 文章封面生成
 * ------------------------------------------------------------------------------
 */

// 封面保存参数
function aya_image_manager_cover_save_options(): array
{
    $format = aya_plugin_opt('site_plugin_image_save_format');

    if (empty($format) || !in_array($format, ['webp', 'avif']) || $format === 'off') {
        $format =  'jpg';
    }

    return aya_image_manager_save_options($format);
}

// 计算封面目标路径
function aya_image_manager_cover_dest_path(): string
{
    $format = aya_plugin_opt('site_plugin_image_save_format');

    if (empty($format) || !in_array($format, ['webp', 'avif']) || $format === 'off') {
        $format =  'jpg';
    }

    $id = date('YmdHis') . '_' . wp_rand(1000, 9999);
    $dir = trailingslashit(WP_CONTENT_DIR) . 'thumbnail/cover/' . date('Y/m') . '/';

    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    return trailingslashit($dir) . $id . '.' . $format;
}

/**
 * 生成封面（本地绝对路径输出）
 *
 * model:
 * - photo：背景图 + 黑色半透明蒙版 + 居中标题
 * - pattern：纯色背景 + 随机花纹片段 + 居中标题
 *
 * background_image:
 * - 允许传 URL，本函数会尝试转换为本地路径（aya_local_path_with_url）
 *
 * @return string|false 成功返回封面本地绝对路径
 */
function aya_image_manager_generate_cover_local(array $args = [])
{
    // 约束参数内部固定，不允许由外部调用覆盖。
    $comp_args = [
        'width' => 800,
        'height' => 450,
        'font_file' => aya_image_manager_font_file_path('bold'),
        'font_size' => 54,
        'max_chars' => 15,
        'line_spacing' => 12,
        'overlay_opacity' => 30,
        'pattern_material_path' => get_template_directory() . '/assets/material',
    ];

    $defaults = [
        'model' => 'photo',
        'background_image' => '',
        'background_color' => '#333333',
        'title' => '',
        'title_color' => '#ffffff',
    ];

    $args = array_merge($defaults, is_array($args) ? $args : []);
    $args = array_merge($comp_args, $args);

    if (!empty($args['background_image']) && is_string($args['background_image'])) {
        $bg = $args['background_image'];
        $bg_local = aya_local_path_with_url($bg, true);
        // 非本地图片，回退到 pattern 模式
        if ($bg_local) {
            $args['background_image'] = $bg_local;
        } else {
            $args['model'] = 'pattern';
            $args['background_image'] = '';
        }
    }

    $spec = AYA_Image_Cover_Spec::from_array($args);

    $dest_path = aya_image_manager_cover_dest_path($spec);

    $generator = new AYA_Image_Cover_Generator();
    $save_options = aya_image_manager_cover_save_options();

    return $generator->generate($spec, $dest_path, $save_options);
}

/*
 * ------------------------------------------------------------------------------
 * 文章封面 MetaBox（手动生成）
 * ------------------------------------------------------------------------------
 */

add_action('add_meta_boxes', 'aya_image_manager_register_cover_metabox');
add_action('wp_ajax_aya_image_manager_generate_cover', 'aya_image_manager_ajax_generate_cover');

function aya_image_manager_register_cover_metabox(): void
{
    add_meta_box(
        'aya-generate-cover-metabox',
        __('文章封面', 'aiya-cms'),
        'aya_image_manager_render_cover_metabox',
        'post',
        'side',
        'core'
    );
}

function aya_image_manager_render_cover_metabox(WP_Post $post): void
{
    wp_nonce_field('aya_generate_cover_metabox', 'aya_generate_cover_metabox_nonce');

    $thumb_meta = get_post_meta($post->ID, '_aya_thumb', true);
    $cover_url = '';
    if (is_string($thumb_meta) && $thumb_meta !== '') {
        $cover_url = strpos($thumb_meta, '://') !== false
            ? $thumb_meta
            : trailingslashit(WP_CONTENT_URL) . ltrim($thumb_meta, '/');
    }

    echo '<div id="aya-generate-cover-box">';
    echo '<div id="aya-cover-preview">';

    if (is_string($cover_url) && $cover_url !== '') {
        echo '<img src="' . esc_url($cover_url) . '" style="width:100%;height:auto;" />';
        echo '<p class="description">' . esc_html__('为文章生成封面', 'aiya-cms') . '</p>';
    }
    echo '</div>';
    echo '<p><label style="display:block;margin-bottom:4px;">' . esc_html__('封面模式', 'aiya-cms') . '</label>';
    echo '<select id="aya-cover-model" style="width:100%;">';
    echo '<option value="photo">' . esc_html__('使用封面', 'aiya-cms') . '</option>';
    echo '<option value="pattern">' . esc_html__('图案生成', 'aiya-cms') . '</option>';
    echo '</select></p>';
    echo '<p><label style="display:block;margin-bottom:4px;">' . esc_html__('标题', 'aiya-cms') . '</label>';
    echo '<input type="text" id="aya-cover-title" value="' . esc_attr(get_the_title($post->ID)) . '" style="width:100%;" /></p>';
    echo '<p><label style="display:block;margin-bottom:4px;">' . esc_html__('背景色', 'aiya-cms') . '</label>';
    echo '<input type="text" id="aya-cover-bg-color" value="" style="width:100%;" /></p>';
    echo '<p><label style="display:block;margin-bottom:4px;">' . esc_html__('标题颜色', 'aiya-cms') . '</label>';
    echo '<input type="text" id="aya-cover-title-color" value="" style="width:100%;" /></p>';
    echo '<p><button type="button" class="button button-primary" id="aya-cover-generate-btn">' . esc_html__('生成封面', 'aiya-cms') . '</button></p>';
    echo '<div id="aya-cover-status" style="margin-bottom:8px;"></div>';
    echo '</div>';
?>
    <script>
        jQuery(function($) {
            $('#aya-cover-generate-btn').on('click', function(e) {
                e.preventDefault();
                $('#aya-cover-status').text('正在生成...');

                $.post(ajaxurl, {
                    action: 'aya_image_manager_generate_cover',
                    nonce: '<?php echo esc_js(wp_create_nonce('aya_generate_cover_metabox_nonce')); ?>',
                    post_id: '<?php echo (int) $post->ID; ?>',
                    model: $('#aya-cover-model').val(),
                    background_color: $('#aya-cover-bg-color').val(),
                    title: $('#aya-cover-title').val(),
                    title_color: $('#aya-cover-title-color').val()
                }).done(function(res) {
                    if (!res || !res.success) {
                        const msg = (res && res.data && res.data.message) ? res.data.message : '生成失败';
                        $('#aya-cover-status').text(msg);
                        return;
                    }

                    $('#aya-cover-status').text(res.data.message);
                    if (res.data.cover_url) {
                        $('#aya-cover-preview').html('<img src="' + res.data.cover_url + '" style="width:100%;height:auto;" />');
                    }
                }).fail(function() {
                    $('#aya-cover-status').text('请求失败');
                });
            });
        });
    </script>
<?php
}

function aya_image_manager_ajax_generate_cover(): void
{
    if (!check_ajax_referer('aya_generate_cover_metabox_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => __('安全校验失败', 'aiya-cms')]);
    }

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('无权限操作', 'aiya-cms')]);
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        wp_send_json_error(['message' => __('文章不存在', 'aiya-cms')]);
    }

    $model = isset($_POST['model']) ? sanitize_key((string) $_POST['model']) : 'photo';
    if (!in_array($model, ['photo', 'pattern'], true)) {
        $model = 'photo';
    }

    $background_color = isset($_POST['background_color']) ? sanitize_text_field((string) $_POST['background_color']) : '';
    $title = isset($_POST['title']) ? sanitize_text_field((string) $_POST['title']) : '';
    $title_color = isset($_POST['title_color']) ? sanitize_text_field((string) $_POST['title_color']) : '';

    $bg = '';
    if ($model === 'photo') {
        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            $thumb_url = wp_get_attachment_url($thumb_id);
            if (is_string($thumb_url) && $thumb_url !== '' && !cur_is_external_url($thumb_url)) {
                $bg = $thumb_url;
            }
        }
        if ($bg === '') {
            $content = (string) $post->post_content;
            $first = aya_match_post_first_image($content, false);
            if (is_string($first) && $first !== '' && !cur_is_external_url($first)) {
                $bg = $first;
            }
        }
        if ($bg === '') {
            $model = 'pattern';
        }
    }

    $args = [
        'model' => $model,
        'background_image' => $bg,
        'background_color' => $background_color,
        'title' => $title,
        'title_color' => $title_color,
    ];

    $cover_local = aya_image_manager_generate_cover_local($args);
    if (!$cover_local || !is_file($cover_local)) {
        wp_send_json_error(['message' => __('封面生成失败', 'aiya-cms')]);
    }
    $cover_url = aya_local_path_with_url($cover_local, false);
    if (!$cover_url) {
        wp_send_json_error(['message' => __('封面 URL 解析失败', 'aiya-cms')]);
    }

    $rel = aya_image_relpath_thumb_from_local($cover_local);
    if ($rel) {
        update_post_meta($post_id, '_aya_thumb', $rel);
    } else {
        update_post_meta($post_id, '_aya_thumb', $cover_url);
    }

    wp_send_json_success([
        'cover_url' => $cover_url,
        'message' => __('封面已生成', 'aiya-cms'),
    ]);
}
