<?php
/**
 * 腾讯IM服务端API消息体
 * @author    sxfenglei <442165035@qq.com>
 */
namespace Sxfenglei;

class Message {
    private static $msgType = [
        'TIMTextElem',//文本消息。
        'TIMLocationElem',//地理位置消息。
        'TIMFaceElem',//表情消息。
        'TIMCustomElem',//自定义消息，当接收方为 iOS 系统且应用处在后台时，此消息类型可携带除文本以外的字段到 APNs。一条组合消息中只能包含一个 TIMCustomElem 自定义消息元素。
        'TIMSoundElem',//语音消息。
        'TIMImageElem',//图像消息。
        'TIMFileElem',//文件消息。
        'TIMVideoFileElem',//视频消息。
    ];
 
    /** 文本
      * @param string $content 内容
     */ 
    public static function text($content = ''){
        return [ 
            "MsgType"=> 'TIMTextElem',
            "MsgContent"=> [
                'Text'=>$content
            ]
        ];
    }
    
    /** 地理位置
      * @param number $longitude 经度
      * @param number $latitude 纬度
      * @param string $desc 描述
     */ 
    public static function location($longitude = '',$latitude = '',$desc=''){
        return [ 
            "MsgType"=> 'TIMLocationElem',
            "MsgContent"=> [
                'Desc'=>$desc,
                'Latitude'=>$latitude,
                'Longitude'=>$longitude,
            ]
        ];
    }

    
    /** 表情
      * @param number $index 表情索引
      * @param string $data 额外数据
     */ 
    public static function face($index = 0,$data=''){
        return [ 
            "MsgType"=> 'TIMFaceElem',
            "MsgContent"=> [
                'Index'=>$index,
                'Data'=>$data,
            ]
        ];
    }

    /** 自定义消息
      * @param string $data 自定义消息
      * @param string $desc 描述
      * @param string $ext 扩展字段
      * @param string $sound 自定义APNs推送铃声
     */ 
    public static function custom($data='',$desc='',$ext='',$sound=''){
        return [ 
            "MsgType"=> 'TIMCustomElem',
            "MsgContent"=> [
                'Data'=>$data,
                'Desc'=>$desc,
                'Ext'=>$ext,
                'Sound'=>$sound,
            ]
        ];
    }

    /** 语音
      * @param string $url 音频url
      * @param number $size 语音数据大小(字节)
      * @param number $second 语音时长
      * @param number $downloadFlag 语音下载方式标记。目前 Download_Flag 取值只能为2，表示可通过Url字段值的 URL 地址直接下载语音。
     */ 
    public static function sound($url='',$size=0,$second=0,$downloadFlag=2){
        return [ 
            "MsgType"=> 'TIMSoundElem',
            "MsgContent"=> [
                'Url'=>$url,
                'Size'=>$size,
                'Second'=>$second,
                'Download_Flag'=>$downloadFlag,
            ]
        ];
    }

    /** 图片
      * @param string $uuid 图片序列号。后台用于索引图片的键值
      * @param number $imageFormat 图片格式。JPG = 1，GIF = 2，PNG = 3，BMP = 4，其他 = 255
      * @param array $imageInfoArr 原图、缩略图或者大图下载信息
            * @param number $type 图片类型： 1-原图，2-大图，3-缩略图
            * @param number $size 图片数据大小，单位：字节
            * @param number $width 图片宽度
            * @param number $height 图片高度
            * @param string $url 图片下载地址
     */ 
    public static function image($uuid='',$imageFormat=0,$imageInfoArr=[]){
        return [ 
            "MsgType"=> 'TIMImageElem',
            "MsgContent"=> [
                'UUID'=>$uuid,
                'ImageFormat'=>$imageFormat,
                'ImageInfoArray'=>$imageInfoArr
                // [
                //     'Type'=>'',
                //     'Size'=>'',
                //     'Width'=>'',
                //     'Height'=>'',
                //     'URL'=>'',
                // ]
            ]
        ];
    }

      /** 文件
      * @param string $url 文件下载地址
      * @param number $fileSize 文件数据大小，单位：字节 
      * @param string $fileName 文件名 
      * @param number $downloadFlag 下载方式标记。目前 Download_Flag 取值只能为2，表示可通过Url字段值的 URL 地址直接下载。
     */ 
    public static function file($url='',$fileSize=0,$fileName='',$downloadFlag=2){
        return [ 
            "MsgType"=> 'TIMFileElem',
            "MsgContent"=> [
                'Url'=>$url,
                'FileSize'=>$fileSize,
                'FileName'=>$fileName,
                'Download_Flag'=>$downloadFlag,
            ]
        ];
    }

    /** 视频
      * @param string $videoUrl 视频下载地址
      * @param number $videoSize 视频数据大小，单位：字节 
      * @param number $videoSecond 视频时长，单位：秒 
      * @param string $videoFormat 视频格式，例如 mp4 
      * @param number $videoDownloadFlag 视频下载方式标记。目前 Download_Flag 取值只能为2，表示可通过Url字段值的 URL 地址直接下载。
      
      * @param string $thumbUrl 缩略图下载地址
      * @param number $thumbSize 缩略图数据大小，单位：字节 
      * @param number $thumbWidth 缩略图时长，单位：秒 
      * @param number $thumbHeight 缩略图下载方式标记。
      * @param string $thumbFormat 缩略图格式，例如 mp4 
      * @param number $thumbDownloadFlag 下载方式标记。目前 Download_Flag 取值只能为2，表示可通过Url字段值的 URL 地址直接下载。
     */ 
    public static function video($videoUrl='',$videoSize=0,$videoSecond=0,$videoFormat='',$videoDownloadFlag=0,$thumbUrl='',$thumbSize=0,$thumbWidth=0,$thumbHeight=0,$thumbFormat='',$thumbDownloadFlag=2){
        return [ 
            "MsgType"=> 'TIMVideoFileElem',
            "MsgContent"=> [
                'VideoUrl'=>$videoUrl,
                'VideoSize'=>$videoSize,
                'VideoSecond'=>$videoSecond,
                'VideoFormat'=>$videoFormat,
                'VideoDownloadFlag'=>$videoDownloadFlag,
                'ThumbUrl'=>$thumbUrl,
                'ThumbSize'=>$thumbSize,
                'ThumbWidth'=>$thumbWidth,
                'ThumbHeight'=>$thumbHeight,
                'ThumbFormat'=>$thumbFormat,
                'ThumbDownloadFlag'=>$thumbDownloadFlag,
            ]
        ];
    }

}