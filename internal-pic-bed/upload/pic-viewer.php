<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="pic-bed-warp">
    <h3><?php echo __('已上传的图片', 'aiya-framework'); ?></h3>
    <div class="pic-bed-view-list">
        <?php AYA_SimplePicBed::handle_image_viewer(); ?>
    </div>
</div>