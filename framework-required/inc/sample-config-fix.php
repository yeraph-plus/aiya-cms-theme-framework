<?php
if (!defined('ABSPATH')) exit;

/**
 * 用于生成设置项内容的一些方法
 */

//引入文档页
function framework_doc_about_page()
{
    $document_file = fopen(AYF_PATH . '/document.html', 'r') or die('Unable to open file!');

    echo fread($document_file, filesize(AYF_PATH . '/document.html'));
    
    fclose($document_file);
}