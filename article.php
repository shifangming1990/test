<?php
require_once("config/common.inc.php");
$id = isset($id) ? intval($id) : 0;
$typeId = isset($typeId) ? intval($typeId) : 1;
if($id==0){
    exit();
}

if(!$_SESSION['userInfo']){
    $_SESSION['userInfo'] = $jssdk->GetUserInfo();
    header("location:?id=".$id);
}
$userInfo = $_SESSION['userInfo'];
//print_r($userInfo);

$url = $api_baseurl."/api/getArticleInfoById?id=".$id;
$res = json_decode($jssdk->httpGet($url),true);
$row = $res['result'];
//print_r($res);
if($row['contentType']==2){
    header("location:".$row['link']);
    exit();
}

$wx_sharetit = $row['title'];
$wx_sharedec = "长兴传媒集团出品";
$wx_shareurl = "http://zxcx.cxbtv.cn/app/article.php?id=".$id."&typeId=".$typeId;
$wx_sharepic = $row['litpic'][0];
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $row['title']; ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
<link rel="stylesheet" type="text/css" href="css.css?v=<?php echo time(); ?>">
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<div class="toast"><span></span></div>

<div class="content">
    <div class="main">
        <div class="article">
            <div class="text">
                <div class="t"><?php echo $row['title']; ?></div>
                <div class="d"><?php echo $row['pubTime']; ?></div>
                <?php if($row['contentType']==3 && $row['video']!=''){ ?>
                <div class="v"><video src="<?php echo $row['video']; ?>" controls autoplay playsinline x-webkit-airplay webkit-playsinline preload x5-playsinline></video></div>
                <?php } ?>
                <div class="x"><?php echo $row['body']; ?></div>
            </div>

            <div class="pinglun">
                <div class="tit">全部评论</div>
                <ul></ul>
            </div>

        </div>
    </div>
</div>

<div class="pinglunform">
    <i><input type="text" placeholder="说点什么吧..." id="content"/></i>
    <span onclick="check()">提交评论</span>
</div>

<div class="secondpinglun">
    <div class="tit">
        <div class="l"></div>
        <div class="r">
            <div class="t">
                <h3></h3>
                <i onclick="closeSecondComment()">关闭</i>
            </div>
            <div class="x"></div>
            <div class="d"></div>
        </div>
    </div>
    <ul></ul>
</div>

<script>
var typeId = <?php echo $typeId; ?>;
var rid = <?php echo $id; ?>;
var pid = 0;
var repliedUserFace = "";
var repliedUserId = 0;
var repliedUserName = "";
var repliedUserOpenId = "";
var userFace = "<?php echo $userInfo['headimgurl']; ?>";
var userName = "<?php echo $userInfo['nickname']; ?>";
var userOpenId = "<?php echo $userInfo['openid']; ?>";
var content = "";

function getFirstCommentList(){
    $(".pinglun ul").html('');
    $.ajax({
        type: "get",
        url: "<?php echo  $api_baseurl; ?>/api/selectFirstCommentList?rid="+rid+"&typeId="+typeId+"&userOpenId="+userOpenId+"&pageSize=100",
        dataType : "json",
        headers: {"Content-Type": "application/json"},
        success: function (result) {
            var res = result['data'];
            for(var i=0;i<res.length;i++){
                //console.log(res[i]);
                var html = '';
                html =  html + '<li>';
                html =  html + '<div class="l"><img src="'+res[i]['userFace']+'"/></div>';
                html =  html + '<div class="r">';
                html =  html + '<div class="t">';
                html =  html + '<h3>'+res[i]['userName']+'</h3>';
                html =  html + '<i>'+res[i]['createDatetime']+'</i>';
                html =  html + '</div>';
                html =  html + '<div class="x">'+res[i]['content']+'</div>';
                html =  html + '<div class="d">';
                var btntxt = '回复';
                if(res[i]['childCount']>0){
                    btntxt = '<span>'+res[i]['childCount']+'条回复</span>';
                }
                html =  html + '<span onclick="openSecondComment(';
                html =  html + res[i]['id']+",'"+res[i]['userFace']+"',"+res[i]['userId']+",'"+res[i]['userName']+"','"+res[i]['userOpenId']+"','"+res[i]['userFace']+"','"+res[i]['userName']+"','"+res[i]['content']+"','"+res[i]['createDatetime']+"'";
                html =  html + ')">回复</span>';
                html =  html + '</div>';
                html =  html + '</div>';
                html =  html + '</li>';
                $(".pinglun ul").append(html);
            }
        }
    });
}
getFirstCommentList();
function check(){
    content = $('#content').val();
    if(content!=""){
        var opt = {
            "typeId":typeId,
            "rid":rid,
            "pid":pid,
            "repliedUserFace":repliedUserFace,
            "repliedUserId":repliedUserId,
            "repliedUserName":repliedUserName,
            "repliedUserOpenId":repliedUserOpenId,
            "userFace":userFace,
            "userName":userName,
            "userOpenId":userOpenId,
            "content":content
        };
        //console.log(JSON.stringify(opt));
        $.ajax({
            type: "post",
            url: "<?php echo  $api_baseurl; ?>/api/increaseWeChatComment",
            dataType : "json",
            headers: {"Content-Type": "application/json"},
            data: JSON.stringify(opt),
            success: function (result) {
                //console.log(result);
                $('#content').val('');
                if(pid>0){
                    getSecondCommentList();
                }else{
                    getFirstCommentList();
                }
                $('.toast span').html("评论提交成功！");
		        $(".toast").show().delay(1000).fadeOut();
            }
        });
    }else{
        $('.toast span').html("请先填写评论！");
		$(".toast").show().delay(1000).fadeOut();
    }
}
function closeSecondComment(){
    getFirstCommentList();
    $('#content').val('');
    pid = 0;
    repliedUserFace = "";
    repliedUserId = 0;
    repliedUserName = "";
    repliedUserOpenId = "";
    $('.secondpinglun').hide();
}
function openSecondComment(a,b,c,d,e,f,g,h,i){
    console.log(a);
    console.log(b);
    console.log(c);
    console.log(d);
    console.log(e);
    console.log(f);
    console.log(g);
    console.log(h);
    console.log(i);
    pid = a;
    repliedUserFace = b;
    repliedUserId = c;
    repliedUserName = d;
    repliedUserOpenId = e;
    $('.secondpinglun .tit .l').html('<img src="'+f+'"/>');
    $('.secondpinglun .tit h3').html(g);
    $('.secondpinglun .tit .x').html(h);
    $('.secondpinglun .tit .d').html(i);
    getSecondCommentList();
    $('.secondpinglun').show();
}

function getSecondCommentList(){
    $(".secondpinglun ul").html('');
    $.ajax({
        type: "get",
        url: "<?php echo  $api_baseurl; ?>/api/selectSecondCommentList?pid="+pid+"&rid="+rid+"&typeId="+typeId+"&userOpenId="+userOpenId+"&pageSize=100",
        dataType : "json",
        headers: {"Content-Type": "application/json"},
        success: function (result) {
            var res = result['data'];
            for(var i=0;i<res.length;i++){
                //console.log(res[i]);
                var html = '';
                html =  html + '<li>';
                html =  html + '<div class="l"><img src="'+res[i]['userFace']+'"/></div>';
                html =  html + '<div class="r">';
                html =  html + '<div class="t">'+res[i]['userName']+'</div>';
                html =  html + '<div class="x">'+res[i]['content']+'</div>';
                html =  html + '<div class="d">'+res[i]['createDatetime']+'</div>';
                html =  html + '</div>';
                html =  html + '</li>';
                $(".secondpinglun ul").append(html);
            }
        }
    });
}
</script>


<?php
$signPackage = $jssdk->GetSignPackage();
?>
<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script>
wx.config({
    debug: false,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage']
});
wx.ready(function(){
    wx.onMenuShareAppMessage({
        title: "<?php echo $wx_sharetit; ?>",
        desc: "<?php echo $wx_sharedec; ?>",
        link: "<?php echo $wx_shareurl; ?>",
        imgUrl: "<?php echo $wx_sharepic; ?>",
        success: function(res){}
    });
    wx.onMenuShareTimeline({
        title: "<?php echo $wx_sharetit; ?>",
        desc: "<?php echo $wx_sharedec; ?>",
        link: "<?php echo $wx_shareurl; ?>",
        imgUrl: "<?php echo $wx_sharepic; ?>",
        success: function(res){}
    });
});
</script>


</body>
</html>