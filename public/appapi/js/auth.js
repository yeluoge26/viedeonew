$(function(){
	/*点击提交审核*/
	var is_submit=0;
	$(".auth_ok").click(function(){
		if(is_submit==1){
			return;
		}

		var realname=$("#realname").val();
		var phone=$("#phone").val();
		var cardno=$("#cardno").val();
		var front=$("#front").val();
		var back=$("#back").val();
		var hand=$("#hand").val();

		if(realname==''){
			layer.msg("请输入姓名");
			return;
		}
        
        if(cardno==''){
			layer.msg("请输入身份证号码");
			return;
		}
        
		if(phone==''){
			layer.msg("请输入手机号码");
			return;
		}
        
        if(front==''){
			layer.msg("请上传证件正面");
			return;
		}
        
        if(back==''){
			layer.msg("请上传证件反面");
			return;
		}
        
        if(hand==''){
			layer.msg("请上传手持证件正面照");
			return;
		}

		is_submit=1;

		$.ajax({
			url: '/index.php?g=Appapi&m=Auth&a=auth_save',
			type: 'POST',
			dataType: 'json',
			data: {uid:uid,token:token,realname: realname,phone:phone,cardno:cardno,front:front,back:back,hand:hand},
			success:function(data){
                is_submit=0;
				var code=data.code;
				if(code!=0){
					layer.msg(data.msg);
					return;
				}else{
					//layer.msg("认证成功");
					layer.msg('认证成功', {time:1000},function(){
						location.reload();
					});


				}
			},
			error:function(e){
                is_submit=0;
				console.log(e);
			}
		});
		
	});
    
    $(".up_img img").click(function(){
        if(status!='' && status==0){
            return !1;
        }
        var _this=$(this).parent();
        upload(_this);
    })

    function upload(_this) {

			var iptt=$('.file_input',_this)[0];
			var shadd=$('.shadd',_this);
            //var iptt=document.getElementById(index);
			if(window.addEventListener) { // Mozilla, Netscape, Firefox
                iptt.addEventListener('change',function(){
                    ajaxFileUpload(_this);
                    shadd.show();
                },false);
			}else{
                iptt.attachEvent('onchange',function(){
                    ajaxFileUpload(_this);
                    shadd.show();
                });
			}
			iptt.click();
    }
    function ajaxFileUpload(_this) {
            var animate_div=$(".progress_sp",_this);
            var shadd=$(".shadd",_this);
            
			animate_div.css({"width":"0px"});

            var id=_this.data('fileid');
			animate_div.animate({"width":"100%"},700,function(){
					$.ajaxFileUpload
					(
						{
							url: '/index.php?g=Appapi&m=auth&a=upload',
							secureuri: false,
							fileElementId: id,
							data: { saveName:'shopauth' },
							dataType: 'html',
							success: function(data) {
                                data=data.replace(/<[^>]+>/g,"");
                                var str=JSON.parse(data);
                                console.log(str);
                                if(str.code==200){
                                    $("img",_this).attr("src",str.data.url);
                                    $(".img_input",_this).attr("value",str.data.url);
                                    shadd.hide();
                                }else{
                                    layer.msg(str.msg);
                                    shadd.hide();
                                }
							},
							error: function(data) {
                                layer.msg("上传失败");
                                shadd.hide();
							}
						}
					)
					return true;
			});
    }    
});