<?php
$contents_all = json_decode(file_get_contents('typecho_contents.json'), true)[2]['data'];
$metas_all = json_decode(file_get_contents('typecho_metas.json'), true)[2]['data'];
$relationships_all = json_decode(file_get_contents('typecho_relationships.json'), true)[2]['data'];

$repo = "AyagawaSeirin/Blog@gh-pages";

$post_sort = [];//文章数据合集
$pid_list = [];//文章id合集

//文章基础信息载入
foreach ($contents_all as $value) {
    if ($value['type'] != 'post' && $value['type'] != 'post_draft') continue;

    $pid = $value['cid'];
    if ($value['type'] == 'post_draft') {
        $status = 'draft';
    } else {
        $status = $value['status'];
    }
    $time = date('Y-m-d H:i:s', $value['created']);

    array_push($pid_list, $pid);
    $post_sort[$pid] = array(
        "pid" => $pid,
        "title" => $value['title'],
        "time" => $time,
        "text" => $value['text'],
        "status" => $status,
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
    //echo $metas_now['type'];
    array_push($post_sort[$pid][$metas_now['type']], $metas_now['slug']);
}
//print_r($post_sort);

//开始写出文件
foreach ($post_sort as $value) {
    $post_content = "---\ntitle: " . $value['title'] . "\ndate: " . $value['time'] . "\npid: " . $value['pid'];

    //处理tag
    $tag = "[";
    foreach ($value['tag'] as $tag_now) {
        $tag .= $tag_now . ",";
    }
    if (count($value['tag']) != 0) {
        $tag = substr($tag, 0, -1);
    }
    $tag .= "]\n";
    $post_content .= "\ntags: " . $tag;

    //处理category
//    $category = "";
//    foreach ($value['category'] as $category_now){
//        $category.="\n -[".$category_now."]";
//    }
    //hexo不支持同级分类，固只保留第一个分类确保URL可访问。
    $category = "";
    if (count($value['category']) != 0) {
        $category = $value['category'][0];
    }
    $post_content .= "\ncategories: " . $category . "\n---\n" . $value['text'];

    //选择文章写入目录
    if ($value['status'] == "publish") {
        $path = "./posts/";
    } else {
        $path = "./" . $value['status'] . "/";
    }

    //创建文章资源目录
    mkdir($path . $value['title'],0777,true);

    //处理文章图片
    $pattern = "/http(.*?)uploads\/(.*?)..\.(png|jpg)/";
    preg_match_all($pattern, $value['text'], $matches);
    foreach ($matches[0] as $img_now) {
        //先取出文件名
        $array_url = explode("/", $img_now);
        $img_name = array_pop($array_url);

        //判断文件是否为本博文件
        $right = getSubstr($img_now, "uploads");
        $img_path = "./usr/uploads" . $right;
        $new_path = $path . $value['title'] . "/" . $img_name;
        if (file_exists($img_path)) {
            //是本博文件，直接复制
            copy($img_path, $new_path);
        } else {
            //其他网站的图片，下载下来
            file_put_contents($new_path,file_get_contents($img_now));
        }

        //替换文章中的图片url
        $img_real = "/".$category."/".$value['pid']."/".$img_name;

//        方案1:利用post_asset_folder(hexo-asset-image)构造静态文件时处理图片url，直接从仓库读取
//        $post_content = str_replace($img_now,$img_name,$post_content);

//        方案2:直接上JsDelivr
        $post_content = str_replace($img_now,"https://cdn.jsdelivr.net/gh/".$repo.$img_real,$post_content);


        //写入文件
        $file = fopen($path . $value['title'] . ".md", "w");
        fwrite($file, $post_content);
    }
    echo "Complete: ".$value['title']."\n";
}
echo "All files processed!";


function getSubstr($str, $leftStr)
{
    $left = strpos($str, $leftStr);
    return substr($str, $left + strlen($leftStr));
}