<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/13
 * Time: 17:24
 */

namespace script;


use core\PostMan;

class 京东
{
    /*
     * cookie获取地址：https://bean.m.jd.com
     * */
    private $cookie;

    public function __construct(array $params)
    {
        $this->cookie = $params['cookie'];
    }

    public function 京东商城签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=signBeanIndex&appid=ld',
            'type' => 'GET',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if (preg_match('/跳转至拼图/', $html))
            return ['result' => R_NO, 'msg' => '需要拼图验证'];
        if ($json['data']['status'] == 1)
            if (preg_match('/dailyAward/', $html))
                return ['result' => R_OK, 'msg' => "获得{$json['data']['dailyAward']['beanAward']['beanCount']}京豆"];
            elseif (preg_match('/continuityAward/', $html))
                return ['result' => R_OK, 'msg' => "获得{$json['data']['continuityAward']['beanAward']['beanCount']}京豆"];
            elseif (preg_match('/新人签到/', $html))
                return ['result' => R_OK, 'msg' => '获得?京豆'];
            else
                return ['result' => R_ERROR, 'msg' => '未知'];
        if (preg_match('/(已签到|新人签到)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东商城转盘(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=lotteryDraw&body=%7B%22actId%22%3A%22jgpqtzjhvaoym%22%2C%22appSource%22%3A%22jdhome%22%2C%22lotteryCode%22%3A%224wwzdq7wkqx2usx4g5i2nu5ho4auto4qxylblkxacm7jqdsltsepmgpn3b2hgyd7hiawzpccizuck%22%7D&appid=ld',
            'type' => 'GET',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if (preg_match('/(\"T216\"|活动结束)/', $html))
            return ['result' => R_NO, 'msg' => '活动结束'];
        if (preg_match('/(京豆|\"910582\")/', $html))
            if ($json['data']['chances'] > 0)
                return array_merge(['result' => R_OK, 'msg' => "获得{$json['data']['prizeSendNumber']}京豆"], $this->京东商城转盘());
            else
                return ['result' => R_OK, 'msg' => "获得{$json['data']['prizeSendNumber']}京豆"];
        if (preg_match('/未中奖/', $html))
            if ($json['data']['chances'] > 0)
                return array_merge(['result' => R_OK, 'msg' => '未中奖'], $this->京东商城转盘());
            else
                return ['result' => R_OK, 'msg' => '未中奖'];
        if (preg_match('/(T215|次数为0)/', $html))
            return ['result' => R_OK, 'msg' => '已转过'];
        if (preg_match('/(T210|密码)/', $html))
            return ['result' => R_NO, 'msg' => '无支付密码'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东金融签到(): array
    {
        $data = [
            'url' => 'https://ms.jr.jd.com/gw/generic/zc/h5/m/signRecords',
            'type' => 'POST',
            'data' => 'reqData=%7B%22bizLine%22%3A2%7D',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '登录解析返回结果失败'];
        if (!isset($json['resultData']['data']['login']))
            return ['result' => R_ERROR, 'msg' => '登录失败'];
        if (!$json['resultData']['data']['login'])
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        $data = [
            'url' => 'https://ms.jr.jd.com/gw/generic/zc/h5/m/signRewardGift',
            'type' => 'POST',
            'data' => 'reqData=%7B%22bizLine%22%3A2%2C%22signDate%22%3A%221%22%2C%22deviceInfo%22%3A%7B%22os%22%3A%22iOS%22%7D%2C%22clientType%22%3A%22sms%22%2C%22clientVersion%22%3A%2211.0%22%7D',
            'cookie' => $this->cookie,
            'header' => ['Referer' => 'https://jddx.jd.com/m/jddnew/money/index.html'],
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '签到解析返回结果失败'];
        if ($json['resultData']['resultCode'] == '00000')
            if ($json['resultData']['data']['rewardAmount'] == 1)
                return ['result' => R_OK, 'msg' => "获得{$json['resultData']['data']['beanAward']['rewardAmount']}京豆"];
            else
                return ['result' => R_OK, 'msg' => "未获得"];
        if (preg_match('/(发放失败|70111)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(\"resultCode\":3|请先登录)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东金融钢镚(): array
    {
        $data = [
            'url' => 'https://ms.jr.jd.com/gw/generic/gry/h5/m/signIn',
            'type' => 'POST',
            'data' => 'reqData=%7B%22channelSource%22%3A%22JRAPP%22%2C%22riskDeviceParam%22%3A%22%7B%7D%22%7D',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '签到解析返回结果失败'];
        if (preg_match('/\"resBusiCode\":0/', $html)) {
            $val = $json['resultData']['resBusiData']['actualTotalRewardsValue'];
            return ['result' => R_OK, 'msg' => '获得' . strlen($val) == 1 ? '0.0' : '0.' . "{$val}钢镚"];
        }
        if (preg_match('/(已经领取|\"resBusiCode\":15)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/未实名/', $html))
            return ['result' => R_NO, 'msg' => '未实名'];
        if (preg_match('/(\"resultCode\":3|请先登录)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }
}