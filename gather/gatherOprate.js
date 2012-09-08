var byendsGatherOprate = {
	postUrl:byendsGatherUrl+'gather/',
	imgMaxWidth:100, //Allows the size to gather the pictures
	imgMaxHeight:100,
	arraySelID:[], //To submit array
	allID:[], //Save al pictures id
	selFlag:0, //0=normal,1=select all,2=select no
	title:document.title,
	//pageUrl:window.location.href,
	doSheets:document.styleSheets,
	website_scrolltop: 0,
	init:function(){
		vDcJJQ(window).scrollTop(0);
		this.addHiden();
		this.getImg();
		this.listBind();
		if(this.allID.length == 0) vDcJJQ("#vdisk_caiji_nodata").show();
	},
	vout:function(){
		for( var i = 0, j = this.doSheets.length; i < j; i++){
			if(this.doSheets[i].href && this.doSheets[i].href.indexOf(filterStr)<0)
			this.doSheets[i].disabled = false;
		}
        vDcJJQ('style').attr('disabled', false);
		vDcJJQ('body').children().removeClass('vDiskHidendiv_19');
		vDcJJQ('html').removeClass('vDiskBodyStyle_19');
		vDcJJQ('body').removeClass('vDiskBodyStyle_19');
		vDcJJQ('#vd_select_pic_tt19').remove();
		vDcJJQ('#vd_form_pic_tt19').remove();
		vDcJJQ('#vDiskCjCss').remove();
		vDcJJQ('.vd_pic_scrollTop_tt19').remove();
		vDcJJQ(document).scrollTop(byendsGather.weisite_scrolltop);		
	},
	getImg:function(){
		var self = this;
		var img = document.getElementsByTagName("img");
		var imgHtml = '<div id="vd_select_pic_tt19">\
	        <div class="pic_content_wrap_tt19 clearfix">\
		        <div class="pic_header_tt19">\
			       <h2><a href="'+byendsGatherUrl+'" target="_blank" title="'+siteTitle+'">'+siteLogo+'</a></h2>\
			        <div class="pic_btn_tt19"><a href="javascript:void(0);" title="Next" id="vdisk_caiji_next" class="btn_blue_tt19" target="_self" onclick="byendsGatherOprate.postData();return false;">Next</a><a href="javascript:void(0);" onclick="byendsGatherOprate.vout();return false;" target="_self" title="Cancle" class="btn_gray_tt19">Cancle</a>\
					</div>\
		        </div>\
		        <div class="pic_list_wrap_tt19">\
				  <p class="pic_select_tt19">\
			       <!--\
			       <a href="javascript:void(0);" target="_self" onclick="byendsGatherOprate.selectAll();return false;">Select All</a>\
			       <a href="javascript:void(0);" target="_self" onclick="byendsGatherOprate.selectReverse();return false;">Select Non</a>\
			       -->\
			       </p>\
				  <ul class="clearfix">';
		var imgUrlArray = "";
		for (var i=0;i<img.length;i++ ){
			//part of ie8 - dong
			if(typeof img[i].src != 'string') continue;
			var oneImgSrc = img[i].src;
			if(imgUrlArray.indexOf(oneImgSrc)<0){ //Exclude Duplicates
				var newImg=new Image();
				var showalt = '';
				newImg.src = oneImgSrc;
				if (newImg.height >= self.imgMaxHeight && newImg.width >= self.imgMaxWidth){
					var imgstr = this.DrawImage(newImg.width,newImg.height);
					var arry = imgstr.split(",");
					var alt = vDcJJQ.trim(img[i].alt);
					if(!alt) alt = this.title;
				    else{
						var fileType = oneImgSrc.substring(oneImgSrc.lastIndexOf(".") + 1);
					    fileType = fileType.toLowerCase(); 
					    if(fileType=='jpg' || fileType=='gif' || fileType=='png' || fileType=='bmp' || fileType=='jpeg')
					    showalt = this.substr(alt,24,1)+'.'+fileType;
						else
                        showalt = this.substr(alt,24,1);
					}
					if(!showalt) showalt = this.substr(this.title,24,1);

					imgHtml += '<li id="vdisk_li_'+i+'">\
					<div class="pic_list_tt19" >\
						<div class="vd_pic_tt19"><div class="pic_outer"><img  src="'+oneImgSrc+'" width="'+arry[0]+'" height="'+arry[1]+'"></div></div>\
						<p>'+showalt+'</p><p id="vdisk_caiji_id" style="display:none;">'+i+'</p><p id="vdisk_alt_'+i+'" style="display:none;">'+alt+'</p><p id="vdisk_url_'+i+'" style="display:none;" img_src="' +img[i].src+ '"></p>\
						<div class="vd_pic_size_tt19">'+newImg.width+' × '+newImg.height+'<span class="vd_col_icon_tt19" id="vdisk_caiji_select'+i+'" ></span></div>\
					</div>\
				</li>';
					imgUrlArray += oneImgSrc + ",";
					this.allID[i] = i;
				}
			}
		}
		//<a href="javascript:void(0);" onclick="vDcJJQ(window).scrollTop(0);return false;" style="display:none;" id="vdisk_caiji_scroll" class="vd_pic_scrollTop_tt19">Back to Top</a>\
		imgHtml += '</ul>\
		    <div class="find_none_tips_tt19" id="vdisk_caiji_nodata" style="display:none;">Sorry, did not find the picture in conditions.</div>\
		</div>\
	</div>\
</div>';
		imgHtml += '<div style="display:none;" id="vd_form_pic_tt19"><form target="_blank" id="vdiskCaijiForm" action="'+this.postUrl+'" method="post"><input type="hidden" name="title" id="vdiskCaijiInputTitle" /><input type="hidden" name="image" id="vdiskCaijiInputImage" /><input type="hidden" name="referer" id="vdiskCaijiInputReferer" /></form></div>';
		imgHtml += '<a id="vd_pic_go_to_up" href="javascript:void(0);" target="_self" class="vd_pic_scrollTop_tt19">Back to Top</a>';
		vDcJJQ(document.body).append(imgHtml);
	},
	DrawImage:function(kimgwidth,kimgheight){
		var width=200;//Pre-setting a desired width
		var height=150;//Pre-setting a desired height
		var ow = kimgwidth;
		var oh = kimgheight;
		if(kimgwidth>width||kimgheight>height){//when image over the pre-setting width or height
			w=kimgwidth/width;
			h=kimgheight/height;
			if(w>h){
				ow=width;
				oh=kimgheight/w; //Calculate the height
			}else{
				oh=height;
				ow=kimgwidth/h; //Calculate the width
			}
		}
		return ow+','+oh;
	},
	postData:function(){
		var poststr = "";
		var referer = document.location.href;
		if(this.arraySelID.length == 0) return false;
		for(var c in this.arraySelID){
			if(parseInt(c) == 'NaN') continue;
			var t = vDcJJQ("#vdisk_alt_"+c).html();
			//var u = vDcJJQ("#vdisk_url_"+c).html();
			var u = vDcJJQ('#vdisk_url_' + c).attr('img_src');
			if(!t) t = this.title;
			if(typeof u != 'undefined' && u)
				//poststr += u+"\t"+t+"\n";
				poststr += u;
		}
        if(!poststr) return false;
		//vDcJJQ("#vdiskCaijiInput").val(poststr);
        var b64 = new this.Base64();  
        vDcJJQ("#vdiskCaijiInputTitle").val(b64.encode(t));
        vDcJJQ("#vdiskCaijiInputImage").val(u);
        vDcJJQ("#vdiskCaijiInputReferer").val(referer);
		vDcJJQ("#vdiskCaijiForm").submit();
		this.vout();
	},
	selectAll:function(){
		if(this.selFlag == 1){ //cancle selectAll
			this.selFlag = 0;
			for(var d in this.allID){
				if(parseInt(d) == 'NaN') continue;
				vDcJJQ("#vdisk_li_"+d).removeClass("vd_list_current_tt19");
				vDcJJQ("#vdisk_caiji_select"+d).attr("class","vd_col_icon_tt19");
			}
			this.arraySelID = [];
		}else{ //selectAll
			this.selFlag = 1;
			for(var d in this.allID){
				if(parseInt(d) == 'NaN') continue;
				if(this.allID[d] == undefined) continue;
				vDcJJQ("#vdisk_li_"+d).attr("class","vd_list_current_tt19");
				vDcJJQ("#vdisk_caiji_select"+d).attr("class","vd_collect_ok_tt19");
				this.arraySelID[d] = d;
			}
		}
	},
	selectReverse:function(){
		if(this.arraySelID.length == 0 ) return false;
		var tmparr = this.allID.slice();
		for(var v in this.arraySelID){ //loop selected id
			 if(parseInt(v) == 'NaN') continue;
			 if(this.arraySelID[v] == undefined) continue;
			 delete tmparr[v];
             vDcJJQ("#vdisk_li_"+v).removeClass("vd_list_current_tt19");
			 vDcJJQ("#vdisk_caiji_select"+v).attr("class","vd_col_icon_tt19");
		}
        this.arraySelID = [];//pre set as null
		for(var d in tmparr){
			if(parseInt(d) == 'NaN') continue;
			if(tmparr[d] == undefined) continue;
			vDcJJQ("#vdisk_li_"+d).attr("class","vd_list_current_tt19");
			vDcJJQ("#vdisk_caiji_select"+d).attr("class","vd_collect_ok_tt19");
			this.arraySelID[d] = d; //reassigned
		}
		this.selFlag = 0;
	},
	addHiden:function(){
		try{ 
		  vDcJJQ('style').attr('disabled', true);
		}catch(e) 
        { }

		vDcJJQ('body').children().addClass('vDiskHidendiv_19');
		vDcJJQ('html').addClass('vDiskBodyStyle_19');
		vDcJJQ('body').addClass('vDiskBodyStyle_19');
		//shield css
		for( var i = 0, j = this.doSheets.length; i < j; i++){
			if(this.doSheets[i].href && this.doSheets[i].href.indexOf(filterStr)<0)
			this.doSheets[i].disabled = true;
		}
	},
	listBind:function(){
	    var self = this;
		vDcJJQ('.pic_list_wrap_tt19 li').mouseover(function(){
			if(!vDcJJQ(this).find('.vd_collect_ok_tt19').hasClass('vd_collect_ok_tt19')){
				vDcJJQ(this).attr("class","vd_list_current_tt19");
				vDcJJQ(this).find('.vd_col_icon_tt19').attr("class","vd_col_icon_an_tt19");
			}
		});

		vDcJJQ('.pic_list_wrap_tt19 li').mouseout(function(){
			if(!vDcJJQ(this).find('.vd_collect_ok_tt19').hasClass('vd_collect_ok_tt19')){
				vDcJJQ(this).removeClass("vd_list_current_tt19");
				vDcJJQ(this).find('.vd_col_icon_an_tt19').attr("class","vd_col_icon_tt19");
			}
		});

		vDcJJQ('.pic_list_wrap_tt19 li').click(function(){
			var sid = vDcJJQ(this).find('#vdisk_caiji_id').text();
			if(!vDcJJQ(this).find('.vd_collect_ok_tt19').hasClass('vd_collect_ok_tt19')){ //选取
				vDcJJQ(this).attr("class","vd_list_current_tt19");
				vDcJJQ(this).find('.vd_col_icon_an_tt19').attr("class","vd_collect_ok_tt19");
				if (self.arraySelID.length > 0) {
					for(var m in self.arraySelID){
						if(parseInt(m) == 'NaN') continue;
						vDcJJQ('#vdisk_li_'+m).attr("class","");
						vDcJJQ('#vdisk_li_'+m).find('.vd_collect_ok_tt19').attr("class","vd_col_icon_an_tt19");
					}
				}
				self.arraySelID = [];
				self.arraySelID[sid] = sid; //just select one
				//self.arraySelID[sid] = sid;
			}else{//cancle
				vDcJJQ(this).attr("class","vd_list_current_tt19");
				vDcJJQ(this).find('.vd_collect_ok_tt19').attr("class","vd_col_icon_an_tt19");
				delete self.arraySelID[sid];
				//delete self.arraySelID[sid];
			}
		});

		//click back to top
		function clickGoToTop(event){
			var _event = event || window.event;
			window.scrollTo(0,0);
			if(_event.preventDefault){
				_event.preventDefault();
			}
			else 
			{
				_event.returnValue=false;	//IE
			}
			return false;
		}		
		var $goToTop = document.getElementById('vd_pic_go_to_up');
		if(window.attachEvent){
			$goToTop.attachEvent("onclick",clickGoToTop);
		}
		else{
			$goToTop.addEventListener('click', clickGoToTop, false);
		}

		//scroll back to top
		function scrollGoToTop(){
			vDcJJQ('#vd_pic_go_to_up').css({display: 'none'});
			function eventHandle(event){
				//var $scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
				//var windowHeight = document.documentElement.clientHeight || document.body.clientHeight;
				var $scrollTop = vDcJJQ(document).scrollTop();
				var $scroll = vDcJJQ('#vd_pic_go_to_up');
				var scrollDom = document.getElementById('vd_pic_go_to_up');
				var top = 0;
				if( $scrollTop > 0 ){
					if($scrollTop < vDcJJQ(document).height()){
						$scroll.css({visibility:'visible'});
						$scroll.css({display: 'block'});
						//if(vDcJJQ.browser.version == "6.0")
						if(!window.XMLHttpRequest){
							$scroll.css({visibility: 'hidden'});
							$scroll.css({bottom:'100px',position:'absolute'});
							$scroll.css({visibility:'visible'});
							$scroll.css({display: 'block'});
						}else{
						  $scroll.css({bottom:'100px'})
						}
					}
				}else{
					$scroll.css({display: 'none'});
					$scroll.css({visibility:'hidden'});
				}
			}
			if(window.attachEvent){
				window.attachEvent("onscroll", eventHandle);
				window.attachEvent("onresize", eventHandle);
			}else{
				window.addEventListener("scroll", eventHandle, false);
				window.addEventListener("resize", eventHandle, false);
			}
			window.scrollTo(0,0);
		}
		scrollGoToTop();

		/*vDcJJQ(window).scroll(function(){
			if(vDcJJQ(window).scrollTop()>0)
			vDcJJQ(".vd_pic_scrollTop_tt19").show();
			else
			vDcJJQ(".vd_pic_scrollTop_tt19").hide();
		});

		vDcJJQ(".vd_pic_scrollTop_tt19").click(function(){
			vDcJJQ(window).scrollTop() == 0;
		});*/
	},
	
	/**
	* 截取字符串(默认为18个字符)
	* @param string str 传入的字符
	* @param int len 截取长度(单位为汉字，即2个字符)
	* @param boolean hasDot 是否加上...
	* @return string
	* @注意 : 字体应该设置成宋体(\u5b8b\u4f53),这样2字母宽度=1汉字宽度 2012-06-06 liuwei9@
	*/
	substr:function(str, len, hasDot)
	{
		if (str==null) return;
		if(typeof len=='undefined') len=18;
		len*=2;
		if(typeof hasDot=='undefined') hasDot=true;
		var newLength = 0;
		var newStr = "";
		var chineseRegex = /[^\x00-\xff]/g;
		var singleChar = "";
		var strLength = str.replace(chineseRegex,"**").length;
		for(var i = 0;i < strLength;i++)
		{
			singleChar = str.charAt(i).toString();
			if(singleChar.match(chineseRegex) != null)
			{
				newLength += 2;
			}
			else
			{
				newLength++;
			}
			if(newLength > len)
			{
				break;
			}
			newStr += singleChar;
		}

		if(hasDot && strLength > len)
		{
			newStr += "...";
		}
		return newStr;
	},
	
	Base64:function() {  
		   
	    // private property  
	    _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";  
	   
	    // public method for encoding  
	    this.encode = function (input) {  
	        var output = "";  
	        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;  
	        var i = 0;  
	        input = _utf8_encode(input);  
	        while (i < input.length) {  
	            chr1 = input.charCodeAt(i++);  
	            chr2 = input.charCodeAt(i++);  
	            chr3 = input.charCodeAt(i++);  
	            enc1 = chr1 >> 2;  
	            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);  
	            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);  
	            enc4 = chr3 & 63;  
	            if (isNaN(chr2)) {  
	                enc3 = enc4 = 64;  
	            } else if (isNaN(chr3)) {  
	                enc4 = 64;  
	            }  
	            output = output +  
	            _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +  
	            _keyStr.charAt(enc3) + _keyStr.charAt(enc4);  
	        }  
	        return output;  
	    }  
	   
	    // public method for decoding  
	    this.decode = function (input) {  
	        var output = "";  
	        var chr1, chr2, chr3;  
	        var enc1, enc2, enc3, enc4;  
	        var i = 0;  
	        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");  
	        while (i < input.length) {  
	            enc1 = _keyStr.indexOf(input.charAt(i++));  
	            enc2 = _keyStr.indexOf(input.charAt(i++));  
	            enc3 = _keyStr.indexOf(input.charAt(i++));  
	            enc4 = _keyStr.indexOf(input.charAt(i++));  
	            chr1 = (enc1 << 2) | (enc2 >> 4);  
	            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);  
	            chr3 = ((enc3 & 3) << 6) | enc4;  
	            output = output + String.fromCharCode(chr1);  
	            if (enc3 != 64) {  
	                output = output + String.fromCharCode(chr2);  
	            }  
	            if (enc4 != 64) {  
	                output = output + String.fromCharCode(chr3);  
	            }  
	        }  
	        output = _utf8_decode(output);  
	        return output;  
	    }  
	   
	    // private method for UTF-8 encoding  
	    _utf8_encode = function (string) {  
	        string = string.replace(/\r\n/g,"\n");  
	        var utftext = "";  
	        for (var n = 0; n < string.length; n++) {  
	            var c = string.charCodeAt(n);  
	            if (c < 128) {  
	                utftext += String.fromCharCode(c);  
	            } else if((c > 127) && (c < 2048)) {  
	                utftext += String.fromCharCode((c >> 6) | 192);  
	                utftext += String.fromCharCode((c & 63) | 128);  
	            } else {  
	                utftext += String.fromCharCode((c >> 12) | 224);  
	                utftext += String.fromCharCode(((c >> 6) & 63) | 128);  
	                utftext += String.fromCharCode((c & 63) | 128);  
	            }  
	   
	        }  
	        return utftext;  
	    }  
	   
	    // private method for UTF-8 decoding  
	    _utf8_decode = function (utftext) {  
	        var string = "";  
	        var i = 0;  
	        var c = c1 = c2 = 0;  
	        while ( i < utftext.length ) {  
	            c = utftext.charCodeAt(i);  
	            if (c < 128) {  
	                string += String.fromCharCode(c);  
	                i++;  
	            } else if((c > 191) && (c < 224)) {  
	                c2 = utftext.charCodeAt(i+1);  
	                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));  
	                i += 2;  
	            } else {  
	                c2 = utftext.charCodeAt(i+1);  
	                c3 = utftext.charCodeAt(i+2);  
	                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));  
	                i += 3;  
	            }  
	        }  
	        return string;  
	    }  
	}  
}
byendsGatherOprate.init();
