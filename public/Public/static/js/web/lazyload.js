/*
功能说明：当页面加载时只对你看到的图片加载而以。其它未看到的暂时不加载，当滚动向下或向上拉时才会去加载你看到的图片
使用说明：
	1.在你要让图片预加载的页面部底加上<script language="javascript" src="{$LODO_WEB_DIY$}js/lazyload.js"></script>
	2.将要预加载的图片的src改为src2
*/
function lazyload(option){
	var settings = { 
		defObj: null, 
		defHeight: 0 
	}; 
	settings = $.extend(settings, option || {}); 
	var defHeight = settings.defHeight, defObj = (typeof settings.defObj == "object") ? settings.defObj : $(settings.defObj).find("img[src2]"); 
	var pageTop = function() { 
		var d = document, y = (navigator.userAgent.toLowerCase().match(/iPad/i) == "ipad") ? window.pageYOffset : Math.max(d.documentElement.scrollTop, d.body.scrollTop); 
		return d.documentElement.clientHeight + y - settings.defHeight 
	}; 
	var imgLoad = function() {
		defObj.each(function() { 
			if ($(this).offset().top <= pageTop()) { 
				var src2 = $(this).attr("src2");
				if (src2){
					this.onerror=function(){
						this.src=lodo_web_path+"Error.jpg";
					};
					$(this).attr("src", src2).removeAttr("src2");
				} 
			} 
		})
	}; 
	imgLoad();
	$(window).bind("scroll", function() { imgLoad() })
};
$(function(){
	lazyload({defObj:$("img[src2!='']")});
})