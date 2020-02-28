<?php
$contents_all = json_decode(file_get_contents('typecho_contents.json'), true)[2]['data'];
$metas_all = json_decode(file_get_contents('typecho_metas.json'), true)[2]['data'];
$relationships_all = json_decode(file_get_contents('typecho_relationships.json'), true)[2]['data'];

$post_sort = [];//文章数据合集
$pid_list = [];//文章id合集
//文章基础信息载入
foreach ($contents_all as $value) {

    $pid = $value['cid'];
    $type = $value['type'];
    if ($type == 'post_draft') {
        $type = 'post';
    }
    if($type != "post" && $type != "page")  continue;
    $time = date('Y-m-d H:i:s', $value['created']);

    array_push($pid_list, $pid);
    $post_sort[$pid] = array(
        "pid" => $pid,
        "title" => $value['title'],
        "type" => $value['type'],
        "tag" => [],
        "category" => [],
    );
}

//metas基础信息载入
$metas_sort = [];//metas数据合集
foreach ($metas_all as $value) {
    $mid = $value['mid'];
    $name = $value['name'];
    $slug = $value['slug'];
    $type = $value['type'];
    $description = $value['description'];

    $metas_sort[$mid] = array(
        "mid" => $mid,
        "name" => $name,
        "slug" => $slug,
        "type" => $type,
        "description" => $description,
    );
}

//处理tag和category
foreach ($relationships_all as $value) {
    $pid = $value['cid'];
    $mid = $value['mid'];
    if (!in_array($pid, $pid_list)) continue;
    $metas_now = $metas_sort[$mid];
    array_push($post_sort[$pid][$metas_now['type']], $metas_now['slug']);
}

$out = [];
//开始写出文件
foreach ($post_sort as $value) {

    $category = "";
    if (count($value['category']) != 0) {
        $category = $value['category'][0];
    }
    $out[$value['pid']] = array(
        "category" => $category,
        "type" => $value['type']
    );
    echo "Complete: ".$value['title']."\n";
}
echo "All files processed!";
//写入文件
$file = fopen("./post_info.json", "w");
fwrite($file, json_encode($out));