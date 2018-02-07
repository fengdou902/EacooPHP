//dom加载完成后执行的js
;
(function ($) {
    
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
            var is_pjax = $this.attr('data-pjax');
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
                    setTimeout(function () {
                        redirect(result.url);
                    }, 2000);
                } else if (is_remove_disabled) {
                    $(this).removeClass('disabled').prop('disabled', false);
                } else {
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }

            });

        }
        return false;
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
                if (!updateConfirm(confirm_info)) {
                    return false;
                }
            }

            if(query=='' && $(this).attr('hide-data') != 'true'){
                updateAlert('请勾选操作对象。','danger','注意');
                return false;
            }
            $this.addClass('disabled').prop('disabled', true);
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
                        redirect(result.url);
                    }, 2000);
                    
                } else if (is_remove_disabled) {
                    $this.removeClass('disabled').prop('disabled', false);
                } else {
                    setTimeout(function () {
                        location.reload();
                    }, 2000);

                }

            });
        }
        return false;
    });

    //ajax table btn
    $('body').on('click','.ajax-table-btn',function (event) {
        event.preventDefault();
        var $this = $(this);
        var need_confirm = false;
        var target = $this.attr('href');

        var getSelectRows = $table.bootstrapTable('getSelections');
        var row_len = getSelectRows.length;
        if (row_len<1) {
            updateAlert('没有可操作数据。','danger');
            return false;
        }
        var result=[];
        var key = $this.data('primary-key');
        for (var i = 0; i < getSelectRows.length; i++) {
            result[i]=getSelectRows[i]['id'];
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
        $this.addClass('disabled').prop('disabled', true);
        $.post(target, {ids:result}).success(function (result) {
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
                    redirect(result.url);
                }, 2000);
                
            } else if (is_remove_disabled) {
                $this.removeClass('disabled').prop('disabled', false);
            } else {
                setTimeout(function () {
                    location.reload();
                }, 2000);

            }

        });
    })
})(jQuery);
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
        website = website.toLowerCase();
    }
    return website;
}