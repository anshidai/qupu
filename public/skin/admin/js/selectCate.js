
$(function() {

	$(document).on('change','.catid1', function(){
		var _this = $(this);
		var cidIndex = _this.val();
		if(cidIndex > 0) {
			var url = "/cate/CateTwo"+"?cid="+cidIndex;

			//异步请求
			$.getJSON(url, function(res) {
				var data = res.data.list;
				if(data != null || data != '') {
					var select_html = "<option value=''>选择二级分类</option>";
					for(var i=0; i<data.length; i++) {
						select_html += "<option value='"+data[i].id+"'>"+data[i].name+"</option>";
					}
					// $(".catid2").html(select_html);
					_this.parent().parent().find(".catid2").html(select_html);

				}
			});
		}

	});

	//选择二级分类
	$(document).on('change', '.catid2', function(){
		var _this = $(this);
		var cidIndex = _this.val();
		if(cidIndex > 0) {
			var url = "/cate/CateThree"+"?cid="+cidIndex;

			//异步请求
			$.getJSON(url, function(res) {
				var data = res.data.list;
				if(data != null || data != '') {
					var select_html = "<option value=''>选择三级分类</option>";
					for(var i=0; i<data.length; i++) {
						select_html += "<option value='"+data[i].id+"'>"+data[i].name+"</option>";
					}
					// $(".catid2").html(select_html);
					_this.parent().parent().find(".catid3").html(select_html);
				}
			});
		}
	});

	//新增扩展
	$(".cate-btn-add").click(function(){
		var randstr = 'block'+_getRandomString();
		var html = $(".cate-block").html();
		_html = '<div class="row cl cate-block-add '+ randstr +'">';
		_html += html;
		_html += '</div>';

		$(".cate-block").after(_html);
		$("."+randstr).find(".catid1").val("");
		$("."+randstr).find(".catid2").val("");
		$("."+randstr).find(".catid3").val("");

		$(".cate-block-add .cate-title").html('');
		$(".cate-block-add .extendbtn").html('<button type="button" class="btn btn-danger radius cate-btn-del"><i class="Hui-iconfont Hui-iconfont-close"></i></button>');
	});

});

