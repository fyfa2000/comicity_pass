<?php
/**
 * 文章类型
 */
function get_style_name($style){
    $styles=array(
        '1'=>'普通',
        '2'=>'图文',
        '3'=>'焦点',
        '4'=>'头条',
    );
    return $styles[$style];
}
/**
 * 获取用户名
 */
function get_user_name($uid){
    $name=M('users')->where(array('id'=>$uid))->getField('user_nicename');
    return $name;
}
/**
 * 文章状态
 * 状态，1：正常，2：草稿，3：已审核，4：终审，0,：退回
 */
function get_status_name($status){
    $status_name=array(
        '1'=>'新发布',
        '2'=>'草稿',
        '3'=>'已审核',
        '4'=>'已终审',
        '0'=>'退回',
    );
    return $status_name[$status];
}
/**
 * 店铺分组
 */
function group_dian($arr){
    $newArr=array();
    foreach($arr as $v){
        $newArr[$v['floor_1']][]=$v;
    }
    return $newArr;
}
function characet($data,$eccode='GBK'){

    if( !empty($data) ){
        $fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
        if($fileType!=$eccode){
            $data = mb_convert_encoding($data ,$eccode , $fileType);
        }
    }
    return $data;
}
/**
 * 生成二维码
 */
function create_qrcode($path,$filename,$string,$level='L',$size=10){
    if(!is_dir($path)){
        mkdir($path,0777,true);
    }
    include "application/News/Org/phpqrcode/qrlib.php";
    $errorCorrectionLevel = $level;

    $matrixPointSize = min(max((int)$size, 1), 10);

    $filename=$path.$filename.'_'.$level.'_'.$size.'.png';
    if ($string) {
        QRcode::png($string, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        return $filename;
    } else {
        return false;
    }
}


/**
 * 出入证主页面审核状态的中文显示
 * @param $status
 * @return mixed
 */

function get_pass_status_name($status){
    $status_name=array(
        '1'=>'正常生效',
        '2'=>'未通过',
        '3'=>'已过期',
        '0'=>'待审核',
    );
    return $status_name[$status];
}