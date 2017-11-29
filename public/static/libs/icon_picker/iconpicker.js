    $.fn.iconpicker = function(options) {
        var self = this;
        return this.buttonSize = "sm", this.columns = 10, this.height = 360, this.extend(options), this.open=!1,this.templates = {
            filter: "<div class='row'><div class='col-xs-12'><div class='icon-filter-box input-group'><span class='input-group-addon'>Search: </span><input type='text' class='icon-filter form-control'></div></div></div>"
        }, this.icons = options.icons, $(this).on("click", function(e) {
            self.$el = $(e.currentTarget), $(e.currentTarget).find(".icon-menu").length ? self.closeMenu() : self.openMenu()
        }), $(this).on("click", ".icon", function(e) {
            e.stopPropagation();
            var t = $(e.currentTarget).data("icon");
            void 0 !== self.clickCallback ? self.clickCallback(t) : self.$el.find("> .form-control").val(t).focus().select(), self.closeMenu()
        }), $(this).on("click", ".icon-menu", function(e) {
            e.stopPropagation()
        }), $(this).on("keyup", ".icon-filter", function(e) {
            var t = $(e.target).val();
            self.doFilter(t)
        }),this.createMenu = function() {
            this.icons = eval(this.icons), this.$menu = $("<div>", {
                "class": "icon-menu",
                style: "height:" + this.height + "px"
            }), this.filter!==!1 && (this.$filter = $(this.templates.filter), this.$menu.append(this.$filter)), this.$container = $("<div>", {
                "class": "icon-container",
                style: "height:" + this.innerHeight + "px"
            });
            for (var i in this.icons) {
                var  button = $("<a>", {
                    "class": "icon",
                    title: this.icons[i].name,
                    "data-icon": "fa-"+this.icons[i].selector,
                    "data-filter": "fa-"+this.icons[i].filter
                });
                button.html("<i class='fa fa-" + this.icons[i].selector + "'></i>"), this.$container.append(button)
            }
            this.$menu.append(this.$container)
        }, this.openMenu = function(e) {
            this.open=!0, $(this.$el).append(this.$menu), this.resize(), this.$menu.find(".icon-filter").focus()
        }, this.closeMenu = function() {
            this.open=!1, this.$menu.detach()
        }, this.doFilter = function(e) {
            "" !== e ? ($(this).find("a.icon[data-filter*='" + e + "']").show(), $(this).find("a.icon:not([data-filter*='" + e + "'])").hide()) : $(this).find("a.icon").show()
        }, this.ucwords = function(e) {
            return (e.replace(/-/g, " ") + "").replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function(e) {
                return e.toUpperCase()
            })
        }, this.sortObj = function(e) {
            var t, i, n = [], s = {};
            for (t in e)
                n.push(t);
            for (n.sort(function(e, t) {
                return e.toLowerCase().localeCompare(t.toLowerCase())
            }), i = 0; i < n.length; i++)
                s[n[i]] = e[n[i]];
            return s
        }, this.getScrollbarWidth = function() {
            var e = $('<div style="width: 100%; height:200px;">test</div>'), t = $('<div style="width:200px;height:150px; position: absolute; top: 0; left: 0; visibility: hidden; overflow:hidden;"></div>').append(e), i = e[0], n = t[0];
            $("body").append(n);
            var s = i.offsetWidth;
            t.css("overflow", "scroll");
            var r = n.clientWidth;
            return t.remove(), s - r
        }, self.createMenu(), this
    }