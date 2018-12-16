/**
 * Created by 赵晓凡 on 2017/3/6.
 */
$.ajax({
    url:"/api.php/order/pay?type=account",
    type:"get",
    dataType:"json",
    data:$('#form-cart-settl').serialize(),
    success: function(data) {
        if(data.status==1){
            layer.msg(data.msg,{},function(){
                window.location.href='/index.php/member/order';
            });
        }else{
            layer.msg(data.msg);
        }
    }
});