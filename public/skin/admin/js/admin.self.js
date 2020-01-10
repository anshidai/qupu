/*
* response 返回结果
* status 请求状态
*/
function showResponse(response, status)
{	
	console.log(response)
	//return;
	if(response.code == '2000') {

		//提示消息
		$.Huimodalalert(response.msg, 2000);

		//如果有跳转url
		if(response.url) {
			//延时关闭窗口
			setTimeout(function () { 
				var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
				parent.layer.close(index);
				window.location.href = response.url;
			}, 1000);
			
		}else {
			//延时关闭窗口
			delayCloseWin(1000);
		}
		
	}else {
		$.Huimodalalert(response.msg,2000);
	}
}

/**
* 延时关闭窗口
* time 延时时间 单位:毫秒
*/
function delayCloseWin(time = 2000)
{
	//延时关闭窗口
	setTimeout(function () { 
		var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
		parent.layer.close(index);
		parent.location.reload(); // 父页面刷新
	}, time);
}

//提交前 
/*
function showRequest(formData, jqForm, options) { 
	//formdata是数组对象,在这里，我们使用$.param()方法把他转化为字符串.
	var queryString = $.param(formData); //组装数据，插件会自动提交数据
	alert(queryString); //类似 ： name=1&add=2  
	return true; 
} 
*/

/*
  参数解释：
  title	标题
  url 请求的url
  id 需要操作的数据id
  width 弹出层宽度（缺省调默认值）
  height 弹出层高度（缺省调默认值）
*/
/*------------------- 弹出页面 -------------------------*/
function winPage(title, url, width = 0, height = 0)
{
	if (width == '' || width == '0') {
		width = '800';
	}
	if (height == '' || height == '0') {
		height = '600';
	}
	width = width + 'px';
	height = height + 'px';

	var index = layer.open({
		type: 2,
		title: title,
		content: url,
		area:[width, height],
	});
	// layer.full(index);
}

function _getRandomString(len = 10) 
{
    len = len || 32;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678'; // 默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1
    var maxPos = $chars.length;
    var str = '';
    for (i = 0; i < len; i++) {
        str += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return str;
}

function layer_close()
{
	var index = parent.layer.getFrameIndex(window.name);
	parent.layer.close(index);
}


/*textarea 字数限制*/
function textarealength(obj, maxlength)
{
	var v = $(obj).val();
	var l = v.length;
	if( l > maxlength){
		v = v.substring(0,maxlength);
		$(obj).val(v);
	}
	$(obj).parent().find(".textarea-length").text(v.length);
}

$(function(){

	$(".Htab-link").on("click", function(){
		Hui_admin_tab(this);
	});

    $('#jump-btn').click(function(){
        var page = $('#jump-page').val();
        if(page == '') {
            alert('输入跳转页码');
            return false;
        }
        
        var url = window.location.href;
        
        //url有分页
        if(url.indexOf('&p=') !== -1) {
            url = url.replace(/&p=(\d+)/g, '&p='+page); 
        } else {
            url = url + '&p='+page; 
        }
        window.location.href = url;  
    });
    
});