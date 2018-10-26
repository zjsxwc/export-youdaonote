<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 25/10/2018
 * Time: 3:20 PM
 */


$shareId = "<这里填你的目录分享id>";

/**
 * @param $url
 * @return array
 */
function getJson($url)
{
    $responseString = file_get_contents($url);
    return json_decode($responseString, true);
}

require('phpQuery/phpQuery.php');
function downloadImages($html)
{
    phpQuery::newDocument($html);
    $imgEles = pq('img');
    foreach ($imgEles as $ele) {
        /** @var DOMElement $ele */
        $src = $ele->getAttribute("src");
        list($_, $path) = explode("http://note.youdao.com/", $src);
        if (!$path) {
            continue;
        }
        $filefullPath = __DIR__ . "/" . $path;
        $dirname = dirname($filefullPath);
        @mkdir($dirname, 0777, true);
        $imageData = file_get_contents($src);
        echo "start write $filefullPath\n";
        file_put_contents($filefullPath, $imageData);
    }
}


$response = getJson(sprintf("https://note.youdao.com/yws/api/personal/share?method=get&shareKey=%s", $shareId));
$entryId = $response["entry"]["id"];

//var_dump($entryId);

$response = getJson(sprintf("https://note.youdao.com/yws/public/notebook/%s/subdir/%s", $shareId, $entryId));

$shareDirectoryName = $response[1];
/** @var array[] $articleList */
$articleList = $response[2];

//var_dump($articleList, $shareDirectoryName);

@mkdir(__DIR__ . "/" . $shareDirectoryName);
foreach ($articleList as $article) {

    $p = $article['p'];
    list($_, $_, $articleId) = explode("/", $p);

    $isArticle = isset($article['pp']['dg']);

    $response = getJson(sprintf("https://note.youdao.com/yws/public/note/%s/%s", $shareId, $articleId));
    $articleTitle = $response["tl"];
    $articleTitle = str_replace("?", "", $articleTitle);
    $articleTitle = str_replace(".", "", $articleTitle);
    $articleTitle = str_replace("..", "", $articleTitle);
    $articleTitle = str_replace("/", "", $articleTitle);
    $articleTitle = str_replace("\\", "", $articleTitle);
    $articleContent = $response["content"];

    if ($isArticle) {
        downloadImages($articleContent);
        file_put_contents(__DIR__ . "/" . $shareDirectoryName . "/" . $articleTitle . ".note.html", $articleContent);
    } else {

        $fileData = file_get_contents(sprintf("https://note.youdao.com/yws/api/personal/file/%s?method=download&shareKey=%s", $articleId, $shareId));
        file_put_contents(__DIR__ . "/" . $shareDirectoryName . "/" . $articleTitle, $fileData);
    }

//    sleep(rand(1,3));
    echo "finish " . $articleTitle . PHP_EOL;
}
