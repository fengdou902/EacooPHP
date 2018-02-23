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

    //ajax table btn
    $('body').on('click','.ajax-table-btn',function (event) {
        event.preventDefault();
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
                handleBuilderListAjaxEvent($this);
            }, function(e){
                parent.layer.close(e);
            });
        } else{
            handleBuilderListAjaxEvent($this);
        }

        
    })
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
            redirect(result.url);
        }, interval);
        
    } else if (is_remove_disabled) {
        $this.removeClass('disabled').prop('disabled', false);
    } else {
        setTimeout(function () {
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

