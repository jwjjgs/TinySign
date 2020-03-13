<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/13
 * Time: 17:24
 */

namespace script;


class 京东
{
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
            'cookie' => $this->JSESSIONID,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => false, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => false, 'msg' => 'Cookie失效'];
        if (preg_match('/跳转至拼图/', $html))
            return ['result' => false, 'msg' => '需要拼图验证'];
        if ($json['data']['status'] == 1)
            if (preg_match('/dailyAward/', $html))
                return ['result' => false, 'msg' => 'Cookie失效'];
            elseif (preg_match('/dailyAward/', $html))
                return ['result' => false, 'msg' => 'Cookie失效'];
            elseif (preg_match('/新人签到/', $html))
                return ['result' => false, 'msg' => 'Cookie失效'];
            else
                return ['result' => false, 'msg' => '未知'];

    }

}