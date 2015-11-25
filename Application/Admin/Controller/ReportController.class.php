<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013  All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin <lamp365@163.com> 
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台数据统计控制器
 *  Author: kevin <lamp365@163.com> 
     */

class ReportController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){ 
		$day      = date('Y年m月d日',NOW_TIME);/*格式时间戳为 201410*/
        $this->assign('day',$day);
        $turnover = M("turnover");
        $field    = 'id,uid,sales,back,change,profits,cancel,create_time,update_time,status,info';
        $list=$turnover->where("status='1'")->field($field)->select();
            $this->assign('list',$list);
      
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->meta_title = '每日数据报表';
        $this->display();
    }

 public function week(){
	  $week = date('W',NOW_TIME);/*格式时间戳为 201410*/
      $this->assign('week',$week);
      $turnover = M("turnover");
      $field    = 'id,uid,sales,back,change,profits,cancel,create_time,update_time,status,info';
      $list     = $turnover->where("status='2'")->field($field)->select();
      $this->assign('list',$list);
      
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->meta_title = '每周数据报表';
        $this->display();
    }


 public function month(){
     $month    = date('Y年m月',NOW_TIME);/*格式时间戳为 201410*/
     $this->assign('month',$month);
     $turnover=M("turnover");
     $field    = 'id,uid,sales,back,change,profits,cancel,create_time,update_time,status,info';
     $list     = $turnover->where("status='3'")->field($field)->select();
     $this->assign('list',$list);
      
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->meta_title = '每月数据报表';
        $this->display();
    }


/**
 *接收数据并统计
 * @return none
 */
 public function detail(){
     $day=I('get.day');
     if($day)
     {
	    $this->detailByDay($day);
     }

    $week=I('get.week');
    if($week)
    {
        $this->detailByWeek($week);
    }

    $month=I('get.month');
    if($month)
    {
        $this->detailByMonth($month);
    }
    $this->display();
 
 }



   public function edit($id = 0){
        if(IS_POST){
            $Form = D('turnover');
            $uid  = is_login();
            if($_POST["id"]){
			    $Form->create();
				$id = $_POST["id"];
			    /*更新时间*/
	            $Form->update_time = NOW_TIME;
                $result = $Form->where("id='$id'")->save();
                if($result){
                    //记录行为
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败'.$id);
                }
            } else {
                $this->error('参数有误');
            }
        } else {
            $info = array();
            /* 获取数据 */
            $field    = 'id,uid,sales,back,change,profits,cancel,create_time,update_time,status,info';
            $info = M('turnover')->field($field)->find($id);

            if(false === $info){
                $this->error('获取订单信息错误');
            }

			$this->assign('info', $info);
            $this->meta_title = '编辑报表';
            $this->display();
        }
    }

    /**
    * 删除后台Report
    * 后台数据统计控制器
    *  Author: kevin <lamp365@163.com>
    */
    public function del(){
       if(IS_POST){
            $ids   = I('post.id');
            $order = M("turnover");
			
            if(is_array($ids)){
                 foreach($ids as $id){
                    $order->where("id='$id'")->delete();
                 }
            }
           $this->success("删除成功！");
        }else{
            $id = I('get.id');
            $db = M("turnover");
            $status = $db->where("id='$id'")->delete();
            if ($status){
                $this->success("删除成功！");
            }else{
                $this->error("删除失败！");
            }
        } 
    }


   public function detailByDay($day)
   {
       /***按天数统计 */
       $a =   date('Ymd',$day);/*格式时间戳为 20141024*/
       $this->meta_title =date('Y年m月d日',$day).'的数据统计';
       $title=date('Y年m月d日',$day).'的数据统计';

       /*销售统计*/
       $field  = 'id,orderid,tag,pricetotal,create_time,status,ispay,total,shipprice';
       $order  = M("order")->where("total!=''")->field($field)->select();

       $salesmoney = $trans = $total = $salenum = $salecount = 0;
       foreach($order as $n=> $val){
           $time = $val['create_time'];
           $b    = date('Ymd',$time);
           if($a == $b){
               /*商品销售额*/
               $salesmoney += $val['total'];
               /*运费*/
               $trans  += $val['shipprice'];
               /*总销售额*/
               $total += $val['pricetotal'];
               $id   = $val['id'];

               $field = 'id,goodid,num,orderid,uid,status,create_time,price,total,sort,tag,parameters';
               $list  = M("shoplist")->where("orderid='$id'")->field($field)->select();

               foreach($list as $k=> $vo){
                   /*退货中的商品id*/
                   $goodid = $vo['goodid'];
                   $price  = $vo['price'];

                   /*销售的商品件数*/
                   $salenum+=$vo['num'];
                   /*销售的商品种类数*/
                   $salecount+=1;
               }
           }
       }
       $this->assign('salecount',$salecount);
       $this->assign('salenum',$salenum);
       $this->assign('trans',$trans);
       $this->assign('salesmoney',$salesmoney);
       $this->assign('total',$total);

       /**
        * 当天退货金额、种类、数量计算
        */
       $back  = $backnum = $backcount = 0;
       $field = 'id,goodid,num,tool,toolid,status,create_time,info,total,shopid';
       $data  = M("backlist")->where("status='1'")->field($field)->select();
       foreach ($data as $k=>$val) {
           /*格式时间戳为 20141024*/
           $time = $val['create_time'];
           $d    = date('Ymd',$time);
           if($a == $d){//当天
               /*退货中的商品id*/
               $goodid = $val['goodid'];
               $price  = get_good_price($goodid);
               /*退货中的商品总额*/
               $back     += $val['num'] * $price;
               /*退货中的商品件数*/
               $backnum  +=$val['num'];
               /*退货中的商品种类数*/
               $backcount +=1;
           }
       }
       $this->assign('back',$back);
       $this->assign('backcount',$backcount);
       $this->assign('backnum',$backnum);

       /**
        * 当天换货金额、种类、数量计算
        */
       $field = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,shopid';
       $data = M("change")->where("status='1'")->field($field)->select();
       $change = $changenum = $changecount = 0;
       foreach ($data as $k=>$val) {
           $time = $val['create_time'];
           $f    = date('Ymd',$time);
           if($a==$f){//当天
               /*换货中的商品总额*/
               $goodid=$val['goodid'];
               $price=get_good_price($goodid);
               $change += $val['num'] * $price;
               /*换货中的商品件数*/
               $changenum+=$val['num'];
               /*换货中的商品种类数*/
               $changecount+=1;
           }
       }
       $this->assign('change',$change);
       $this->assign('changecount',$changecount);
       $this->assign('changenum',$changenum);

       /**
        * 当天申请取消订单金额、种类、数量计算
        */
       $field = 'id,goodid,num,orderid,create_time,shopid,status,cash,count';
       $cdata = M("cancel")->where("status='1'")->field($field)->select();
       $cancel = $cancelnum = $cancelcount = 0;
       foreach ($cdata as $k=>$val)
       {
           /*格式时间戳为 20141024*/
           $stime=$val['create_time'];
           $s    =date('Ymd',$stime);
           if( $a == $s){
               //当天
               /*取消订单的商品总额*/
               $cancel+= $val['cash'];
               /*取消订单的商品件数*/
               $cancelnum+=$val['num'];
               /*取消订单的商品种类数*/
               $cancelcount+=$val['count'];
           }
       }
       $this->assign('cancel',$cancel);
       $this->assign('cancelcount',$cancelcount);
       $this->assign('cancelnum',$cancelnum);
       /*比率统计*/
       $percent_a=$total/($total+$back+$change+$cancel);
       $percent_b=$back/($total+$back+$change+$cancel);
       $percent_c=$change/($total+$back+$change+$cancel);
       $percent_d=$cancel/($total+$back+$change+$cancel);
       $this->assign('a',$percent_a);
       $this->assign('b',$percent_b);
       $this->assign('c',$percent_c);
       $this->assign('d',$percent_d);
       /*利润*/
       $profits=$total-$back-$change-$cancel;
       $this->assign('profits',$profits);
       $this->assign('title',$title);
   }

    public function detailByWeek($week)
    {
        $w     = date('W',$week);/*格式时间戳为本年度周数*/
        $y     = date('Y',$week);
        $this->meta_title =$y."年度第".date('W',$week).'周的数据统计';
        $title = $y."年度第".date('W',$week).'周的数据统计';
        /*销售统计*/
        $field  = 'id,orderid,tag,pricetotal,create_time,status,ispay,total,shipprice';
        $order  = M("order")->where("total!=''")->field($field)->select();
        $salesmoney = $trans = $total = $salenum = $salecount = 0;
        foreach($order as $n=> $val){
            $time=$val['create_time'];
            $b=date('W',$time);
            if($w==$b){//
                /*商品销售额*/
                $salesmoney+=$val['total'];
                /*运费*/
                $trans+=$val['shipprice'];
                /*总销售额*/
                $total+=$val['pricetotal'];
                $id=$val['id'];

                $field = 'id,goodid,num,orderid,uid,status,create_time,price,total,sort,tag,parameters';
                $list  = M("shoplist")->where("orderid='$id'")->field($field)->select();
                foreach($list as $k=> $vo){
                    /*退货中的商品id*/
                    $goodid = $vo['goodid'];
                    $price  = $vo['price'];

                    /*销售的商品件数*/
                    $salenum+=$vo['num'];
                    /*销售的商品种类数*/
                    $salecount+=1;
                }
            }
        }
        $this->assign('salecount',$salecount);
        $this->assign('salenum',$salenum);
        $this->assign('trans',$trans);
        $this->assign('salesmoney',$salesmoney);
        $this->assign('total',$total);
        /**
         * 当周退货金额、种类、数量计算
         */
        $back  = $backnum = $backcount = 0;
        $field = 'id,goodid,num,tool,toolid,status,create_time,info,total,shopid';
        $data = M("backlist")->where("status='1'")->field($field)->select();
        foreach ($data as $k=>$val) {
            /*格式时间戳为 20141024*/
            $time=$val['create_time'];
            $d=date('W',$time);
            if($w==$d){//当天周
                /*退货中的商品id*/
                $goodid=$val['goodid'];
                $price=get_good_price($goodid);
                /*退货中的商品总额*/
                $back+= $val['num'] * $price;
                /*退货中的商品件数*/
                $backnum+=$val['num'];
                /*退货中的商品种类数*/
                $backcount+=1;
            }
        }
        $this->assign('back',$back);
        $this->assign('backcount',$backcount);
        $this->assign('backnum',$backnum);

        /**
         * 当周换货金额、种类、数量计算
         */
        $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,shopid';
        $change = $changenum = $changecount = 0;
        $data   = M("change")->where("status='1'")->field($field)->select();

        foreach ($data as $k=>$val) {
            $time=$val['create_time'];
            $f=date('W',$time);
            if($w==$f){//同周
                /*换货中的商品总额*/
                $goodid = $val['goodid'];
                $price  = get_good_price($goodid);
                $change += $val['num'] * $price;
                /*换货中的商品件数*/
                $changenum+=$val['num'];
                /*换货中的商品种类数*/
                $changecount+=1;
            }
        }
        $this->assign('change',$change);
        $this->assign('changecount',$changecount);
        $this->assign('changenum',$changenum);
        /**
         * 本周申请取消订单金额、种类、数量计算
         */
        $field  = 'id,goodid,num,orderid,create_time,shopid,status,cash,count';
        $cancel = $cancelnum = $cancelcount = 0;
        $cdata  = M("cancel")->where("status='1'")->field($field)->select();
        foreach ($cdata as $k=>$val)
        {
            /*格式时间戳为 20141024*/
            $setime=$val['create_time'];
            $se=date('W',$setime);
            if($w==$se){
                //当天
                /*取消订单的商品总额*/
                $cancel+= $val['cash'];
                /*取消订单的商品件数*/
                $cancelnum+=$val['num'];
                /*取消订单的商品种类数*/
                $cancelcount+=$val['count'];
            }
        }
        $this->assign('cancel',$cancel);
        $this->assign('cancelcount',$cancelcount);
        $this->assign('cancelnum',$cancelnum);
        /*比率统计*/
        $percent_a=$total/($total+$back+$change+$cancel);
        $percent_b=$back/($total+$back+$change+$cancel);
        $percent_c=$change/($total+$back+$change+$cancel);
        $percent_d=$cancel/($total+$back+$change+$cancel);
        $this->assign('a',$percent_a);
        $this->assign('b',$percent_b);
        $this->assign('c',$percent_c);
        $this->assign('d',$percent_d);
        /*利润*/
        $profits=$total-$back-$change-$cancel;
        $this->assign('profits',$profits);
        $this->assign('title',$title);
    }


    public function detailByMonth($month)
    {
        $m     = date('Ym',$month);;/*格式时间戳为 201410*/
        $this->meta_title =date('Y年m月',$month).'的数据统计';
        $title = date('Y年m月',$month).'的数据统计';
        /*销售统计*/
        $field  = 'id,orderid,tag,pricetotal,create_time,status,ispay,total,shipprice';
        $order=M("order")->where("total!=''")->field($field)->select();
        $salesmoney = $trans = $total = $salenum = $salecount = 0;
        foreach($order as $n=> $val){
            $time = $val['create_time'];
            $b    = date('Ym',$time);
            if( $m==$b ){//
                /*商品销售额*/
                $salesmoney+=$val['total'];
                /*运费*/
                $trans+=$val['shipprice'];
                /*总销售额*/
                $total+=$val['pricetotal'];
                $id    = $val['id'];

                $field = 'id,goodid,num,orderid,uid,status,create_time,price,total,sort,tag,parameters';
                $list  = M("shoplist")->field($field)->where("orderid='$id'")->select();
                foreach($list as $k=> $vo){
                    /*退货中的商品id*/
                    $goodid = $vo['goodid'];
                    $price  = $vo['price'];

                    /*销售的商品件数*/
                    $salenum+=$vo['num'];
                    /*销售的商品种类数*/
                    $salecount+=1;
                }
            }
        }
        $this->assign('salecount',$salecount);
        $this->assign('salenum',$salenum);
        $this->assign('trans',$trans);
        $this->assign('salesmoney',$salesmoney);
        $this->assign('total',$total);
        /**
         * 当月退货金额、种类、数量计算
         */
        $back  = $backnum = $backcount = 0;
        $field = 'id,goodid,num,tool,toolid,status,create_time,info,total,shopid';
        $data  = M("backlist")->field($field)->where("status='1'")->select();
        foreach ($data as $k=>$val) {
            /*格式时间戳为 20141024*/
            $time=$val['create_time'];
            $d=date('Ym',$time);
            if($m==$d){//当天
                /*退货中的商品id*/
                $goodid=$val['goodid'];
                $price=get_good_price($goodid);
                /*退货中的商品总额*/
                $back+= $val['num'] * $price;
                /*退货中的商品件数*/
                $backnum+=$val['num'];
                /*退货中的商品种类数*/
                $backcount+=1;
            }
        }
        $this->assign('back',$back);
        $this->assign('backcount',$backcount);
        $this->assign('backnum',$backnum);

        /**
         * 当月换货金额、种类、数量计算
         */
        $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,shopid';
        $change = $changenum = $changecount = 0;
        $data   = M("change")->where("status='1'")->field($field)->select();

        foreach ($data as $k=>$val) {
            $time=$val['create_time'];
            $f=date('Ym',$time);
            if($m==$f){//同月
                /*换货中的商品总额*/
                $goodid=$val['goodid'];
                $price=get_good_price($goodid);
                $change += $val['num'] * $price;
                /*换货中的商品件数*/
                $changenum+=$val['num'];
                /*换货中的商品种类数*/
                $changecount+=1;
            }
        }
        $this->assign('change',$change);
        $this->assign('changecount',$changecount);
        $this->assign('changenum',$changenum);
        /**
         * 本周申请取消订单金额、种类、数量计算
         */
        $field  = 'id,goodid,num,orderid,create_time,shopid,status,cash,count';
        $cancel = $cancelnum = $cancelcount = 0;
        $cdata  = M("cancel")->where("status='1'")->field($field)->select();
        foreach ($cdata as $k=>$val)
        {
            /*格式时间戳为 20141024*/
            $sytime=$val['create_time'];
            $sy=date('Ym',$sytime);
            if($m==$sy){
                //当天
                /*取消订单的商品总额*/
                $cancel+= $val['cash'];
                /*取消订单的商品件数*/
                $cancelnum+=$val['num'];
                /*取消订单的商品种类数*/
                $cancelcount+=$val['count'];
            }
        }
        $this->assign('cancel',$cancel);
        $this->assign('cancelcount',$cancelcount);
        $this->assign('cancelnum',$cancelnum);
        /*比率统计*/
        $percent_a=$total/($total+$back+$change+$cancel);
        $percent_b=$back/($total+$back+$change+$cancel);
        $percent_c=$change/($total+$back+$change+$cancel);
        $percent_d=$cancel/($total+$back+$change+$cancel);
        $this->assign('a',$percent_a);
        $this->assign('b',$percent_b);
        $this->assign('c',$percent_c);
        $this->assign('d',$percent_d);
        /*利润*/
        $profits=$total-$back-$change-$cancel;
        $this->assign('profits',$profits);
        $this->assign('title',$title);
    }
}