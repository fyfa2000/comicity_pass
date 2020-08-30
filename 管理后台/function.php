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
 * 出入证状态
 */
function get_status_name($status){
    //待审核0，正常生效1，未通过2，已过期3，已删除 -1
    $status_name=array(
        '1'=>'正常生效',
        '2'=>'未通过',
        '3'=>'已过期',
        '-1'=>'已删除',
        '0'=>'待审核',
    );
    return $status_name[$status];
}

/**
 * 出入证类型
 */
function get_type_name($type){
    //待审核0，正常生效1，未通过2，已过期3，已删除 -1
    $type_name=array(
        '1'=>'员工',
        '2'=>'临时施工',

    );
    return $type_name[$type];
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

///**
// * 前台POST过来的数据，写进数据库前过滤
// * @param $content
// * @return array|string
// */
//function escapeString($content) {
//    $pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])|(drop[\s])/i";
//    if (is_array($content)) {
//        foreach ($content as $key=>$value) {
//            $content[$key] = addslashes(trim($value));
//            if(preg_match($pattern,$content[$key])) {
//                $content[$key] = '';
//            }
//        }
//    } else {
//        $content=addslashes(trim($content));
//        if(preg_match($pattern,$content)) {
//            $content = '';
//        }
//    }
//    return $content;
//}