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
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
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

    public function 京东金融双签(): array
    {
        $data = [
            'url' => 'https://nu.jr.jd.com/gw/generic/jrm/h5/m/process?',
            'type' => 'POST',
            'data' => 'reqData=%7B%22actCode%22%3A%22FBBFEC496C%22%2C%22type%22%3A3%2C%22riskDeviceParam%22%3A%22%22%7D',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if (preg_match('/京豆X/', $html))
            return ['result' => R_OK, 'msg' => "获得{$json['resultData']['data']['businessData']['awardListVo']['0']['count']}京豆"];
        if ($json['resultData']['data']['businessData']['businessCode'] == '000sq' && $json['resultData']['data']['businessData']['businessMsg'] == '成功')
            return ['result' => R_OK, 'msg' => '无奖励'];
        if (preg_match('/已领取/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        if (preg_match('/未在/', $html))
            return ['result' => R_NO, 'msg' => '未在京东签到'];
        if (preg_match('/(\"resultCode\":3|请先登录)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东商城摇一摇(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?appid=vip_h5&functionId=vvipclub_shaking',
            'type' => 'GET',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if (isset($json['data']['prizeBean']))
            if ($json['data']['luckyBox']['freeTimes'] > 0)
                return array_merge(['result' => R_OK, 'msg' => "获得{$json['data']['prizeBean']['count']}京豆"], $this->京东商城转盘());
            else
                return ['result' => R_OK, 'msg' => "获得{$json['data']['prizeBean']['count']}京豆"];
        if (isset($json['data']['prizeCoupon']))
            if ($json['data']['luckyBox']['freeTimes'] > 0)
                return array_merge(['result' => R_OK, 'msg' => "获得满{$json['data']['prizeCoupon']['quota']}减{$json['data']['prizeCoupon']['discount']}优惠券→{$json['data']['prizeCoupon']['limitStr']}"], $this->京东商城转盘());
            else
                return ['result' => R_OK, 'msg' => "获得满{$json['data']['prizeCoupon']['quota']}减{$json['data']['prizeCoupon']['discount']}优惠券→{$json['data']['prizeCoupon']['limitStr']}"];
        if (preg_match('/true/', $html))
            return ['result' => R_OK, 'msg' => '无奖励'];
        if (preg_match('/(无免费|8000005)/', $html))
            return ['result' => R_OK, 'msg' => '已摇过'];
        if (preg_match('/(未登录|101)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东超市签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22caA6%2B%2FTo6Jfe%2FAKYm8gLQEchLXtYeB53heY9YzuzsZoaZs%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22signId%5C%22%3A%5C%22hEr1TO1FjXgaZs%2Fn4coLNw%3D%3D%5C%22%7D%22%7D&screen=750%2A1334&client=wh5&clientVersion=1.0.0&sid=0ac0caddd8a12bf58ea7a912a5c637cw&uuid=1fce88cd05c42fe2b054e846f11bdf33f016d676&area=19_1617_3643_8208',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东钟表签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22LW67%2FHBJP72aMSByZLRaRqJGukOFKx9r4F87VrKBmogaZs%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Atrue%2C%5C%22signId%5C%22%3A%5C%22g2kYL2MvMgkaZs%2Fn4coLNw%3D%3D%5C%22%7D%22%7D&client=wh5',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }


    public function 京东宠物签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%226DiDTHMDvpNyoP9JUaEkki%2FsREOeEAl8M8REPQ%2F2eA4aZs%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22signId%5C%22%3A%5C%22Nk2fZhdgf5UaZs%2Fn4coLNw%3D%3D%5C%22%7D%22%7D&client=wh5',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东闪购签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=partitionJdSgin',
            'type' => 'POST',
            'data' => 'body=%7B%7D&client=apple&clientVersion=8.4.6&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&sign=141ab5f9af92126bb46d50f3e8af758a&st=1579305780511&sv=102',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['result']['code'] == 0)
            if (isset($json['result']['count']))
                return ['result' => R_OK, 'msg' => "获得{$json['result']['count']}京豆"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取|\"2005\")/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束|\"2008\")/', $html))
            return $this->京东闪购瓜分();
        if (preg_match('/(\"code\":\"3\"|\"1003\")/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    private function 京东闪购瓜分(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=partitionJdShare',
            'type' => 'POST',
            'data' => 'body=%7B%7D&client=apple&clientVersion=8.5.0&d_brand=apple&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&sign=958ba0e805094b4b0f6216e86190ab51&st=1582042405636&sv=120&wifiBssid=unknown',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['result']['code'] == 0)
            if (isset($json['result']['jdBeanNum']))
                return ['result' => R_OK, 'msg' => "获得{$json['result']['jdBeanNum']}京豆"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已参与|已领取|\"2006\")/', $html))
            return ['result' => R_OK, 'msg' => '已瓜分'];
        if (preg_match('/(不存在|已结束|未开始|\"2008\")/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        if (preg_match('/(\"code\":\"1003\"|未获取)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东图书签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2F3SC6rw5iBg66qrXPGmZMqFDwcyXi%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.linkstars.com%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000089893_157_0_184__cc59020469361878%26utm_term%3De04e88b40a3c4e24898da7fcee54a609%22%7D%2C%22url%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2F3SC6rw5iBg66qrXPGmZMqFDwcyXi%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.linkstars.com%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000089893_157_0_184__cc59020469361878%26utm_term%3De04e88b40a3c4e24898da7fcee54a609%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22ziJpxomssJzA0Lnt9V%2BVYoW5AbqAOQ6XiMQuejSm7msaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200416621_28128239_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%22jw9BKb%5C%2Fb%2BfEaZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&client=apple&clientVersion=8.4.6&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&sign=c1d6bdbb17d0d3f8199557265c6db92c&st=1579305128990&sv=121',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }


    public function 京东拍拍签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2F3S28janPLYmtFxypu37AYAGgivfp%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.linkstars.com%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000089893_157_0_184__cc59020469361878%26utm_term%3Dd802691049c9473897298c4de3159179%22%7D%2C%22url%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2F3S28janPLYmtFxypu37AYAGgivfp%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.linkstars.com%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000089893_157_0_184__cc59020469361878%26utm_term%3Dd802691049c9473897298c4de3159179%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%221aXiBKmxyz6XLsyntfp11AP4x7fjsFotKNTTk2Y39%2BUaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200124860_28262902_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%226CR%5C%2FQvgfF5EaZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&client=apple&clientVersion=8.4.6&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&sign=56a228e0edada1283ba0f971c41633af&st=1579306801665&sv=121',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东美妆签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22-1%22%7D%2C%22url%22%3A%22%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22Ivkdqs6fb5SN1HsgsPsE7vJN9NGIydei6Ik%2B1rAyngwaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200138455_30206794_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%22YU1cvfWmabwaZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&build=167092&client=apple&clientVersion=8.5.2&d_brand=apple&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&scope=11&sign=cc38bf6e24fd65e4f43868ccbe679f85&st=1582992598833&sv=112',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东清洁签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22-1%22%7D%2C%22url%22%3A%22%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22U39rtBF4Dd%2BujQZNkSd%5C%2FtPoL5Cg8baD1q73iQ%2BA4fQ8aZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200561054_30882139_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%22Mws14CT%5C%2FvOcaZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&client=apple&clientVersion=8.5.2&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&scope=11&sign=5e20049fbfb8377ede93a1da912b16dd&st=1583942866451&sv=102',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东女装签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22-1%22%7D%2C%22url%22%3A%22%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22OQmfgxmylrMM6EurCHg9lEjL1ShNb2dVjEja9MceBPgaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200002492_28085975_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%22YE5T0wVaiL8aZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&build=167057&client=apple&clientVersion=8.5.0&d_brand=apple&d_model=iPhone8%2C2&networklibtype=JDNetworkBaseAF&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&osVersion=13.3.1&scope=11&screen=1242%2A2208&sign=7329899a26d8a8c3046b882d6df2b329&st=1581083524405&sv=101&uuid=coW0lj7vbXVin6h7ON%2BtMNFQqYBqMahr',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东现金签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=ccSignInNew',
            'type' => 'POST',
            'data' => 'body=%7B%22pageClickKey%22%3A%22CouponCenter%22%2C%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22childActivityUrl%22%3A%22openapp.jdmobile%253a%252f%252fvirtual%253fparams%253d%257b%255c%2522category%255c%2522%253a%255c%2522jump%255c%2522%252c%255c%2522des%255c%2522%253a%255c%2522couponCenter%255c%2522%257d%22%2C%22monitorSource%22%3A%22cc_sign_ios_index_config%22%7D&client=apple&clientVersion=8.5.0&d_brand=apple&d_model=iPhone8%2C2&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&scope=11&screen=1242%2A2208&sign=1cce8f76d53fc6093b45a466e93044da&st=1581084035269&sv=102',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['busiCode'] == 0)
            if (isset($json['result']['signResult']['signData']['amount']))
                return ['result' => R_OK, 'msg' => "获得{$json['result']['signResult']['signData']['amount']}红包"];
            else
                return ['result' => R_OK, 'msg' => "无红包"];
        if (preg_match('/(\"busiCode\":\"1002\"|完成签到)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        if (preg_match('/(\"busiCode\":\"3\"|未登录)/', $html))
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东鞋靴签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%227Ive90vKJQaMEzWlhMgIwIih1KqMPXNQdPbewzqrg2MaZs%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Atrue%2C%5C%22ruleSrv%5C%22%3A%5C%2200116882_29523722_t0%5C%22%2C%5C%22signId%5C%22%3A%5C%22SeWbLe9ma04aZs%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22riskParam%22%3A%7B%22platform%22%3A%223%22%2C%22orgType%22%3A%222%22%2C%22openId%22%3A%22-1%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22eid%22%3A%22%22%2C%22fp%22%3A%22-1%22%2C%22shshshfp%22%3A%22b3fccfafc270b38e0bddfdc0e455b48f%22%2C%22shshshfpa%22%3A%22%22%2C%22shshshfpb%22%3A%22%22%2C%22childActivityUrl%22%3A%22%22%7D%2C%22siteClient%22%3A%22apple%22%2C%22mitemAddrId%22%3A%22%22%2C%22geo%22%3A%7B%22lng%22%3A%220%22%2C%22lat%22%3A%220%22%7D%2C%22addressId%22%3A%22%22%2C%22posLng%22%3A%22%22%2C%22posLat%22%3A%22%22%2C%22focus%22%3A%22%22%2C%22innerAnchor%22%3A%22%22%2C%22cv%22%3A%222.0%22%7D&client=wh5',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东个护签到(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2FNJ1kd1PJWhwvhtim73VPsD1HwY3%5C%2Findex.html%3FcollectionId%3D294%22%7D%2C%22url%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2FNJ1kd1PJWhwvhtim73VPsD1HwY3%5C%2Findex.html%3FcollectionId%3D294%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22hStxilQclq7q78DPq3us0jpXuBIR%2B%5C%2FhnVJqzHJ7rlfMaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Afalse%2C%5C%22ruleSrv%5C%22%3A%5C%2200167278_30642140_t1%5C%22%2C%5C%22signId%5C%22%3A%5C%22JkIbFVNv3ucaZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&client=apple&clientVersion=8.5.2&d_brand=apple&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&scope=11&sign=fa7e0f78e383b15c63e0958a5f399667&st=1583764925528&sv=112',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东金融广告(): array
    {
        $data = [
            'url' => 'https://ms.jr.jd.com/gw/generic/jrm/h5/m/sendAdGb',
            'type' => 'POST',
            'data' => 'reqData=%7B%22clientType%22%3A%22ios%22%2C%22actKey%22%3A%22176696%22%2C%22userDeviceInfo%22%3A%7B%22adId%22%3A9999999%7D%2C%22deviceInfoParam%22%3A%7B%22macAddress%22%3A%2202%3A00%3A00%3A00%3A00%3A00%22%2C%22channelInfo%22%3A%22appstore%22%2C%22IPAddress1%22%3A%22%22%2C%22OpenUDID%22%3A%22%22%2C%22clientVersion%22%3A%225.3.30%22%2C%22terminalType%22%3A%2202%22%2C%22osVersion%22%3A%22%22%2C%22appId%22%3A%22com.jd.jinrong%22%2C%22deviceType%22%3A%22iPhone8%2C2%22%2C%22networkType%22%3A%22%22%2C%22startNo%22%3A212%2C%22UUID%22%3A%22%22%2C%22IPAddress%22%3A%22%22%2C%22deviceId%22%3A%22%22%2C%22IDFA%22%3A%22%22%2C%22resolution%22%3A%22%22%2C%22osPlatform%22%3A%22iOS%22%7D%2C%22bussource%22%3A%22%22%7D',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['resultData']['canGetGb'] == true)
            if (isset($json['resultData']['data']['volumn']))
                return ['result' => R_OK, 'msg' => "获得{$json['resultData']['data']['volumn']}京豆"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已经发完|已签到|已领取|\"code\":\"2000\")/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束|未找到)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        if ($json['resultCode'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }


    public function 京东金融游戏(): array
    {
        $data = [
            'url' => 'https://ylc.m.jd.com/sign/signGiftDays',
            'type' => 'POST',
            'data' => 'channelId=1',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json || !isset($json['code']))
            return ['result' => R_ERROR, 'msg' => '登录解析返回结果失败'];
        if ($json['code'] == 202)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] != 200)
            return ['result' => R_ERROR, 'msg' => '登录未知'];
        $data = [
            'url' => 'https://ylc.m.jd.com/sign/signDone',
            'type' => 'POST',
            'data' => 'channelId=1',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json || !isset($json['code']))
            return ['result' => R_ERROR, 'msg' => '签到解析返回结果失败'];
        switch ($json['code']) {
            case 200:
                if (isset($json['data']['rewardAmount']))
                    return ['result' => R_OK, 'msg' => "获得{$json['data']['rewardAmount']}京豆"];
                else
                    return ['result' => R_OK, 'msg' => "无京豆"];
                break;
            case 301:
                return ['result' => R_OK, 'msg' => '已签过'];
                break;
            case 303:
                return ['result' => R_OK, 'msg' => '已签过'];
                break;
            case 202:
                return ['result' => R_OK, 'msg' => 'Cookie失效'];
                break;
            default:
                if (preg_match('/(不存在|已结束|未找到)/', $html))
                    return ['result' => R_ERROR, 'msg' => '活动已结束'];
        }
        return ['result' => R_ERROR, 'msg' => '签到未知'];
    }

    public function 京东智能生活(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=userSign',
            'type' => 'POST',
            'data' => 'body=%7B%22riskParam%22%3A%7B%22eid%22%3A%22O5X6JYMZTXIEX4VBCBWEM5PTIZV6HXH7M3AI75EABM5GBZYVQKRGQJ5A2PPO5PSELSRMI72SYF4KTCB4NIU6AZQ3O6C3J7ZVEP3RVDFEBKVN2RER2GTQ%22%2C%22shshshfpb%22%3A%22v1%5C%2FzMYRjEWKgYe%2BUiNwEvaVlrHBQGVwqLx4CsS9PH1s0s0Vs9AWk%2B7vr9KSHh3BQd5NTukznDTZnd75xHzonHnw%3D%3D%22%2C%22pageClickKey%22%3A%22Babel_Sign%22%2C%22childActivityUrl%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2FKcfFqWvhb5hHtaQkS4SD1UU6RcQ%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.luck4ever.net%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000042554_%26utm_term%3D8d1fbab27551485f8f9b1939aee1ffd0%22%7D%2C%22url%22%3A%22https%3A%5C%2F%5C%2Fpro.m.jd.com%5C%2Fmall%5C%2Factive%5C%2FKcfFqWvhb5hHtaQkS4SD1UU6RcQ%5C%2Findex.html%3Fcu%3Dtrue%26utm_source%3Dwww.luck4ever.net%26utm_medium%3Dtuiguang%26utm_campaign%3Dt_1000042554_%26utm_term%3D8d1fbab27551485f8f9b1939aee1ffd0%22%2C%22params%22%3A%22%7B%5C%22enActK%5C%22%3A%5C%22isDhQnCJUnjlNPoFf5Do0JM9l54aZ0%5C%2FeHe0aBgdJgcQaZs%5C%2Fn4coLNw%3D%3D%5C%22%2C%5C%22isFloatLayer%5C%22%3Atrue%2C%5C%22ruleSrv%5C%22%3A%5C%2200007152_29653514_t0%5C%22%2C%5C%22signId%5C%22%3A%5C%22ZYsm01V6Gr4aZs%5C%2Fn4coLNw%3D%3D%5C%22%7D%22%2C%22geo%22%3A%7B%22lng%22%3A%220.000000%22%2C%22lat%22%3A%220.000000%22%7D%7D&client=apple&clientVersion=8.5.0&d_brand=apple&openudid=1fce88cd05c42fe2b054e846f11bdf33f016d676&sign=c7ecee5b465f5edd7ed2e2189fad2335&st=1581317924210&sv=120',
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '解析返回结果失败'];
        if ($json['code'] == 3)
            return ['result' => R_NO, 'msg' => 'Cookie失效'];
        if ($json['code'] == 600)
            return ['result' => R_NO, 'msg' => '认证失败'];
        if ($json['signText'] == '签到成功')
            if (isset($json['awardList']['0']['text']))
                return ['result' => R_OK, 'msg' => "获得{$json['awardList']['0']['text']}"];
            else
                return ['result' => R_OK, 'msg' => "无京豆"];
        if (preg_match('/(已签到|已领取)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(不存在|已结束)/', $html))
            return ['result' => R_ERROR, 'msg' => '活动已结束'];
        return ['result' => R_ERROR, 'msg' => '未知'];
    }

    public function 京东商城大奖(): array
    {
        $data = [
            'url' => 'https://api.m.jd.com/client.action?functionId=vvipscdp_raffleAct_index&client=apple&clientVersion=8.1.0&appid=member_benefit_m',
            'type' => 'GET',
            'header' => ['Referer' => 'https://jdmall.m.jd.com/beansForPrizes'],
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '登录解析返回结果失败'];
        if (preg_match('/(未登录|\"101\")/', $html))
            return ['result' => R_NO, 'msg' => '登录Cookie失效'];
        if (!isset($json['data']['floorInfoList']['0']['detail']['raffleActKey']))
            return ['result' => R_ERROR, 'msg' => '未获取到raffleActKey'];
        $data = [
            'url' => "https://api.m.jd.com/client.action?functionId=vvipscdp_raffleAct_lotteryDraw&body=%7B%22raffleActKey%22%3A%22{$json['data']['floorInfoList']['0']['detail']['raffleActKey']}%22%2C%22drawType%22%3A0%2C%22riskInformation%22%3A%7B%7D%7D&client=apple&clientVersion=8.1.0&appid=member_benefit_m",
            'type' => 'GET',
            'header' => ['Referer' => 'https://jdmall.m.jd.com/beansForPrizes'],
            'cookie' => $this->cookie,
        ];
        $html = PostMan::send($data);
        $json = json_decode($html, true);
        if (!$json)
            return ['result' => R_ERROR, 'msg' => '签到解析返回结果失败'];
        if (isset($json['success']) && $json['success'])
            if (isset($json['data']['beanNumber']))
                return ['result' => R_OK, 'msg' => "获得{$json['data']['beanNumber']}京豆"];
            elseif (isset($json['data']['couponInfoVo']))
                return ['result' => R_OK, 'msg' => !isset($json['data']['couponInfoVo']['limitStr']) ? '获得优惠券' : "获得满{$json['data']['couponInfoVo']['quota']}减{$json['data']['couponInfoVo']['discount']}优惠券→ {$json['data']['couponInfoVo']['limitStr']}"];
            elseif (isset($json['data']['pitType']) && $json['data']['pitType'] == 0)
                return ['result' => R_OK, 'msg' => '未中奖'];
            else
                return ['result' => R_ERROR, 'msg' => '签到未知1'];
        if (preg_match('/(已用光|7000003)/', $html))
            return ['result' => R_OK, 'msg' => '已签过'];
        if (preg_match('/(未登录|\"101\")/', $html))
            return ['result' => R_NO, 'msg' => '签到Cookie失效'];
        return ['result' => R_ERROR, 'msg' => '签到未知2'];
    }
}