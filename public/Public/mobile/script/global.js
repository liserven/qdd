function suofangFn(id, num) {
	var oWidth = document.documentElement.clientWidth || document.body.clientWidth;
	var oHeight = document.documentElement.clientHeight || document.body.clientHeight;
	var objx = document.getElementById(id);
	suofang = oWidth / num;
	oHeight = oHeight/suofang;
	if (oWidth <= 1920) {
		
		objx.style.position = "relative";
		objx.style.left = "0px";
		objx.style.top = "0px";
		
		objx.style.width = num + "px";
		objx.style.overflow ="hidden";
		/* objx.style.height = oHeight + "px"; */
		/* objx.style.webkitOverflowScrolling = "auto"; */
		objx.style.transformOrigin = "left top 0px";
		objx.style.webkitTransformOrigin = "left top 0px";
		objx.style.transform = "scale(" + suofang + ")";
		objx.style.webkitTransform = "scale(" + suofang + ")";
		
		
		$(".fidexnav").css({
			transformOrigin : "left bottom 0px",
			webkitTransformOrigin : "left bottom 0px",
			transform : "scale(" + suofang + ")",
			webkitTransform : "scale(" + suofang + ")"
			
		})
		
		$(".suanbox").css({
			transformOrigin : "left bottom 0px",
			webkitTransformOrigin : "left bottom 0px",
			transform : "scale(" + suofang + ")",
			webkitTransform : "scale(" + suofang + ")"
			
		})
		
		
	} else {
		
	}
}
suofangFn("innerbox", 750)
var oHeight = document.documentElement.clientHeight || document.body.clientHeight;
$("body").height(oHeight)



window.addEventListener("orientationchange", function() {
	suofangFn("innerbox", 750)
}, false);

window.addEventListener("resize", function() {
	suofangFn("innerbox", 750)
}, false);
