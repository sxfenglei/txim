<?php
/**
 * 腾讯IM服务端API封装库
 * @author    sxfenglei <442165035@qq.com>
 */
namespace Sxfenglei;

use Exception;
use Tencent\TLSSigAPIv2;

class Txim {

    static private $instance;

    private $sdkAppId = ''; //SDKAppID
    private $secretkey = ''; //IM密钥
    private $identifier = 'administrator'; //IM管理员账号
    private $baseUrl = 'https://console.tim.qq.com/v4'; //API地址
    private $expire = 86400*180; //IM授权过期时间 默认180天

    /** 防止new创建对象和初始化
    * @param $sdkAppId SDKAppID
    * @param $secretkey IM密钥
    * @param $identifier 管理员账号
    * @param int $expire 过期时间，单位秒，默认 180 天
    * @param $baseUrl API基地址
     */
    private function __construct($init){ 
        if(empty($init['sdkAppId']) || empty($init['secretkey'])){ 
            throw new \Exception('init fail,sdkAppId and secretkey must.'); 
        }
        $this->sdkAppId = $init['sdkAppId'];
        $this->secretkey = $init['secretkey'];
        if(isset($init['identifier'])){ 
            $this->identifier = $init['identifier'];
        }
        if(isset($init['baseUrl'])){ 
            $this->baseUrl = $init['baseUrl'];
        }
        if(isset($init['expire'])){ 
            $this->expire = $init['expire'];
        }
    }

    //防止clone克隆对象
    private function __clone(){}

    //单例
    static public function getInstance($init=[]){
        if(!self::$instance instanceof self){
            self::$instance = new self($init);
        } 
        return self::$instance;
    }

    public function test(){
        return $this->getSig();
    }

    ///////////////////////////////
    /////// 账号管理
    //////////////////////////////
     /** 导入单个帐号
      * @param string $account 用户名，长度不超过32字节 用作IM账号一般取业务服务器的用户账号作为IM账号
      * @param string $name 昵称
      * @param string $faceUrl 头像 
     */ 
    public function importAccount($account = '', $name = '', $faceUrl = '')
    {
        if (empty($account)) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            "Identifier" => $account, //用户名，长度不超过32字节
            "Nick" => empty($name)?$account:$name,
        ];
        if (!empty($faceUrl)) $data['FaceUrl'] = $faceUrl;
        $arr = $this->requestApi('/im_open_login_svc/account_import',$data); 
        return $arr;
    }
    
     /** 导入多个帐号
      * @param array $accountArr 用户名，单个用户名长度不超过32字节，单次最多导入100个用户名 ['user1','user2']
     */ 
    public function importAccountArr($accountArr = [])
    {
        if (count($accountArr)<1) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            "Accounts" => $accountArr, //用户名，单个用户名长度不超过32字节，单次最多导入100个用户名
        ];
        $arr = $this->requestApi('/im_open_login_svc/multiaccount_import',$data); 
        return $arr;
    }

    /** 删除帐号
      * @param array $accountArr 用户名,单次请求最多支持100个帐号
     */ 
    public function deleteAccount($accountArr = [])
    {
        if (count($accountArr)<1) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [];
        foreach($accountArr as $v){
            $data['DeleteItem'][] = [
                'UserID' => $v
            ];
        }
        $arr = $this->requestApi('/im_open_login_svc/account_delete',$data);  
        return $arr;
    }
    
    /** 查询帐号
      * @param array $accountArr 用户名,单次请求最多支持100个帐号
     */ 
    public function queryAccount($accountArr = [])
    {
        if (count($accountArr)<1) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [];
        foreach($accountArr as $v){
            $data['CheckItem'][] = [
                'UserID' => $v
            ];
        }
        //返回的字段AccountStatus: Imported 表示已导入IM(存在)，NotImported 表示未导入IM(不存在) 
        $arr = $this->requestApi('/im_open_login_svc/account_check',$data); 
        return $arr;
    }

    /** 失效帐号登录状态
      * @param array $account 用户名,将该用户当前的登录状态失效，这样用户使用历史 UserSig 登录即时通信 IM 会失败。
     */ 
    public function kickAccount($account = '')
    {
        if (empty($account)) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            'Identifier'=>$account
        ];
        $arr = $this->requestApi('/im_open_login_svc/kick',$data); 
        return $arr;
    }

    
    /** 查询帐号在线状态
      * @param array $accountArr 用户名
      * @param array $IsNeedDetail 0无须详细信息, 1获取详细的登录平台信息
     */ 
    public function statusAccount($accountArr = [],$IsNeedDetail = 0)
    {
        if (count($accountArr)<1) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            'IsNeedDetail'=>$IsNeedDetail,
            'To_Account'=>$accountArr
        ];
        $arr = $this->requestApi('/openim/querystate',$data); 
        return $arr;
    }

    ///////////////////////////////
    /////// 单聊消息
    //////////////////////////////
    /** 单发单聊
      * @param string $toAccount 接收人
      * @param string $fromAccount 发送人
      * @param array $msgBody 消息体
      * @param number $syncOtherMachine 1：把消息同步到 From_Account 在线终端和漫游上；2：消息不同步至 From_Account；若不填写默认情况下会将消息存 From_Account 漫游
     */ 
    public function sendMsg($toAccount = '',$msgBody=[],$fromAccount = '',$syncOtherMachine = 1)
    {
        if (empty($toAccount)) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"toAccount不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            'SyncOtherMachine'=>$syncOtherMachine,
            'To_Account'=>$toAccount,
            // 'MsgLifeTime'=>604800,//默认7天
            'MsgRandom'=>rand(10000, 9999999),
            'MsgTimeStamp'=>time(),
            'MsgBody'=>$msgBody,
            // 'CloudCustomData'=>'',
        ];
        if(!empty($fromAccount)){
            $data['From_Account'] = $fromAccount;
        }
        $arr = $this->requestApi('/openim/sendmsg',$data); 
        return $arr;
    }

    //TODO:待补全

    ///////////////////////////////
    /////// 全员推送
    //////////////////////////////

    //TODO:待实现

    ///////////////////////////////
    /////// 资料管理
    //////////////////////////////

    //TODO:待实现

    ///////////////////////////////
    /////// 关系链管理
    //////////////////////////////

    //TODO:待实现

    ///////////////////////////////
    /////// 群组管理
    //////////////////////////////

    /** 获取app中的所有群组
      * @param int $limit 本次获取的群组 ID 数量的上限，不得超过 10000。如果不填，默认为最大值 10000
      * @param int $next 群太多时分页拉取标志，第一次填0，以后填上一次返回的值，返回的 Next 为0代表拉完了
      * @param int $groupType 群组形态包括 Public（公开群），Private（私密群），ChatRoom（聊天室），AVChatRoom（音视频聊天室）和 BChatRoom（在线成员广播大群）
     */ 
    public function getGroupList($limit = 10000,$next=0,$groupType='')
    {
        $data = [
            'Limit'=>$limit,
            'Next'=>$next,
        ];
        if (!empty($groupType)){
            $data['GroupType'] = $groupType;
        };
        $arr = $this->requestApi('/group_open_http_svc/get_appid_group_list',$data); 
        return $arr;
    }

    /** 创建群组
      * @param string $name 群名称，最长30字节，使用 UTF-8 编码，1个汉字占3个字节
      * @param string $type 群组形态，包括 Public（陌生人社交群），Private（即 Work，好友工作群），ChatRoom（即 Meeting，会议群），AVChatRoom（直播群）
      * @param string $ownerAccout 群主 ID，自动添加到群成员中。如果不填，群没有群主
      * @param array $memberList 初始群成员列表，最多500个
     */ 
    public function groupCreate($name = '', $type = 'Public', $ownerAccout = '', $memberList = [])
    {
        if (empty($name)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"name不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'Name' => $name,
            'Type' => $type
        ]; 
        if (!empty($ownerAccout)) {
            $data['Owner_Account'] = $ownerAccout;
        }
        if (count($memberList) > 0) {
            foreach ($memberList as $v) {
                $data['MemberList'][] = [
                    'Member_Account' => $v
                ];
            }
        }
        $arr = $this->requestApi('/group_open_http_svc/create_group',$data); 
        return $arr; 
    }

     /** 群组添加用户
      * @param string $groupId 组id
      * @param array $memberList 群成员列表
     */ 
    public function groupAddUser($groupId = '', $memberList = [])
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId
        ];
        if (count($memberList) > 0) {
            foreach ($memberList as $v) {
                $data['MemberList'][] = [
                    'Member_Account' => $v
                ];
            }
        }
        $arr = $this->requestApi('/group_open_http_svc/add_group_member',$data); 
        return $arr;  
    }

     /** 移除群组用户
      * @param string $groupId 组id
      * @param array $memberList 成员列表
     */ 
    public function groupRemoveUser($groupId = '', $memberList = [])
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId
        ];
        if (count($memberList) > 0) {
            foreach ($memberList as $v) {
                $data['MemberToDel_Account'][] = $v;
            }
        }
        $arr = $this->requestApi('/group_open_http_svc/delete_group_member',$data); 
        return $arr; 
    }

    /** 群组发普通消息
      * @param string $groupId 组id
      * @param string $content 内容
     */ 
    public function groupSend($groupId = '', $content = '')
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId,
            'Random' => rand(1000000, 9999999),
        ];
        $data['MsgBody'][] = [
            'MsgType' => 'TIMTextElem',
            'MsgContent' => [
                "Text" => $content
            ]
        ]; 
        $arr = $this->requestApi('/group_open_http_svc/send_group_msg',$data); 
        return $arr; 
    }

     /** 群组发系统消息
      * @param string $groupId 组id
      * @param string $content 内容
     */ 
    public function groupSystemSend($groupId = '', $content = '')
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId,
            'Content' => $content,
        ];
        $arr = $this->requestApi('/group_open_http_svc/send_group_system_notification',$data); 
        return $arr; 
    }

    /** 解散群组
      * @param string $groupId 组id
     */ 
    public function groupDelete($groupId = '')
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId
        ];
        $arr = $this->requestApi('/group_open_http_svc/destroy_group',$data); 
        return $arr;
    }

    /** 导入群消息
     * 一次最多导入7条
      * @param string $groupId 组ID
      * @param array $msg 消息列表
     */ 
    public function importGroupMsg($groupId = '', $msg = [])
    {
        if (empty($groupId)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"groupId不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'GroupId' => $groupId,
            'MsgList' => $msg
        ];
        $arr = $this->requestApi('/group_open_http_svc/import_group_msg',$data); 
        return $arr;
    }

    //TODO:待补全

    ///////////////////////////////
    /////// 全局禁言管理
    //////////////////////////////

    /** 设置全局禁言
      * @param string $account 用户名
      * @param string $type single单聊 group组 all全部
      * @param number $time 0取消禁言 4294967295永久禁言 其它秒数为具体禁言时间
     */ 
    public function setnospeaking($account = '',$type='single',$time=0)
    {
        if (empty($account)) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            'Set_Account'=>$account
        ];
        if('single' == $type){
            $data['C2CmsgNospeakingTime'] = $time;
        }else if('group' == $type){
            $data['GroupmsgNospeakingTime'] = $time;
        }else{ 
            $data['C2CmsgNospeakingTime'] = $time;
            $data['GroupmsgNospeakingTime'] = $time;
        }
        $arr = $this->requestApi('/openconfigsvr/setnospeaking',$data); 
        return $arr;
    }
    
    /** 查询全局禁言
      * @param string $account 用户名
     */ 
    public function getnospeaking($account = '')
    {
        if (empty($account)) return [
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>"account不能为空",
                "ErrorCode"=>-1
        ];
        $data = [
            'Get_Account'=>$account
        ];
        $arr = $this->requestApi('/openconfigsvr/getnospeaking',$data); 
        return $arr;
    }

    ///////////////////////////////
    /////// 运营管理
    //////////////////////////////

    /** 下载最近消息记录
      * @param string $type 消息类型，C2C 表示单发消息 Group 表示群组消息
      * @param string $time 需要下载的消息记录的时间段，2015120121表示获取2015年12月1日21:00 - 21:59的消息的下载地址。该字段需精确到小时。每次请求只能获取某天某小时的所有单发或群组消息记录
     */ 
    public function msgHistory($time = '2021062416',$type = 'Group')
    {
        $data = [
            'ChatType' => $type,
            'MsgTime' => $time
        ];
        $arr = $this->requestApi('/open_msg_svc/get_history',$data); 
        return $arr;
    }

    

    /** 导入单聊消息
      * @param string $fromAccount 发送人
      * @param string $toAccount 接收人
      * @param array $msg 消息列表
     */ 
    public function importMsg($fromAccount = '', $toAccount = '', $msg = [])
    {
        if (empty($fromAccount)||empty($toAccount)) return [
            "ActionStatus"=>"FAIL",
            "ErrorInfo"=>"参数不能为空",
            "ErrorCode"=>-1
        ];
        $data = [
            'SyncFromOldSystem' => 2,
            'From_Account' => $fromAccount,
            'To_Account' => $toAccount,
            'MsgRandom' => rand(1000000, 9999999),
            'MsgTimeStamp' => time(),
            'MsgList' => $msg
        ];
        $arr = $this->requestApi('/openim/importmsg',$data); 
        return $arr;
    }

    ///////////////////////////////
    /////// 其它
    //////////////////////////////
    //curl post 封装
    public function curlPost($url='',$body=[],$header='')
    {
        if(empty($url)) return false;
        if(is_array($header)){
            $arr = [];
            foreach ($header as $k=>$v){
                $arr[] = $k.': '.$v;
            }
            $header = $arr;
        }
        $postJosnData = count($body)>0?json_encode($body):new \stdClass();
        $ch = curl_init();
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
    //    curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('curl POST fail: ' . curl_error($ch));
        }
        curl_close($ch);
        return $output;
    }

    //组装IM请求url
    private function getUrl($api = '')
    {
        return $this->baseUrl . $api .  '?sdkappid=' . $this->sdkAppId . '&identifier=' . $this->identifier . '&usersig=' . $this->getSig() . '&random=' . rand(100, 9999999) . '&contenttype=json';
    }

    //获取IM授权
    private function getSig()
    {
        $tx = new TLSSigAPIv2($this->sdkAppId, $this->secretkey);
        return $tx->genUserSig($this->identifier);
    }

    /** 请求接口
      * @param string $api IM api url
      * @param array $data 请求数据
      * @param bool $isConvert 是否json转换数组
     */
    public function requestApi($api='',$data=[],$isConvert=true)
    {
        if(empty($api) || count($data)<1) return false;
        try{ 
            $url = $this->getUrl($api);
            $res = $this->curlPost($url, $data);
            error_log('请求:url='.$url.PHP_EOL);
            error_log('数据:'.PHP_EOL.json_encode($data,256).PHP_EOL);
            if($isConvert){ 
                $arr = json_decode($res,256);
                if(isset($arr['ErrorCode'])){
                    return $arr;
                }else{
                    throw new \Exception('Failed to parse the returned data: ' .$res);
                }
            }else{
                return $res;
            }
        }catch (\Exception $e){ 
            throw new \Exception('request IM api fail: ' . $e->getMessage());
        }
    }

  
     
}