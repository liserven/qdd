$(function(){
   $(".ktXz li").on("click", function () {
        $(this).addClass("bk").siblings().removeClass("bk");
        $(this).children("i").css({
            "display":"block"
        }).end().siblings().children("i").css({
            "display":"none"
        })
    });
    $(".zffs li").on("click",function(){
        $(this).addClass("bk").siblings().removeClass("bk");
        $(this).children("i").css({
            "display":"block"
        }).end().siblings().children("i").css({
            "display":"none"
        })
    })
})