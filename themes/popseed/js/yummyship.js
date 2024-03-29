$(document).ready(function(){
	//dropdown
	$("ul.dropdown-menu").dropdownMenu();
	
	//recipe
	$("#more-recipes").live('click', function() {
		Yummyship.fetchMoreSeeds();
	});
	$('.recipe-card, .recipe-meta .action-like').hover(function(){
		$(this).addClass('recipe-card-hover');
	}, function(){
		$(this).removeClass('recipe-card-hover');
	});
	$('.recipe-card .like, .recipe-meta .action-like').live('click', function(e) {
		if (signedIn) {
			Yummyship.likeRecipe($(this).attr('data-cid'));
		}
		else {
			window.location.href = signInUrl;
		}
	}).attr('title', function() {
		if (!$(this).hasClass('saved')) {
			return 'Like this recipe';
		}
		else {
			return 'Unlike this recipe';
		}
	}).tipTip({
		delay: 150,
		defaultPosition: 'left',
		fadeIn: 0,
		fadeOut: 0,
	});
	$('.recipe-big-cover').click(function(){
		var b= $(this);
		var a= b.children('img').height();
		if (b.hasClass('slide')) {
			b.animate({height:'396px'},300);
		}
		else {
			b.animate({height:a+'px'},300);
		}
		b.toggleClass('slide');
	});
	
	
	//upload image
	$('textarea').autoResize();
	$('a.delete').live('click', function(){
		$(this).parent().find('input').val('');
		$(this).parent().find('img').remove();
		$(this).hide();
		$(this).parent().children('a.btn-regular').show();
	});
	$('.cover-add, .steps-image-add').live('click', function(){
		$(this).uploadFile(this);
	});	
	$('.ingredients-row-add').live('click', function(){
		var ingredientsRows = '<div class="ingredients-row">\
			<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>\
			<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>\
		</div>';
		$(this).before(ingredientsRows);
	});
	$('.steps-row-add').live('click', function(){
		var stepsNum = $('.steps-row').size();
			steps = '<div class="steps-row clearfix">\
			<div class="steps-num">' + (stepsNum+1) + '</div>\
			<div class="steps-text"><textarea name="steps[]"></textarea></div>\
			<div class="steps-image">\
				<div class="steps-image-uploader">\
					<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>\
					<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>\
					<input type="hidden" name="stepsImage[]" value=""/>\
				</div>\
			</div>\
		</div>';
		$(this).before(steps);
		$('textarea').autoResize();
	});
	$('#recipeFm').submit(function(){
		if (Yummyship.checkRecipe()) {
			return true;
		}
		return false;
	});
	$('#recipeFm input[name=clear]').click(function(){
		window.location.href = '?post&add&clear';
	});
	
	//oAuth sign up
	$('.oauth .fb').click(function(e) {
		Yummyship.redirectOAuthSign(e, 'facebook');
	});
	$('.oauth .twitter').click(function(e) {
		Yummyship.redirectOAuthSign(e, 'twitter');
	});
	$('#username').keyup(function() {
		var username = $(this).val().replace(/[^A-Za-z0-9_]/g, '');
		if (username == '') username = 'username';
		$('#profile-url').html(username);
	});
	
	//sign in
	window.setInterval(function () {
        Yummyship.displayLabel([$('#mail'), $('#fullname'), $('#username'), $('#password')]);
    }, 100);
	
	
	//zoom
	$('.img img').hover(function(){
		var width = $(this).parent().width() - $(this).width()-4;
		$('.zoom').css('right', width);
		$('.zoom').show();;
	}, function(){
		$('.zoom').hide();
	});	
	$('.zoom').hover(function(){
		$(this).show();
		$(this).addClass('zoom-hover');
	}, function(){
		$(this).removeClass('zoom-hover');
		$(this).hide();
	});	
	
	//scroll
	$('#scroll-to-top').scrollspy({
	      min: 500,
	      max: 1E6,
	      mode: 'vertical',
	      onEnter: function(element, position) {
	          $('#scroll-to-top').fadeIn(500)
	      },
	      onLeave: function(element, position) {
	          $('#scroll-to-top').fadeOut(500)
	      }
	});
	$('#scroll-to-top').click(function(e) {
		e.preventDefault();
		$('body,html').animate({
			scrollTop: 0
		},
		300);
	});
	
	// seed
	$('#link-url').click(function(){
		this.select();
	});	
	$('.share dd').click(function(){
		var share = $(this).attr('share-name');
		Yummyship.share(share);
	});	
	if ($('#sidebar-inner').is('div')) {
		Yummyship.setStick($('#sidebar-inner'));
	}
	if ($('#recipe-left-inner').is('div')) {
		Yummyship.setStick($('#recipe-left-inner'));
	}
	$('#navigation a, #navigation span').tipTip({
		defaultPosition: 'top',
		delay: 150,
		fadeIn: 0,
		fadeOut: 0,
	});
	$('.share img').tipTip({
		defaultPosition: 'top',
		delay: 150,
		fadeIn: 0,
		fadeOut: 0,
		attribute: 'alt',
	});
	$('.recipe-info-steps li img').tipTip({
		defaultPosition: 'bottom',
		delay: 150,
		fadeIn: 0,
		fadeOut: 0,
		attribute: 'alt',
	});
	$('#sidebar-inner .popular  a, .related dd a').tipTip({
		defaultPosition: 'top',
		delay: 150,
		fadeIn: 0,
		fadeOut: 0
	});
});


var Yummyship = {
	likeRecipe : function(recipeId) {
		if (typeof recipeId === 'undefined' || recipeId === '') {
			displayError('Recipe not found.');
			return
		}
		$('.like[data-cid=' + recipeId + ']').addClass('loading');
		$('.action-like[data-cid=' + recipeId + '] like').addClass('loading');
		var that = this;
		jQuery.ajax({
			type: 'post',
			url: siteUrl + 'api/save-recipe',
			dataType: 'json',
			data: {
				ri: recipeId
			},
			cache: false,
			timeout: 1E4,
			error : that.handleAjaxError,
			success : function(data, textStatus, jqXHR) {
				that.likeRecipeCardCallback(recipeId, data, textStatus, jqXHR)
			}
		})
	},
	likeRecipeCardCallback : function (recipeId, data, textStatus, jqXHR) {
		if (recipeId === null || data === null) {
			this.handleNullAjaxResponse();
			return
		}
		if (typeof data.state == 'undefined') {
			return
		}
		this.setRecipeLikeStatus(recipeId, data.state == 'saved', data.count)
	},
	setRecipeLikeStatus : function (recipeId, liked, likeCount) {
		if (recipeId === null) {
			this.handleNullAjaxResponse();
			return
		}
		$('.like[data-cid=' + recipeId + ']').removeClass('loading').toggleClass('saved', liked).html(function() {
			return likeCount !== undefined ? likeCount : 0
		});
		$('.action-like[data-cid=' + recipeId + '] .like').removeClass('loading')
		.toggleClass('saved', liked).parent('.action-like').toggleClass('saved', liked)
		.prev().prev('.likeNum').children().html(function() {
			return likeCount !== undefined ? likeCount : 0
		});
	},
	setStick : function ($stick) {
		theWindow = $(window),
		oldTop = $stick.offset().top;
		theWindow.scroll(function() {
			var top = theWindow.scrollTop();
			if (top + 10 >= oldTop) {
				$stick.addClass('fixed');
			} else if (top + 10 < oldTop) {
				$stick.removeClass('fixed');
			}
		});
	},
	displayLabel : function (inputs) {
		if(inputs){
            for(var i = 0, len = inputs.length; i < len; i++){
                var input = inputs[i];
                var label = input.prev();
                if (input.val() != '') {
                    label.css('display') != 'none' && label.hide();
                }
                else {
                    label.css('display') == 'none' && label.show();
                }
            }
        }
	},
	initSeeds : function (item) {
		$('#recipe-cards').waterfall({
			itemSelector: item,
			columnCount: 4,
			columnWidth: 286,
			isResizable: true,
			isAnimated: false,
			Duration: 500,
			Easing: 'easeInOutBack', //'swing',
			endFn: function(){
				fetchingMore = false;
			}
		});
	},
	scrollSeed : function () {
		if (!fetchingMore && !scrolledToEnd) {
	        var pixelsFromBottom = 0 + $(document).height() - $(window).scrollTop() - $(window).height();
	        if (pixelsFromBottom < 500) Yummyship.fetchMoreSeeds();
	    }
	},
	fetchMoreSeeds : function () {
		fetchingMore = true;
	    $('#more-recipes').hide();
	    $('#loading-more').show();
	    Yummyship.fetchSeeds(Yummyship.scrollCallback, Yummyship.handleAjaxError);
	},
	fetchSeeds : function (success, error) {
		$.ajax({
		    type: 'post',
		    url: siteUrl + 'api/rc',
		    dataType: 'json',
		    data: {
		    	action: seedAction,
		    	nextRecipe: nextRecipe
		    },
		    cache: false,
		    timeout: 1E4,
		    error: error,
		    success: success
		});
	},
	scrollCallback : function (data, textStatus, jqXHR) {
	    if (data === null) {
	    	Yummyship.handleNullAjaxResponse();
	        return
	    }
	    var n = data.seeds.length;
	    
	    var html = '';
	    for (var i = 0; i < n; ++i) {
	        var item = data.seeds[i],
	        	saved = '';
	        if (item.favorite) {
	        	saved = ' saved';
	        }
	        
	        html += '<div class="recipe-card" id="'+ item.cid +'">\
						<a href="' + item.permalink + '" title="' + item.title +'">\
							<div class="recipe-card-title">\
								<div class="recipe-card-image">\
									<img src="' + item.thumb +'" alt="' + item.title +'"/>\
									<div class="overlay"></div>\
								</div>\
								<div class="title">' + item.title +'</div>\
							</div>\
						</a>\
						<div class="meta clearfix">\
							<div class="meta-info">\
								<div class="author"><a href="' + item.userUrl +'">' + item.fullname +'</a></div>\
								<span class="sprite like' + saved + '" data-cid="' + item.cid + '">' + item.favoritesNum + '</span>\
								<span class="sprite clock published">' + item.dateWord + '</span>\
							</div>\
							<div class="rating">\
								<div class="num">' + item.viewsWord + '</div>\
								Rating\
							</div>\
						</div>\
					</div>';
	        
	    }
	    $('#recipe-cards').append(html).waterfall({
	    	columnCount: 4,
	    	columnWidth: 286,
	    	isResizable: true,
	    	isAnimated: true,
	    	Duration: 800,
	    	Easing: 'easeInOutBack', //'swing',
	    	endFn: function(){
	    		fetchingMore = false;
	    		if (n < recipeNum) Yummyship.gotAllSeed();
			    else {
			        $('#more-recipes').show();
			        $('#loading-more').hide();
			    }
			    nextRecipe = data.next;
			}
	    });
	},
	gotAllSeed : function () {
	    scrolledToEnd = true;
	    var numSeeds = $('.recipe-card').size();
	    $('#end-marker').html('<div class="result">' + numSeeds + ' '+ seedName +' found</div>');
	},
	handleAjaxError : function (jqXHR, textStatus, errorThrown) {
	    if (textStatus == 'error') if (jqXHR.responseText !== '') displayError(jqXHR.responseText);
	    else displayError('Sorry, an unrecognized error occurred. Our team has just ' + 'been sent an alert email, and we\'ll look into the ' + 'problem ASAP. If the failure persists, please contact ' + 'us at support@' + domain + '. Thanks for your patience.');
	    else if (textStatus == 'timeout') displayError('The server took too long to respond. There could be heavy ' + 'network traffic, or something might have gone wrong with ' + 'the connection. Try submitting your ingredients again. ' + 'If the failure persists, please let us know at ' + 'support@' + domain + ' so we can fix the problem.');
	    else if (textStatus == 'parsererror') Yummyship.displayError('Sorry, something went wrong with the response sent by our ' + 'server. Please let us know at support@' + domain + ' so we can ' + 'fix the problem. Thanks for your patience.');
	    else displayError('Something mysterious went wrong with your request. ' + 'Please let us know at support@' + domain + ' so we can fix the ' + 'problem. Thanks for your patience.');
	},
	handleNullAjaxResponse : function () {
	    displayError('Could not reach the server. ' + 'Please check your internet connection and try again.');
	},
	displayError : function (text) {
		alert(text);
		//$('<p>'+text+'</p>').modal({
			//title: "Error"
		//})
	},
	checkRecipe : function () {
		var msg = '',
			ingredients = false,
			steps = false,
			title = $.trim($('#title').val()),
			cover = $.trim($('#cover').val());
		if (!title) {
			msg += 'title';
			$('#title').error('Pls enter title');
			//$('#title').error('Pls enter title');
		}
		if (!cover) {
			msg += ' cover';
			$('.cover').error('');
		}
		$('.ingredients-row-name>input').each(function(){
			if ($.trim($(this).val())) {
				ingredients = true;
			}
		});
		if (!ingredients) {
			msg += ' ingredients';
			$('.ingredients').error('');
		}
		$('.steps-text>textarea').each(function(){
			if ($.trim($(this).val())) {
				steps = true;
			}
		});
		if (!steps) {
			msg += ' steps';
			$('.steps').error('');
		}
		if (msg != '') {
			$('body,html').animate({
				scrollTop: 0
			},
			800);
			return false;
		}
		return true;
	},
	redirectOAuthSign : function (event, provider) {
		event.preventDefault();
		var newLocation = signOAuthUrl + provider;
		window.location.href = newLocation;
	},
	popupOAuthSignin : function (event, provider) {
		event.preventDefault();
		var newLocation = signOAuthUrl + provider;
		this.openPopup(newLocation);
	},
	getQueryParameterByName : function (name) {
		var match = RegExp("[?&]" + name + "=([^&]*)").exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, " "))
	},
	openPopup : function (url, popupWidth, popupHeight) {
		if (typeof popupWidth == 'undefined') popupWidth = 700;
		if (typeof popupHeight == 'undefined') popupHeight = 500;
		var xPos = 0;
		var yPos = 0;
		if ('screenLeft' in window) {
			xPos = window.screenLeft;
			yPos = window.screenTop
		} else if ('screenX' in window) {
			xPos = window.screenX;
			yPos = window.screenY
		}
		xPos += Math.max(0, Math.floor(($(window).width() - popupWidth) / 2));
		yPos += Math.max(0, Math.floor(($(window).height() - popupHeight) / 2));
		return window.open(url, '', 'width=' + popupWidth + ',height=' + popupHeight + ',status=1,location=1,resizable=yes' + ',left=' + xPos + ',top=' + yPos)
	},
	share : function(w) {
		var surl = encodeURIComponent(location.href),
			stitle = encodeURIComponent(document.title),
			spic = encodeURIComponent($('.cover img').attr('src')),
			url = null;
		
		switch (w) {
			case 'fb':
				url = 'http://www.facebook.com/sharer/sharer.php?u='+surl,'fb','toolbar=0,status=0,width=660,height=360';
				break;
			case 'tw':
				url = 'http://twitter.com/intent/tweet?text='+stitle+'&url='+surl,'tw','toolbar=0,status=0,width=660,height=360';
				break;
			case 'tu':
				url = 'http://www.tumblr.com/share/photo?source='+spic+'&caption='+stitle+'&click_thru='+surl,'tu','toolbar=0,status=0,width=500,height=460';
				break;
			case 'pi':
				url = 'http://pinterest.com/pin/create/bookmarklet/?media='+spic+'&url='+surl+'&title=Popular%20IMG&is_video=false&description='+stitle;
				break;
			case 'we':
				url = 'http://weheartit.com/create_entry/?bookmarklet_version=1.0.1&extension_version=&hearting_method=bookmarklet&media='+spic+'&encoding=utf-8&title=Inspiring+images&via='+surl+'&tags=';
				break;
			case 'rd':
				url = 'http://www.reddit.com/submit?url='+surl;
				break;
			case 'dg':
				url = 'http://digg.com/submit?phase=2&url='+surl+'&title='+stitle;
				break;
			case 'gg':
				url = 'https://plus.google.com/share?url='+surl+'&hl=en-US';
				break;
			case 'su':
				url = 'http://www.stumbleupon.com/submit?url='+surl+'&title='+stitle;
				break;
			case 'lk':
				url = 'http://www.linkedin.com/shareArticle?mini=true&url='+surl+'&title='+stitle+'&summary='+stitle+'&source=';
				break;
		}
	
		window.open(url);
	}
};