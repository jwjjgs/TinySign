<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 12:01
 */

namespace script;

use core\PostMan;

class 四川移动掌上营业厅
{
    private $JSESSIONID;
    private $SSOCookie;

    public function __construct(array $params)
    {
        $this->JSESSIONID = $params['JSESSIONID'];
        $this->SSOCookie = $params['SSOCookie'];
    }

    public function 防疫知识(): array
    {
        $data = [
            'url' => 'https://wap.sc.10086.cn/scmccCampaign/epidemicAnswer/epidemicDraw.do',
            'type' => 'POST',
            'data' => "SSOCookie={$this->SSOCookie}&canals=sh2",
            'cookie' => "JSESSIONID={$this->JSESSIONID}",
        ];
        $json = json_decode(PostMan::send($data), true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        return ['result' => isset($json['result']['info']) ? R_OK : R_NO, 'msg' => $json['result']['info']];
    }

    public function 签到(): array
    {
        $data = [
            'url' => 'https://wap.sc.10086.cn/scmccCampaign/signCalendar/sign.do',
            'type' => 'POST',
            'data' => "SSOCookie={$this->SSOCookie}&canals=sh2",
            'cookie' => "JSESSIONID={$this->JSESSIONID}",
        ];
        $json = json_decode(PostMan::send($data), true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        return ['result' => isset($json['result']['info']) ? R_OK : R_NO, 'msg' => $json['result']['info']];
    }

    public function 大转盘1(): array
    {
        $data = [
            'url' => 'https://wap.sc.10086.cn/scmccCampaign/newturntable/dzpDraw.do',
            'type' => 'POST',
            'data' => "SSOCookie={$this->SSOCookie}&canals=zt1",
            'cookie' => "JSESSIONID={$this->JSESSIONID}",
        ];
        $json = json_decode(PostMan::send($data), true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        return ['result' => isset($json['result']['info']) ? R_OK : R_NO, 'msg' => $json['result']['info']];
    }

    public function 大转盘2(): array
    {
        $data = [
            'url' => 'https://wap.sc.10086.cn/scmccCampaign/newturntable/dzpDraw.do',
            'type' => 'POST',
            'data' => "SSOCookie={$this->SSOCookie}&canals=sh2",
            'cookie' => "JSESSIONID={$this->JSESSIONID}",
        ];
        $json = json_decode(PostMan::send($data), true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        return ['result' => isset($json['result']['info']) ? R_OK : R_NO, 'msg' => $json['result']['info']];
    }
}