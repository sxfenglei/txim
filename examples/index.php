<?php

use Sxfenglei\ErrorCode;
use Sxfenglei\Message;
use Sxfenglei\Txim;

require_once '../vendor/autoload.php';

//支持.env
if (is_file('../.env')) {
    $env = parse_ini_file('../.env', true);    //解析env文件,name = PHP_KEY
    foreach ($env as $key => $val) {
        $name = strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {    //如果是二维数组 item = PHP_KEY_KEY
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
        }
    }
}
//用getenv('对应的key')获取值  

$config = [
    'sdkAppId'=>getenv('TX_SDKAPPID'), //必须
    'secretkey'=>getenv('TX_SECRETKEY'), //必须
    'identifier'=>'administrator',
    'expire'=>86400*180,
];
try{ 
    $tx = Txim::getInstance($config);
    // var_dump($tx);
    // echo $tx->test();

    //导入单账号
    // $t = $tx->importAccount('sxfenglei','小冯同学');
    //导入多账号
    // $t = $tx->importAccountArr(['feng0','feng1','feng2']);
    //删除账号
    // $t = $tx->deleteAccount(['feng0','feng1','feng2']);
    //查询账号
    // $t = $tx->queryAccount(['feng0','feng1','feng2']); //AccountStatus: Imported 表示已导入IM(存在)，NotImported 表示未导入IM(不存在) 
    //失效账号登录状态
    // $t = $tx->kickAccount('feng1');
    //查询账号在线状态
    // $t = $tx->statusAccount(['feng0','feng1','feng2'],1);

    //单发 组合消息不成功!
    // $msgBody[] = Message::text("还好");
    // $msgBody[] = Message::text("走2");
    // $t = $tx->sendMsg('feng2',$msgBody,'feng0'); 
    
    //获取所有群组
    $t = $tx->getGroupList();
    
    //创建群
    // $t = $tx->groupCreate('测试群'); //@TGS#2LKEYAIHH
    //添加成员
    // $t = $tx->groupAddUser('@TGS#2LKEYAIHH',['user0','user1','user2']);
    //移除成员
    // $t = $tx->groupRemoveUser('@TGS#2LKEYAIHH',['user1']);
    //发送普通消息
    // $t = $tx->groupSend('@TGS#2LKEYAIHH','普通消息');
    //发送系统消息
    // $t = $tx->groupSystemSend('@TGS#2LKEYAIHH','系统消息');
    //解散群
    // $t = $tx->groupDelete('@TGS#2LKEYAIHH');
    
    
    $t = ErrorCode::getCN($t);
    echo json_encode($t,256);
}catch(\Exception $e){
    echo $e->getMessage();
}