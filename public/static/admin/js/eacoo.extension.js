/**
 * 应用扩展
 */

//应用本地上传
function appLocalInstall(arg) {
  var uploader_localinstall= WebUploader.create({
        // 选完文件后，是否自动上传。
        auto: true,
        duplicate: true,// 同一文件是否可以重复上传
        // swf文件路径
        swf: '__PUBLIC__/libs/webuploader/Uploader.swf',
        // 文件接收服务端。
        server: url('admin/Extension/localInstall',['apptype=plugin']),
        //验证文件总数量, 超出则不允许加入队列
        fileNumLimit: 1,
        // 如果此选项为false, 则图片在上传前不进行压缩
        compress: false, 
        // 验证单个文件大小是否超出限制, 超出则不允许加入队列 
        fileSingleSizeLimit:10*1024*1024,  
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.

        //选择文件的按钮
        pick: '#app-localupload',
        // 只允许选择图片文件
        accept:{title:'plugin',extensions:'zip',mimeTypes:'application/zip'}
    });
    uploader_localinstall.on('fileQueued', function (file) {
        uploader_localinstall.upload();
    });
    /*上传成功**/
    uploader_localinstall.on('uploadSuccess', function (file, result) {
        //console.log(result);
        if (result.code==1) {
            updateAlert(result.msg,'success');
            setTimeout(function () {
                if ($.support.pjax) {
                    //重新当前页面容器的内容
                    $.pjax.reload('#pjax-container');
                } else{
                    location.reload();
                }
            }, 1000);
            
            uploader_localinstall.reset();
        } else {
            updateAlert(result.msg);
        }
    });
}

// 卸载返回
function uninstallCallBack(result)
{
    layer.closeAll('iframe');
    updateAlert(result.msg,'success');
    setTimeout(function () {
        if ($.support.pjax) {
            //重新当前页面容器的内容
            $.pjax.reload('#pjax-container');
        } else{
            location.reload();
        }
    }, 1000);
}

function purchaseApp(result) {
  //console.log(result);
  layer.open({
          title: '购买提示',
          shadeClose: true,
          shade: 0.8,
          //area: ['400px', '300px'],
          content: result.msg,
          resize: false,
          btn: ['前往购买','关闭'],
          yes: function(index, layero){
              window.open(result.url, "_blank"); 
          },
          btn2: function(index, layero){
              return false;
          }
      });
}

/**
 * 在线安装
 * @param  {[type]} argument [description]
 * @return {[type]} [description]
 * @date   2017-11-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function onlineInstall(name,app_type,install_method,only_download) {
    $.ajax({
        type: 'POST',
        url: url("admin/Extension/onlineInstall"),
        data: {
          name:name,
          apptype:app_type,
          only_download:only_download,
          install_method:install_method,
        },
        beforeSend:function(){
          layer.load(2,{shade: [0.1,'#fff']});
        },
        success:function(result){
          layer.closeAll();
          if(result.code==1){
            window.location.href = result.url; 
          } else if(result.code==2){
            eacooTokenIdentification();
          }else if(result.code==3){
            result.url='http://www.eacoo123.com/appstore_'+app_type+'/'+name;
            purchaseApp(result);
          }else{
            layer.msg(result.msg, {icon:5});
          }

        }
    });
}

/**
 * 身份验证
 * @return {[type]} [description]
 * @date   2017-11-05
 * @author 心云间、凝听 <981248356@qq.com>
 */
function eacooTokenIdentification() {
    layer.open({
          type: 2,
          title: '会员身份验证',
          shadeClose: true,
          shade: 0.8,
          area: ['400px', '300px'],
          content: url('admin/Extension/userinfo')+'?load_type=iframe',
          resize: false,
          btn: ['登录','注册账户'],
          yes: function(index, layero){
              var account = layer.getChildFrame('#inputAccount', index).val();
              var password = layer.getChildFrame('#password', index).val();
              $.post(url("admin/Extension/userinfo"),{from:'login',account:account,password:password},function(result){
                  if (result.code==1) {
                    layer.msg(result.msg, {icon:1});
                    layer.closeAll();
                    getEacooUserinfo();
                  } else{
                    layer.msg(result.msg, {icon:5});
                    
                  }
              });
          },
          btn2: function(index, layero){
              return false;
          },
          success: function (layero, index) {
              $(".layui-layer-btn1", layero).prop("href", "http://www.eacoo123.com/register.html").prop("target", "_blank");
          } 
      });
}

/**
 * 获取用户信息
 * @return {[type]} [description]
 * @date   2017-11-05
 * @author 心云间、凝听 <981248356@qq.com>
 */
function getEacooUserinfo() {
    layer.open({
          type: 2,
          title: '会员信息',
          shadeClose: true,
          shade: 0.8,
          area: ['400px', '360px'],
          content: url('admin/Extension/userinfo')+'?load_type=iframe',
          resize: false,
          btn: ['刷新','退出登录'],
          yes: function(index, layero){
              
          },
          btn2: function(index, layero){
            if(updateConfirm('您确定要退出登录吗')){
                $.post(url("admin/Extension/userinfo"),{from:'logout'},function(result){
                //var result = eval('(' + result + ')');console.log(result);
                    if (result.code==1) {
                      layer.msg(result.msg, {icon:1});
                      layer.closeAll();
                      eacooTokenIdentification();
                    } else{
                      layer.msg(result.msg, {icon:5});
                      
                    }
              });
            }
              
          },
          
      });
}

$(function () {
  //准备安装之前
  $(document).on('click','.app-install-before', function() {
      layer.closeAll();
      var app_type = $(this).data('type');
      var app_name = $(this).data('name');
      layer.open({
            type: 2,
            title: '准备安装之前',
            shadeClose: true,
            shade: 0.8,
            area: ['25%', '35%'],
            content: url('admin/'+app_type+'/installBefore')+'?name='+app_name+'&load_type=iframe', 
        });
    });
  //应用在线安装
    $(document).on('click','.app-online-install,.view-app-detail', function() {
        layer.closeAll();
        var name           = $(this).data('name');
        var app_type       = $(this).data('type');
        var install_method = $(this).data('install-method');
        if (install_method=='upgrade') {
            var btn_text_1 = '立即升级';
            var btn_text_2 = '仅下载覆盖';
        } else{
            var btn_text_1 = '直接安装';
            var btn_text_2 = '仅下载';
        };
        layer.open({
          type: 2,
          title: '准备在线安装',
          shadeClose: true,
          shade: 0.8,
          area: ['580px', '530px'],
          //content: url('admin/Extension/onlineInstallBefore',['load_type=iframe','install_method='+install_method]),
          content:EacooPHP.eacoo_api_url+'/appstore/appinfo?install_method='+install_method+'&type='+app_type+'&name='+name,
          resize: false,
          btn: [btn_text_1,btn_text_2],
          yes: function(index, layero){
              onlineInstall(name,app_type,install_method,0);
          },
          btn2: function(index, layero){
              onlineInstall(name,app_type,install_method,1);
              
          } 
      });
       
   });
    //卸载
    $(document).on('click','.app-local-uninstall', function() {
        layer.closeAll();
        var app_type = $(this).data('type');
        var app_id = $(this).data('id');
        layer.open({
              type: 2,
              title: '准备卸载',
              shadeClose: true,
              shade: 0.8,
              area: ['25%', '35%'],
              content: url('admin/'+app_type+'/uninstallBefore')+'?id='+app_id+'&load_type=iframe', 
          });
      });
    //会员信息
    $(document).on('click','#eacoo-userinfo', function() {
        layer.closeAll();
        $.ajax({
          type: 'POST',
          url: url("admin/Extension/userinfo"),
          data: {
            from:'iframe',
          },
          beforeSend:function(){
            layer.load(2,{shade: [0.1,'#fff']});
          },
          success:function(result){
            layer.closeAll();
            if(result.code==1){
              getEacooUserinfo();
            }else if(result.code==2){
                eacooTokenIdentification();
            }else{
              layer.msg(result.msg, {icon:5});
            }
            
          }
      });
        
    });
})