<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 12:14
 */

namespace core;
class PostMan
{
    public static function send(array &$data): string
    {
        /*
         * 参数
         * 网址 url
         * 类型 type [POST/GET]
         * 数据 data
         * 令牌 cookie
         * 协头 header //数组
         * */
        if (!isset($data['url']))
            return '';
        $header = self::header($data['header'] ?? []);
        $ch = curl_init();
        if ($data['type'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
        }
        curl_setopt($ch, CURLOPT_URL, $data['url']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_COOKIE, $data['cookie'] ?? '');
        $output = curl_exec($ch);
        curl_close($ch);
        list($header, $body) = explode("\r\n\r\n", $output);
        if (preg_match("/set\-cookie:([^\r\n]*)/i", $header, $matches))
            $data['cookie'] = $matches[1];
        $data['header'] = [];
        foreach (explode("\r\n", $header) as $val) {
            $reHeader = explode(': ', $val, 2);
            if (count($reHeader) == 2)
                $data['header'][$reHeader['0']] = $reHeader['1'];
        }
        var_dump($body);
        return $body ?? '';
    }

    private static function header(array $header): array
    {
        $ip = self::ranip();
        $header['X-FORWARDED-FOR'] = $ip;
        $header['CLIENT-IP'] = $ip;
        $header['REMOTE_ADDR'] = $ip;

        if (!isset($header['Content-Type']))
            $header['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
        if (!isset($header['User-Agent']))
            $header['User-Agent'] = 'Mozilla/5.0 (Linux; Android 10; MI 8 UD Build/QKQ1.190828.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/73.0.3683.121 Mobile Safari/537.36';

        foreach ($header as $key => $val) {
            $re[] = "{$key}: {$val}";
        }
        return $re;
    }

    private static function ranip()
    {
        $ip_long = array(
            array('607649792', '608174079'), //36.56.0.0-36.63.255.255
            array('975044608', '977272831'), //58.30.0.0-58.63.255.255
            array('999751680', '999784447'), //59.151.0.0-59.151.127.255
            array('1019346944', '1019478015'), //60.194.0.0-60.195.255.255
            array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
            array('1947009024', '1947074559'), //116.13.0.0-116.13.255.255
            array('1987051520', '1988034559'), //118.112.0.0-118.126.255.255
            array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 14);
        $huoduan_ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
        return $huoduan_ip;
    }
}