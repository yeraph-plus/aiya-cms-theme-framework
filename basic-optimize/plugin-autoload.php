<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
 * ------------------------------------------------------------------------------
 * 测试用 Composer 实例中的方法
 * ------------------------------------------------------------------------------
 */

//加载库
include_once (__DIR__) . '/vendor/autoload.php';

use Overtrue\PHPOpenCC\OpenCC;
//use Overtrue\PHPOpenCC\Strategy;
use Overtrue\ChineseCalendar\Calendar;

//应用繁体转换实例
function aya_opencc_setting($content, $drive = 's2t')
{
    //传入如果不是字符串
    $content = strval($content);

    //创建转换器实例
    $converter = new OpenCC();

    //进行转换
    return $converter->convert($content, 'T2JP');
}

//应用农历转换实例
function aya_calendar_setting($content, $drive = 's2t')
{
    //传入如果不是字符串
    $content = strval($content);

    //创建转换器实例
    $calendar = new Calendar();

    //进行转换
    return '';

    //date_default_timezone_set('PRC'); 
    $result = $calendar->solar(2017, 5, 5); // 阳历
    $result = $calendar->lunar(2017, 4, 10); // 阴历
    $result = $calendar->solar(2017, 5, 5, 23); // 阳历，带 $hour 参数

}
