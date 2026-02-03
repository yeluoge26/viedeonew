$(function(){
    var issubmit=0;
    $(".apply_ok").click(function(){
        if(issubmit){
            return !1;
        }
        var thumb=$("#thumb").val();
        var name=$("#name").val();
        var des=$("#des").val();
        var tel=$("#tel").val();
        var certificate=$("#certificate").val();
        var license=$("#license").val();
        var other=$("#other").val();
        
        if(thumb==''){
            layer.msg('请上传店铺图片');
            return !1;
        }
        
        if(name==''){
            layer.msg('请输入店铺名称');
            return !1;
        }
        
        if(des==''){
            layer.msg('请输入店铺简介');
            return !1;
        }
        
        if(certificate==''){
            layer.msg('请上传营业执照');
            return !1;
        }
        
        if(license==''){
            layer.msg('请上传许可证');
            return !1;
        }
        
        issubmit=1;
        $.ajax({
            url:'/index.php?g=appapi&m=shop&a=apply_post',
            type:'POST',
            data:{uid:uid,token:token,thumb:thumb,name:name,des:des,tel:tel,certificate:certificate,license:license,other:other},
            dataType:'json',
            success:function(rs){
                issubmit=0;
                if(rs.code!=0){
                    layer.msg(rs.msg);
                    return !1;
                }
                layer.msg(rs.msg);
                location.reload();
            },
            error:function(e){
                issubmit=0;
                layer.msg('提交失败，请重试');
            }
        })
    })

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
							url: '/index.php?g=Appapi&m=shop&a=upload',
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