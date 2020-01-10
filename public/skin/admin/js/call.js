(function(e) {
    function YTX() {
		this.login_type = 1; //1  手机号登陆  2 VIOP账号登陆
        this._appid = '8a216da85d7dbf78015d81e92e4601b2';
        this._appToken = '0dddff023a81447a6fafe4129603ef0e';
        this.flag = false;//是否从第三方服务器获取sig
		this._3rdServer = 'https://imapp.yuntongxun.com/2016-08-15/Corp/yuntongxun/inner/authen/';
		
        this.is_online = false;
        this.user_account = "18310281657"; // 登陆账号
        this.username = ""; //登陆用户名
        this.pwd = ""; //登陆密码
        this.nickName = "";
        this._onMsgReceiveListener = null; // 消息监听
        this._noticeReceiveListener = null; // SDK消息通知监听
        this._onConnectStateChangeLisenter = null; //连接状态监听
        this._onCallMsgListener = null; //呼叫事件监听
		this.call_type = 2; //呼叫的类型 0 音频 1视频[呼叫网络] 2呼叫落地

        this.call_phone = ""; //被呼电话
        this.customer_id = ""; //客户电话id
        this.call_tips_id = null;

        this.currentCallId = null;
        this.currentCallWith = null;
        this.fireMsgWindow = null;
        this.fireMsgContent = null;
        // this.currentCallType = null;
        this._transfer = 12;
        this._msgBack = 25;
        this.contactMember = null;
        this.userStateInterval = null;
        this._Notification = window.Notification || window.mozNotification || window.webkitNotification || window.msNotification || window.webkitNotifications;
	};
	
	YTX.prototype._login_error_show = false;
	YTX.prototype = {
		init: function() {
			var resp = RL_YTX.init(this._appid);
			if(resp.code != 200) {
                alert('SDK初始化错误');
                return;
            }else if(174001 == resp.code){
                alert('您的浏览器不支持html5，请更换新的浏览器。推荐使用chrome浏览器。');
                return ;
            }else if(170002 == resp.code){
                console.log("错误码：170002,错误码描述" + resp.msg);
                return ;
            }
			if($.inArray(174004, resp.unsupport) > -1 || $.inArray(174009, resp.unsupport) > -1) { 
				//不支持getUserMedia方法或者url转换
                // IM.Check_usermedie_isDisable(); //拍照、录音、音视频呼叫都不支持
            }else if ($.inArray(174007, resp.unsupport) > -1) { 
				//不支持发送附件
                // IM.SendFile_isDisable();
            }else if ($.inArray(174008, resp.unsupport) > -1) { 
				//不支持音视频呼叫，音视频不可用
                // IM.SendVoiceAndVideo_isDisable();
            }
            this.call_phone = $("#callPhone").val();
            this.customer_id = $("#customer_id").val();
            this.call_tips_id = $("#call_tips");
		},
		
		//登录
		Do_login: function(tar) {
            this.login_type = 1;
            var _this = $(tar);
			var val = this.user_account;
            if(val.length == 0) {
                _this.siblings('.error').show();
                this._login_error_show = true;
                return;
            }
            this.getSig(val, this.pwd);
        },
		
		//退出
		DO_logout: function(needLogout) {
			if(!needLogout){
                //return ;
            }
			
			RL_YTX.logout(function(){
				
			}, 
			function(err){
				console.log(err);
			});
        },
        
        //取消音频【挂电话】
        DO_cancelCall: function(){
            IM.isCalling = false;
            var ReleaseCallBuilder = new RL_YTX.ReleaseCallBuilder();
            ReleaseCallBuilder.setCallId(IM.currentCallId);
            ReleaseCallBuilder.setCaller(IM.user_account);
            
            RL_YTX.releaseCall(ReleaseCallBuilder, function(e) {
                console.log('取消呼叫');
            }, function(e) {
                $.scojs_message(e.code + ' : ' + e.msg, $.scojs_message.TYPE_ERROR);
            });
            
        },
		
		//计算秘钥
		getSig: function(account_number, pwd) {
            var pass = pwd ? pwd : "";
            var timestamp = this.getTimeStamp();
            if(IM.flag) {
                this.privateLogin(account_number, timestamp, function(obj) {
                    IM.EV_login(account_number, pass, obj.sig, timestamp);
                }, function(obj) {
                    alert("错误码：" + obj.code + "; 错误描述：" + obj.msg);
                });
            } else {
                //仅用于本地测试，官方不推荐这种方式应用在生产环境
                //没有服务器获取sig值时，可以使用如下代码获取sig
                var sig = hex_md5(this._appid + account_number + timestamp + this._appToken);
                console.log("本地计算sig：" + sig);
                this.EV_login(account_number, pass, sig, timestamp);
            }
        },
		
		//第三方服务器校验sign
		privateLogin: function(user_account, timestamp, callback, onError) {
            console.log("privateLogin");
            var data = {
                "appid": this._appid,
                "username": user_account,
                "timestamp": timestamp
            };
            var url = this._3rdServer + 'genSig';
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'jsonp',
                data: data,
                contentType: "application/x-www-form-urlencoded",
                jsonp: 'cb',
                success: function(result) {
                    if(result.code != 000000) {
                        var resp = {};
                        resp.code = result.code;
                        resp.msg = "Get SIG fail from 3rd server!...";
                        onError(resp);
                        return;
                    } else {
                        var resp = {};
                        resp.code = result.code;
                        resp.sig = result.sig;
                        callback(resp);
                        return;
                    }
                },
                error: function(e) {
                    var resp = {};
                    console.log(e);
                    resp.msg = 'Get SIG fail from 3rd server!';
                    onError(resp);
                },
                timeout: 5000
            });
        },
		
		//获取当前时间
		getTimeStamp: function() {
            var now = new Date();
            var timestamp = now.getFullYear() + '' + ((now.getMonth() + 1) >= 10 ? "" + (now.getMonth() + 1) : "0" + (now.getMonth() + 1)) + (now.getDate() >= 10 ? now.getDate() : "0" + now.getDate()) + (now.getHours() >= 10 ? now.getHours() : "0" + now.getHours()) + (now.getMinutes() >= 10 ? now.getMinutes() : "0" + now.getMinutes()) + (now.getSeconds() >= 10 ? now.getSeconds() : "0" + now.getSeconds());
            return timestamp;
        },
		
		EV_login: function(user_account, pwd, sig, timestamp) {
			//console.log("EV_login");
            var loginBuilder = new RL_YTX.LoginBuilder();
            loginBuilder.setType(this.login_type);
            loginBuilder.setUserName(user_account);
			loginBuilder.setSig(sig);
			loginBuilder.setTimestamp(timestamp);
			RL_YTX.login(loginBuilder, function(obj) {
				//console.log(obj);return;
				/*音视频呼叫监听
				 obj.callId;//唯一消息标识  必有
				 obj.caller; //主叫号码  必有
				 obj.called; //被叫无值  必有
				 obj.callType;//0 音频 1 视频 2落地电话
				 obj.state;//1 对方振铃 2 呼叫中 3 被叫接受 4 呼叫失败 5 结束通话 6 呼叫到达
				 obj.reason//拒绝或取消的原因
				 obj.code//当前浏览器是否支持音视频功能
				 */ 
				IM.sendVoipCall(IM.call_phone);
                IM._onCallMsgListener = RL_YTX.onCallMsgListener(function(obj) {
					IM.EV_onCallMsgListener(obj);
                });
				
			}, function(obj) {
				console.log(obj);
                $.scojs_message("错误码： " + obj.code + "; 错误描述：" + obj.msg, $.scojs_message.TYPE_ERROR);
            });
		},
		
		//呼叫音频、视频
		sendVoipCall: function(calledUser, nickName) {
            var makeCallBuilder = new RL_YTX.MakeCallBuilder();
            makeCallBuilder.setCalled(calledUser.toString());
            makeCallBuilder.setCallType(this.call_type);
            makeCallBuilder.setNickName(nickName ? nickName : "normal");
            RL_YTX.makeCall(makeCallBuilder, function(e) {
            }, function(e) {
                console.log(e);
                //$.scojs_message(e.code + ' : ' + e.msg, $.scojs_message.TYPE_ERROR);
            });
        },
		
		//呼叫音频、视频结果处理
		EV_onCallMsgListener: function(obj) {
            if(obj.code != 200) {
                console.error(obj.code);
                return;
            }
			//音频请求
			this.processAudio(obj);
        },
		
		//音频请求
		processAudio: function(obj) {
			console.log(obj)
            var noticeMsg = null;
            if(obj.state == 1) { //，对方收到呼叫，对方振铃中
                IM.isCalling = true;
            } else if(obj.state == 2) { //发送请求成功 呼叫中
                this.currentCallId = obj.callId;
                IM.call_tips_id.html('呼叫中...');
                //document.getElementById('call_ring').play();
                IM.isCalling = true;
            } else if(obj.state == 3) { //对方接受
                //document.getElementById('call_ring').pause();
                //var s = this.currentCallWith.attr("data-call-state");
                //if(s == 3) {
                //    return;
                //}
                //this.currentCallWith.attr("data-call-state", "3");
                //var t = '<button data-btn="shutdownVoip">挂断</button>';
                //this.currentCallWith.find('[data-btn="cancelVoip"]').replaceWith(t);
                //var times = this.currentCallWith.find('[data-call="msg"]');
                //RL_YTX.setTimeWindow(times);
                noticeMsg = "[接收语音通话]";
                IM.call_tips_id.html('通话中...');
                IM.isCalling = true;
            } else if(obj.state == 4) { //呼叫失败 对主叫设定：自动取消，对方拒绝或者忙
                //document.getElementById('call_ring').pause();
                //this.currentCallWith.remove();
                //this.currentCallWith = null;
                this.currentCallId = null;
                noticeMsg = "[语音通话结束]";
                IM.call_tips_id.html('呼叫失败');
                IM.isCalling = false;
            } else if(obj.state == 5) { //对方挂断
                //this.currentCallWith.remove();
                //this.currentCallWith = null;
                this.currentCallId = null;
                noticeMsg = "[语音通话结束]";
                IM.call_tips_id.html('通话结束,对方挂断');
                //document.getElementById('call_ring').pause();
                IM.isCalling = false;
            } else if(obj.state == 6) { //接到对方发来的通话
                this.createAudioView(obj.caller, false, obj.callType);
                this.currentCallId = obj.callId;
                //document.getElementById('call_ring').play();
                noticeMsg = "[语音呼叫]";
                IM.isCalling = false;
            }
            if (!!noticeMsg) {
                //IM.DO_deskNotice(obj.caller, '', noticeMsg, '', false, true);
            }
            IM.callRecord(obj);
        },
		isCalling:false,
        
        //记录通话 
        callRecord: function(obj) {
            var data = {
                customer_id: IM.customer_id,
                mobile: obj.caller,
                callid: obj.callId,
                callsid: obj.userdata,
                call_status: obj.state,  
            };         
           $.post("/index.php?m=admin&c=customer&a=callRecord", data, function($result){
      
           },'json'); 
            
        },
	}
	
	window.IM = new YTX();
    IM.init();
})(jQuery);