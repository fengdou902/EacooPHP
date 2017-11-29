(function () {

    // 获取 wangEditor 构造函数和 jquery
    var E = window.wangEditor;
    var $ = window.jQuery;

    // 通过 E.plugin 注入插件代码
    E.plugin(function () {

        // 此处的 this 指向 editor 对象本身
        var editor = this;
        var $txt = editor.$txt;

        $txt.on('click', 'img', function (e) {
            var $img = $(e.currentTarget);
            alert($img.attr('src'));
        });
    });

})();