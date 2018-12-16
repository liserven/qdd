// search
//获得Cookie解码后的值
function GetCookieVal(offset) {
    var endstr = document.cookie.indexOf(";", offset);
    if (endstr == -1) endstr = document.cookie.length;
    return unescape(document.cookie.substring(offset, endstr));
}
//设定Cookie值-将值保存在Cookie中
function SetCookie(name, value) {
    var expdate = new Date(); //获取当前日期
    var argv = SetCookie.arguments; //获取cookie的参数
    var argc = SetCookie.arguments.length; //cookie的长度
    var expires = (argc > 2) ? argv[2] : null; //cookie有效期
    var path = (argc > 3) ? argv[3] : null; //cookie路径
    var domain = (argc > 4) ? argv[4] : null; //cookie所在的应用程序域
    var secure = (argc > 5) ? argv[5] : false; //cookie的加密安全设置
    if (expires != null) expdate.setTime(expdate.getTime() + (expires * 1000));
    document.cookie = name + "=" + escape(value) + ((expires == null) ? "": ("; expires=" + expdate.toGMTString())) + ((path == null) ? "": ("; path=" + path)) + ((domain == null) ? "": ("; domain=" + domain)) + ((secure == true) ? "; secure": "");
}
//删除指定的Cookie
function DelCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = GetCookie(name); //获取当前cookie的值
    document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString(); //将日期设置为过期时间
}
//获得Cookie的值-name用来搜索Cookie的名字
function GetCookie(name) {
    var arg = name + "=";
    var argLen = arg.length; //指定Cookie名的长度
    var cookieLen = document.cookie.length; //获取cookie的长度
    var i = 0;
    while (i < cookieLen) {
        var j = i + argLen;
        if (document.cookie.substring(i, j) == arg) //依次查找对应cookie名的值
        return GetCookieVal(j);
        i = document.cookie.indexOf(" ", i) + 1;
        if (i == 0) break;
    }
    return null;
}

function $$(id) {
    if (document.getElementById) {
        return document.getElementById(id);
    } else if (document.layers) {
        return document.layers[id];
    } else {
        return false;
    }
} (function() {
    function initHead() {
        setTimeout(showSubSearch, 0)
    };
    function showSubSearch() {
        $$("pt1").onmouseover = function() {
            $$("pt2").style.display = "";
            $$("pt1").className = "select_nav select_nav_hover"
        };
        $$("pt1").onmouseout = function() {
            $$("pt2").style.display = "none";
            $$("pt1").className = "select_nav"
        };
        $$("s1").onclick = function() {
            selSubSearch(1);
            $$("q").focus()
        };
        $$("s2").onclick = function() {
            selSubSearch(2);
            $$("q").focus()
        };
        $$("s3").onclick = function() {
            selSubSearch(3);
            $$("q").focus()
        };
        $$("s4").onclick = function() {
            selSubSearch(4);
            $$("q").focus()
        };
        $$("s5").onclick = function() {
            selSubSearch(5);
            $$("q").focus()
        };
    };

    function selSubSearch(iType) {
        hbb = [];
        hbb = {
				1 : ["全站搜索", "0"],
				2 : ["劲爆抢购", "2"],
				3 : ["飞度优品", "4"],
				4 : ["创客空间", "1"],
				5 : ["积分兑购", "6"],
        };
        $$("s0").innerHTML = hbb[iType][0];
        $$("pt2").style.display = "none";
        SetCookie('sousuosss', iType);
        $$("catid").value = hbb[iType][1];
    };
    initHead();
})();

hbb = [];
hbb = {
				1 : ["全站搜索", "0"],
				2 : ["劲爆抢购", "2"],
				3 : ["飞度优品", "4"],
				4 : ["创客空间", "1"],
				5 : ["积分兑购", "6"],
};
if (GetCookie('sousuosss')) {
    var sss = GetCookie('sousuosss');
    $$("s0").innerHTML = hbb[sss][0];
    $$("catid").value = hbb[sss][1];
}

function bottomForm(search_form) {
    if(search_form.infos.value!=""){
		cpkeyw=encodeURI(search_form.infos.value);
	}else{
		cpkeyw=0;	
	}
	if (search_form.catid.value == 0) {
        search_form.action = "http://www.hc2688.com/index.php/home/product/product_list_search_all/cpkey/"+encodeURI(cpkeyw)+"/ishit/0";
        document.search_form.submit();
        return false;
    } else if (search_form.catid.value == 2) {
        search_form.action = "http://www.hc2688.com/index.php/home/product/product_list_search/cpkey/"+encodeURI(cpkeyw)+"/ishit/2";
        document.search_form.submit();
        return false;
    } else if (search_form.catid.value == 4) {
        search_form.action = "http://www.hc2688.com/index.php/home/product/product_list_search/cpkey/"+encodeURI(cpkeyw)+"/ishit/4";
        document.search_form.submit();
        return false;
    } else if (search_form.catid.value == 1) {
        search_form.action = "http://www.hc2688.com/index.php/home/product/product_list_search/cpkey/"+encodeURI(cpkeyw)+"/ishit/1";
        document.search_form.submit();
        return false;
    } else if (search_form.catid.value == 6) {
        search_form.action = "http://www.hc2688.com/index.php/home/product/product_list_jifen_search/cpkey/"+encodeURI(cpkeyw)+"/ishit/6";
        document.search_form.submit();
        return false;
    }
}