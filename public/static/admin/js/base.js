//dom加载完成后执行的js
//只在iframe框架中加载
;
(function ($) {
	//列表全选的实现
    $('body').on('click',".check-all",function () {
        $(".ids").prop("checked", this.checked);
    });
    $('body').on('click',".ids",function () {
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
    //icheck
	// $('input').iCheck({
	//       checkboxClass:'icheckbox_minimal-blue',
	//       radioClass:'iradio_minimal-blue',
	//       increaseArea:'20%' // optional
 //    });
 //    $(".checkbox-toggle,.check-all").click(function () {
 //          var clicks = $(this).data('clicks');
 //          if (clicks) {
 //            //Uncheck all checkboxes
 //            $("input[type='checkbox']").iCheck("uncheck");
 //            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
 //          } else {
 //            //Check all checkboxes
 //            $("input[type='checkbox']").iCheck("check");
 //            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
 //          }
 //          $(this).data("clicks", !clicks);
 //    });
 //    $(".checkbox-toggle .iCheck-helper").click(function () {
 //          var all_checked=$('.checkbox-toggle>div.icheckbox_minimal-blue').hasClass('checked');
 //          if (!all_checked) {
 //            $("input[type='checkbox']").iCheck("uncheck");
 //          } else{
 //            $("input[type='checkbox']").iCheck("check");
 //          }
 //      });
    //表单验证器
   // Custom theme
    $.validator.setTheme('bootstrap', {
        validClass: 'has-success',
        invalidClass: 'has-error',
        bindClassTo: '.form-group',
        formClass: 'n-default n-bootstrap',
        msgClass: 'n-right'
    });

    //ajax table btn适用于基于bootstrap-table页面
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