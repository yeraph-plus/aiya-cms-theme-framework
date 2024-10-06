<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('upload_files')) {
    die('没有上传权限！');
}

?>
<div class="pic-bed-warp">
    <form class="pic-bed-upload-form" class="text-center" action="#" method="post" enctype="multipart/form-data">
        <input class="form-input" type="file" id="image_upload" name="image_upload" accept="image/*">
        <?php wp_nonce_field('handle_image_upload_action', 'handle_image_upload_nonce'); ?>

        <input class="form-button button" type="submit" value="上传图片">
    </form>
    <hr />
    <div class="pic-bed-upload-result">
        <?php AYA_Shortcode_Pic_Bed::handle_image_upload(); ?>
    </div>
</div>