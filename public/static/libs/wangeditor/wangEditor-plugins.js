/**
 * 添加全屏预览
 */
window.wangEditor.fullscreen = {
    // editor create之后调用
    init: function(editorSelector){
        $(editorSelector + " .w-e-toolbar").append('<div class="w-e-menu"><i class="_wangEditor_btn_fullscreen" onclick="window.wangEditor.fullscreen.toggleFullscreen(\'' + editorSelector + '\')">全屏</i></div>');
    },
    toggleFullscreen: function(editorSelector){
        $(editorSelector).toggleClass('fullscreen-editor');
        if($(editorSelector + ' ._wangEditor_btn_fullscreen').text() == '全屏'){
            $(editorSelector + ' ._wangEditor_btn_fullscreen').text('退出全屏');
        }else{
            $(editorSelector + ' ._wangEditor_btn_fullscreen').text('全屏');
        }
    }
};

/**
 * 添加预览&源码
 */
window.wangEditor.switchCode = {
    // editor create之后调用
    init: function(editorSelector){
        $(editorSelector + " .w-e-toolbar").append('<div class="w-e-menu"><i class="w-e-btn-switchCode" onclick="window.wangEditor.switchCode.toggleCode(\'' + editorSelector + '\')">源码</i></div>');
    },
    toggleCode: function(editorSelector){
        $(editorSelector).toggleClass('switchCode-editor');
        if($(editorSelector + ' .w-e-btn-switchCode').text() == '源码'){
            $(editorSelector + ' .w-e-btn-switchCode').text('预览');
        }else{
            $(editorSelector + ' .w-e-btn-switchCode').text('源码');
        }
    }
};

/**
 * 添加图片选择器
 */
window.wangEditor.picturesModal = {
    // editor create之后调用
    init: function(editorSelector,url){
        $(editorSelector + " .w-e-toolbar").append('<div class="w-e-menu"><i class="w-e-btn-picturesModal" data-url="'+url+'" data-gettype="multiple" data-from="wangeditor" onclick="openAttachmentLayer(this);" >多图</i></div>');
    },
};

/**
 * 添加符号
 */
window.wangEditor.symbol = {
    // editor create之后调用
    init: function(editorSelector){
        $(editorSelector + " .w-e-toolbar").append('<div class="w-e-menu"><span class="wangeditor-menu-img-omega" onclick="window.wangEditor.symbol.toggleSymbol(\'' + editorSelector + '\')">符号</span></div>');
    },
    toggleSymbol: function(editorSelector){
        $(editorSelector).toggleClass('symbol-menu');
        // 要插入的符号（可自行添加）
        var symbols = ['∑', '√', '∫', '∏', '≠', '♂', '♀','♞','♘', '☫', '♚', '☃', '☸', '〠', '☊', '☋' ,'❡', '۩', '♤', '♠', '♧', '♣', '☜' ,'☞', '☎', '☏', '☃', 'ↂ', '☂', '☽', '☾', '㍿', '№',  '™', '℗', 'ஐ', '℡', '❄', '❅', '❆', '❇', '卍' ,'卐', '〄','①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '⑩', '⑪', '⑫', '⑬', '⑭', '⑮', '⑯', '⑰', '⑱', '⑲', '⑳', '⓪','❶', '❷', '❸', '❹', '❺', '❻', '❼', '❽', '❾', '❿', '⑴', '⑵', '⑶', '⑷', '⑸', '⑹', '⑺', '⑻', '⑼', '⑽' ,'㈠', '㈡', '㈢', '㈣', '㈤', '㈥', '㈦', '㈧', '㈨', '㈩', '㊀','㊁', '㊂', '㊃', '㊄', '㊅', '㊆','㊇', '㊈', '㊉', '♫', '♬', '♪', '♩', '♭', '♪' ]

        // panel 内容
        var $container = $('<div></div>');
        $.each(symbols, function (k, value) {
            $container.append('<a href="#" style="display:inline-block;margin:5px;">' + value + '</a>');
        });

        // 插入符号的事件
        $container.on('click', 'a', function (e) {
            var $a = $(e.currentTarget);
            var s = $a.text();

            // 执行插入的命令
            editor.command(e, 'insertHtml', s);
        });

        // 添加panel
        // menu.dropPanel = new E.DropPanel(editor, menu, {
        //     $content: $container,
        //     width: 350
        // });
    }
};
