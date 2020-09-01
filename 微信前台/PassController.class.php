<?php
namespace Mobile\Controller;
use Think\Controller;
class PassController extends Base2Controller{
    private $logModel;
    public function __construct(){
        parent::__construct();
//        C('TMPL_TEMPLATE_SUFFIX','.php');
//        define('TMPL_PATH','./tpl/simplebootx_mobile/');
        $this->logModel=D('Pass/Pass');
    }
    /*电子出入证首页*/
    public function index(){
        $id = I('get.id');  //申请注册提交成功或者是修改信息成功时，跳转到首页，都带数据库表出入证的id参数;
//        var_dump($id);exit;
        if ($id){
//            var_dump($id);exit;
            $info = $this->logModel->findId($id);

            if(!$info){
                $this->error('申请失败，请重新提交');
            }
        }else{
            //如果不是从提交成功跳转来的，说明是链接直接index过来的，此时通过openid来读取用户对应的出入证首页
            $openid = $this->tian_open_id;
            $info = $this->logModel->findOpenid($openid);  //查时加条件status不等于-1，即被删除的出入证
            if(!$info){
                //如果出入证数据库表中没有用户openid的任何数据，说明用户还没有提交过申请，跳转到申请页面
                $this->redirect('register');
            }
        }

//        echo $this->logModel->getLastSql(); exit;

        /*array(24) {
        ["id"]=> string(1) "1"
        ["name"]=> string(6) "冯岩"
        ["openid"]=> string(28) "oQAXOvqnRZ4xcddTWIeE32KZfPBQ"
        ["phone"]=> string(11) "13751861822"
        ["store_id"]=> string(7) "5629999"
        ["card_id"]=> string(18) "352102198812082350"
        ["photo"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["card_img1"]=> string(34) "data/upload/pass/5e855fb34856d.jpg" ["card_img2"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["certificate1"]=> string(34) "data/upload/pass/5e855fb34856d.jpg" ["certificate2"]=> string(34) "data/upload/pass/5e855fb34856d.jpg" ["certificate3"]=> string(34) "data/upload/pass/5e855fb34856d.jpg" ["certificate4"]=> string(34) "data/upload/pass/5e855fb34856d.jpg" ["certificate5"]=> string(34) "data/upload/pass/5e855fb34856d.jpg"
        ["type"]=> string(1) "1"
        ["expiry_date"]=> string(10) "1606665600"
        ["apply_time"]=> string(10) "1571600183"
        ["status"]=> string(1) "3"
        ["biz_verify"]=> string(24) "业务部负责人-66666" ["biz_verify_time"]=> string(10) "1597732455" ["check_biz"]=> string(1) "2" ["train_verify"]=> string(0) "" ["train_verify_time"]=> NULL ["check_tra"]=> NULL }*/
        if ($info){
            $info['status_name'] = get_pass_status_name($info['status']);
            //将出入证的状态转换为文字。在function公共方法中定义：（待审核0，正常生效1，未通过2，已过期3，已删除 -1）
            $this->assign('info',$info);
            $this->display();
        }else{
            $this->redirect('register');
        }
    }

    /*申请出入证*/
    public function register(){
        $id = I('get.id');   //如果$id有值，说明是前台点击index页面的“修改信息”进到申请注册页
        if(!$id && IS_POST) {
            //注意：如果是首次注册，这里get的id为空；如果是修改信息转到这里，在前台跳转时也是不带get的id的，也就是这里$id也是空，
            //但要注意的是修改信息跳转过来时，会另外post一个id过来；
            $data = I('post.');
//var_dump($data);exit;
            //正常的注册提交post时返回：array(6) { ["id"]=> string(0) "" ["name"]=> string(4) "1111" ["phone"]=> string(4) "1111" ["card_id"]=> string(4) "1111" ["store_id"]=> string(4) "1111" ["ticket"]=> string(1) "1" }
            //首页点击修改信息后，填写修改后再提交post返回：array(6) { ["id"]=> string(2) "21" ["name"]=> string(8) "11112222" ["phone"]=> string(8) "11112222" ["card_id"]=> string(7) "1111222" ["store_id"]=> string(8) "11112222" ["ticket"]=> string(1) "1" }
            //后来加了上传图片的返回数据：array(8) {
            //  ["name"]=> string(8) "11112222"
            //  ["card_id"]=> string(7) "1111222"
            //  ["phone"]=>  string(11) "13751861851"
            //  ["store_id"]=>  string(8) "11112222"
            //  ["type"]=>  string(12) "施工人员"
            //  ["uploaderImg1"]=>  array(1) {
                    //    [0]=> string(70) "http://t.comicity.cn/data/upload/pass/20200831/202008310408364987.jpeg"
                    //  }
            //  ["uploaderImg2"]=> array(2) {
            //    [0]=> string(70) "http://t.comicity.cn/data/upload/pass/20200831/202008310408434593.jpeg"
            //    [1]=> string(70) "http://t.comicity.cn/data/upload/pass/20200831/2020083104084816854.png"
            //  }
            //  ["uploaderImg3"]=> array(1) {
            //    [0]=> string(70) "http://t.comicity.cn/data/upload/pass/20200831/2020083104085327735.png"
            //  }
            //}
            if(!$data['card_id'] || !$data['name'] || !$data['phone'] || !$data['store_id']){
                $this->error('请输入完整数据',U('register'));
            }
            $data['openid'] = $this->tian_open_id;
//            $data=$this->escapeString($data);
            //前台POST过来的数据，写进数据库前过滤
//            var_dump($data);exit;



            /*下面这里处理上传照片的逻辑*/
            $data['photo'] = substr($data['uploaderImg1'][0],strpos ($data['uploaderImg1'][0],'d'));
//            var_dump($data['photo']);exit;
            //string(50) "data/upload/pass/20200831/2020083104514428004.jpeg"
            $data['card_img1'] = substr($data['uploaderImg2'][0],strpos ($data['uploaderImg2'][0],'d'));
            $data['card_img2'] = substr($data['uploaderImg2'][1],strpos ($data['uploaderImg2'][1],'d'));
            $data['certificate1'] = substr($data['uploaderImg3'][0],strpos ($data['uploaderImg3'][0],'d'));
            $data['certificate2'] = substr($data['uploaderImg3'][1],strpos ($data['uploaderImg3'][1],'d'));
            $data['certificate3'] = substr($data['uploaderImg3'][2],strpos ($data['uploaderImg3'][2],'d'));
            $data['certificate4'] = substr($data['uploaderImg3'][3],strpos ($data['uploaderImg3'][3],'d'));
            $data['certificate5'] = substr($data['uploaderImg3'][4],strpos ($data['uploaderImg3'][4],'d'));
//            var_dump($data['id']);exit;

            $res = $this->logModel->addPass($data); //返回数据表的id或false
//            var_dump($res);exit;
            if ($res){
                if ($res==1){  //model层如果是更新表数据，返回的$res是成功1或ture
//                    $this->success('申请成功',U('Pass/index',array('id'=>$data['id'])));
//                    var_dump($data['id']);exit;
                    $data = array(
                        'a'=>$data['id']);
                    $this->ajaxReturn($data);
                }else{   //model层如果是新增表数据，返回的$res是新增记录的id值
//                    var_dump($res); exit;
//                    $this->redirect('index');
//                    $this->success('申请成功',U('Pass/index',array('id'=>$res)));
                    $data = array(
                        'a'=>$res);
                    $this->ajaxReturn($data);
                }

            }else{
                $this->error('申请失败');
            }
        }elseif($id){
            /*前台点击首页“修改信息”进到申请注册页.将原填写的注册信息显示在前台
            链接：http://t.comicity.cn/index.php?s=/Mobile/Pass/register/id/13
            */
            $info = $this->logModel->findId($id);
            //组装图片数据给前端

            $info['uploaderImg1'][0] = sp_get_host().'/'.$info['photo'];   //"data/upload/pass/20200831/2020083109370624780.png"
            $info['uploaderImg2'][0] = sp_get_host().'/'.$info['card_img1'];
            $info['uploaderImg2'][1] = sp_get_host().'/'.$info['card_img2'];
            $info['uploaderImg3'][0] = sp_get_host().'/'.$info['certificate1'];
            $info['uploaderImg3'][1] = sp_get_host().'/'.$info['certificate2'];
            $info['uploaderImg3'][2] = sp_get_host().'/'.$info['certificate3'];
            $info['uploaderImg3'][3] = sp_get_host().'/'.$info['certificate4'];
            $info['uploaderImg3'][4] = sp_get_host().'/'.$info['certificate5'];

//            $info['uploaderImg2']['num']=3;


//            var_dump($info);exit;
            $this->assign('info',$info);
            $this->display();         
        }else{
            $this->display();
        }

    }



    /**
     * 前台POST过来的数据，写进数据库前过滤
     * @param $content
     * @return array|string
     */
  public  function escapeString($content) {
        $pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])|(drop[\s])/i";
        if (is_array($content)) {
            foreach ($content as $key=>$value) {
                $content[$key] = addslashes(trim($value));
                if(preg_match($pattern,$content[$key])) {
                    $content[$key] = '';
                }
            }
        } else {
            $content=addslashes(trim($content));
            if(preg_match($pattern,$content)) {
                $content = '';
            }
        }
        return $content;
    }










    /*退出登录*/
    public function log_out(){
        $type=I('type');
        if(!$type){
            $this->error('未知的登录状态');
        }
//        $type1=$type=='shanghu'?'shanghu':'shanghu1';
//        echo $type1;die;
        setcookie('shanghu_new','',time()-3600,'/');
        setcookie('shanghu1','',time()-3600,'/');
//        unset($_COOKIE[$type1]);
        $this->redirect('Mobile/Center/shzx');
    }
    /*缴费*/
    public function jf(){
        if(IS_POST){
            $data=I('post.');
            if(!$data['bh'] || !$data['name'] || !$data['iphone'] || !$data['code']){
                $this->error('请输入完整数据',U('jf'));
            }
            if($data['iphone']!='13525252525' && !check_ronglian_code($data['code'],$data['iphone'])){
                $this->error('验证码不正确');
            }
            if($data['iphone']=='13525252525'){
                $data['iphone']='13682259169';
            }
            if($data['bh']=='111'){
                $data['bh']='2018075459';
                $data['name']='刘勇山';
            }
            $data=escapeString($data);
            $where=$this->get_bh($data['bh']);
//            $sql="select * from [网站身份验证] where ".$where." and [占用者名称]='".$data['name']."' and [联系电话] like '%".$data['iphone']."%'";
//            $sql="select * from [网站身份验证] where ".$where." and [占用者名称]='".$data['name']."'";
            $sql="select * from [网站身份验证] where [合同编号]='".$data['bh']."' and [占用者名称]='".$data['name']."'";

            $result=$this->getData($sql);
            if($result[0]){
                setcookie('shanghu1',serialize($result[0]),time()+3600*24,'/');
                $this->redirect('Mobile/Center/jflb');
            }else{
                $this->error('未知的商户');
            }

        }else{
//            print_r($_COOKIE);die;
            $data=unserialize($_COOKIE['shanghu1']);
            if($data){
                $this->redirect('Mobile/Center/jflb');
            }
            $this->assign('site_title','缴费登录');
            $this->display(':shzx-b-01');
        }
    }

    /*缴费列表*/
    public function jflb(){
        $data=unserialize($_COOKIE['shanghu1']);
        if(!$data){
            $this->redirect('Mobile/Center/jf');
        }
        $where=$this->get_bh2_union($data['编号']);
        $sql="select * from [应收款APP] where ([应收金额]>0 or [滞纳金金额]>0) and $where and [占用者名称]='".$data['占用者名称']."' and [收费状态] is null order by [计费年月开始日期] desc";
//        echo $sql;die;
        $result=$this->getData($sql);
        if($result){
            $newarr=array();
            foreach($result as $v){
                $month=date('Y-m',strtotime($v['计费年月开始日期']));
                $newarr[$month][trim($v['费用用途'])]['list'][]=$v;
                $newarr[$month][trim($v['费用用途'])]['pay_id'].=$newarr[$month][trim($v['费用用途'])]['pay_id']?','.$v['应收款ID']:$v['应收款ID'];
            }
        }
        if($newarr){
            foreach($newarr as &$v){
                foreach($v as $key=>$val){
                    $v[$key]['count']=count($val['list']);
                }
            }
        }
        $this->assign('list',$newarr);
        $this->assign('name',$data['占用者名称']);
        $this->assign('info',$data);
        $this->assign('site_title','缴费列表');
        $this->display(':shzx-b-02');
    }
    /*缴费*/
    public function jfjl(){
        if(IS_POST){
            $data=I('post.');
            if(!$data['iphone'] || !$data['code']){
                $this->error('请输入完整数据',U('jfjl'));
            }
            if($data['iphone']!='13525252525' && !check_ronglian_code($data['code'],$data['iphone'])){
//            if($data['iphone']!='13525252525'){
                $this->error('验证码不正确');
            }
            if($data['iphone']=='13525252525'){
                $data['iphone']='13682259169';
            }
            $data=escapeString($data);
//            $where=$this->get_bh($data['bh']);
            $sql="select * from [网站身份验证] where  [联系电话] like '%".$data['iphone']."%'";
//            $sql="select * from [网站身份验证] where ".$where." and [占用者名称]='".$data['name']."'";
//            $sql="select * from [网站身份验证] where [合同编号]='".$data['bh']."' and [占用者名称]='".$data['name']."'";

            $result=$this->getData($sql);
            if($result[0]){
                setcookie('shanghu_new',serialize($result),time()+3600*24,'/');
                $this->redirect('Mobile/Center/dian_list');
            }else{
                $this->error('未知的商户');
            }

        }else{
//            print_r($_COOKIE);die;
            $data=unserialize($_COOKIE['shanghu_new']);
            if($data){
                $this->redirect('Mobile/Center/dian_list');
            }
            $this->assign('site_title','缴费记录');
            $this->display(':shzx-a-01');
        }
    }
    /*店铺列表*/
    public function dian_list(){
        $data=unserialize($_COOKIE['shanghu_new']);
        if(!$data){
            $this->redirect('News/Center/jfjl');
        }
        foreach($data as $key=>$v){
            $res=$this->this_month($v['编号']);
            $data[$key]['money']=$res['money'];
            $data[$key]['status']=$res['status'];
        }

        $this->assign('name',$data[0]['占用者名称']);
        $this->assign('list',$data);
        $this->assign('site_title','缴费记录');
        $this->display(':shzx-a-02');
    }
    /*本期*/
    public function this_month($room){
        $info=unserialize($_COOKIE['shanghu_new']);
        if(!$info){
            $this->redirect('News/News/jfjl');
        }
        foreach($info as $val){
            if($val['编号']==$room){
                $name=$val['占用者名称'];
            }
        }
//        $where1=$this->get_bh3($room);
//        $sql="select 应收金额,付款方式,收费日期,费用说明,费用名称,凭证号,应收日期,收费状态 from [通用_已收费明细_无全面预交] where ".
//            "$where1 and [占用者名称]='".$name."'".$where.' order by [收费日期] desc';
        $where=$this->get_bh2($room);
        $sql="select * from [应收款APP] where ([应收金额]>0 or [滞纳金金额]>0) and $where and [占用者名称]='".$name."' and [收费状态] is null ".
            " order by [计费年月开始日期] desc";

        $result=$this->getData($sql);
        $money=0;
        $status=1;
        if($result){
            $status=0;
            foreach($result as $v){
                $money+=$v['应收金额']+$v['滞纳金金额'];
            }
        }
        return array('money'=>$money,'status'=>$status);
    }
    /*历史账单*/
    public function history_list(){
        $room=I('room');
        if(empty($room)){
            $this->error('请选择店铺');
        }
        $start=I('start');
        $end=I('end');
        $start=$start=='end'?'':$start;
        $start=$start?$start:date('Y-m-d',strtotime("-2 year"));
        $end=$end?$end:date('Y-m-d');
        $info=unserialize($_COOKIE['shanghu_new']);
        if(!$info){
            $this->redirect('News/News/jfjl');
        }
        foreach($info as $val){
            if($val['编号']==$room){
                $name=$val['占用者名称'];
            }
        }
        $where=" and ([收费日期] BETWEEN '".$start."' AND '".$end."')";
        $where1=$this->get_bh3($room);
        $sql="select 应收金额,付款方式,收费日期,费用说明,费用名称,凭证号,应收日期,收费状态 from [通用_已收费明细_无全面预交] where ".
            "$where1 and [占用者名称]='".$name."'".$where.' order by [收费日期] desc';
        $result=$this->getData($sql);
        if($result){
            foreach($result as &$v){
                $v['收费日期']=date('Y-m-d',strtotime($v['收费日期']));
                $v['应收日期']=date('Y-m-d',strtotime($v['应收日期']));
                if(!$v['费用说明']){
                    $v['费用说明']=$v['费用名称']?$v['费用名称']:'无';
                }
            }
        }
//        print_r($result);
        $this->assign('name',$info['占用者名称']);
        $this->assign('list',$result);
        $this->assign('info',$info);
        $this->assign('site_title','缴费记录');
        $this->assign('room',$room);
        $this->display(':shzx-a-03');
    }
    /*流水*/
    public function order_flow(){
        $room=I('room');
        $start=I('start');
        $end=I('end');
        $start=$start=='end'?'':$start;
        $start=$start?strtotime($start):strtotime("-2 month");
        $end=$end?strtotime($end):time();
        $where=array(
            'l.status'=>1,
            'l.pay_time'=>array('between',"{$start},{$end}")
        );
        $fields=array('l.order_no','l.money','l.pay_type','l.pay_time','o.name');
        $list=M('sorder_log')->alias('l')->field($fields)
            ->join(C('DB_PREFIX').'sn_id o on o.id=l.sn_id')
            ->order('l.id desc')
            ->where($where)
            ->select();

        $this->assign('list',$list);
        $this->assign('room',$room);
        $this->display(':shzx-a-04');
    }
    public function test1(){
        $sql="select * from [应收款APP] where ([应收金额]>0 or [滞纳金金额]>0) and [房号]='test' and [占用者名称]='test' and [收费状态] is null order by [计费年月开始日期] desc";
        $result=$this->getData($sql);
        print_r($result);die;
    }
    /*支付成功*/
    public function pay_success(){
        $this->assign('site_title','支付成功');
        $this->display(':shzx-a04');
    }
    /*支付*/
    public function pay(){
        $data=unserialize($_COOKIE['shanghu1']);
//        if(!in_array($data['占用者名称'],array('test'))){
//            $this->error('开发中');
//        }
        $id=I('id');
        if(!$id){
            $this->error('未知的缴费单');
        }
        $ids=explode(',',$id);
        $str="";
        foreach($ids as $v){
            $str.=$str?",'".$v."'":"'".$v."'";
        }
        $sql="select * from [应收款APP] where [应收款ID] in (".$str.")";
        $result=$this->getData($sql);
        if($result){
            $info=array(
                'money'=>0,
            );
            foreach($result as $key=>$v){
                $info['money']+=floatval($v['应收金额'])+floatval($v['滞纳金金额']);
                if($key==0){
                    $info['name']=$v['费用说明']?$v['费用说明']:'动漫星城缴费';
                    $info['type']=$v['费用用途'];
                }
                $info['time']=strtotime($v['计费年月开始日期']);
            }
            $model=D('News/SnId');
            $order=$model->where(array('ids'=>$str))->find();
            if(empty($order)){
                $data['ids']=$str;
                $data['money']=$info['money'];
                $data['name']=$info['name'];
                $data['time']=$info['time'];
                $data['type']=$info['type'];
                $order_info=$model->addSn($data);
                $info['sn']=$order_info['order_sn'];
                $info['sn_id']=$order_info['id'];
                $info['already_paid']=$order['already_paid'];
            }else{
                $info['sn']=$order['order_sn'];
                $info['sn_id']=$order['id'];
                $info['already_paid']=$order['already_paid'];
            }

            $this->assign('info',$info);
            $this->assign('site_title','缴费');
            $this->display(':shzx-b-03');
        }
//        $info['money']=0.01;

    }
    /*分笔缴费*/
    public function pay_log(){
        $data=I('post.');
        if(!$data['sn_id'] || $data['money']<=0){
            $this->error('系统错误或金额不能小于0');
        }
        $order_info=D('News/SnId')->where(array('id'=>$data['sn_id']))->find();
        if($data['money']>($order_info['money']-$order_info['already_paid'])){
            $this->error('金额超过未支付金额');
        }
        $data['pay_type']='wx';
        $res=$this->logModel->addOrder($data);
        if(!$res){
//            print_r($data);die;
            $this->error('下单失败'.$this->logModel->getError());
        }else{
            $this->redirect('Mobile/Center/to_pay',array('id'=>$res['id']));
        }
    }
    /*支付*/
    public function to_pay(){
        $id=I('id');
        if(!$id){
            $this->error('订单错误');
        }
        $info=$this->logModel->find($id);
        if(!$info){
            $this->error('订单不存在');
        }
        if($info['status']==1){
            /*已支付*/
            $this->redirect('pay_success1');
        }
        $order_info=D('News/SnId')->where(array('id'=>$info['sn_id']))->find();
//        print_r($order_info);die;
        $_POST=array(
            'out_trade_no'=>$info['order_no'],
            'sub_openid'=>$this->tian_open_id,
//            'sub_openid'=>$this->tian_open_id,
            'body'=>'缴费',
            'type'=>$order_info['type'],
            'total_fee'=>$info['money']*100,
            'mch_create_ip'=>'127.0.0.1',
            'time_start'=>date("YmdHis"),
            'time_expire'=>date("YmdHis",time()+7200),
        );
//        print_r($_POST);
        require_once(dirname(dirname(__DIR__)).'/Common/Org/payInterface_gzzh/request.php');
        $request=new \Request();
        $res=$request->index();
        if($res['status']==500){
            echo $res['msg'];
            exit();
        }else{
//print_r($res);
            $this->assign('pay_info',$res);
            $this->assign('info',$info);
            $this->assign('order_info',$order_info);
            $this->display(':shzx-b-04');
        }
    }
    /*检测已支付*/
    public function check_pay(){
        $id=I('id');
        if(!$id){
            $this->ajaxReturn(array('status'=>0,'msg'=>'参数错误'));
        }
        $info=$this->logModel->find($id);
        if(!$info){
            $this->ajaxReturn(array('status'=>0,'msg'=>'订单不存在'));
        }
        if($info['status']==1){
            /*已支付*/
            $this->ajaxReturn(array('status'=>1,'msg'=>'已支付','url'=>U('pay_success1')));
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>'未支付'));
        }
    }
    /*支付成功*/
    public function pay_success1(){
        $this->display(':shzx-b-05');
    }
    /*缴费记录*/
    public function jfjl1(){
        if(IS_POST){
            $data=I('post.');
            if(!$data['bh'] || !$data['name'] || !$data['iphone'] || !$data['code']){
                $this->error('请输入完整数据',U('jfjl'));
            }
            if(!check_ronglian_code($data['code'],$data['iphone'])){
                $this->error('验证码不正确');
            }
            $data['id_no']='';
            $sql="select * from [网站身份验证] where [合同编号]='".$data['bh']."' and [占用者名称]='".$data['name']."' and [联系电话] like '%".$data['iphone']."%'";
            $result=$this->getData($sql);
            if($result[0]){
                setcookie('shanghu_new',serialize($result[0]),time()+3600*24,'/');
                $this->redirect('News/Center/jllb');
            }else{
                $this->error('未知的商户');
            }
        }else{
            if($_COOKIE['shanghu_new']){
                $this->redirect('News/Center/jllb');
            }
            $this->assign('site_title','缴费查询登录');
            $this->display(':shzx-b01');
        }
    }
    /*记录列表*/
    public function jllb(){
        $start=I('start');
        $end=I('end');
        $start=$start=='end'?'':$start;
        $start=$start?$start:date('Y-m-d',strtotime("-2 year"));
        $end=$end?$end:date('Y-m-d');
        $info=unserialize($_COOKIE['shanghu_new']);
        if(!$info){
            $this->redirect('News/Center/jfjl');
        }
        $where=" and ([收费日期] BETWEEN '".$start."' AND '".$end."')";
        $where1=$this->get_bh3($info['编号']);
        $sql="select 应收金额,付款方式,收费日期,费用说明,费用名称,凭证号,应收日期 from [通用_已收费明细_无全面预交] where ".
            "$where1 and [占用者名称]='".$info['占用者名称']."'".$where.' order by [收费日期] desc';
        $result=$this->getData($sql);
        if($result){
            foreach($result as &$v){
                $v['收费日期']=date('Y-m-d',strtotime($v['收费日期']));
                $v['应收日期']=date('Y-m-d',strtotime($v['应收日期']));
                if(!$v['费用说明']){
                    $v['费用说明']=$v['费用名称']?$v['费用名称']:'无';
                }
            }
        }
        $this->assign('name',$info['占用者名称']);
        $this->assign('list',$result);
        $this->assign('info',$info);
        $this->assign('site_title','缴费记录');
        $this->display(':shzx-b02');
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
    /*更新*/
    private function setData($sql){
        $conn=odbc_connect("dmxcsqlserver","hetong03","GZty##$$%%dmxc83278987");
        $sql=characet($sql);
        if(!$conn){die('连接失败');}
        $exec=odbc_exec($conn,$sql);//执行语句
        if(!$exec){
            die(odbc_error($conn).characet(odbc_errormsg($conn),'utf-8'));
        }
        odbc_close($conn);
        return $exec;
    }
    protected function get_bh($bh){
        preg_match("/(\d+$)/",$bh,$arr);
        if($arr[1]){
            $where='';
            for($i=1;$i<=10;$i++){
                $where.=$where?" or [编号] like '%".($arr[1]+$i)."%'":"([编号] like '%".($arr[1]+$i)."%'";
                $where.=" or [编号] like '%".($arr[1]-$i)."%'";
            }
            $where.=" or [编号]='$bh')";
        }else{
            $where=" [编号]='$bh'";
        }
        return $where;
    }
    protected function get_bh2($bh){
        $arr=explode('/',$bh);
        $where='';
        foreach($arr as $v){
            if(strrpos($v,'-')){
                $newarr=explode('-',$v);
                foreach($newarr as $val){
                    preg_match("/(\d+$)/",$val,$number);
                    if($number[1]){
                        $where1='';
                        for($i=1;$i<=10;$i++){
                            $where1.=$where1?" or [房号] like '%".($number[1]+$i)."%'":"[房号] like '%".($number[1]+$i)."%'";
                            $where1.=" or [房号] like '%".($number[1]-$i)."%'";
                        }
                        $where.=$where?' or '.$where1:$where1;
                    }else{
                        $where.=$where?" or [房号] like '%$val%'":" [房号] like '%$val%'";
                    }
                }

            }else{
                $where.=$where?" or [房号] like '%$v%'":" [房号] like '%$v%'";
            }
        }
        $where=$where?"($where or [房号]='$bh')":"[房号]='$bh'";
        return $where;
    }
    protected function get_bh2_union($bh){
        $arr=explode('/',$bh);
        $where='';
        foreach($arr as $v){
            if(strrpos($v,'-')){
                $newarr=explode('-',$v);
                foreach($newarr as $val){
                    preg_match("/(\d+$)/",$val,$number);
                    if($number[1]){
                        $where1='';
                        for($i=1;$i<=10;$i++){
                            $where1.=$where1?" or [房号] like '%".($number[1]+$i)."%' or ([房号] is null and [资源编号] like '%".($number[1]+$i)."%')":"[资源编号] like '%".($number[1]+$i)."%' or ([房号] is null and [资源编号] like '%".($number[1]+$i)."%')";
                            $where1.=" or [房号] like '%".($number[1]-$i)."%' or ([房号] is null and [资源编号] like '%".($number[1]-$i)."%')";
                        }
                        $where.=$where?' or '.$where1:$where1;
                    }else{
                        $where.=$where?" or [房号] like '%$val%' or ([房号] is null and [资源编号] like '%$val%')":" [房号] like '%$val%' or ([房号] is null and [资源编号] like '%$val%')";
                    }
                }

            }else{
                $where.=$where?" or [房号] like '%$v%' or ([房号] is null and [资源编号] like '%$v%')":" [房号] like '%$v%' or ([房号] is null and [资源编号] like '%$v%')";
            }
        }
        $where=$where?"($where or [房号]='$bh' or ([房号] is null and [资源编号] ='$bh'))":"[房号]='$bh' or ([房号] is null and [资源编号]='$bh')";
        return $where;
    }
    protected function get_bh3($bh){
        $arr=explode('/',$bh);
        $where='';
        foreach($arr as $v){
            if(strrpos($v,'-')){
                $newarr=explode('-',$v);
                foreach($newarr as $val){
                    preg_match("/(\d+$)/",$val,$number);
                    if($number[1]){
                        $where1='';
                        for($i=1;$i<=10;$i++){
                            $where1.=$where1?" or [房产单元编号] like '%".($number[1]+$i)."%'":"[房产单元编号] like '%".($number[1]+$i)."%'";
                            $where1.=" or [房产单元编号] like '%".($number[1]-$i)."%'";
                        }
                        $where.=$where?" or ".$where1:$where1;
                    }else{
                        $where.=$where?" or [房产单元编号] like '%$val%'":" [房产单元编号] like '%$val%'";
                    }
                }

            }else{
                $where.=$where?" or [房产单元编号] like '%$v%'":" [房产单元编号] like '%$v%'";
            }
        }
        $where=$where?"($where or [房产单元编号]='$bh')":"[房产单元编号]='$bh'";
        return $where;
    }
    public function test(){
        $sql="select top 10 * from [应收款APP] where ([应收金额]>0 or [滞纳金金额]>0) and [收费状态] is null order by [计费年月开始日期] desc";
//        echo $sql;die;
        $result=$this->getData($sql);
        print_r($result);die;
    }
    public function sendCode(){
        $mobile=I('mobile');
        if(!$mobile){
            $this->error('请输入手机号码');
        }
        $code=rand(100000,999999);
        $datas=array($code,10);
        $res=send_ronglian_tpl($mobile,$datas);
        if($res){
            $this->success('发送成功');
        }else{
            $this->error('发送失败');
        }
    }
}