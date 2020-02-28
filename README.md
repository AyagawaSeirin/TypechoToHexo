# TypechoToHexo

将Typecho文章、评论数据转换到Hexo格式的PHP脚本。

代码中有详细的注释，你可以进行深度定制。

可以将文章从Typecho格式转换为Hexo格式，保留分类与标签，且可以处理图片。<br>
会将原来Typecho的cid保存到文章内的pid参数，在设置文章链接时可以直接使用:pid来载入，方便保留原来Typecho文章URL格式，如果你原来在文章URL里使用到了cid<br>
如果图片是网络图片，也会下载下来存入相应的位置，且修改文章内的图片链接。
<br><br>
评论迁移是转换到ValineJS + leancloud评论系统方案的格式。

## 环境版本

phpmyadmin: 4.7.9

typecho: 1.1 (17.10.30)

mysql: 5.6

hexo: 4.2.0

以上是我的环境。并不是说不是这个环境版本就不能用本方案，而是不保证非此版本一定能正常使用本方案。

## 准备工作

### 工作环境

请确保已安装PHP并设置好环境变量。
> 建议使用PHP7

### 导出数据库Json文件

进入phpmyadmin，选择typecho的数据库，依次将以下数据库表导出，格式为Json，放到脚本根目录。

数据库表前缀根据你的设置而异。如果你的数据库表前缀不是默认的"typecho_"，在导出到Json文件后请将文件重命名，将表前缀改为默认的"typecho_"方便脚本读取。

typecho_comments<br>
typecho_contents<br>
typecho_metas<br>
typecho_relationships<br>

### 复制文件

将你网站根目录的/usr文件夹复制到脚本根目录，目的是为了处理上传的文章附件。/usr下只需要uploads文件夹就够了。

### 资源文件夹支持

请设置配置文件`_config.yml`，将`post_asset_folder`的值设置为`true`

### 构建配置文件

为方便接下来程序的处理，先要执行另一个程序构建一个pid、category、type关系表的Json文件。

在脚本根目录执行以下指令构建配置文件：
```
php -f "./post_info.php" 
```

执行完毕后即可。

### 创建相关文件夹

程序不会自动创建文件夹，所以需要手动创建

在脚本根目录创建`posts`,`draft`,`hidden`文件夹。

其中，posts储存普通文章，draft储存草稿文章，hidden储存隐藏的文章。



至此，全部准备工作已经完成，目录下的文件结构应该如下图所示，缺一不可。

![目录结构](https://cdn.jsdelivr.net/gh/AyagawaSeirin/Assets/repo/github/TypechoToHexo/img/2.png)


## 文章迁移

编辑`post.php`文件

找到第六行左右的`$repo`变量，将内容改为你的Github Pages仓库信息，格式为`user/repo@brunch`，分支为构建的静态文件分支。<br>
可以参考默认值是如何填写的，比如我的是`AyagawaSeirin/Blog@gh-pages`。<br>
用来将图片链接换为JsDelivr链接。<br>
如果你不想使用JsDelivr，想直接从源站读取图片，请编辑`post.php`文件，找到第123~129行左右，将注释有"方案1"的代码解除注释，将注释有"方案2"的代码删除。


在脚本根目录执行以下指令执行文章迁移程序：
```
php -f "./post.php" 
```

迁移时可能会比较慢，是因为正在处理文章中的图片文件，请耐心等待。

若出现Notice报错，请无视。

迁移完毕后，根目录下posts储存普通文章，draft储存草稿文章，hidden储存隐藏的文章。

建议进行手动检查是否有出错的地方。

## 独立页面迁移

鉴于独立页面较少，没有开发独立页面迁移的程序，建议手动迁移。


## 文章评论迁移

评论迁移是转换到ValineJS + leancloud评论系统方案的格式。

在脚本根目录执行以下指令执行文章评论迁移程序：
```
php -f "./comments_post.php" 
```

迁移完毕后会将数据储存在根目录的`comments_post.json`文件内。

登录LeanCloud，进入控制台，选择你的应用，“存储”=》“导入导出”=》“数据导入”<br>
Class名称填写`Comment`，然后“选择文件”，上传刚刚的`comments_post.json`文件，单击“导入”按钮即可导入数据。

## 独立页面评论迁移

评论迁移是转换到ValineJS + leancloud评论系统方案的格式。

一次只能迁移一个独立页面，需要修改变量内容来决定当前迁移哪个独立页面。

编辑`comments_page.php`文件

找到第5~6行左右，<br>
编辑`$url`变量内容，修改为你在Hexo的页面的相对链接（不需要斜杠\），<br>
编辑`$cid`变量内容，修改为独立页面在Typecho的cid。<br>
可以参考默认值是如何填写的。

在脚本根目录执行以下指令执行独立页面评论迁移程序：
```
php -f "./comments_page.php" 
```

迁移完毕后会将数据储存在根目录的`comments_page_页面名称.json`文件内。

登录LeanCloud，进入控制台，选择你的应用，“存储”=》“导入导出”=》“数据导入”<br>
Class名称填写`Comment`，然后“选择文件”，上传刚刚生成的json文件，单击“导入”按钮即可导入数据。