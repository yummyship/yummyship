<?php 
if (!defined('__DIR__')) {
	define('__DIR__', dirname(__FILE__));
}
require_once __DIR__.'/../config.inc.php';
require_once 'Widget/Config.php';
?>
var byendsGather = {
	caijiDivOk:0,
	weisite_scrolltop:0,
	init:function(){
		if(this.caijiDivOk === 0)
		this.caijiDivOk = 1;
	},
	createCss:function(cssName){
		if(this.caijiDivOk == 1) return false;
		var css=document.createElement("link");
		css.setAttribute("rel", "stylesheet");
		css.setAttribute("type", "text/css");
		css.setAttribute("href", cssName);
		document.getElementsByTagName("head")[0].appendChild(css);
	},
	createJs:function(jsName){
		if(this.caijiDivOk == 1) return false;
		var s = document.createElement("script");
		s.type = "text/javascript";
		s.src = jsName;
		s.charset = 'utf-8';
		document.getElementsByTagName("head")[0].appendChild(s);
	},
	ignoreError:function(){
		return true;
	}
};

function VdCjloadScript(sScriptSrc,callbackfunction)   
{  
    //gets document head element  
    var oHead = document.getElementsByTagName('head')[0];  
    if(oHead)  
    {  
        //creates a new script tag        
        var oScript = document.createElement('script');  
                  
        //adds src and type attribute to script tag  
        oScript.setAttribute('src',sScriptSrc);  
        oScript.setAttribute('type','text/javascript');  
		oScript.setAttribute('charset','utf-8');
  
        //calling a function after the js is loaded (IE)  
        var loadFunction = function()  
            {  
                if (this.readyState == 'complete' || this.readyState == 'loaded')  
                {  
                    callbackfunction();   
                }  
            };  
        oScript.onreadystatechange = loadFunction;  
  
        //calling a function after the js is loaded (Firefox)  
        oScript.onload = callbackfunction;  
          
        //append the script tag to document head element          
        oHead.appendChild(oScript);  
    }  
}  

var 
siteTitle = '<?php echo $options->title; ?>',
siteLogo = siteTitle,//'<img src="<?php echo BYENDS_SITE_URL; ?>gather/images/vdisk_logo.jpg" width="100" height="21" alt="'+siteTitle+'">',
byendsGatherUrl = '<?php echo BYENDS_SITE_URL; ?>',
filterStr = '<?php echo $options->domain; ?>';

//Ignore the error
window.onerror = byendsGather.ignoreError;

//var $vDcJJQ = '';
if(document.readyState == 'complete'){
	if(document.getElementsByTagName('frameset').length > 0){
		alert('This page does not support to gather.');
	}
	else{
		byendsGather.weisite_scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
		byendsGather.createCss(byendsGatherUrl+'gather/gatherSelect.css?'+new Date().getTime());
		
		VdCjloadScript(byendsGatherUrl+'gather/gatherJQuery.js',function(){
			///$vDcJJQ = jQuery.noConflict(); 
			if(!document.getElementById('vd_select_pic_tt19'))
				VdCjloadScript(byendsGatherUrl+'gather/gatherOprate.js?'+new Date().getTime(),function(){});
		});
		
		byendsGather.init();
	}
	
}else{
	alert('The page is Loading, please wait and gather again ...');
}