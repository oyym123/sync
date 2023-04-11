<?php

namespace AsyncCenter\Service;

use Exception;
use InvalidArgumentException;

class IdGenerator
{
    private $parentId;
    private $branchId;

    public function __construct($parentId = '')
    {
        $this->parentId = $parentId;
        $this->branchId = 0;
    }

    public function newBranchId()
    {
        if ($this->branchId >= 99) {
            throw new InvalidArgumentException('branch id is larger than 99');
        }
        if (strlen($this->parentId) > 20) {
            throw new InvalidArgumentException('total branch id is longer than 20');
        }
        $this->branchId = $this->branchId + 1;
        return $this->parentId . str_pad($this->branchId, 2, '0');
    }

    /**
     * @param string $dtmUrl
     * @return string
     * @throws Exception
     */
    public function genGid()
    {
//        list($httpCode, $output) = $this->curlPost($dtmUrl . '?action=newGid');
//        $this->checkStatus($httpCode);
//        $data = json_decode($output, true);
        return md5(time() . uniqid());
    }

    /**
     * @param int $status
     * @param string $errorMsg
     * @throws Exception
     */
    public function checkStatus($status, $errorMsg = '')
    {
        if ($status !== 200) {
            throw new Exception($errorMsg);
        }
    }

    /**
     * @param string $str
     * @param string $errorMsg error message default ''
     * @throws Exception
     */
    public function checkFailure($str, $errorMsg = '')
    {
        $res = json_decode($str, true);
        if (!isset($res['tcc_code']) || $res['tcc_code'] < 0) {
            throw new Exception($errorMsg);
        } else {
            return $res;
        }
    }

    /**
     * @param string $dtmUrl
     * @param string $gid
     * @param string $branchId
     * @return TccService
     */
    public function tccFromReq($dtmUrl, $gid, $branchId)
    {
        if (!$dtmUrl || !$gid || !$branchId) {
            throw new InvalidArgumentException("bad req info for tcc dtm: {$dtmUrl} gid: {$gid} branchId: {$branchId}");
        }
        $tcc = new TccService($dtmUrl, $gid);
        $tcc->idGen = new IdGenerator($branchId);
        return $tcc;
    }

    public function curlPost($url, $post_data = [], $head = [])
    {
        $ch = curl_init();//初始化cURL
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串并输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//Post请求方式
        if (!empty($head)) curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post_data) ? http_build_query($post_data, '', '&') : $post_data);//Post变量
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $output = curl_exec($ch);//执行并获得HTML内容
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errInfo = curl_error($ch);
        curl_close($ch);//释放cURL句柄
        return [$httpCode, $output, $errInfo];
    }
}