// JavaScript Document
TouchSlide({
	slideCell:"#focus",
	titCell:".hd ul",
	mainCell:".bd ul",
	effect:"left",
	autoPlay:true,
	autoPage:true,
	switchLoad:"_src"
});



$(function(){
	// 商品分类 展开二级
	$(".classic .part1").click(function(){
		$(".part2, .part3").slideUp("300");
		$(this).next().slideToggle("300");
	});
	// 商品分类 展开三级
	$(".classic .part2 a").click(function(){
		$(this).parent().next().slideToggle("300");
		return false;
	});

	//数量加减框
	$(".jia-jian .jia").click(function(){
		$(this).siblings(".num").val( parseInt($(this).siblings(".num").val())+1);
	});
	$(".jia-jian .jian").click(function(){
		if( $(this).siblings(".num").val()>1 ){
			$(this).siblings(".num").val( parseInt($(this).siblings(".num").val())-1);
		}
	});

	$(".checkbox input").click(function(){
		$(this).parent().toggleClass('checkbox-on');
	});

});