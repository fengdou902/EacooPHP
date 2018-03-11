//dom加载完成后执行的js
;
(function ($) {
    //收藏菜单
    $('body').on('click', '.eacoo-menu-collect', function() {
        var $this = $(this);
        $.get(url("admin/menu/toggleCollect"),{title:$this.data('title'),url:$this.data('url')}).success(function (result) {
            //console.log(result);
            parent.loadTopMenus();
            if (result.code==1) {
                $this.children('i').attr('class','fa fa-star');
            } else if(result.code==2){
                $this.children('i').attr('class','fa fa-star-o');
            } else{
                updateAlert(result.msg, 'warning');
            }
        })
    })

    //ajax get请求
    $('body').on('click','.ajax-get',function () {
        event.preventDefault();
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
            parent.layer.confirm(confirm_info, {offset: 't',icon: 3, title:'询问',shadeClose: true,shade: 0.5,}, function(e){
                parent.layer.close(e);
                if ((target = $this.attr('href')) || (target = $this.attr('url'))) {
                    $.get(target).success(function (result) {
                        handleAjax(result,$this);
                    });
                }
            }, function(e){
                parent.layer.close(e);
            });
        } else{
            if ((target = $this.attr('href')) || (target = $this.attr('url'))) {
                $.get(target).success(function (result) {
                    handleAjax(result,$this);
                });
            }
        }


    });

    //ajax post submit请求
    $('body').on('click','.ajax-post',function (event) {
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
                parent.layer.confirm(confirm_info, {offset: 't',icon: 3, title:'询问',shadeClose: true,shade: 0.5,}, function(e){
                    parent.layer.close(e);
                    if(query=='' && $(this).attr('hide-data') != 'true'){
                        updateAlert('请勾选操作对象。','danger','注意');
                        return false;
                    }
                    $this.addClass('disabled').prop('disabled', true);
                    $.post(target, query).success(function (result) {
                        handleAjax(result,$this);
                    });
                }, function(e){
                    parent.layer.close(e);
                });
            } else{
                if(query=='' && $(this).attr('hide-data') != 'true'){
                    updateAlert('请勾选操作对象。','danger','注意');
                    return false;
                }
                $this.addClass('disabled').prop('disabled', true);
                $.post(target, query).success(function (result) {
                    handleAjax(result,$this);
                });
            }
  
        }
        return false;
    });

})(jQuery);

/**
 * 处理BuilderListAjax数据
 * @param  {[type]} argument [description]
 * @return {[type]} [description]
 * @date   2018-02-19
 * @author 心云间、凝听 <981248356@qq.com>
 */
function handleBuilderListAjaxEvent(object) {
    var $this = object;
    var target = $this.attr('href');
    var getSelectRows = $table.bootstrapTable('getSelections');
    var row_len = getSelectRows.length;
    if (row_len<1) {
        updateAlert('没有可操作数据。','danger');
        return false;
    }
    var result=[];
    var key = $this.attr('primary-key');//主键
    for (var i = 0; i < getSelectRows.length; i++) {
        result[i]=getSelectRows[i][key];
    }
    console.log(getSelectRows);
    $this.addClass('disabled').prop('disabled', true);
    $.post(target, {ids:result}).success(function (result) {
        handleAjax(result,$this);
    });
}

/**
 * 处理ajax返回结果
 */
function handleAjax(result,object) {
    var $this = object;
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

    //需要跳转的话就跳转
    var interval = 1500;
    if (is_redirect==true) {
        setTimeout(function () {
            //是否关闭layer_iframe
            if ($this.hasClass('close_layer_iframe')) {
                parent.layer.closeAll('iframe');
            }
            if ($this.hasClass('is_iframe')) {
                window.parent.redirect(result.url);
            } else{
                redirect(result.url);
            }
            
        }, interval);
        
    } else if (is_remove_disabled) {
        //是否关闭layer_iframe
        if ($this.hasClass('close_layer_iframe')) {
            parent.layer.closeAll('iframe');
        }
        $this.removeClass('disabled').prop('disabled', false);
    } else {
        setTimeout(function () {
            //是否关闭layer_iframe
            if ($this.hasClass('close_layer_iframe')) {
                parent.layer.closeAll('iframe');
            }
            location.reload();
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
    var url_model = window.EacooPHP.url_model;
    if (url_model == 2) {
        website = website+'?s=';
    } 
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

    website = website+ '/'  + url;
    if (params) {
        params = params.join('&');
        //设置分割符，主要解决nginx兼容问题
        if (url_model == 2) {
            var delimiter = '&';
        } else{
            var delimiter = '?';
        }
        
        website = website + delimiter + params;
    }

    if(typeof (window.EacooPHP.url_model)!='undefined'){
        website = website.toLowerCase();
    }
    return website;
}

//重置alert
window.updateAlert = function (message,type,title) {
    if (typeof title=='undefined') {
        var title;
        switch(type){
            case 'success': title = "提示"; break;
            case 'warning': title = "注意"; break;
            case 'danger':  title = "错误"; break;
            case 'error':   title = "错误"; break;
            default:        title = "未知错误"; break;
        }
        
    };
    if(typeof type !='undefined')
    {
        $.toaster({ priority : type, title :title, message :message});
    }else {
        $.toaster({ priority : 'warning', title :title, message :message});
    }
};   

//重置confirm
window.updateConfirm = function (confirm_info) {
    //询问框
    // var result = parent.layer.confirm(confirm_info, {offset: 't',icon: 3, title:'信息',shadeClose: true,shade: 0.5,}, function(e){
    //     parent.layer.close(e);
    //     return true;
    // }, function(e){
    //     parent.layer.close(e);
    //     return false;
    // });
    var result = confirm(confirm_info);
    return result;
}

/**************************附件选择器弹框 start*******************************/
/**
 * 打开图片选择器组件
 * @param  {[type]} obj [description]
 * @return {[type]} [description]
 * @date   2018-03-02
 * @author 心云间、凝听 <981248356@qq.com>
 */
function openAttachmentLayer(obj) {
    var $this = $(obj);
    var gettype = $this.data('gettype');if (!gettype) gettype = 'single';//选取类型
    var from = $this.data('from');//来源标识，如：wangeditor
    var layer_type = $this.data('type');if (!layer_type) layer_type = 2;
    var layer_title = $this.data('title');if (!layer_title) layer_title = '图片选择器';
    var layer_url = $this.data('url');if (!layer_url) layer_url = url('admin/upload/attachmentLayer');
    
    var win = window;
    window.parent.layer.open({
          type: layer_type,
          title: layer_title,
          shadeClose: true,
          shade: 0.6,
          area: ['62%', '82%'],
          content:layer_url,
          btn: ['确定','关闭'],
            yes: function(index, layero){

                var p_layer    = parent.layer;
                var input_name = p_layer.getChildFrame('#input_name', index).val();//输入项
                var ids        = p_layer.getChildFrame('#attachment_ids', index).val();
                var srcs       = p_layer.getChildFrame('#attachment_srcs', index).val();
                if (gettype=='single') {
                    win.setAttachmentInputVal(input_name,ids,srcs);
                } else if(gettype=='multiple'){
                    if (from=='wangeditor') {
                        var nolayout=1;
                    } else{
                        var nolayout=0;
                    }
                    $.get(url('admin/Upload/getViewAttachmentHtml'), {ids:ids,nolayout:nolayout}, function (content) {
                        if (from=='wangeditor') {
                            EditorObj = eval('editor_'+input_name);//字符串转换成变量
                            //EditorObj是定义在wangeditor定义的编辑器对象
                            //win.EditorObj.txt.append(content);
                            win.EditorObj.cmd.do('insertHTML', content)
                        } else{
                            win.setAttachmentMultipleVal(input_name,ids,content);
                        }
                        
                    })
                }
                
                p_layer.closeAll('iframe');
            }
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

}

//多图
/**
 * 设置附件多图值
 * @param {[type]} inputName [description]
 * @param {[type]} ids       [description]
 * @param {[type]} srcs      [description]
 */
function setAttachmentMultipleVal(inputName,ids,content) {
     //插入数据ids
    var field_ids = $("#"+inputName).val();    
    $("#"+inputName).val(field_ids+ids);

    $('#'+inputName+'-gallery-box').addClass('gallery-box-bg').append(content);
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