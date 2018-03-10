$(function () {
    initAppstore(apptype);
    /**
     * 打开页面结果
     * @param  {[type]} ) {    } [description]
     * @return {[type]} [description]
     * @date   2018-03-09
     * @author 心云间、凝听 <981248356@qq.com>
     */
    $(document).on('click','.eacoo-tabs a.reset-appstore-conent', function(event) {
        event.preventDefault();
        var $this = $(this);
        //切换Tab
        $(".eacoo-tabs .nav li").removeClass('active');
        $this.parent().addClass('active');
        var action_url = $this.attr('href');
        loadAppstore(action_url);
    })

    /**
     * 应用中心分页
     * @param  {[type]} ) {    } [description]
     * @return {[type]} [description]
     * @date   2018-03-09
     * @author 心云间、凝听 <981248356@qq.com>
     */
    $(document).on('click','.appstore-page-warp ul li a', function(event) {
        event.preventDefault();
        var $this = $(this);
        //切换Tab
        $(".appstore-page-warp ul li").removeClass('active');
        $this.parent().addClass('active');
        var paged = $this.data('paged');
        loadAppstore(false,paged);
    })

  /**
   * 应用在线安装
   * @param  {[type]} ) {        layer.closeAll();        var name [description]
   * @return {[type]} [description]
   * @date   2018-02-16
   * @author 心云间、凝听 <981248356@qq.com>
   */
  $(document).on('click','.app-online-install,.view-app-detail', function() {
      layer.closeAll();
      var name           = $(this).data('name');
      var app_type       = $(this).data('type');
      var install_method = $(this).data('install-method');
      if (install_method=='upgrade') {
          var layer_btns = ['立即升级','仅下载覆盖'];
      } else if(install_method=='install') {
          var layer_btns = ['直接安装','仅下载'];
      } else{
          var layer_btns = [];
      };
      parent.layer.open({
        type: 2,
        title: '准备在线安装',
        shadeClose: true,
        shade: 0.6,
        area: ['670px', '530px'],
        //content: url('admin/Extension/onlineInstallBefore',['load_type=iframe','install_method='+install_method]),
        content:EacooPHP.eacoo_api_url+'/appstore/appinfo?install_method='+install_method+'&type='+app_type+'&name='+name+'&epv='+EacooPHP.eacoophp_version,
        resize: false,
        btn: layer_btns,
        yes: function(index, layero){
            onlineInstall(name,app_type,install_method,0);
        },
        btn2: function(index, layero){
            onlineInstall(name,app_type,install_method,1);
            
        } 
    });
     
 });

  /**
   * 准备安装之前
   * @param  {[type]} ) {      layer.closeAll();      var app_type [description]
   * @return {[type]} [description]
   * @date   2018-03-09
   * @author 心云间、凝听 <981248356@qq.com>
   */
  $(document).on('click','.app-install-before', function() {
      layer.closeAll();
      var app_type = $(this).data('type');
      var app_name = $(this).data('name');
      parent.layer.open({
            type: 2,
            title: '准备安装之前',
            shadeClose: true,
            shade: 0.6,
            area: ['25%', '35%'],
            content: url('admin/'+app_type+'/installBefore',['name='+app_name,'load_type=iframe']), 
            btn: ['提交'],
            yes: function(index, layero){
                var action_url = p_layer.getChildFrame('#action_url', index).val();
                var params = p_layer.getChildFrame('.app-form', index).serialize();
                app_form_submit(action_url,params,function(result) {
                        setTimeout(function () {
                            loadAppstore(result.url);//加载应用列表
                        }, 1000);
                  });
            }
        });
    });
    //卸载
    $(document).on('click','.app-local-uninstall', function() {
        p_layer.closeAll();
        var app_type = $(this).data('type');
        var app_id = $(this).data('id');
        p_layer.open({
              type: 2,
              title: '准备卸载',
              shadeClose: true,
              shade: 0.6,
              area: ['25%', '35%'],
              content: url('admin/'+app_type+'/uninstallBefore',['id='+app_id,'load_type=iframe']), 
              btn: ['提交'],
              yes: function(index, layero){
                  var action_url = p_layer.getChildFrame('#action_url', index).val();
                  var params = p_layer.getChildFrame('.app-form', index).serialize();
                  app_form_submit(action_url,params,function(result) {
                        setTimeout(function () {
                            loadAppstore(result.url);//加载应用列表
                        }, 1000);
                  });
              }
          });
      });
    //会员信息
    $(document).on('click','#eacoo-userinfo', function() {
        p_layer.closeAll();
        $.ajax({
          type: 'POST',
          url: url("admin/Extension/userinfo"),
          data: {
            from:'iframe',
          },
          beforeSend:function(){
            p_layer.load(2,{shade: [0.1,'#fff']});
          },
          success:function(result){
            p_layer.closeAll();
            if(result.code==1){
              getEacooUserinfo();
            } else if(result.code==2){
                eacooTokenIdentification();
            } else{
              p_layer.msg(result.msg, {icon:5});
            }
            
          }
      });
        
    });
})

//初始化应用中心
function initAppstore() {
    if (apptype!='module' && apptype!='plugin' && apptype!='theme') {
        updateAlert('类型错误','warning');
    }
    loadAppstore();//加载应用列表
    appLocalInstall();
}

/**
 * 加载应用中心
 * @param  {[type]} action_url 请求地址
 * @return {[type]} [description]
 * @date   2018-03-09
 * @author 心云间、凝听 <981248356@qq.com>
 */
function loadAppstore(action_url,paged) {
    if (!action_url) {
        var url_pa;
        if (apptype=='theme') {
            url_pa = apptype;
        } else{
            url_pa = apptype+'s';
        }
        action_url = url('admin/'+url_pa+'/index');
    }
    var paged;
    if (!paged) {
        paged=1;
    }
    $.get(action_url, {paged: paged}, function (result) {
        $('#appstore-content').html('');
        var appstore_list = template('appstore_tpl', result);
        $('#appstore-content').append(appstore_list);
    })
}

/**
 * 应用扩展
 */
var p_layer = parent.layer;
//应用本地上传
function appLocalInstall() {
      if (apptype!='module' && apptype!='plugin' && apptype!='theme') {
        updateAlert('类型错误','warning');
      }
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
    uploader_localinstall.on( 'uploadBeforeSend', function( block, data ) {  
        data.apptype = apptype;//添加参数
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
                location.reload();
            }, 1000);
            
            uploader_localinstall.reset();
        } else {
            updateAlert(result.msg);
        }
    });
}

function purchaseApp(result) {
  //console.log(result);
  parent.layer.open({
          title: '购买提示',
          shadeClose: true,
          shade: 0.6,
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
    if (install_method!='install' && install_method!='upgrade') {
        return false;
    }
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
          p_layer.load(2,{shade: [0.1,'#fff']});
        },
        success:function(result){
          p_layer.closeAll();
          if(result.code==1){
            //切换Tab
            $(".eacoo-tabs .nav li").removeClass('active');
            $(".eacoo-tabs .nav li:first").addClass('active');
            loadAppstore(result.url);//加载应用列表
            parent.loadSidebarMenus();//加载菜单
          } else if(result.code==2){
            eacooTokenIdentification();
          }else if(result.code==3){
            result.url='http://www.eacoo123.com/appstore_'+app_type+'/'+name;
            purchaseApp(result);
          }else{
            p_layer.msg(result.msg, {icon:5});
          }

        }
    });
}

/**
 * 单个应用提交表单操作
 * @param  {[type]} argument [description]
 * @return {[type]} [description]
 * @date   2018-02-08
 * @author 心云间、凝听 <981248356@qq.com>
 */
function app_form_submit(action_url,params,callback) {
  $.ajax({
        type: 'POST',
        url: action_url,
        data: params,
        beforeSend:function(){
          p_layer.load(2,{shade: [0.1,'#fff']});
        },
        success:function(result){
          p_layer.closeAll();
          if(result.code==1){
            parent.layer.msg(result.msg);
            // 回调函数
            if ($.isFunction(callback)) {
                callback(result);
            }
            parent.loadSidebarMenus();//加载菜单
          } else{
            updateAlert(result.msg,'warning');
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
    parent.layer.open({
          type: 2,
          title: '会员身份验证',
          shadeClose: true,
          shade: 0.6,
          area: ['400px', '300px'],
          content: url('admin/Extension/userinfo')+'?load_type=iframe',
          resize: false,
          btn: ['登录','注册账户'],
          yes: function(index, layero){
              var account = p_layer.getChildFrame('#inputAccount', index).val();
              var password = p_layer.getChildFrame('#password', index).val();
              $.post(url("admin/Extension/userinfo"),{from:'login',account:account,password:password},function(result){
                  if (result.code==1) {
                    p_layer.msg(result.msg, {icon:1});
                    p_layer.closeAll();
                    getEacooUserinfo();
                  } else{
                    p_layer.msg(result.msg, {icon:5});
                    
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
    parent.layer.open({
          type: 2,
          title: '会员信息',
          shadeClose: true,
          shade: 0.6,
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