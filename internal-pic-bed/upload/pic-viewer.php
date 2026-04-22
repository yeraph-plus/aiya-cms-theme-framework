<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="pic-bed-warp">
    <h3>已上传的文件</h3>
    <div class="pic-bed-view-list">
        <?php AYA_SimplePicBed::handle_image_viewer(); ?>
    </div>
</div>