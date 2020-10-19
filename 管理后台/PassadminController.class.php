<?php
/**
 * 文章
 */
namespace Pass\Controller;
use Common\Controller\AdminbaseController;
use Api\Controller\FileController;
class PassadminController extends AdminbaseController {
    private $newsModel;
    private $newsOtherModel;
    private $newsCateModel;
    private $newsFloorModel;
    private $passModel1;
    public function __construct(){
        parent::__construct();
        $this->newsModel=D('News');
        $this->newsOtherModel=D('NewsOther');
        $this->newsCateModel=D('NewsCate');
        $this->newsFloorModel=D('NewsFloor');
        $this->passModel1=D('Pass');
    }
    /*列表*/
    public function pass_list(){
        $cid=I('cid');
        $title=I('title');
        $author=I('author');
//        $where=array('status'=>array('gt',0));
        if($cid){
            $where['cid']=$cid;
        }
//        if($cid==22){
//            $where['cid']=array('in','21,22,23,24');
//        }elseif($cid==21){
//            $where['cid']=array('in','21,24');
//        }elseif($cid==23){
//            $where['cid']=array('in','23,24');
//        }
        if($cid==57){
//            $passModel=M('Pass');
//            $where1['type']=1;
//            $count=$passModel->where($where1)->count();

            $count=$this->passModel1->count();
            $page=new \Think\Page($count);
//            $list=$passModel->where($where1)
//            $where1['status'] = array('neq',-1);
////            $list=$passModel
//            $list=$this->passModel1
//                ->where($where1)
//                ->order('apply_time desc')
//                ->limit($page->firstRow.','.$page->listRows)
//                ->select();
            $list=$this->passModel1->getList();
            //这个是遇到身份证被替换成星号时，以为通过model层可以解决的尝试，最后并没解决问题


//            var_dump($list);die;  //1597927110--1606665600
//            var_dump($list[0]['expiry_date']);die;
            if($list){
                foreach($list as $key=>$v){
                    /*判断是否过期并更新数据表*/
//                    if ($v['status']==1 && time()>$v['expiry_date']){
                    //  //如果出入证是正常生效的，并且当前时间大于有效期的
//                    var_dump($v['expiry_date']);exit;
                    if ($v['expiry_date']!=null && time()>$v['expiry_date']){
                    //不管当前出入证是什么状态，只要当前时间大于有效期，都设置为已过期
                        $res=M('Pass')->where(array('id'=>$v['id']))->setField('status',3);
//                        echo M('Pass')->getLastSql();exit;
                    }
                    if ($v['status']==3 && time()<$v['expiry_date'] && $v['check_biz']==2){
                        //如果出入证当前状态是过期的,且客服部审核状态是通过的，但当前时间小于有效期的，说明中途去后台延长了有效期，出入证状态变有效了
                        M('Pass')->where(array('id'=>$v['id']))->setField('status',1);
                    }
                    //如果客服部或安管部有一个未通过审核，并且出入证状态不是未通过时，修改状态为未通过
                    if (($v['check_biz']==1 or $v['check_tra']==1) && $v['status']!=2){
                        M('Pass')->where(array('id'=>$v['id']))->setField('status',2);
                    }
//                    //如果客服部或安管部有一个待审核，并且客服部或安管部都不为未通过，说明中途去后台修改了正常生效的出入证为待审核，设置出入证为待审核状态
//                    if (($v['check_biz']==0 or $v['check_tra']==0) && ($v['check_biz']!=1 && $v['check_tra']!=1)){
//                        M('Pass')->where(array('id'=>$v['id']))->setField('status',0);
//                    }


                    $list[$key]['type_name']=get_type_name($v['type']);
                    //get_type_name这个方法在/application/Pass/Common/function.php中定义
                    $list[$key]['status_name']=get_status_name($v['status']);
//                    $v['user_name']=$v['name'];
                    $str = $list[$key]['phone'];
                    $list[$key]['phone'] = substr($str,0,3)."-".substr($str,3,4)."-".substr($str,7,4);
                }
            }
//            var_dump($list);die;

            /*array(2) {
                    [0]=> array(14) {
                        ["id"]=> string(1) "1"
                        ["name"]=> string(6) "冯岩"
                        ["phone"]=> string(11) "13751861851"
                        ["store_id"]=> string(4) "5621"
                        ["card_id"]=> string(18) "352102198812082350"
                        ["photo"]=> NULL
                        ["type"]=> string(1) "1"
                        ["expiry_date"]=> string(10) "1571630183"
                        ["apply_time"]=> string(10) "1571600183"
                        ["status"]=> string(1) "0"
                        ["biz_verify"]=> string(9) "崔燕青"
                        ["biz_verify_time"]=> string(10) "1571610183"
                        ["train_verify"]=> string(0) ""
                        ["train_verify_time"]=> NULL }
                        ["type_name"]=> string(6) "员工"
                        ["status_name"]=> string(9) "待审核"
                        }
                    [1]=> array(14) {……
                    */

            $this->assign('title','出入证列表');
            $this->assign('cid',$cid);
            $this->assign('list',$list);
            $this->assign('page',$page->show('Admin'));
            $this->display();
//            die;
        }
//        if($title){
//            $where['title']=array('like',"%$title%");
//        }
//        if($author){
//            $users=M('users')->where(array('user_nicename'=>array('like',"%$author%")))->getField('id',true);
//            if($users){
//                $where['created']=array('in',implode(',',$users));
//            }
//        }
//        $count=$this->newsModel->get_count(true,$where);
//        $page=new \Think\Page($count);
//        $list=$this->newsModel->get_list(true,$where,$page->firstRow.','.$page->listRows);
//        if($list){
//            foreach($list as &$v){
//                $v['style_name']=get_style_name($v['style']);
//                $v['status_name']=get_status_name($v['status']);
//                if($v['created']){
//                    $v['user_name']=get_user_name($v['created']);
//                }
//            }
//        }
//
//        $this->assign('title','列表');
//        $this->assign('cid',$cid);
//        $this->assign('list',$list);
//        $this->assign('page',$page->show('Admin'));
//        $this->display();
    }

    /*删除出入证*/
    public function del_msg(){
        $id=I('id');
        $cid=I('cid');
        if(!$id){
            $this->error('未知的留言');
        }
        $data=array('id'=>$id);
        $res=M('Pass')->where($data)->setField('status',-1);
        if($res!==false){
            $this->success('删除成功',U('pass_list',array('cid'=>57)));
        }else{
            $this->error('删除失败');
        }
    }

    /*查看审核出入证*/
    public function detail_check(){
        //http://www.comicity.cn/index.php?s=/pass/passadmin/detail_check/id/1/check_type/1
        //其中：check_type是在出入证列表页面，视图层点击“业务审核1”和“培训部审核2”不同按钮进来赋的参数
        $id=I('id');
        $check_type=I('check_type');
        $passModel=M('Pass');   //没有model层，直接M函数写进数据库表。
        if(IS_POST){
            if(!$id){
                $this->error('未知的出入证');
            }
            $take_data=I('post.');
//            var_dump($take_data);exit;   //array(6) { ["store_id"]=> string(2) "83" ["type"]=> string(1) "2" ["status"]=> string(1) "2" ["expiry_date"]=> string(0) "" ["id"]=> string(3) "106" ["check_type"]=> string(1) "1" }
//            $back_id=I('back_id');
            $content=$take_data['content'];   //审核人姓名
            $data['type'] =  $take_data['type'];   //申请人类型，是员工还是临时工
            if (!$take_data['expiry_date'] && $data['type']==1){
                //因为临时工安管部审核时虽然前台隐藏了有效期设置，但仍会POST一个空过来，所以条件限定为$data['type']==1
                $this->error('请设置出入证有效期后再提交');
            }
            $data['store_id'] =  $take_data['store_id'];   //商铺号
//            $data['back']=$id;
            $check_res_status=$take_data['status'];   //审核通过还是不通过。1通过，2不通过；
//            var_dump($check_type);exit;
            if ($check_type==1){
                $data['check_biz']=$check_res_status;
                $data['biz_verify']=$content;
                $data['biz_verify_time']=time();
                //如果当前页面是在客服部审核页面，就把有效期存到数据库；
                //如果当前页面是在培训安管部审核页面，有效期是空的，就不要去覆盖数据库了。
                $data['expiry_date']=strtotime($take_data['expiry_date']);  //expiry_date有效期
            }else{
                $data['check_tra']=$check_res_status;
                $data['train_verify']=$content;
                $data['train_verify_time']=time();
            }

//            if(!$back_id){
//                $res=$passModel->add($data);
//            }else{
//                $data['id']=$back_id;

//            var_dump($data);exit;
            /*array(5) {
                    ["type"]=> string(1) "2"
                    ["expiry_date"]=> int(1609344000)
                    ["check_biz"]=> string(1) "2"
                    ["biz_verify"]=> string(24) "业务部负责人-66666"
                    ["biz_verify_time"]=> int(1597675024) }*/


                $data['id']=$id;
                $res=$passModel->save($data);
//            }
            if($res!==false){
                $info=$passModel->find($id);
//                var_dump($info);exit;
                switch ($info['type']){
                    case 1:
                        //如果类型是员工，则只需要客服部核销通过即可；且当前时间是小于设置的出入证有效日期的
                        if ($info['check_biz']==2 && time()<$info['expiry_date']){
                            $passModel->where(array('id'=>$id))->setField('status',1);
                        }
                        //如果客服部待审核，并且安管部不为未通过，说明中途去后台修改了正常生效的出入证为待审核，设置出入证为待审核状态
                        if ($info['check_biz']==0 && $info['check_tra']!=1){
                            $passModel->where(array('id'=>$id))->setField('status',0);
                        }
                        break;
                    case 2:
//                        var_dump($info);exit;
                        //如果类型是临时工，需要客服部和培训部都通过，且在有效期内
                        if ($info['check_biz']==2 && $info['check_tra']==2 && time()<$info['expiry_date']){

                            $passModel->where(array('id'=>$id))->setField('status',1);
//                            echo $passModel->getLastSql();exit;
                        }
                        if ($info['check_tra']==0 && $info['check_biz']!=1){
                            $passModel->where(array('id'=>$id))->setField('status',0);
                        }
                        break;

//                        ["check_biz"]=>  string(1) "2"    ["check_tra"]=> string(1) "0"
                }
                $this->success('审核成功',U('pass_list',array('cid'=>57)));

            }else{
                $this->error('审核失败');
            }
        }else{
            if($id){
                $info=$passModel->find($id);
//               echo $passModel->getLastSql();exit;
//                $back=$passModel->where(array('back'=>$id,'type'=>2))->find();
//                $a="440923199207274823";
//                $b=intval($a);
//                $b=number_format($a);
//                $a = number_format($info['card_id']);
//                var_dump($a);exit;
//                $info['card_id'] = str_replace(',', '', $a);
//                $b = json_decode($a, false, 512);
//                $b = str_replace(',', '', $b);
//                $this->assign('info',Array ( 'id' => 112, 'name' => "李劲麟",'hkkne' => $b));
                $str = $info['phone'];
                $info['phone'] = substr($str,0,3)."-".substr($str,3,4)."-".substr($str,7,4);
                $info['card_id'] = "";
                    $this->assign('info',$info);
                $this->assign('check_type',$check_type);
//var_dump($info);exit;
//                $this->assign('back',$back);
            }
            $this->display();
        }
    }

//var_dump($info);exit;
/*array(23) {
        ["id"]=> string(1) "1"
        ["name"]=> string(6) "冯岩"
        ["phone"]=> string(11) "13751861851"
        ["store_id"]=> string(4) "5621"
        ["card_id"]=> string(18) "352102198812082350"
        ["photo"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["card_img1"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["card_img2"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate1"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate2"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate3"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate4"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate5"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["type"]=> string(1) "1"
        ["expiry_date"]=> string(10) "1609344000"
        ["apply_time"]=> string(10) "1571600183"
        ["status"]=> string(1) "1"
        ["biz_verify"]=> string(24) "业务部负责人-66666"
        ["biz_verify_time"]=> string(10) "1597672032"
        ["check_biz"]=> string(1) "2"
        ["train_verify"]=> string(0) ""
        ["train_verify_time"]=> NULL
        ["check_tra"]=> NULL }*/


    /*管理后台审核页面获取用户信息，用于身份证号显示*/
    public function getCardId(){
        $passModel=M('Pass');
        $id = I('get.id');
//        $id = 116;
        $info = $passModel->find($id);
        $this->ajaxReturn($info);
    }

/*管理后台-待审核出入证列表*/

public function check_pass(){
        $cid=I('cid');
        $title=I('title');
        $author=I('author');
        //        $where=array('status'=>array('gt',0));
        if($cid){
            $where['cid']=$cid;
        }
        if($cid==58){
            $passModel=M('Pass');
        //            $where1['type']=1;
        //            $count=$passModel->where($where1)->count();

            $count=$passModel->count();
            $page=new \Think\Page($count);
        //            $list=$passModel->where($where1)
            $where1['status'] = array('eq',0);
            $list=$passModel
                ->where($where1)
                ->order('apply_time desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        //            var_dump($list);die;  //1597927110--1606665600
        //            var_dump($list[0]['expiry_date']);die;
            if($list){
                foreach($list as $key=>$v){
                    /*判断是否过期并更新数据表*/
//                    if ($v['status']==1 && time()>$v['expiry_date']){
//                        //如果出入证是正常生效的，并且当前时间大于有效期的
//                        $res=M('Pass')->where(array('id'=>$v['id']))->setField('status',3);
//        //                        echo M('Pass')->getLastSql();exit;
//                    }
//                    if ($v['status']==3 && time()<$v['expiry_date'] && $v['check_biz']==2){
//                        //如果出入证当前状态是过期的,且客服部审核状态是通过的，但当前时间小于有效期的，说明中途去后台延长了有效期，出入证状态变有效了
//                        M('Pass')->where(array('id'=>$v['id']))->setField('status',1);
//                    }
//                    //如果客服部或安管部有一个未通过审核，并且出入证状态不是未通过时，修改状态为未通过
//                    if (($v['check_biz']==1 or $v['check_tra']==1) && $v['status']!=2){
//                        M('Pass')->where(array('id'=>$v['id']))->setField('status',2);
//                    }


                    $list[$key]['type_name']=get_type_name($v['type']);
                    //get_type_name这个方法在/application/Pass/Common/function.php中定义
                    $list[$key]['status_name']=get_status_name($v['status']);
        //                    $v['user_name']=$v['name'];
                }
            }
            $this->assign('title','待审核出入证列表');
            $this->assign('cid',$cid);
            $this->assign('list',$list);
            $this->assign('page',$page->show('Admin'));
            $this->display('pass_list');
            }
}














    /**
     * 审核
     */
    public function shen(){
        $id=I('ids');
        $status=I('get.status');
        $status=$status>0?$status:0;
        if(!$id){
            $this->error('未知的内容');
        }
        $res=$this->newsModel->where(array('id'=>array('in',implode(',',$id))))->setField('status',$status);
        if($res!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
    /**
     * 审核
     */
    public function shen_msg(){
        $id=I('ids');
        $status=I('get.status');
        $status=$status>0?$status:0;
        if(!$id){
            $this->error('未知的内容');
        }
        $res=M('Msg')->where(array('id'=>array('in',implode(',',$id))))->setField('status',$status);
        if($res!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
    public function ajax_floor(){
        $id=I('id');
        if(!$id){
            $this->ajaxReturn(array('status'=>0,'msg'=>'未知的楼层'));
        }
        $floor2=$this->newsFloorModel->get_list_floor($id);
        $data=array('status'=>1,'list'=>$floor2);
        $this->ajaxReturn($data);
    }
    /**
     * 处理图片组
     *
     * @param array $img
     * @param int $nid
     */
    protected function setImgs($img,$nid){
        $newarr=array();
        foreach($img as $key=>$v){
            $newarr[$key]['nid']=$nid;
            $newarr[$key]['type']=1;
            $newarr[$key]['path']=$v;
        }
        return $newarr;
    }

    /*订单列表*/
    public function order_list(){
        $order_sn=I('order_sn');
        $b_time=I('b_time');
        $e_time=I('e_time');
        $order_status=I('order_status');
        $type=I('type');
        if(IS_POST){
            $post_parameter=array_filter($_POST);
        }
        if(IS_GET){
            $get_parameter=array_filter($_GET);
        }
        $parameter=!empty($post_parameter)?$post_parameter:$get_parameter;

        extract($parameter);
        if($order_sn){
            $where['order_sn']=$order_sn;
        }
        if($b_time && $e_time){
            $where['order_time']=array(array('egt',strtotime($b_time)),array('elt',strtotime($e_time)),'and');
        }elseif($b_time){
            $where['order_time']=array('egt',strtotime($b_time));
        }elseif($e_time){
            $where['order_time']=array('elt',strtotime($e_time));
        }

        if($order_status){
            if($order_status==1){
                $where['order_status']=1;
            }elseif($order_status==3){
                $where['order_status']=array('neq',1);
            }
        }
        if($type==1){
            $where['type']='物业';
        }elseif($type==2){
            $where['type']='租赁';
        }
//        if($where){
//            print_r($where);die;
//        }
        $model=D('SnId');
        $fields=array('id','order_sn','order_status','money','money_paid','order_time','name','type','ids','already_paid');
        $count=$model->field($fields)->where($where)->count();
        $page=new \Think\Page($count,10,$parameter);
        $list=$model->field($fields)
            ->where($where)
            ->order('order_time desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        if($list){
            $new_list=array();
            foreach($list as $v){
                $sql="select * from [应收款APP] where [应收款ID] in (".$v['ids'].")";
                $info=$this->getData($sql);
                if($info){
                    foreach($info as &$val){
                        $val['order_sn']=$v['order_sn'];
                        $val['order_status']=$v['order_status'];
                        $val['already_paid']=$v['already_paid'];
                        $val['order_id']=$v['id'];
                        $new_list[]=$val;
                    }
                }
            }
        }
//print_r($new_list);die;
        $this->assign('title','列表');
        $this->assign('list',$new_list);
        $this->assign('page',$page->show('Admin'));
        $this->display('order_list');
    }
    /*租金支付订单*/
    public function order_next(){
        $id=I('id');
        if(!$id){
            $this->error('系统错误，请刷新');
        }
        $where['sn_id']=$id;
        $where['status']=1;
        $model=D('News/SorderLog');
        $list=$model
            ->where($where)
            ->order('id')
            ->select();

        $this->assign('title','列表');
        $this->assign('list',$list);
        $this->display('order_next');
    }
    /*订单列表*/
    public function car_order(){
        $order_sn=I('order_sn');
        $b_time=I('b_time');
        $e_time=I('e_time');
        $type=intval(I('type'));
        if(IS_POST){
            $post_parameter=array_filter($_POST);
        }
        if(IS_GET){
            $get_parameter=array_filter($_GET);
        }
        $parameter=!empty($post_parameter)?$post_parameter:$get_parameter;

        extract($parameter);
        if($order_sn){
            $where['orderno']=array('like',"%$order_sn%");
        }
        if($b_time && $e_time){
            $where['create_time']=array(array('egt',strtotime($b_time)),array('elt',strtotime($e_time)),'and');
        }elseif($b_time){
            $where['create_time']=array('egt',strtotime($b_time));
        }elseif($e_time){
            $where['create_time']=array('elt',strtotime($e_time));
        }

        if($type){
            $where['type']=$type;
        }
        $where['status']=2;
        $model=D('CarOrder');
        $fields=array('id','orderno','status','money','money_paid','carno','pay_time','create_time','desc','type','backmsg','backstatus');
        $count=$model->field($fields)->where($where)->count();
        $page=new \Think\Page($count,10,$parameter);
        $list=$model->field($fields)
            ->where($where)
            ->order('create_time desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        $this->assign($post_parameter);
        $this->assign('title','列表');
        $this->assign('list',$list);
        $this->assign('page',$page->show('Admin'));
        $this->display('car_order');
    }
    /*订单列表*/
    public function export_order(){
        $order_sn=I('order_sn');
        $b_time=I('b_time');
        $e_time=I('e_time');
        $order_status=I('order_status');
        $type=I('type');
        if($order_sn){
            $where['order_sn']=$order_sn;
        }
        if($b_time && $e_time){
            $where['order_time']=array(array('egt',strtotime($b_time)),array('elt',strtotime($e_time)),'and');
        }elseif($b_time){
            $where['order_time']=array('egt',strtotime($b_time));
        }elseif($e_time){
            $where['order_time']=array('elt',strtotime($e_time));
        }

        if($order_status){
            if($order_status==1){
                $where['order_status']=1;
            }elseif($order_status==3){
                $where['order_status']=array('neq',1);
            }
        }
        if($type==1){
            $where['type']='物业';
        }elseif($type==2){
            $where['type']='租赁';
        }
//        if($where){
//            print_r($where);die;
//        }
        $model=D('SnId');
        $fields=array('id','order_sn','order_status','money','money_paid','order_time','name','type','ids');
        $list=$model->field($fields)
            ->where($where)
            ->order('order_time desc')
            ->select();
        if($list){
            $new_list=array();
            foreach($list as $v){
                $sql="select * from [应收款APP] where [应收款ID] in (".$v['ids'].")";
                $info=$this->getData($sql);
                if($info){
                    foreach($info as &$val){
                        $val['order_sn']=$v['order_sn'];
                        $val['order_status']=$v['order_status']==1?'已支付':'未支付';
                        $new_list[]=$val;
                    }
                }
            }
        }
        if(!empty($new_list)){
            $xlsName  = "订单列表";
            $xlsCell  = array(
                array('order_sn','订单号'),
                array('资源名称','资源名称'),
                array('资源编号','资源编号'),
                array('占用者名称','占用者名称'),
                array('费用名称','费用名称'),
                array('费用说明','费用说明'),
                array('计费年月','计费年月'),
                array('计费年月开始日期','计费年月开始日期'),
                array('计费年月截至日期','计费年月截至日期'),
                array('应收金额','应收金额'),
                array('滞纳金金额','滞纳金金额'),
                array('收费状态','收费状态'),
                array('付款方式','付款方式'),
                array('费用用途','费用用途'),
                array('order_status','状态'),
            );
            exportExcel($xlsName,$xlsCell,$new_list);
        }else{
            $this->error('没有数据');
        }
    }
    /*订单列表*/
    public function export_car_order(){
        $order_sn=I('order_sn');
        $b_time=I('b_time');
        $e_time=I('e_time');
        $type=intval(I('type'));
        if(IS_POST){
            $post_parameter=array_filter($_POST);
        }
        if(IS_GET){
            $get_parameter=array_filter($_GET);
        }
        $parameter=!empty($post_parameter)?$post_parameter:$get_parameter;

        extract($parameter);
        if($order_sn){
            $where['orderno']=array('like',"%$order_sn%");
        }
        if($b_time && $e_time){
            $where['create_time']=array(array('egt',strtotime($b_time)),array('elt',strtotime($e_time)),'and');
        }elseif($b_time){
            $where['create_time']=array('egt',strtotime($b_time));
        }elseif($e_time){
            $where['create_time']=array('elt',strtotime($e_time));
        }

        if($type){
            $where['type']=$type;
        }
        $where['status']=2;
        $model=D('CarOrder');
        $fields=array('id','orderno','status','money','carno','cardno','money_paid','pay_time','create_time','desc','type','backmsg','backstatus');
        $list=$model->field($fields)
            ->where($where)
            ->order('create_time desc')
            ->select();
        if(!empty($list)){
            foreach($list as &$v){
                $v['type']=$v['type']==2?'月卡':'临时卡';
                $v['status']=$v['status']==2?'已支付':'未支付';
                $v['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
            }
            $xlsName  = "停车场订单列表";
            $xlsCell  = array(
                array('orderno','订单号'),
                array('desc','订单内容'),
                array('type','类型'),
                array('money','金额'),
                array('money_paid','实付金额'),
                array('pay_time','支付时间'),
                array('backmsg','捷汇通返回'),
                array('status','订单状态'),
                array('carno','车牌号'),
                array('cardno','卡号'),

            );
            exportExcel($xlsName,$xlsCell,$list);
        }else{
            $this->error('没有数据');
        }
    }
    /*订单详情*/
    public function order_detail(){
        $id=I('id');
        if(!$id){
            $this->error('未知的订单');
        }
        $info=D('SnId')->find($id);
        if(!$info){
            $this->error('订单不存在');
        }
        if(!$info['ids']){
            $this->error('非法订单');
        }
        $sql="select * from [应收款APP] where [应收款ID] in (".$info['ids'].")";
        $info['detail']=$this->getData($sql);


        $this->assign('info',$info);
        $this->display('order_detail');
    }
    /*查询*/
    private function getData($sql){
        $conn=odbc_connect("dmxcsqlserver","hetong03","GZty##$$%%dmxc83278987");

//        $sql="SELECT top 10 * from [网站身份验证]";
        $sql=characet($sql);
        if(!$conn){die('连接失败');}
        $exec=odbc_exec($conn,$sql);//执行语句
        if(!$exec){
            die(odbc_error($conn).characet(odbc_errormsg($conn),'utf-8'));
        }
        $data=array();
        $f_num=odbc_num_fields($exec);
        if($f_num<=0){
            return false;
        }
        while(odbc_fetch_array($exec))
        {
            $row=array();
            for($i=1;$i<=$f_num;$i++){
                $row[characet(odbc_field_name($exec,$i),'utf-8')]=characet(odbc_result($exec,$i),'utf-8');
            }
            $data[]=$row;
        }
        odbc_close($conn);
        return $data;
    }




}
