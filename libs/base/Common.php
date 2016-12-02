<?php

class Base_Common {

   private static $statInfo = array();

    /** 
     * 获取统计信息
     * @param void
     * @return array
     */
    public static function getStatInfo() {
        return self::$statInfo;
    }   

    /** 
     * 累计统计信息
     * @param string $type 统计类型mc|db|request
     * @param int $startTime 开始时间，默认0为不统计时长
     * @param int $offset 增加的大小，默认为1
     * @return mix
     */
    public static function addStatInfo($type, $startTime = 0, $offset = 1) {
        if (!isset(self::$statInfo[$type]['count'])) {
            self::$statInfo[$type]['count'] = 0;
        }
        
        if (!isset(self::$statInfo[$type]['time'])) {
            self::$statInfo[$type]['time'] = 0;
        }

        self::$statInfo[$type]['count'] += $offset;
        if($startTime > 0) {
            $runTime = sprintf("%0.2f", (microtime(true) - $startTime) * 1000);
            self::$statInfo[$type]['time'] += $runTime;
            return $runTime . " ms";
        }   
        return true;
    } 
   
    /** 
     * 按ID NUM获取表hash_id号
     *
     * @param bigint|string $hashId(64位整型)
     * @param int $s hash因子
     * @return int $retHash
     */
    public static function getHashId($hashId = 0, $s = 10) {

        $crcH = sprintf('%u', crc32($hashId));
        return intval(fmod($crcH, $s)) + 1;
    } 

    /**
     * 转码函数
     * @param Mixed $data 需要转码的数组
     * @param String $dstEncoding 输出编码
     * @param String $srcEncoding 传入编码
     * @param bool $toArray 是否将stdObject转为数组输出
     * @return Mixed
     */
    public static function convertEncoding($data, $dstEncoding, $srcEncoding, $toArray=false) {
        if ($toArray && is_object($data)) {
            $data = (array)$data;
        }
        if (!is_array($data) && !is_object($data)) {
            $data = mb_convert_encoding($data, $dstEncoding, $srcEncoding);
        } else {
            if (is_array($data)) {
                foreach($data as $key=>$value) {
                    if (is_numeric($value)) {
                        continue;
                    }
                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data[$key]);
                    $data[$keyDstEncoding] = $valueDstEncoding;
                }
            } else if(is_object($data)) {
                $dataVars = get_object_vars($data);
                foreach($dataVars as $key=>$value) {
                    if (is_numeric($value)) {
                        continue;
                    }
                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data->$key);
                    $data->$keyDstEncoding = $valueDstEncoding;
                }
            }
        }
        return $data;
    }

    /**
     * 递归创建目录
     * @param string $pathname 需要创建的目录路径
     * @param int $mode 创建的目录属性，默认为755
     * @return void
     */
    public static function recursiveMkdir($pathname, $mode = 0755) {
        return is_dir($pathname) ? true : mkdir($pathname, $mode, true);
    }


    /**
     * 返回程序开始到调用函数处的执行时间
     * @return string 运行此函数调用的时间
     */
    public static function getRunTime() {
        if(strpos(STARTTIME, ' ')) {
            $time = explode(' ', STARTTIME);
            $startTime = (double)$time[1] + (double)$time[0];
            return sprintf("%0.3f", microtime(true) - $startTime) . " s";
        }
        return sprintf("%0.3f", microtime(true) - STARTTIME) . " s";
    }


    /** 
     * 根据 参数和token计算签名
     * @param type $params
     * @param type $token
     * @return type
     */
    public static function getSign($params, $token) {

        $sign = ''; 
        unset($params['sign']);
        ksort($params);
        foreach ($params as $k => $v) {
            $sign .= $k . '=' . urlencode($v);
        }   
        return md5($sign . $token);
    }  

    /**
     * 对称加密算法
     * @param string $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return string
     */
    public static function authCode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
        $ckey_length = 4;
        $key = md5($key ? $key : 'c1856bb2');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    /**
     *   * 获取客户端IP地址
     *   * @return String
     */
    public static function getClientIp() {
        $onlineip = '';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
        $onlineip = empty($onlineipmatches[0]) ? '0.0.0.0' : $onlineipmatches[0];
        return $onlineip;
    }

    /**
     *   * 获取客户端真实IP地址
     *   * @return String
     */
    public static function getRealClientIp() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 数字与字符串的映射, 字符串不能以字符'0'开始，否则不能互转
     * @param int or string $input
     * @param boolean $toNum default false
     * @return string/int
     */
    public static function alphaId($input, $toNum = false) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        //转数字
        if ($toNum) {
            $integer = 0;
            $input = strrev( $input  );  
            $baselen = strlen( $chars );
            $inputlen = strlen( $input );
            for ($i = 0; $i < $inputlen; $i++) {
                $index = strpos( $chars, $input[$i] );
                $integer = bcadd( $integer, bcmul( $index, bcpow( $baselen, $i ) ) );
            }   
            return $integer;
        }
        //转字符串
        $string = ''; 
        $len = strlen( $chars );
        while( $input >= $len ) { 
            $mod = bcmod( $input, $len );
            $input = bcdiv( $input, $len );
            $string = $chars[ $mod ] . $string;
        }   
        $string = $chars[ intval( $input ) ] . $string;
        return $string;
    } 
}
