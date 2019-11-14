<?php
namespace org\util;
/**
* PHP调用百科开放数据平台接口的例子
*/
class BaikeApi {
    /**
    * AK，用于标识使用者身份 */
    private $_strAk = 'abcdef0123456789abcdef0123456789';
    /**
    * SK，用于签名验证，不要泄露 */
    private $_strSk = '9876543210fedcba9876543210fedcba';
    /**
    * 签名过期时间 */
    private $_intExpiration = 600;
    /**
    * 构造函数 *
    * @param string $strAk AK
    * @param string $strSk SK
    * @param int $intExpiration 生成签名串的有效期(秒)，默认600
    * @return void */
    public function __construct($strAk, $strSk, $intExpiration = 600) {
        $this->_strAk = $strAk;
        $this->_strSk = $strSk;
        $this->_intExpiration = $intExpiration;
    }
    /**
    * 调用百科接口 *
    * @param string $strInterface  接口名，例如v1/sample/hello
    * @param array $arrParams 传入参数，Key-Value数组
    * @return array 接口返回结果，包括errno、errmsg及数据;调用失败返回false*/
    public function callBaikeApi($strInterface, $arrParams) {
        // 对参数进行签名
        $strAuthSign = $this->_getAuthSign($arrParams);
        // 请求API
        $strUrl = "https://baikeapi.baidu.com/opendata/{$strInterface}"; // 外网地址
        //$strUrl = "http://inner.baikeapi.baidu.com/opendata/{$strInterface}"; // 内网地址，北京 //$strUrl = "http://inner-nj.baikeapi.baidu.com/opendata/{$strInterface}"; // 内网地址，南京 if (!empty($arrParams)) {
        $strUrl .= "?" . http_build_query($arrParams);
        $arrHeaders = array("Authorization: {$strAuthSign}");
        $arrResult = $this->_getByCurl($strUrl, $arrHeaders);
        return $arrResult;
    }
    /**
    * 根据参数、AK、SK、时间戳、过期时间获取认证串 *
    * @param array $arrParams 要签名的参数，Key-Value格式，均为字符串
    * @param string $strAk  AK
    * @param string $strSk  加密用Key，即SK
    * @param int $intTimestamp Unix时间戳(秒)
    * @param int $intExpiration 过期时间(秒)
    * @return string 签名后的sign;失败返回false
    * */
    private function _getAuthSign($arrParams) { // 参数校验
        if (!is_array($arrParams)) {
            return false;
        }
        // 生成签名串前缀
        $intTimestamp = time();
        $strAuthPrefix = "baikeapi-auth/{$this->_strAk}/{$intTimestamp}/{$this->_intExpiration}"; // 计算本次签名用Key(根据签名串和SK得到)
        $strSignKey = hash_hmac('sha256', $strAuthPrefix, $this->_strSk);
        // 生成数据串
        ksort($arrParams);
        $arrQuery = array();
        foreach ($arrParams as $strKey => $strValue) {
            if (!is_null($strValue)) {
                $arrQuery[] = $strKey . '=' . rawurlencode($strValue);
            }
        }
        $strQuery = implode('&', $arrQuery);
        // 计算最终签名
        $strSignature = hash_hmac('sha256', $strQuery, $strSignKey); // 拼认证串
        $strAuthSign = $strAuthPrefix . '/' . $strSignature;
        return $strAuthSign;
    }
    /**
    * 通过curl获取URL数据 *
    * @param string $strUrl
    * @param array $arrHeaders
    * @return array */
    // URL地址 请求的HTTP Header
    // 返回的接口数据，失败返回false
    private function _getByCurl($strUrl, $arrHeaders) {
        $arrOptions = array(
            CURLOPT_URL => $strUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'baikeapi-php/1.0',
            CURLOPT_HTTPHEADER => $arrHeaders,
            CURLOPT_TIMEOUT_MS => 1500,
            CURLOPT_CONNECTTIMEOUT_MS => 1000, // 根据实际情况设置
        );
        $resCurl = curl_init();
        curl_setopt_array($resCurl, $arrOptions);
        $strResult = curl_exec($resCurl);
        $arrCurlInfo = curl_getinfo($resCurl);
        curl_close($resCurl);
        $intHttpCode = intval($arrCurlInfo['http_code']);
        if (($strResult === false) || ($intHttpCode != 200)) {
            return false;
        } else {
            $arrResult = json_decode($strResult, true);
            return $arrResult;
        }
    }
}
