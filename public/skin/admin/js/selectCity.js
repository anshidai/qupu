
/**
* 选择省
*/
$(".province").change(function(){
	var provinceIndex = $(this).val();
	if(provinceIndex > 0) {
		var url = "/area/city"+"?province="+provinceIndex;
		$.getJSON(url, function(res) {
			var data = res.data.list;
			if(data != null || data != '') {
				var select_html = "<option value=''>请选择市</option>";
				for(var i=0; i<data.length; i++) {
					select_html += "<option value='"+data[i].area_id+"'>"+data[i].area_name+"</option>";
				}
				$(".city").html(select_html);
			}
		});
	}
});


/**
* 选择市
*/
$(".city").change(function(){
	var cityIndex = $(this).val();
	if(cityIndex > 0) {
		var url = "/area/county"+"?city="+cityIndex;
		$.getJSON(url, function(res) {
			var data = res.data.list;
			if(data != null || data != '') {
				var select_html = "<option value=''>请选择县</option>";
				for(var i=0; i<data.length; i++) {
					select_html += "<option value='"+data[i].area_id+"'>"+data[i].area_name+"</option>";
				}
				$(".county").html(select_html);
			}
		});
	}
});