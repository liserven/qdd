//浮动楼层
$(function(){
	var fLoor = $('.floor'),
	fNav = $('.float-nav'),
	fLi = $('.float-nav li'),
	fWidth = $('.float-nav').outerWidth(),
	$width = $('.float-width').width(),
	fHeight = $('.float-nav').outerHeight(),
	$wHeight = $(window).height(),
	$footerNav = $('.float-b').offset().top,
	fsH = $('.tpage_count2').first().offset().top
	fNav.css({marginTop:-fHeight/2,marginLeft:-$width/2 - fWidth -10});
	fLi.each(function(){
		var eindex = $(this).index();
		var fTop = fLoor.eq(eindex).offset().top;
		$(window).scroll(function(){
			var sTop = $(window).scrollTop();
			var fOffset_top = $('.float-b').offset().top;
			if(sTop >= fsH){
				fNav.show();
			}else{
				fNav.hide();
			}
			if(sTop >= (fOffset_top-500)){
				fNav.hide();
			}
			if(sTop >= (fTop-200)){
				fLi.eq(eindex).addClass('curr').siblings('li').removeClass('curr');
			}
		})
		$(this).click(function(){
			var lIndex = $(this).index();
			var cTop = fLoor.eq(lIndex).offset().top;
			$(this).addClass('curr').siblings().removeClass('curr');
			$('html,body').scrollTop(cTop);
		})
	})
});