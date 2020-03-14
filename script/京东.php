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

}