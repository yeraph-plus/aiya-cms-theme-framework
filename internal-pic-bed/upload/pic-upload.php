<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="pic-bed-warp">
    <form id="pic-bed-upload-form" class="pic-bed-upload-form" action="#" method="post" enctype="multipart/form-data">
        <div class="upload-form-inner">
            <input class="form-input" type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/gif,image/webp">
            <?php AYA_SimplePicBed::current_nonce(); ?>
            <input type="hidden" name="action" value="handle_image_upload">
            <button id="upload-button" class="form-button button" type="submit">
                <?php echo __('上传图片', 'aiya-framework'); ?>
            </button>
        </div>
    </form>
    <div class="pic-bed-heaper">
        <p><?php echo __('支持 JPEG、PNG、BMP、GIF、WebP、AVIF 格式，最大 <?php echo AYA_SimplePicBed::$upload_max_size; ?>MB', 'aiya-framework'); ?></p>
    </div>
    <div id="upload-response" class="pic-bed-upload-result">
        <div class="upload-status"></div>

        <div class="result-container" style="display:none;">
            <div class="result-grid">
                <!-- 左侧预览 -->
                <div class="result-preview">
                    <div class="panel">
                        <h3 class="panel-title"><?php echo __('上传图片预览', 'aiya-framework'); ?></h3>
                        <div class="image-preview">
                            <img id="preview-img" src="" alt="">
                        </div>
                    </div>
                </div>

                <!-- 右侧信息 -->
                <div class="result-info">
                    <div class="panel">
                        <h3 class="panel-title"><?php echo __('上传图片信息', 'aiya-framework'); ?></h3>
                        <div class="info-grid">
                            <div class="data-item">
                                <strong><?php echo __('图片尺寸', 'aiya-framework'); ?>:</strong>
                                <span id="image-dimensions"></span>
                            </div>
                            <div class="data-item">
                                <strong><?php echo __('图片类型', 'aiya-framework'); ?>:</strong>
                                <span id="image-mime"></span>
                            </div>
                        </div>
                        <div class="data-item">
                            <strong><?php echo __('图片保存路径', 'aiya-framework'); ?>:</strong>
                            <div class="copy-wrapper">
                                <code id="image-path"></code>
                            </div>
                        </div>
                        <div class="data-item">
                            <strong><?php echo __('图片保存URL', 'aiya-framework'); ?>:</strong>
                            <div class="copy-wrapper">
                                <a id="image-url" href="" target="_blank"></a>
                            </div>
                        </div>
                        <div class="data-item">
                            <strong><?php echo __('图片短代码', 'aiya-framework'); ?>:</strong>
                            <div class="textarea-wrapper">
                                <textarea id="image-shortcode"></textarea>
                            </div>
                        </div>
                        <div class="data-item">
                            <strong><?php echo __('图片HTML代码', 'aiya-framework'); ?>:</strong>
                            <div class="textarea-wrapper">
                                <textarea id="image-html"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // 获取DOM元素
        const uploadForm = document.getElementById('pic-bed-upload-form');
        const uploadButton = document.getElementById('upload-button');
        const statusDiv = document.querySelector('.upload-status');
        const resultContainer = document.querySelector('.result-container');
        const previewImg = document.getElementById('preview-img');

        // 数据显示元素
        const imageDimensions = document.getElementById('image-dimensions');
        const imageMime = document.getElementById('image-mime');
        const imageUrl = document.getElementById('image-url');
        const imagePath = document.getElementById('image-path');
        const imageShortcode = document.getElementById('image-shortcode');
        const imageHtml = document.getElementById('image-html');

        // 重置状态和表单
        function resetForm() {
            statusDiv.innerHTML = '';
            resultContainer.style.display = 'none';
        }

        // 显示上传状态
        function showStatus(type, message) {
            statusDiv.innerHTML = `<div class="status-${type}">${message}</div>`;
        }

        // 显示上传结果
        function displayImageData(data) {
            // 设置图片预览
            previewImg.src = data.url;
            previewImg.width = data.image.width;
            previewImg.height = data.image.height;

            // 设置图片信息
            imageDimensions.textContent = `${data.image.width} × ${data.image.height}`;
            imageMime.textContent = data.image.mime;
            imageUrl.href = data.url;
            imageUrl.textContent = data.url;
            imagePath.textContent = data.relative_path;
            imageShortcode.value = data.shortcode;
            imageHtml.value = data.html;

            // 显示结果区域
            resultContainer.style.display = 'block';
        }

        // 表单提交事件
        uploadForm.addEventListener('submit', function (event) {
            event.preventDefault();
            resetForm();
            showStatus('info', __('图片上传中...', 'aiya-framework'));

            const formData = new FormData(uploadForm);

            // 禁用提交按钮防止重复上传
            uploadButton.disabled = true;
            uploadButton.innerHTML = '上传中...';

            // 发送AJAX请求
            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(__('网络请求失败:', 'aiya-framework') + response.status);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);

                        if (data.status === 'success') {
                            // 上传成功，显示数据
                            showStatus('success', __('图片上传成功!', 'aiya-framework'));
                            displayImageData(data.data);
                        } else {
                            // 显示错误信息
                            showStatus('error', data.data || __('图片上传失败', 'aiya-framework'));
                        }
                    } catch (e) {
                        console.error(__('服务器返回非JSON响应:', 'aiya-framework') + text);
                        showStatus('error', __('处理响应数据失败:', 'aiya-framework') + e.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatus('error', __('图片上传失败:', 'aiya-framework') + error.message);
                })
                .finally(() => {
                    // 恢复按钮状态
                    uploadButton.disabled = false;
                    uploadButton.innerHTML = __('上传图片', 'aiya-framework');
                });
        });
    });
</script>