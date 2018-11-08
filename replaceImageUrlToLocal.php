<?php

//把网易CDN图片地址替换为本地文件图片地址

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}


function process($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        echo "file: $file\n path: ".$src . DIRECTORY_SEPARATOR . $file."\n";
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) {
                process($src . DIRECTORY_SEPARATOR . $file);
            }
            else {
                if (endsWith($file, ".html")) {
                    $content = file_get_contents($src . DIRECTORY_SEPARATOR . $file);
                    $content = str_replace("src=\"http://note.youdao.com/yws/", "src=\"../yws/", $content);
                    file_put_contents($src . DIRECTORY_SEPARATOR . $file, $content);
                }
            }
        }
    }
    closedir($dir);
}

process(__DIR__);



