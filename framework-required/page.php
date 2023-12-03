<?php

function dashicons_page()
{
    $file = fopen(ABSPATH . '/' . WPINC . '/css/dashicons.css', 'r') or die("Unable to open file!");

    $html = '';
    //遍历文件
    while (!feof($file)) {

        if ($line = fgets($file)) {
            print_r($line);
            $matches = preg_match_all('/.dashicons-(.*?):before/i', $line, $matches);

            if ($matches && $matches[1][0] != 'before') {
                $html .= '<p data-dashicon="dashicons-' . $matches[1][0] . '"><span class="dashicons-before dashicons-' . $matches[1][0] . '"></span> <br />' . $matches[1][0] . '</p>' . "\n";
            }
        }
    }

    fclose($file);

    echo '<code>&lt;span class="dashicons #dashicon#"&gt;&lt;/span&gt;</code>';
    echo '<div class="page-dashicons">' . $html . '</div>';
?>
    <style type="text/css">
        div.page-dashicons {
            max-width: 800px;
            float: left;
        }

        div.page-dashicons p {
            float: left;
            margin: 0px 10px 10px 0;
            padding: 10px;
            width: 70px;
            height: 70px;
            text-align: center;
            cursor: pointer;
        }

        div.page-dashicons .dashicons-before:before {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
    </style>
<?php
}

function about_page()
{
?>
    <div class="about-page">
        <h2>AIYA-Framework 设置框架</h2>
        <table class="widefat striped">
            <tbody>
                <tr>
                    <th>
                        <p>提示：</p>
                    </th>
                </tr>
                <tr>
                    <th>
                        <p>这是一个早期版本，剩余功能还需要构建。</p>
                        <p>详细文档请阅Github项目页面。</p>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}
