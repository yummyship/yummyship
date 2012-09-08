/**
 * scrollspy
 */
(function($, window, document, undefined) {
    $.fn.extend({
        scrollspy: function(options) {
            var defaults = {
                min: 0,
                max: 0,
                mode: "vertical",
                buffer: 0,
                container: window,
                onEnter: options.onEnter ? options.onEnter: [],
                onLeave: options.onLeave ? options.onLeave: [],
                onTick: options.onTick ? options.onTick: []
            };
            var options = $.extend({},
            defaults, options);
            return this.each(function(i) {
                var element = this;
                var o = options;
                var $container = $(o.container);
                var mode = o.mode;
                var buffer = o.buffer;
                var enters = leaves = 0;
                var inside = false;
                $container.bind("scroll",
                function(e) {
                    var position = {
                        top: $(this).scrollTop(),
                        left: $(this).scrollLeft()
                    };
                    var xy = mode == "vertical" ? position.top + buffer: position.left + buffer;
                    var max = o.max;
                    var min = o.min;
                    if ($.isFunction(o.max)) max = o.max();
                    if ($.isFunction(o.min)) min = o.min();
                    if (max == 0) max = mode == "vertical" ? $container.height() : $container.outerWidth() + $(element).outerWidth();
                    if (xy >= o.min && xy <= max) {
                        if (!inside) {
                            inside = true;
                            enters++;
                            $(element).trigger("scrollEnter", {
                                position: position
                            });
                            if ($.isFunction(o.onEnter)) o.onEnter(element, position)
                        }
                        $(element).trigger("scrollTick", {
                            position: position,
                            inside: inside,
                            enters: enters,
                            leaves: leaves
                        });
                        if ($.isFunction(o.onTick)) o.onTick(element, position, inside, enters, leaves)
                    } else if (inside) {
                        inside = false;
                        leaves++;
                        $(element).trigger("scrollLeave", {
                            position: position,
                            leaves: leaves
                        });
                        if ($.isFunction(o.onLeave)) o.onLeave(element, position)
                    }
                })
            })
        }
    })
})(jQuery, window); 

/**
 * Drop down Menu
 */
(function($) {
	$.fn.dropdownMenu = function() {
		this.each(function() {
			var submenus = $(this).find("ul");
			submenus.each(function() {
				var submenu = $(this);
				submenu.parent().hover(function() {
					if (!submenu.hasClass("blocked")) {
						$(this).addClass("open");
						submenu.addClass("open")
					}
				}, function() {
					if (!submenu.hasClass("pinned")) {
						$(this).removeClass("open");
						submenu.removeClass("open")
					}
				});
				submenu.parent().click(function() {
					if (submenu.hasClass("pinned")) {
						$(this).removeClass("open");
						submenu.removeClass("open").removeClass("pinned");
						submenus.removeClass("blocked")
					} else {
						$(this).addClass("open");
						submenu.addClass("pinned").addClass("open");
						submenus.not(submenu).removeClass("open").removeClass("pinned").addClass("blocked");
						submenus.parent().not(this).removeClass("open")
					}
				})
			})
		});
		return this
	}
})(jQuery);

/*
 * TipTip
 * www.drewwilson.com
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 * Version 1.3   -   Updated: Mar. 23, 2010
 *
 */
(function($){
	$.fn.tipTip = function(options) {
		var defaults = { 
			activation: "hover",
			keepAlive: false,
			maxWidth: "200px",
			edgeOffset: 3,
			defaultPosition: "bottom",
			delay: 400,
			fadeIn: 200,
			fadeOut: 200,
			attribute: "title",
			content: false, // HTML or String to fill TipTIp with
		  	enter: function(){},
		  	exit: function(){}
	  	};
	 	var opts = $.extend(defaults, options);
	 	
	 	// Setup tip tip elements and render them to the DOM
	 	if($("#tiptip_holder").length <= 0){
	 		var tiptip_holder = $('<div id="tiptip_holder" style="max-width:'+ opts.maxWidth +';"></div>');
			var tiptip_content = $('<div id="tiptip_content"></div>');
			var tiptip_arrow = $('<div id="tiptip_arrow"></div>');
			$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')));
		} else {
			var tiptip_holder = $("#tiptip_holder");
			var tiptip_content = $("#tiptip_content");
			var tiptip_arrow = $("#tiptip_arrow");
		}
		
		return this.each(function(){
			var org_elem = $(this);
			if(opts.content){
				var org_title = opts.content;
			} else {
				var org_title = org_elem.attr(opts.attribute);
			}
			if(org_title != ''){
				if(!opts.content){
					org_elem.removeAttr(opts.attribute); //remove original Attribute
				}
				var timeout = false;
				
				if(opts.activation == "hover"){
					org_elem.hover(function(){
						active_tiptip();
					}, function(){
						if(!opts.keepAlive){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.hover(function(){}, function(){
							deactive_tiptip();
						});
					}
				} else if(opts.activation == "focus"){
					org_elem.focus(function(){
						active_tiptip();
					}).blur(function(){
						deactive_tiptip();
					});
				} else if(opts.activation == "click"){
					org_elem.click(function(){
						active_tiptip();
						return false;
					}).hover(function(){},function(){
						if(!opts.keepAlive){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.hover(function(){}, function(){
							deactive_tiptip();
						});
					}
				}
			
				function active_tiptip(){
					opts.enter.call(this);
					tiptip_content.html(org_title);
					tiptip_holder.hide().removeAttr("class").css("margin","0");
					tiptip_arrow.removeAttr("style");
					
					var top = parseInt(org_elem.offset()['top']);
					var left = parseInt(org_elem.offset()['left']);
					var org_width = parseInt(org_elem.outerWidth());
					var org_height = parseInt(org_elem.outerHeight());
					var tip_w = tiptip_holder.outerWidth();
					var tip_h = tiptip_holder.outerHeight();
					var w_compare = Math.round((org_width - tip_w) / 2);
					var h_compare = Math.round((org_height - tip_h) / 2);
					var marg_left = Math.round(left + w_compare);
					var marg_top = Math.round(top + org_height + opts.edgeOffset);
					var t_class = '';
					var arrow_top = '';
					var arrow_left = Math.round(tip_w - 12) / 2;

                    if(opts.defaultPosition == "bottom"){
                    	t_class = "_bottom";
                   	} else if(opts.defaultPosition == "top"){ 
                   		t_class = "_top";
                   	} else if(opts.defaultPosition == "left"){
                   		t_class = "_left";
                   	} else if(opts.defaultPosition == "right"){
                   		t_class = "_right";
                   	}
					
					var right_compare = (w_compare + left) < parseInt($(window).scrollLeft());
					var left_compare = (tip_w + left) > parseInt($(window).width());
					
					if((right_compare && w_compare < 0) || (t_class == "_right" && !left_compare) || (t_class == "_left" && left < (tip_w + opts.edgeOffset + 5))){
						t_class = "_right";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left = -12;
						marg_left = Math.round(left + org_width + opts.edgeOffset);
						marg_top = Math.round(top + h_compare);
					} else if((left_compare && w_compare < 0) || (t_class == "_left" && !right_compare)){
						t_class = "_left";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left =  Math.round(tip_w);
						marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
						marg_top = Math.round(top + h_compare);
					}

					var top_compare = (top + org_height + opts.edgeOffset + tip_h + 8) > parseInt($(window).height() + $(window).scrollTop());
					var bottom_compare = ((top + org_height) - (opts.edgeOffset + tip_h + 8)) < 0;
					
					if(top_compare || (t_class == "_bottom" && top_compare) || (t_class == "_top" && !bottom_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_top";
						} else {
							t_class = t_class+"_top";
						}
						arrow_top = tip_h;
						marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
					} else if(bottom_compare | (t_class == "_top" && bottom_compare) || (t_class == "_bottom" && !top_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_bottom";
						} else {
							t_class = t_class+"_bottom";
						}
						arrow_top = -12;						
						marg_top = Math.round(top + org_height + opts.edgeOffset);
					}
				
					if(t_class == "_right_top" || t_class == "_left_top"){
						marg_top = marg_top + 5;
					} else if(t_class == "_right_bottom" || t_class == "_left_bottom"){		
						marg_top = marg_top - 5;
					}
					if(t_class == "_left_top" || t_class == "_left_bottom"){	
						marg_left = marg_left + 5;
					}
					tiptip_arrow.css({"margin-left": arrow_left+"px", "margin-top": arrow_top+"px"});
					tiptip_holder.css({"margin-left": marg_left+"px", "margin-top": marg_top+"px"}).attr("class","tip"+t_class);
					
					if (timeout){ clearTimeout(timeout); }
					timeout = setTimeout(function(){ tiptip_holder.stop(true,true).fadeIn(opts.fadeIn); }, opts.delay);	
				}
				
				function deactive_tiptip(){
					opts.exit.call(this);
					if (timeout){ clearTimeout(timeout); }
					tiptip_holder.fadeOut(opts.fadeOut);
				}
			}				
		});
	}
})(jQuery); 
/**
 * pop modal
 */
(function(j) {
	j.fn.error = function(m) {
		this.each(function() {
			var n = j(this);
			if (!n.data("errorBinded")) {
				//n.keyup
				n.click(function() {
					j(".text-error", j(this).removeClass("input-error").parent()).remove()
				});
				n.data("errorBinded", 1)
			}
			j(".text-error", n.parent()).remove();
			j('<span class="text-error">' + m + "</span>").insertAfter(n.addClass("input-error"))
		})
	};
	j.fn.modal = function(v) {
		var n = j.extend({
			title: '',
			onShow: null,
			auto: false,
			action: '',
			cancle: "Cancle",
			data: null,
			onAction: null,
			onClose: null
		}, v),
			o = j('<div class="pop-overlay"></div>').appendTo(document.body),
			u = this;
		o.css({
			opacity: 0.5,
			position: "absolute",
			backgroundColor: "#000000",
			left: 0,
			top: 0,
			width: "100%",
			height: j(document).height(),
			"z-index": 10000
		});
		var m = j('<div class="pop-window' + (n.auto ? " pop-auto" : '') + '"></div>').appendTo(document.body).css("position", "absolute");
		var r = j('<div class="pop-head"><a class="i-cancel close" href="#">&#10005;</a><h4>' + n.title + "</h4></div>").appendTo(m),
			q = j('<div class="pop-body form"></div>').append(u).appendTo(m),
			s = j('<div class="pop-foot form-action"><input type="button" class="btn btn-medium cancel" value="' + n.cancle + '" />' + (n.action ? '<input type="button" class="btn btn-medium action" value="' + n.action + '" />' : '') + "</div>").appendTo(m);
		m.css({
			top: j(window).scrollTop() + Math.ceil((j(window).height() - j(u).outerHeight()) / 3),
			left: Math.ceil((j(document).width() - j(u).outerWidth()) / 2),
			"z-index": 10001
		}).bind("close", {
			container: m
		}, function(t) {
			o.remove();
			m.remove();
			if (n.onClose) {
				n.onClose.call(t.data.container)
			}
		});
		if ("function" == typeof(n.onShow)) {
			n.onShow.call(u, m)
		}
		if ("function" == typeof(n.onAction)) {
			j(".action", s).bind("click", {
				container: m,
				body: u,
				callback: n.onAction,
				data: n.data
			}, function(t) {
				t.data.callback.call(t.data.body, t.data.container, t.data.data);
				return false
			})
		}
		function p(t) {
			t.data.container.trigger("close");
			return false
		}
		j(".close", r).bind("click", {
			container: m
		}, p);
		j(".cancel", s).bind("click", {
			container: m
		}, p);
		return this
	};
	j.fn.uploadFile = function(C) {
		var q = {
				uploadUrl: siteUrl + 'api/upload',
				fetchUrl:  siteUrl + 'api/upload',
			},
			p = j.extend(q, C),
			T = j(this),
			G = "upload",
			D = false,
			E = j('<p class="hor-tabs"><a href="#" rel="tab-upload" class="current">Local File</a>\
					<i></i><a href="#" rel="tab-fetch">Remote address</a></p><p id="tab-upload" class="file-upload relative">\
					<input type="file" name="image" class="file" /><input type="text" class="text-28" disabled /> \
					<a href="#" class="button btn-regular">Select</a></p>\
					<p id="tab-fetch" class="relative" style="display:none"><input type="url" name="image" class="text-28" /></p>').modal({
				title: "Insert Image",
				action: "Upload",
				onShow: function(H) {
					var uploadType = '';
					if (T.hasClass('cover-add')) {
						uploadType = 'cover';
					}else if (T.hasClass('steps-image-add')) {
						uploadType = 'steps';
					}else if (T.hasClass('avatar-add')) {
						uploadType = 'avatar';
					}
					j(".hor-tabs a", H).click(function() {
						j(".hor-tabs a", H).removeClass("current");
						j(this).addClass("current");
						if ("tab-upload" == j(this).attr("rel")) {
							j("#tab-upload", H).show();
							j("#tab-fetch", H).hide();
							G = "upload"
						} else {
							j("#tab-fetch", H).show();
							j("#tab-upload", H).hide();
							G = "fetch";
							if (!D) {
								D = true
							}
						}
						return false
					});
					j(".pop-foot .action", H).click(function(I) {
						if ("fetch" == G) {
							j("#tab-fetch .text-28").addClass("loading");
							j("#tab-fetch .text-28", H).trigger("keyup");
							j.post(p.fetchUrl, {
								url: j("#tab-fetch .text-28", H).val(),
								action: 'fetch',
								uploadType: uploadType
							}, function(K) {
								j("#tab-fetch .text-28").removeClass("loading");
								if (!K.status) {
									H.trigger("close");
									switch (K.data.uploadType) {
										case 'cover':
										case 'steps':
											j(C).parent().find('input').val(K.data.fileName);
											j(C).hide();
											j(C).parent().append('<img src="' + K.data.permanlink + '" />');
											j(C).parent().find('a.delete').show();
											break;
									}
								} else {
									j("#tab-fetch .text-28", H).error(K.data)
								}
							}, "json");
							I.stopImmediatePropagation();
							return false
						}
						j("#tab-upload .text-28").addClass("loading");
					});
					j(".file", H).frameFileUpload({
						uploadUrl: p.uploadUrl,
						uploadType: uploadType,
						trigger: j(".pop-foot .action", H),
						onChange: function() {
							j("#tab-upload .text-28", H).val(j(this).val())
						},
						onUpload: function() {
							j("#tab-upload .text-28", H).trigger("keyup")
						},
						onComplete: function(J) {
							j("#tab-upload .text-28").removeClass("loading");
							if (!J.status) {
								H.trigger("close");
								switch (J.data.uploadType) {
									case 'cover':
									case 'steps':
										j(C).parent().find('input').val(J.data.fileName);
										j(C).hide();
										j(C).parent().append('<img src="' + J.data.permanlink + '" />');
										j(C).parent().find('a.delete').show();
										break;
								}
							} else {
								j("#tab-upload .text-28", H).val('');
								j("#tab-upload .text-28", H).error(J.data)
							}
						}
					})
				}
			})
	};
	j.fn.frameFileUpload = function(m) {
		var n = j.extend({
			uploadUrl: '',
			uploadType: '',
			trigger: '',
			callback: "frameFileUploadComplete",
			onUpload: null,
			onComplete: null,
			onChange: null
			}, m),
			o = this.selector;
		window[n.callback] = function(p) {
			j(o).removeAttr("disabled").val('');
			if (n.onComplete) {
				n.onComplete(p)
			}
		};
		this.each(function(p) {
			var q = j(this),
				s = "upload-frame-" + p,
				r = j('<iframe style="display:none;" id="' + s + '" src="about:blank"></iframe>').insertAfter(q);
			if (n.onChange) {
				q.change(function() {
					n.onChange.call(this)
				})
			}
			j(n.trigger).click(function() {
				var v = j(o),
					z = j('<form id="upload-form" action="' + n.uploadUrl + '" method="post" enctype="multipart/form-data"></form>'),
					u = v.clone(true).attr("disabled", "disabled").val(''),
					B = r[0],
					A = B.contentWindow ? B.contentWindow.document : B.contentDocument ? B.contentDocument : B.document;
				u.insertBefore(v);
				z.append(v);
				z.append('<input type="hidden" name="action" value="upload" /><input type="hidden" name="uploadType" value="' + n.uploadType + '" />');
				j("body", A).html('').append(z);
				z.submit();
				if (n.onUpload) {
					n.onUpload.call(v)
				}
				return false
			})
		})
	}
})(jQuery);

/**
 * autoresize
 */
(function(e,h,f){function g(){var a=e(this),d=a.height(),b=a.data("scrollOffset"),c=a.data("minHeight"),i=f.scrollTop();b=a.height(c).prop("scrollHeight")-b;a.height(b);f.scrollTop(i);d!==b&&a.trigger("autoresize:resize",b)}function j(){var a=e(this),d=a.val(),b=a.val('').height(),c=this.scrollHeight;c=c>b?c-b:0;a.data("minHeight",b);a.data("scrollOffset",c);a.val(d).bind(k,g);g.call(this)}var k="keyup change paste input";h.autoResize=function(){return this.filter("textarea").each(j)}})(jQuery,jQuery.fn,jQuery(window));

/**
 * @name jQuery waterfall Plugin (https://github.com/iMuFeng/Waterfall)
 * @version 1.0.6
 **/
~function(b){var e=[],d=0,c=function(f,g){this.options=f;this.element=b(g);this.init()};c.prototype={init:function(){this.initArg();this.reSize();this.layout(this.getBricks(this.element))},getBricks:function(g){var f=this.options.itemSelector;return $bricks=!f?g.children().not(".waterfall"):g.filter(f).not(".waterfall").add(g.find(f))},getSize:function(){var l=this.getBricks(this.element)[0],j=b(l),m=this.options.columnCount,f=this.options.columnWidth,k=[],i=j.outerWidth(true),g=b(window).width()-20,o=!f?i:f,h=!m?parseInt(g/o):m;return k.concat(o,h)},initArg:function(){if(!d){var f,h=this.getSize(),g=h[1];for(f=0;f<g;f++){e[f]=0}d=1}},reSize:function(){this.element.css("position","relative");if(this.options.isResizable){var h=this.getSize(),g=h[1],f=h[0];this.element.css({width:g*f,margin:"0 auto"})}},layout:function(r){var m=0,s=this.element,f=r.length,q=s.filter("img").add(s.find("img")).eq(0),x=q.parent(),h=this.options.isAnimated,p=this.options.Duration,g=this.options.Easing,A=this.options.endFn,o=this.getSize(),u=o[0],z,v=x.width()-parseInt(q.css("padding-left"))-parseInt(q.css("padding-right"))-parseInt(q.css("margin-left"))-parseInt(q.css("margin-right"));r.css("display","none");var j=(function(){var w=[],t=null,n=function(){var B=0;for(;B<w.length;B++){w[B].end?w.splice(B--,1):w[B]()}!w.length&&i()},i=function(){clearTimeout(t);t=null};return function(C,I,J,H){var E,D,K,G,B,F=new Image();F.src=C;if(F.complete){I(F.width,F.height);J&&J(F.width,F.height);return}D=F.width;K=F.height;E=function(){G=F.width;B=F.height;if(G!==D||B!==K||G*B>1024){I(G,B);E.end=true}};E();F.onerror=function(){H&&H();G=B=0;I(G,B);E.end=true;F=F.onload=F.onerror=null};F.onload=function(){J&&J(F.width,F.height);!E.end&&E();F=F.onload=F.onerror=null};if(!E.end){w.push(E);if(t===null){t=setTimeout(n,50)}}}})();l();function l(){if(m<f){var B=r[m],i=b(B),w=i.filter("img").add(i.find("img")),C=i.outerHeight(true),t=k(e);if(w.length>0){var n=w.attr("src");w.removeAttr("src");j(n,function(G,F){G=G?G:v;F=F?F:0;y=F*v/G;G=v;w.attr("src",n).css({width:G,height:F});var I=Math.max.apply(Math,e);s.height(I);i.addClass("waterfall");var H=i.outerHeight(true);!h?(i.css({display:"block",position:"absolute",left:t*u,top:e[t]})):(i.css({display:"block",position:"absolute",left:0,top:I}).stop().animate({left:t*u,top:e[t]},{Duration:p,Easing:g}));e[t]+=H;m++;z=setTimeout(l,50)})}else{var E=Math.max.apply(Math,e);s.height(E);i.addClass("waterfall");var D=i.outerHeight(true);!h?(i.css({display:"block",position:"absolute",left:t*u,top:e[t]})):(i.css({display:"block",position:"absolute",left:0,top:E}).stop().animate({left:t*u,top:e[t]},{Duration:p,Easing:g}));e[t]+=D;m++;z=setTimeout(l,10)}}else{if(m>=f){clearTimeout(z);z=null;var E=Math.max.apply(Math,e);s.height(E);if(typeof A=="function"){A.call(r)}}}}function k(n){var t,w=0,i=n[0],B=n.length;for(t=1;t<B;t++){if(n[t]<i){i=n[t];w=t}}return w}}};b.waterfall=function(f,g){f=b.extend({isResizable:false,isAnimated:false,isAppend:false,Duration:500,Easing:"swing",endFn:function(){}},f);b.data(g,"waterfall",new c(f,g));return g};b.fn.waterfall=function(f){return b.waterfall(f,this)};function a(f){console.log({}.toString.call(f)+" | "+f)}}(jQuery);

jQuery.extend(jQuery.easing, {
    easeInOutBack: function(l, k, b, c, d, j) {
        if (j == undefined) {
            j = 1.70158;
        }
        if ((k /= d / 2) < 1) {
            return c / 2 * (k * k * (((j *= (1.525)) + 1) * k - j)) + b;
        }
        return c / 2 * ((k -= 2) * k * (((j *= (1.525)) + 1) * k + j) + 2) + b;
    }
});
