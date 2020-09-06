<?php
namespace Pass\Model;
use Think\Model;
class PassModel extends Model{
    //自动验证
    protected $_validate = array(
//        array('sn_id', 'require', 'id不能为空！',1),
//        array('order_no', 'require', '订单号不能为空！',1),
//        array('money', 'require', '金额不能为空！',1),
//        array('pay_type', 'require', '支付类型不能为空！',1),
    );
    //自动完成
    protected $_auto = array(
        //array(填充字段,填充内容,填充条件,附加规则)
        array ('apply_time', 'time', self::MODEL_INSERT, 'function'),
//        array ('order_no', 'newSn', self::MODEL_INSERT, 'callback'),
    );


    /**
     * 通过openid查找出入证申请表
     * @param $openid
     * @return mixed
     */
    public function findOpenid($openid){

        $where['openid'] = $openid;
        $where['status'] = array('neq',-1);


//        return $this->where($where)->find();
        return $this->where($where)->order('id desc')->limit(1)->select();
    }

    /**
     * 通过出入证数据表的id查找出入证申请表
     * @param $id
     * @return mixed
     */
    public function findId($id){
        $where['id'] = $id;
//        $where['status'] = 0 or 2;
        $where['_string']= "status=0 or status=2";
        return $this->where($where)->find();
    }


    /**
     * 添加与更新出入证申请信息
     *
     * @param array $data
     * @return boolean
     */
    public function addPass($data){
        if(!$this->create($data)){
            return false;
        }
        if($data['id']){
            $id=$this->save();  //更新原有数据，返回的是成功1（或true），失败false，返回0表示数据没有任何更新的地方。
//        var_dump($id);exit;
        }else{
            $id=$this->add();  //新增的时候返回的是数据库表的自增id值
        }
//        echo $id;die;
        if(!$id && $id!==0){
            //如果没有后面一个条件，则当$id=0的时候，也会符合这个条件，从而导致前台在没有修改任何信息时提交会报错，无法提交；
            $this->error='添加失败';
            return false;
        }
        return $id;
    }






    
    
    /**
     * 添加
     *
     * @param array $data
     * @return boolean
     */
    public function addOrder($data){
        if(!$this->create($data)){
            return false;
        }
        $id=$this->add();
        if(!$id){
            $this->error='添加失败';
            return false;
        }
        $data['id']=$id;
        return $data;
//        return $data['order_sn'];
    }

    /**
     * 获取详情
     *
     * @param string $sn
     * @return array
     */
    public function checkBySn($sn){
        $where['order_no']=$sn;
        return $this->where($where)->find();
    }
    /**
     * 获取详情
     *
     * @param string $ids
     * @return array
     */
    public function checkById($ids){
        $where['ids']=$ids;
        return $this->where($where)->find();
    }
    /**
     * 删除原有
     *
     * @param string $ids
     * @return array
     */
    public function delById($ids){
        $where['ids']=$ids;
        $where['order_time']=array('lt',time()-60*10);
        return $this->where($where)->delete();
    }
    /**
     * 获取详情
     *
     * @param string $ids
     * @return array
     */
    protected function newSn(){
        $sn=date('YmdHis').rand(100,999);
        if($this->checkBySn($sn)){
            $this->newSn();
        }
        return $sn;
    }
    /*设置支付*/
    public function set_paid($order_id,$order_no='',$paid_money=0,$pay_type='wx'){
        if(!$order_id && !$order_no){
            $this->error='未知的订单';
            return false;
        }
        if($order_id){
            $where['id']=$order_id;
        }else{
            $where['order_no']=$order_no;
        }
        $info=$this->where($where)->find();
        if(empty($info)){
            $this->error='订单不存在';
            return false;
        }
        if($info['status']==1){
            $this->error='订单已支付';
            return false;
        }
        $data=array(
            'status'=>1,
            'pay_type'=>$pay_type,
            'pay_money'=>$paid_money>0?$paid_money:0,
            'pay_time'=>time(),
        );
        $res=$this->where($where)->save($data);
        if($res!==false){
            /*支付成功*/
            $model=M('sn_id');
            $where1=array('id'=>$info['sn_id']);
            $sn_info=$model->where($where1)->find();
            $already_paid=$sn_info['already_paid']+$paid_money;
            $order_status=$already_paid>=$sn_info['money']?1:0;
            $data1=array(
                'already_paid'=>$already_paid,
                'order_status'=>$order_status
            );
            $res2=$model->where($where1)->save($data1);
            if($res2 && $order_status==1){
                /*支付完成*/
                $this->pay_order($sn_info,$pay_type=='wx'?'微信':"支付宝");
            }
            return true;
        }else{
            $this->error='订单支付失败';
            return false;
        }
    }
    protected function pay_order($info,$type='微信'){
        if($info['order_status']==1){
            return true;
        }
        $sql="update  [应收款APP] set [收费状态]='已收费',[收款人]='".$info['type']."',[收费日期]='".date('Y-m-d')."',[付款方式]='$type',[收费说明]='网站支付' where [应收款ID] in (".$info['ids'].")";
        unset($info['id']);
        $info['pay_type']=$type;
        $info['pay_date']=date('Y-m-d');
        $this->setData($sql,$info);
    }
    /*更新*/
    private function setData($sql,$info=array()){
        $conn=odbc_connect("dmxcsqlserver","hetong03","GZty##$$%%dmxc83278987");
        $sql=characet($sql);
        if(!$conn){
            /*未运行记录*/
            D("Common/BatList")->add_bat($info);
            die('连接失败');
        }
        $exec=odbc_exec($conn,$sql);//执行语句
        if(!$exec){
            D("Common/BatList")->add_bat($info);
            die(odbc_error($conn).characet(odbc_errormsg($conn),'utf-8'));
        }
        odbc_close($conn);
//        file_put_contents('data/apli.log',characet($exec,'utf-8'));
        return $exec;
    }
}