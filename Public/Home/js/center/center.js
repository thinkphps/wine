/**
 * Created by kevin on 15-10-19.
 */
define(function(require, exports, module){
    T    = require('T');
    main = require('main');
    main.modalEvent();

    var center_init = function(){
        //首页默认被选中
        $(".menu li").eq(0).addClass('active');

        //菜单移动时显示和收缩
        $("#all-goods").hide();
        $(".goods").mouseover(function(){
            $("#all-goods").show();
        });

        $(".goods").mouseout(function(){
            $("#all-goods").hide();
        });



        /** 左边菜单高亮 **/
        url = window.location.pathname + window.location.search;
        url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
        if(url == '/Center/needpay.html' || url=='/Center/tobeshipped.html' || url=='/Center/tobeconfirmed.html'){
            $("a[href='/Center/allorder.html']").addClass("current") ;
        }else{
            $("a[href='" + url + "']").addClass("current") ;
        }

    }


    var center_order_action = function(){
        //全选的实现
        $(".check-all").click(function(){
            $(".ids").prop("checked", this.checked);
        });
        $(".ids").click(function(){
            var option = $(".ids");
            option.each(function(i){
                if(!this.checked){
                    $(".check-all").prop("checked", false);
                    return false;
                }else{
                    $(".check-all").prop("checked", true);
                }
            });
        });


        //取消订单
        $(".cancle_order").click(function(){
            var url = $(this).data('url');
            var id  = $(this).data('id');
            T.restPost(url,{id:id},function(success){
                main.modalAlert(success.msg);
                if($(".cancle_order").length == '1'){
                    main.redirect('')
                }
                $(this).closest(".order-detail").parent().remove();
            },function(error){
                main.modalAlert(error.msg,'danger');
            });
        });

        //订单管理  删除订单
        $(".delorder").click(function(){
            var url = $("form[name=delform]").attr('action');
            var param = $("form[name=delform]").serialize();
            T.restPost(url,param,function(success){
                main.modalAlert(success.msg);
                main.redirect('');
            },function(error){
                main.modalAlert(error.msg,'danger');
            });
        })

    }

    module.exports = {
        center_init : center_init,
        center_order_action : center_order_action
    }
});