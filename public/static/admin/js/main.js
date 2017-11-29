require.config({
	baseUrl: EacooPHP.static + '/', //资源根路径
	paths: {
		'jquery': ['http://libs.baidu.com/jquery/2.0.3/jquery','assets/js/jquery-1.10.2.min'],
		'nprogress':'libs/nprogress/nprogress',
		'bootstrap':'assets/js/bootstrap.min',
		'cookie':'assets/js/jquery.cookie',
		'toaster':'assets/js/jquery.toaster',
		'slimscroll':'assets/js/jquery.slimscroll.min',
		'fastclick':'assets/js/fastclick.min',
		'adminlte_app':'admin/js/app.min',
		'nestable':'assets/js/jquery.nestableapp.min',
		'layer':'libs/layer/layer',
		'icheck':'libs/iCheck/icheck.min',
		'pjax':'assets/js/jquery.pjax',
		'common':'admin/js/common',
	},
	shim: {
		// 'jquery': {
	 //      init: function() {
	 //        return jQuery.noConflict(true);
	 //      }
	 //    },

		'bootstrap': ['jquery'],
		'cookie': ['jquery'],
		'toaster': ['jquery'],
		'slimscroll': ['jquery'],
		'nestable': ['jquery'],
		'pjax': ['jquery'],
		'toaster': ['jquery'],
		'slimscroll': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'adminlte_app': {
            deps: ['bootstrap', 'slimscroll'],
            exports: '$.AdminLTE'
        },
        'layer': {
            deps: ['jquery'],
            //exports: 'layer'
        },
        'common':{
        	deps: ['jquery'],
        	init:function() {
        		return {
        			openAttachmentLayer:openAttachmentLayer,
        			setAttachmentInputVal:setAttachmentInputVal,
        			setAttachmentMultipleVal:setAttachmentMultipleVal,
        			reloadPage:reloadPage,
        			redirect:redirect,
        			handleAjax:handleAjax,
        			highlight_subnav:highlight_subnav,
        		}
        	}
        },
	},
	waitSeconds: 30,
    charset: 'utf-8' // 文件编码
});

require(['jquery','nprogress','bootstrap','layer','toaster','icheck','common'], function ($, NProgress,undefined, layer, toaster,iCheck,common) {
	  //全局配置
	  layer.config({
	      extend: [
	          'extend/layer.ext.js' 
	      ]
	  });

	//设置全局
    window.layer = layer;
    window.toaster = toaster;
});