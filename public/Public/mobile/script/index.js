$(window).load(function() {
	$('.fidinfo').hide();
});
$(document).ready(function(){
	
	TouchSlide({ 
		slideCell:"#slideBox",
		titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
		mainCell:".bd ul", 
		effect:"leftLoop", 
		autoPage:true,//自动分页
		autoPlay:true //自动播放
	});

	$('.xuan_ul ul li').click(function(){
		$(this).addClass('cur').siblings('li').removeClass('cur');
	});

	$('.leftqie ul li').click(function(){
		var xs=$(this).index();
		var cx=$('.qie_ulbox').index();
		cx=xs;
		$('.leftqie ul li').eq(xs).addClass('cur').siblings('li').removeClass('cur');
		$('.qie_ulbox').eq(xs).addClass('cur').siblings('.qie_ulbox').removeClass('cur');
	});
	
	
})