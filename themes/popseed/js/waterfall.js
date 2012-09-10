~
function(b) {
	var e = [],
		d = 0,
		c = function(f, g) {
			this.options = f;
			this.element = b(g);
			this.init()
		};
	c.prototype = {
		init: function() {
			this.initArg();
			this.reSize();
			this.layout(this.getBricks(this.element))
		},
		getBricks: function(g) {
			var f = this.options.itemSelector;
			return $bricks = !f ? g.children().not(".waterfall") : g.filter(f).not(".waterfall").add(g.find(f))
		},
		getSize: function() {
			var l = this.getBricks(this.element)[0],
				j = b(l),
				m = this.options.columnCount,
				f = this.options.columnWidth,
				k = [],
				i = j.outerWidth(true),
				g = b(window).width() - 20,
				o = !f ? i : f,
				h = !m ? parseInt(g / o) : m;
			return k.concat(o, h)
		},
		initArg: function() {
			if (!d) {
				var f, h = this.getSize(),
					g = h[1];
				for (f = 0; f < g; f++) {
					e[f] = 0
				}
				d = 1
			}
		},
		reSize: function() {
			this.element.css("position", "relative");
			if (this.options.isResizable) {
				var h = this.getSize(),
					g = h[1],
					f = h[0];
				this.element.css({
					width: g * f,
					margin: "0 auto"
				})
			}
		},
		layout: function(r) {
			var m = 0,
				s = this.element,
				f = r.length,
				q = s.filter("img").add(s.find("img")).eq(0),
				x = q.parent(),
				h = this.options.isAnimated,
				p = this.options.Duration,
				g = this.options.Easing,
				A = this.options.endFn,
				o = this.getSize(),
				u = o[0],
				z, v = x.width() - parseInt(q.css("padding-left")) - parseInt(q.css("padding-right")) - parseInt(q.css("margin-left")) - parseInt(q.css("margin-right"));
			r.css("display", "none");
			var j = (function() {
				var w = [],
					t = null,
					n = function() {
						var B = 0;
						for (; B < w.length; B++) {
							w[B].end ? w.splice(B--, 1) : w[B]()
						}!w.length && i()
					},
					i = function() {
						clearTimeout(t);
						t = null
					};
				return function(C, I, J, H) {
					var E, D, K, G, B, F = new Image();
					F.src = C;
					if (F.complete) {
						I(F.width, F.height);
						J && J(F.width, F.height);
						return
					}
					D = F.width;
					K = F.height;
					E = function() {
						G = F.width;
						B = F.height;
						if (G !== D || B !== K || G * B > 1024) {
							I(G, B);
							E.end = true
						}
					};
					E();
					F.onerror = function() {
						H && H();
						G = B = 0;
						I(G, B);
						E.end = true;
						F = F.onload = F.onerror = null
					};
					F.onload = function() {
						J && J(F.width, F.height);
						!E.end && E();
						F = F.onload = F.onerror = null
					};
					if (!E.end) {
						w.push(E);
						if (t === null) {
							t = setTimeout(n, 50)
						}
					}
				}
			})();
			l();

			function l() {
				if (m < f) {
					var B = r[m],
						i = b(B),
						w = i.filter("img").add(i.find("img")),
						C = i.outerHeight(true),
						t = k(e);
					if (w.length > 0) {
						var n = w.attr("src");
						w.removeAttr("src");
						j(n, function(G, F) {
							G = G ? G : v;
							F = F ? F : 0;
							y = F * v / G;
							G = v;
							w.attr("src", n).css({
								width: G,
								height: F
							});
							var I = Math.max.apply(Math, e);
							s.height(I);
							i.addClass("waterfall");
							var H = i.outerHeight(true);
							!h ? (i.css({
								display: "block",
								position: "absolute",
								left: t * u,
								top: e[t]
							})) : (i.css({
								display: "block",
								position: "absolute",
								left: 0,
								top: I
							}).stop().animate({
								left: t * u,
								top: e[t]
							}, {
								Duration: p,
								Easing: g
							}));
							e[t] += H;
							m++;
							z = setTimeout(l, 50)
						})
					} else {
						var E = Math.max.apply(Math, e);
						s.height(E);
						i.addClass("waterfall");
						var D = i.outerHeight(true);
						!h ? (i.css({
							display: "block",
							position: "absolute",
							left: t * u,
							top: e[t]
						})) : (i.css({
							display: "block",
							position: "absolute",
							left: 0,
							top: E
						}).stop().animate({
							left: t * u,
							top: e[t]
						}, {
							Duration: p,
							Easing: g
						}));
						e[t] += D;
						m++;
						z = setTimeout(l, 10)
					}
				} else {
					if (m >= f) {
						clearTimeout(z);
						z = null;
						var E = Math.max.apply(Math, e);
						s.height(E);
						if (typeof A == "function") {
							A.call(r)
						}
					}
				}
			}
			function k(n) {
				var t, w = 0,
					i = n[0],
					B = n.length;
				for (t = 1; t < B; t++) {
					if (n[t] < i) {
						i = n[t];
						w = t
					}
				}
				return w
			}
		}
	};
	b.waterfall = function(f, g) {
		f = b.extend({
			isResizable: false,
			isAnimated: false,
			isAppend: false,
			Duration: 500,
			Easing: "swing",
			endFn: function() {}
		}, f);
		b.data(g, "waterfall", new c(f, g));
		return g
	};
	b.fn.waterfall = function(f) {
		return b.waterfall(f, this)
	};

	function a(f) {
		console.log({}.toString.call(f) + " | " + f)
	}
}(jQuery);