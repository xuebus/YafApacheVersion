<?php

class Base_Http {

    const HTTP_TIMEOUT = 3; // curl超时设置，单位是秒。基类方法可自定义重试次数，故而如果接口超时，最大重试次数倍此设置时间。
    const HTTP_MAXREDIRECT = 2; // 301、302、303、307最大跳转次数。
    const HTTP_REDO = 0; // 访问失败后的重试次数, 默认0次为不重试。
    const HTTP_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 Dagger/1.1';// 默认UA头
    const HTTP_MC_SERVER_KEY = 'chope';
    const HTTP_FLASE_LOCK_TIMES = 0;
    
    private static $httpUseragent = self::HTTP_USERAGENT;
    private static $httpLockTimes = self::HTTP_FLASE_LOCK_TIMES;

    private static $last_header_info;

    private function __construct() {}
    private function __clone() {}
    private function __destruct() {}

    /**
     * 设置请求失败的锁的次数阈值
     * @param int $times
     * @return void
     */
    public static function setLockTimes($times = self::HTTP_FLASE_LOCK_TIMES) {
        self::$httpLockTimes = $times;
    }

    /**
     * 设置User-Agent头信息
     * @param $userAgent string 发送请求url的User-Agent头,default = ''
     * @return void
     */
    public static function setUserAgent($userAgent = self::HTTP_USERAGENT) {
        self::$httpAseragent = $userAgent;
    }

    /**
     * 获取最后一次请求的header头信息
     * @param void
     * @return mix 
     */
    public static function getLastHeader() {
        return self::$lastHeaderInfo;
    }

    /**
     * 发送post请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @return mix 失败返回false，成功返回array(抓取结果已解析成数组)
     */
    public static function post($req, $post, array $header = array(), $userpass = '', $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req']            = $req;
        $args['post']           = $post;
        $args['header']         = $header;
        $args['timeout']        = $timeout;
        $args['cookie']         = $cookie;
        $args['redo']           = $redo;
        $args['maxredirect']    = $maxredirect;
        $args['userpass']       = $userpass;
        return self::_httpExec($args);
    }

    /**
     * 发送get请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回抓取结果
     */
    public static function get($req, array $header = array(), $userpass = '', $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req']            = $req;
        $args['header']         = $header;
        $args['timeout']        = $timeout;
        $args['cookie']         = $cookie;
        $args['redo']           = $redo;
        $args['maxredirect']    = $maxredirect;
        $args['userpass']       = $userpass;
        return self::_httpExec($args);
    }

    /**
     * 发送请求获取header头信息，推荐使用
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回array(抓取结果已解析成数组)
     */
    public static function head($req, $post = array(), array $header = array(), $userpass = '', $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req']            = $req;
        $args['post']           = $post;
        $args['header']         = $header;
        $args['timeout']        = $timeout;
        $args['cookie']         = $cookie;
        $args['redo']           = $redo;
        $args['maxredirect']    = $maxredirect;
        $args['headOnly']       = true;
        $args['userpass']       = $userpass;
        return self::_httpExec($args);
    }

    /**
     * 发送请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回抓取结果
     */
    private static function _httpExec($args) {

        if (!extension_loaded('curl')) {
            return self::_error(90400, '服务器没有安装curl扩展！');
        }

        $args['req'] = isset($args['req']) ? $args['req'] : array(); // 必传
        $args['post'] = isset($args['post']) ? $args['post'] : array();
        $args['header'] = isset($args['header']) ? $args['header'] : array();
        $args['timeout'] = isset($args['timeout']) && is_numeric($args['timeout']) && $args['timeout'] > 0 ? intval($args['timeout']) : self::HTTP_TIMEOUT;
        $args['cookie'] = isset($args['cookie']) ? $args['cookie'] : '';
        $args['redo'] = isset($args['redo']) ? $args['redo'] : self::HTTP_REDO;
        $args['maxredirect'] = isset($args['maxredirect']) ? intval($args['maxredirect']) : null;
        $args['headOnly'] = isset($args['headOnly']) ? $args['headOnly'] : false;
        $args['userpass'] = isset($args['userpass']) ? $args['userpass'] : null;

        $url = self::_makeUri($args['req']);
        if (empty($url)) {
            return self::_error(90401, '页面抓取请求url缺失');
        }

        // MC 频率控制，待加 to do  

        $args['header'][] = 'Expect:'; // 解决100问题
        $ch = curl_init();
        self::_set_curl_opts($ch, $args);
        $rs = curl_setopt($ch, CURLOPT_URL, $url);

        $startRunTime = microtime(true);
        $header = $ret = false;
        do {
            $ret = self::_getContent($ch, $args['maxredirect']);
            if(strpos($ret, "\r\n\r\n") !== false) {
                list($header, $ret) = explode("\r\n\r\n", $ret, 2);
                break;
            }
            Base_Log::debug("request_redo", 0, array($url));
        } while ($args['redo']-- > 0);
        curl_close($ch);
        $runTime = Base_Common::addStatInfo('request', $start_run_time, 0);

        self::$lastHeaderInfo = $header;
        // 抓取header时，解析header头
        if ($args['headOnly'] && $header !== false) {
            $ret = self::_parseHeader($header);
        }

        return $ret;
    }

    private static function _makeUri($req) {
        $url = '';
        if (is_array($req)) {
            switch (count($req)) {
                case 1:
                    $url = $req[0];
                    break;
                case 2:
                    list($url, $params) = $req;
                    $paramStr = http_build_query($params);
                    $url .= strpos($url, '?') !== false ? "&{$paramStr}" : "?{$paramStr}";
                    break;
                default:
                    return self::_error(90402, 'url参数错误');
            }
        } else if(is_string($req)) {
            $url = $req;
        } else {
            return self::_error(90402, 'url参数错误');
        }
        return $url;
    }

    private static function _error($errno, $error) {
        Base_Log::warning('Http error', $errno, array($error));
        return false;
    }

    private static function _set_curl_opts(&$ch, $args, $first = true) {
        // 本函数不设置url
        $opt = array();
        if($first) {
            $opt[CURLOPT_RETURNTRANSFER] = true;
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
            $opt[CURLOPT_SSL_VERIFYHOST] = false;
            $opt[CURLOPT_MAXCONNECTS] = 100;
            $opt[CURLOPT_HEADER] = true;
            $opt[CURLOPT_TIMEOUT] = $args['timeout'];
            // useragent头
            if(!empty(self::$httpUseragent)) {
                $opt[CURLOPT_USERAGENT] = self::$httpUseragent;
            }
            // 只抓header头
            if ($args['headOnly']) {
                $opt[CURLOPT_NOBODY] = true;
            }
        }
        if($first || !empty($args['header'])) { 
            // header头
            $setheader = array();
            if (!empty($args['header']) && is_array($args['header'])) {
                foreach($args['header'] as $k => $v) {
                    if(is_numeric($k)) {
                        if($pos = strpos($v, ':')) {
                            $setheader[strtolower(substr($v, 0, $pos))] = $v;
                        }
                    } else {
                        $setheader[strtolower($k)] = "$k: $v";
                    }
                }
            }
            $setheader['expect'] = 'Expect:'; // 解决100问题
            $opt[CURLOPT_HTTPHEADER] = $setheader;
        }
        // post数据
        if (!empty($args['post'])) {
            //多维数组用http_build_query强制转换，curl不支持多维，转换后，无法支持文件提交
            Base_Log::debug('request_post_data', 0, $args['post']);
            if (is_array($args['post']) && count($args['post']) !== count($args['post'], COUNT_RECURSIVE)) {
                $args['post'] = http_build_query($args['post']);
            }
            $opt[CURLOPT_POST] = true;
            $opt[CURLOPT_POSTFIELDS] = $args['post'];
        }
        
        if (!empty($args['userpass'])) {
            $opt[CURLOPT_USERPWD] = $args['userpass'];
        }
        
        // cookie设置
        if (!empty($args['cookie'])) {
            $opt[CURLOPT_COOKIE] = $args['cookie'];
        }
        return curl_setopt_array($ch, $opt);
    }

    private static function _getContent($ch, $maxredirect) {
        $redirect = 0;
        do {
            $retry = false;
            $ret = curl_exec($ch);
            Base_Common::addStatInfo('request');
            if(!self::_curlCheck($ch)) {
                return false;
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            if(in_array($code, array(301, 302, 303, 307), true)) {
                if(++$redirect <= $maxredirect) {
                    Base_Log::debug('request_redirect_times', 0, array($redirect));
                    preg_match('/Location:(.*?)\n/i', $ret, $matches);
                    $newurl = trim($matches[1]);
                    if($newurl{0} === '/') {
                        preg_match("@^([^/]+://[^/]+)/@", $url, $matches);
                        $newurl = $matches[1] . $newurl;
                    }
                    curl_setopt($ch, CURLOPT_URL, $newurl);
                    Base_Log::debug('request_redirect_url', 0, array($newurl));
                    $retry = true;
                } else {
                    $msg = "redirect larger than {$maxredirect} [{$url}]";
                    Base_Log::debug('request_redirect_warn', 0, array($msg));
                    self::_error(90406, $msg);
                }
            } else if($code !== 200) {
                $msg = "http code unnormal : [{$code}] [{$url}] [{$ret}]";
                self::_error(90405, $msg);
                if(in_array($code, array(403,404), true)) {
                    return false;
                }
            }
        } while($retry);
        return $ret;
    }

    private static function _curlCheck($ch) {
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            self::_error(90404, "curl内部错误信息[{$curlErrno}][{$curlError}][{$url}]");
            return false;
        }
        return true;
    }

}
