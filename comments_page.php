<?php
$comments_all = json_decode(file_get_contents('typecho_comments.json'), true)[2]['data'];
$post_info = json_decode(file_get_contents('post_info.json'), true);

$url = "msg";
$cid = "78";

//屏蔽掉烦人的Notice级报错
error_reporting(E_ALL ^ E_NOTICE);

$comments_sort = [];
$results = array("results"=>[]);
foreach ($comments_all as $value) {

    $post_id = $value['cid'];
    if ($post_id != $cid) continue;


    $time = date("Y-m-d\TH:i:s.000\Z",$value['created'] - 28800);
    $nick = $value['author'];
    $mail = $value['mail'];
    $link = $value['url'];
    $ip = $value['ip'];
    $ua = $value['agent'];
    $comment = str_replace("\n","<br>",$value['text']);
    $updatedAt = $time;
    $createdAt = $time;
    $insertedAt = array(
        "__type" => "Date",
        "iso" => $time
    );

    //pid:被评论的id，几级都有可能
    //rid:父评论id，一级评论
    //二级评论，pid=rid
    //$objectId：当前评论id
    $objectId = $value['coid'];
    if ($value['parent'] == "0") {
        //一级评论
        $pid = $rid = null;
        $comment = "<p>" . $comment . "</p>";
    } else {
        //二级评论
        $pid = $value['parent'];
        $comment = '<p><a class="at" href="#' . $pid . '">@' . $comments_sort[$pid]['nick'] . ' </a> , ' . $comment . '</p>';
        if ($comments_sort[$pid]['rid'] == null) {
            //真就只到了二级评论
            $rid = $pid;
        } else {
            //好咯，是三级评论
            $rid = $comments_sort[$pid]['rid'];
        }
    }

    $comments_this = array(
        "nick" => $nick,
        "updatedAt" => $updatedAt,
        "objectId" => $objectId,
        "mail" => $mail,
        "ua" => $ua,
        "insertedAt" => $insertedAt,
        "createdAt" => $createdAt,
        "pid" => $pid,
        "link" => $link,
        "comment" => $comment,
        "url" => "/".$url."/",
        "rid" => $rid,
    );
    $comments_sort[$objectId] = $comments_this;
    array_push($results['results'],$comments_this);

}
$file = fopen("./comments_page_".$url.".json", "w");
fwrite($file, json_encode($results));
echo "Page: ".$url."'s comments processed!";