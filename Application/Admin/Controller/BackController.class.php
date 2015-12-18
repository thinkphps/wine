<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台订单控制器
  * @author kevin <lamp365@163.com>
 */
class BackController extends AdminController {

    /**
     * 订单管理
     * author kevin <lamp365@163.com>
     */
    public function index(){
        /* 查询条件初始化 */
        $map    = array('status' =>1);
        $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,backinfo,shopid,reason,parameters';
        $list   = $this->lists('backlist', $map,'id desc',$field);
        $data   = getOrderListDocument($list,'goodid');
        $this->assign('list', $data);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        
        $this->meta_title = '退货管理';
        $this->display();
    }

    /**
     * 新增订单
     * @author kevin <lamp365@163.com>
     */
    public function add(){
        if(IS_POST){
            $Config = D('backlist');
            $data = $Config->create();
			  /* 新增时间并更新时间*/
  	$shopid=$_POST["shopid"];
      $shoplist=M('shoplist')->where("id='$shopid'")->setField('status','4');
	  if($data){
                if($Config->add()){
                    S('DB_CONFIG_DATA',null);
                    $this->success('新增成功', U('index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Config->getError());
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display();
        }
    }

    /**
     * 编辑订单
     * @author kevin <lamp365@163.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $Form      =  D('backlist');
            $memberid  = I('post.memberid');
            $shopid    = I('post.shopid');
            $id        = I('post.id');
            if($id){
                unset($_POST['id']);
                $Form->create();
                $Form->update_time = time();
                $result = $Form->where("id='$id'")->save();
                if($result){
                    //记录行为
                    user_log("管理员修改了,用户({$memberid})退货商品({$shopid})");
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $this->error('更新失败'.$id);
                }
            } else {
                $this->error('参数有误！');
            }
        } else {
            $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,backinfo,shopid,reason,parameters';
            $info   = M('backlist')->field($field)->find($id);
            if(empty($info)){
                $this->error('获取订单信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑订单';
            $this->display();
        }
    }
 /**
     * 同意订单
     * @author kevin <lamp365@163.com>
     */
    public function agree($id = 0){
       if(IS_POST){
            $Form = D('backlist');
           $Form->startTrans();
           $id     = I('post.id');
           $goodid = I('post.goodid');
           $num    = I('post.num');
           $shopid = I('post.shopid');
           $memberid = I('post.memberid');
           unset($_POST['id']);
           unset($_POST['goodid']);
           unset($_POST['num']);
           unset($_POST['shopid']);
           unset($_POST['memberid']);
            if($id){
                //销量减1 库存加1
                $total_num = "`total_num`+{$num}";
                $setdata = array(
                    'sale'           => array('exp', '`sale`-1'),
                    'total_num'      => array('exp', $total_num),
                );
                $sales = M('document')->where("id='$goodid'")->save($setdata);

                $Form->create();
                $Form->update_time = time();
                $Form->status      = 2;  //确认换货

                $res1 = $Form->where("id='$id'")->save();
                /* 编辑后更新商品反馈信息*/
                $res2 = M('shoplist')->where("id='$shopid'")->setField('status','5');

                if($res1 && $res2){
                    $Form->commit();
                    //记录行为
                    user_log("管理员确认了用户({$memberid})退货(gid:{$goodid})");
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $Form->rollback();
                    $this->error('更新失败');
                }
            } else {
                $this->error('参数有误！');
            }
        } else {
           /* 获取数据 */
           $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,backinfo,shopid,reason,parameters';
           $info   = M('backlist')->field($field)->where('status=1')->find($id);

           if(false === $info){
               $this->error('获取订单信息错误');
           }
           $this->assign('info', $info);
           $this->meta_title = '编辑订单';
           $this->display();
        }
    }

   /**
     * 拒绝订单
     * @author kevin <lamp365@163.com>
     */
public function refuse(){
       if(IS_POST){
            $Form     = D('backlist');
            $id       = I('post.id');
            $goodid   = I('post.goodid');
            $shopid   = I('post.shopid');
            $memberid = I('post.memberid');
            if($id){
                unset($_POST['id']);
                unset($_POST['goodid']);
                unset($_POST['shopid']);
                $Form->startTrans();
                $Form->create();
                $Form->status      = 3;
                $Form->update_time = time();
                $res1 = $Form->where("id='$id'")->save();

                /* 编辑后更新商品反馈信息*/
                $res2 = M('shoplist')->where("id='$shopid'")->setField('status','7');//买家填写退货快递，状态6
                if($res1 && $res2){
                    $Form->commit();
                    //记录行为
                    user_log("管理员拒绝用户({$memberid})换货({$goodid})");
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    $Form->rollback();
                    $this->error('更新失败');
                }
            } else {
                $this->error('参数有误！');
            }
        } else {
           /* 获取数据 */
           $field  = 'id,goodid,num,tool,toolid,uid,status,create_time,info,total,backinfo,shopid,reason,changetool,changetoolid,parameters';
           $info   = M('backlist')->field($field)->where('status=1')->find(I('get.id'));
           if(false === $info){
               $this->error('获取订单信息错误');
           }

           $this->assign('info', $info);
           $this->meta_title = '拒绝退货订单';
           $this->display();
        }
    }
   /**
     * 删除订单
     * @author kevin <lamp365@163.com>
     */
    public function del(){
       if(IS_POST){
           $ids   = I('post.id');
           $order = M("backlist");

           if(is_array($ids)){
               $wh['id'] = array ('in',$ids);
               $order->where($wh)->delete();
           }
           $this->success("删除成功！");
           $this->success("删除成功！");
        }else{
            $id = I('get.id');
            $db = M("backlist");
            $status = $db->where("id='$id'")->delete();
            if ($status){
                $this->success("删除成功！");
            }else{
                $this->error("删除失败！");
            }
        } 
    }

    public function see()
    {
        $shopid = I('get.shopid');
        if(empty($shopid))
            $this->error('获取订单信息错误');
        $tag    = M('shoplist')->field('tag')->find($shopid);
        $order  = M('order')->where($tag)->field('id')->find();

        $data = seeUserOrderDetail($order['id']);
        if(!$data)
            $this->error('获取订单信息错误');
        $this->assign('list', $data['list']);
        $this->assign('detail', $data['detail']);

        $this->meta_title = '订单发货';
        $this->display();
    }

}