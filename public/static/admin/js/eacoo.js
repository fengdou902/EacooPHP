//dom加载完成后执行的js
;
(function ($) {
    NProgress.configure({
        template: '<div class="bar" role="bar" style="background: white"><div class="peg" style="box-shadow: 0 0 10px #fff, 0 0 5px #fff;"></div></div><div class="spinner" role="spinner"><div class="spinner-icon" style="border-top-color:white;border-left-color: white"></div></div>'
    });
    if ($.support.pjax) {
        $.pjax.defaults.timeout = 3000;

        //$(document).pjax('a[target!=_blank][target!=_parent][data-pjax!=false]', '#pjax-container');
        $(document).on('click', 'a[target!=_blank][target!=_parent][data-pjax!=false]', function(event) {
            if ($(this).hasClass('ajax-post') || $(this).hasClass('ajax-get')) {
                event.preventDefault();
            }
            var container = $(this).closest('[data-pjax-container]');
            var containerSelector = '#pjax-container';
            $.pjax.click(event, {container: containerSelector})

        });

        $(document).on('submit', 'form[data-pjax=true]', function (event) {
            //隐藏返回值
            $.pjax.submit(event, '#pjax-container');
        });
        $(document).on('pjax:send', function () {
            NProgress.start();
        });
        $(document).on('ready pjax:end', function(event) {   
             $('input').iCheck('destroy');
            $('input').on('ifDestroyed', function(event){
                //console.log('yes');
            });
           
            $('input').iCheck({
              checkboxClass:'icheckbox_minimal-blue',
              radioClass:'iradio_minimal-blue',
              increaseArea:'20%' // optional
            });

            //Enable check and uncheck all functionality
            $(".checkbox-toggle,.check-all").click(function () {
              var clicks = $(this).data('clicks');
              if (clicks) {
                //Uncheck all checkboxes
                $("input[type='checkbox']").iCheck("uncheck");
                $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
              } else {
                //Check all checkboxes
                $("input[type='checkbox']").iCheck("check");
                $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
              }
              $(this).data("clicks", !clicks);
            });
            //复选框方式
              $(".checkbox-toggle .iCheck-helper").click(function () {
                  var all_checked=$('.checkbox-toggle>div.icheckbox_minimal-blue').hasClass('checked');
                  if (!all_checked) {
                    $("input[type='checkbox']").iCheck("uncheck");
                  } else{
                    $("input[type='checkbox']").iCheck("check");
                  }
              });

            $('.popover-toggle').popover();
          //$(event.target).echarts()
          //var myChart = echarts.init(document.getElementById('user_statistics'),'macarons');
        })
        $(document).on('pjax:complete', function () {
            NProgress.done();
        });
        $(document).on('pjax:timeout', function (event) {
            redirect(event.target.baseURI);
            // Prevent default timeout redirection behavior
            //event.preventDefault()
        });
        $(document).on('pjax:error', function (event) {
            
        });
        $(document).on('pjax:beforeReplace', function (contents, options) {
            //处理服务器返回的json通知
            if (options['0'].data != undefined) {
                options['0'].data = '';
            }
        });
        $(document).on('pjax:success', function (event, result, status, xhr) {
            //正则匹配JSON
            if (result.match("^\{(.+:.+,*){1,}\}$")) {
                var result = JSON.parse(result);
                if(result.code == 1){
                    updateAlert(result.msg,'success');
                }else{
                    updateAlert(result.msg,'error');
                }

                if (result.url) {
                    $.pjax({
                        url: result.url,
                        container: '#pjax-container'
                    })
                }
            }
        });
    }

    //列表全选的实现
    $('body').on('click',".check-all",function () {
        $(".ids").prop("checked", this.checked);
    });

    //刷新浏览器导航菜单同步
    var pathname = window.location.pathname;
    if (pathname=='/admin.php') {
        var search = window.location.search;
        pathname = pathname + search;
    } 
    //pathname = EacooPHP.root_domain+pathname;
    pathname = pathname;

    //列表全选的实现
    $('body').on('click',".sidebar-menu .treeview-menu a",function () {
        $('.sidebar-menu').find('ul.treeview-menu li').removeClass('active');
        $(this).parent().addClass('active');
        
        $.cookie('old_eacoo_pathname',pathname, { expires: 7, path: '/' });
    });

    var findsamehref = $('.sidebar-menu').find('a[href="' + pathname + '"]');
    if (findsamehref.length>0) {
        $.cookie('old_eacoo_pathname',pathname, { expires: 7, path: '/' });
    } else{
        var old_eacoo_pathname = $.cookie('old_eacoo_pathname');
        findsamehref = $('.sidebar-menu').find('a[href="' + old_eacoo_pathname + '"]');
    }
    findsamehref.parent().addClass('active').parent().parent().addClass('active').parent().parent().addClass('active');

    //列表全选的实现
    $('#pjax-container').on('click',".check-all",function () {
        $(".ids").prop("checked", this.checked);
    });
    $('#pjax-container').on('click',".ids",function () {
        var option = $(".ids");
        option.each(function (i) {
            if (!this.checked) {
                $(".check-all").prop("checked", false);
                return false;
            } else {
                $(".check-all").prop("checked", true);
            }
        });
    });

    //iframe打开
    $('body').on('click','[load-type=iframe]',function () {
        var target;
        var $this = $(this);
        var title = $(this).attr('title');
        if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            target = url(target,['load_type=iframe']);
            layer.open({
              type: 2,
              title: title,
              shadeClose: true,
              shade: 0.8,
              area: ['50%', '52%'],
              content:target, 
          });
        }
        return false;
    });

    //ajax get请求
    $('body').on('click','.ajax-get',function () {
        var target;
        var $this = $(this);
        var need_confirm = false;
        if ($this.hasClass('confirm')) {
            need_confirm = true;
        }

        //验证
        if (need_confirm) {
            var confirm_info = $this.attr('confirm-info');
            confirm_info = confirm_info ? confirm_info : "确认要执行该操作吗?";
            if (!updateConfirm(confirm_info)) {
                return false;
            }
        }

        if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            $.get(target).success(function (result) {
                var is_redirect = false;//是否跳转
                var is_remove_disabled = $(this).hasClass('no-refresh');

                if (result.code == 1) {
                    if (result.url) {
                        is_redirect = true;
                        updateAlert(result.msg + ' 正在自动跳转~', 'success');
                    } else {
                        updateAlert(result.msg, 'success');
                    }
                    
                } else {
                    if (result.url) {
                        is_redirect = true;
                        updateAlert(result.msg + ' 正在自动跳转~', 'warning');
                    } else {
                        updateAlert(result.msg, 'warning');
                    }

                }

                if (is_redirect==true) {
                    if ($.support.pjax) {
                        // 加载内容到指定容器
                        $.pjax({ url: result.url, container: '#pjax-container' });
                    } else{
                        redirect(result.url);
                    }
                } else if (is_remove_disabled) {
                    $(this).removeClass('disabled').prop('disabled', false);
                } else {
                    if ($.support.pjax) {
                        //重新当前页面容器的内容
                        $.pjax.reload('#pjax-container');
                    } else{
                        location.reload();
                    }
                    
                }

            });

        }
        return false;
    });

    //ajax post submit请求
    $(document).on('click','.ajax-post',function (event) {
        event.preventDefault();
        var target, query, form;
        var $this = $(this);
        var target_form = $this.attr('target-form');
        var need_confirm = false;
        if (($this.attr('type') == 'submit') || (target = $this.attr('href')) || (target = $this.attr('url'))) {
            form = $('.' + target_form);

            if (form.get(0) == undefined) {
                updateAlert('没有可操作数据。','danger');
                return false;
            } else if (form.get(0).nodeName == 'FORM') {
                if ($this.attr('url') !== undefined) {
                    target = $this.attr('url');
                } else {
                    target = form.get(0).action;
                }
                query = form.serialize();
            } else if (form.get(0).nodeName == 'INPUT' || form.get(0).nodeName == 'SELECT' || form.get(0).nodeName == 'TEXTAREA') {
                form.each(function (k, v) {
                    if (v.type == 'checkbox' && v.checked == true) {
                        need_confirm = true;
                    }
                })
                query = form.serialize();
            } else {
                query = form.find('input,select,textarea,button').serialize();
            }

            if ($this.hasClass('confirm')) {
                need_confirm = true;
            }

            //验证
            if (need_confirm) {
                var confirm_info = $(this).attr('confirm-info');
                confirm_info = confirm_info ? confirm_info : "确认要执行该操作吗?";
                if (!updateConfirm(confirm_info)) {
                    return false;
                }
            }

            if(query=='' && $(this).attr('hide-data') != 'true'){
                updateAlert('请勾选操作对象。','danger','注意');
                return false;
            }
            $this.addClass('disabled').prop('disabled', true);
            var is_pjax = $this.attr('data-pjax');
            $.post(target, query).success(function (result) {
                var is_redirect = false;//是否跳转
                var is_remove_disabled = $this.hasClass('no-refresh');
                if (result.code == 1) {
                    if (result.url) {
                        is_redirect = true;
                        updateAlert(result.msg + ' 正在自动跳转~', 'success');
                    } else {
                        updateAlert(result.msg, 'success');
                    }
                } else {
                    if (result.url) {
                        is_redirect = true;
                        updateAlert(result.msg + ' 正在自动跳转~', 'warning');
                    } else {
                        is_remove_disabled = true;
                        updateAlert(result.msg, 'warning');
                    }
                }

                if (is_redirect==true) {
                    setTimeout(function () {
                        if ($.support.pjax && is_pjax!='false') {
                            // 加载内容到指定容器
                            $.pjax({ url: result.url, container: '#pjax-container' });
                        } else{
                            redirect(result.url);
                        }
                    }, 1000);
                    
                } else if (is_remove_disabled) {
                    $this.removeClass('disabled').prop('disabled', false);
                } else {
                    setTimeout(function () {
                        if ($.support.pjax && is_pjax!='false') {
                            //重新当前页面容器的内容
                            $.pjax.reload('#pjax-container');
                        } else{
                            location.reload();
                        }
                    }, 1000);
                    
                    
                }

            });
        }
        return false;
    });

    //开启提示框
    $("[data-toggle='tooltip']").tooltip();
    
    //图标选取
    $('body').on('focus', 'input.iconpicker', function() {
        var $this = $(this);
        filter_icon($this);
    });

    //搜索功能
    $('body').on('click', '.search-btn', function() {
        var url = $(this).closest('form').attr('action');
        var query = $(this).closest('form').serialize();
        query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
        query = query.replace(/(^&)|(\+)/g, '');
        if (url.indexOf('?') > 0) {
            url += '&' + query;
        } else {
            url += '?' + query;
        }
        window.location.href = url;
        return false;
    });

     //回车搜索
    $(".search-input").keyup(function(e) {
        if (e.keyCode === 13) {
            $(".search-btn").click();
            return false;
        }
    });

    /*退出*/
      $('.loginout').click(function(){
          layer.confirm('确定要退出吗？', {icon: 3},function(){
              parent.layer.msg('退出成功!', {
                shift: 2,
                time: 1000,
                shade: [0.1,'#000'],
                end: function(){
                  window.location.href = url("admin/index/logout");
                }
              });
          });

       });

    //全局配置
  layer.config({
      extend: [
          'extend/layer.ext.js' 
      ]
  });

})(jQuery);

//清理缓存
function delcahe(){
    $.get(url('admin/index/delcache'),function(data){
      updateAlert(data.msg,'success');
    })
  }

//重置alert
window.updateAlert = function (msg_content,type,msg_title) {
    if (typeof msg_title=='undefined') {
        var msg_title;
        switch(type){
            case 'success': msg_title = "提示"; break;
            case 'warning': msg_title = "注意"; break;
            case 'danger':  msg_title = "错误"; break;
            case 'error':   msg_title = "错误"; break;
            default:        msg_title = "未知错误"; break;
        }
        
    };
    if(typeof type !='undefined')
    {
        $.toaster({ priority : type, title :msg_title, message :msg_content});
    }else {
        $.toaster({ priority : 'warning', title :msg_title, message :msg_content});
    }
};

//重置confirm
window.updateConfirm = function (confirm_info) {
    //询问框
    // layer.confirm(confirm_info, {icon: 3, title:'信息'}, function(confirm_layer){
    //     layer.close(confirm_layer);
    //     return true;
    // }, function(confirm_layer){
    //     layer.close(confirm_layer);
    //     return false;
    // });
    var result = confirm(confirm_info);
    return result;
}

/**
 * 浮动DIV定时显示提示信息,如操作成功, 失败等
 * @param string confirm_info (提示的内容)
 * @param int height 显示的信息距离浏览器顶部的高度
 * @param int time 显示的时间(按秒算), time > 0
 * @sample <a href="javascript:void(0);" onclick="showTips( '操作成功', 100, 3 );">点击</a>
 * @sample 上面代码表示点击后显示操作成功3秒钟, 距离顶部100px
 */
function newConfirm( confirm_info, height, time ){
    var windowWidth = document.documentElement.clientWidth;
    var tipsDiv = '<div class="confirmClass">' + confirm_info + '</div>';

    $( 'body' ).append( tipsDiv );
    $( 'div.confirmClass' ).css({
        'top' : 200 + 'px',
        'left' : ( windowWidth / 2 ) - ( confirm_info.length * 13 / 2 ) + 'px',
        'position' : 'fixed',
        'padding' : '20px 50px',
        'background': '#EAF2FB',
        'font-size' : 14 + 'px',
        'margin' : '0 auto',
        'text-align': 'center',
        'width' : 'auto',
        'color' : '#333',
        'border' : 'solid 1px #A8CAED',
        'opacity' : '0.90',
        'z-index' : '9999'
    }).show();
    $( 'div.confirmClass' ).fadeOut().remove();
  }    

//导航高亮
function highlight_subnav(url) {
    $('#sub_menu').find('a[href="' + url + '"]').closest('li').addClass('active');
}

/**
 * 处理ajax返回结果
 */
function handleAjax(result) {
    //如果需要跳转的话，消息的末尾附上即将跳转字样
    if (result.url) {
        result.msg += '，页面即将跳转～';
    }

    //弹出提示消息
    if (result.code) {
        updateAlert(result.msg, 'success');
    } else {
        updateAlert(result.msg, 'danger');
    }

    //需要跳转的话就跳转
    var interval = 1500;
    if (result.url == "refresh") {
        setTimeout(function () {
            location.href = location.href;
        }, interval);
    } else if (result.url) {
        setTimeout(function () {
            location.href = result.url;
        }, interval);
    }
}

//重新刷新页面，使用location.reload()有可能导致重新提交
function reloadPage(win) {
    var location = win.location;
    location.href = location.pathname + location.search;
}

/**
 * 页面跳转
 * @param url
 */
function redirect(url) {
    location.href = url;
}

function batchUrl(url, is_pajx) {
    var id = "";
    $("table .check").each(function () {
        if ($(this).prop("checked")) {
            id += $(this).val() + ",";
        }
    });
    id = id.substr(0, id.length - 1);
    if (id) {
        is_pajx = (typeof(is_pajx) == "undefined") ? true : false;
        if (is_pajx) {
            $.pjax({
                url: url + '?id=' + id,
                container: '#pjax-container',
                push: false,
            });
        } else {
            window.location.href = url + '?id=' + id;
        }
    } else {
        toastr.error("请先选择目标");
    }
}

/**
 * 模拟url函数
 * @param url
 * @param params
 * @returns {string}
 * @constructor
 */
function url(url, params, rewrite) {
    var website = EacooPHP.root_domain;
    if (window.EacooPHP.url_model == 2) {

    } else{
        
        url = url.split('/');
        if (url[0] == '' || url[0] == '@')
            url[0] = EacooPHP.root;
        if (!url[1])
            url[1] = 'Index';
        if (!url[2])
            url[2] = 'index';
        if (!url[3])
            url[3] = 'index';
        if (!url[4])
            url[4] = 'index';
        website = website + '/' + url[0] + '/' + url[1] + '/' + url[2]+ '/' + url[3]+ '/' + url[4];


        if (!rewrite) {
            website = website + '.html';
        }
    }

    website = website+ '/'  + url;
    if (params) {
        params = params.join('&');
        website = website + '?' + params;
    }

    if(typeof (window.EacooPHP.url_model)!='undefined'){
        website=website.toLowerCase();
    }
    return website;
}

/**
 * 图标选择器
 * @param  {[type]} obj [description]
 * @return {[type]} [description]
 * @date   2017-10-13
 * @author 心云间、凝听 <981248356@qq.com>
 */
function filter_icon(obj) {
    var $this = $(obj);
    var layer_url = $this.data('url');if (!layer_url) layer_url = url('admin/tools/iconPicker');
    layer.open({
          type: 2,
          title: '图标选择器',
          shadeClose: true,
          shade: 0.8,
          area: ['36%', '52%'],
          content:layer_url, 
      });
}
/**************************附件选择器弹框 start*******************************/
//打开图片选择器组件
function openAttachmentLayer(obj) {
    var $this = $(obj);
    var layer_type = $this.data('type');if (!layer_type) layer_type = 2;
    var layer_title = $this.data('title');if (!layer_title) layer_title = '图片选择器';
    var layer_url = $this.data('url');if (!layer_url) layer_url = url('admin/Attachment/attachmentLayer');
    
    layer.open({
          type: layer_type,
          title: layer_title,
          shadeClose: true,
          shade: 0.8,
          area: ['70%', '82%'],
          content:layer_url, 
      });
}

/**
 * 设置附件输入框值
 * @param {[type]} inputName 输入框名
 * @param {[type]} ids       附件IDs
 * @param {[type]} srcs      附件SRCs
 */
function setAttachmentInputVal(inputName,ids,srcs) {
    if (ids.length>0 && srcs.length>0) {
        if(window.newSetAttachmentInputVal) {
            newSetAttachmentInputVal(inputName,ids,srcs);
        } else{
            $("#"+inputName).val(ids);
            $("#"+inputName).parent().find('.popup-gallery').html(
                  '<div class="each"><i onclick="admin_image.removeImage($(this),'+ids+')" class="fa fa-times-circle remove-attachment"></i><a href="'+ srcs+'" title="点击查看大图片"><img src="'+ srcs+'"></a><div class="text-center opacity del_btn" ></div></div>'
            );
        }
        
    }
    
    layer.closeAll('iframe');
}

//多图
/**
 * 设置附件多图值
 * @param {[type]} inputName [description]
 * @param {[type]} ids       [description]
 * @param {[type]} srcs      [description]
 */
function setAttachmentMultipleVal(inputName,ids,data) {
     //插入数据ids
    var field_ids=$("#"+inputName).val();    
    $("#"+inputName).val(field_ids+ids);

    $('#'+inputName+'-gallery-box').append(data);
    layer.closeAll('iframe');
}
/**************************附件选择器弹框 end*******************************/


admin_image ={
    /**
     *
     * @param obj
     * @param attachId
     */
    removeImage: function (obj, attachId) {
        // 移除附件ID数据
        this.upAttachVal('del', attachId, obj);
        obj.parents('.each').find('img').attr('src','/static/img/noimage.gif');
        obj.parents('.each').find('.remove-attachment').remove();
    },
    /**
     * 更新附件表单值
     * @return void
     */
    upAttachVal: function (type, attachId,obj) {
        var $attach_ids = obj.parents('.controls').find('.attach');
        var attachVal = $attach_ids.val();
        var attachArr = attachVal.split(',');
        var newArr = [];
        for (var i in attachArr) {
            if (attachArr[i] !== '' && attachArr[i] !== attachId.toString()) {
                newArr.push(attachArr[i]);
            }
        }
        type === 'add' && newArr.push(attachId);
        $attach_ids.val(newArr.join(','));
        return newArr;
    }
}