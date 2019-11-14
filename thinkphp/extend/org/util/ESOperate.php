<?php
// not support cluster
/**
 * es 2.3.2
 * this class supply a lots of functions which to operate es storage.
 */
namespace org\util;

class ESOperate {

    private $host;
    private $index;
    private $type;

    public function __construct($host, $index, $type){
        $this->host = $host;
        $this->index = $index;
        $this->type = $type;
    }

    private function curls($query, $postData = "", $method = "PUT") {
        $headers = array('Content-type: application/json');
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_PORT, '');
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ci, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ci, CURLOPT_TIMEOUT, 200);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method);
        if ($postData) {
            curl_setopt($ci, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ci, CURLOPT_URL, $query);
        $response = curl_exec($ci);
        if (curl_errno($ci)) {
            return false;
        } else {
            curl_close($ci);
            return $response;
        }
    }

    //add
    public function addData($data){
        $url = "{$this->host}/{$this->index}/{$this->type}?refresh=true";
        $rs = $this->curls($url, $data, 'POST');
        $obj = json_decode($rs);
        if (isset($obj->created) && $obj->created == true) {
            return [true, $obj->_id];
        } else {
            return [false, 'es服务器连接出现问题'];
        }
    }

    //add by id.
    public function addDataById($data, $id){
        $url = "{$this->host}/{$this->index}/{$this->type}/{$id}?refresh=true";
        $rs = $this->curls($url, $data ,'POST');
        $obj = json_decode($rs);
        if (isset($obj->created) && $obj->created == true) {
            return [true, $id];
        } else {
            return [false, $obj->error->reason];
        }
    }

    //update
    public function updateDataById($id, $data){
        $url = "{$this->host}/{$this->index}/{$this->type}/{$id}?refresh=true";
        $rs = $this->curls($url, $data, 'PUT');
        $obj = json_decode($rs);
        if (isset($obj->_shards) && isset($obj->_shards->total) && isset($obj->_shards->successful) && $obj->_shards->successful > 0) {
            return [true];
        } else {
            return [false, 'es服务器连接出现问题'];
        }
    }

    //del
    public function deleteDataById($id) {
        $url = "{$this->host}/{$this->index}/{$this->type}/{$id}?refresh=true";

        $rs = json_decode($this->curls($url, '', "DELETE"));
        if (isset($rs->found) && $rs->found == true) {
            return [true];
        } else {
            return [false, 'es服务器连接出现问题'];
        }
    }

    //del by query
    public function delByQuery($type, $query)
    {
        if ($type && $query) {
            $url = self::$index . self::$join . $type . '/_query';
            $json_data = json_encode ( $query );
            $rs = json_decode(self::curls($url, $json_data, "DELETE"));
            Util::log('delByQuery : ' . json_encode($rs));
            //log
            if (isset($rs->error->reason)) {
                Util::log($rs->error->reason);
            }

            if (isset($rs->deleted) && $rs->deleted > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getByQuery($query){
        $url = "{$this->host}/{$this->index}/{$this->type}/_search";
        $rs = $this->curls($url, $query, "GET");
        $obj = json_decode($rs);
        if (isset($obj->hits->total)) {
            return [
                'status'=>1,
                'total'=>$obj->hits->total,
                'hits'=>$obj->hits->hits
            ];
        } else {
            return [
                'status'=>0,
                'msg'=>'连接出现问题或查询结果超出服务器缓存'
            ];
        }
    }

    public function getDataById($id) {
        $url = "{$this->host}/{$this->index}/{$this->type}/{$id}?refresh=true";
        $rs = $this->curls($url, '', "GET");
        $obj = json_decode($rs);

        if (isset($obj->found) && $obj->found == true) {
            return [true, $obj->_source];
        } else {
            return [false, '没有搜索结果'];
        }
    }

    public function getAll($ip, $esIndex, $type){
        $url = $ip . '/' . $esIndex . '/' . $type . '/' . '_search';
        if ($ip && $esIndex && $type) {
            $rs = self::curls($url, '', "GET");
            $obj = json_decode($rs);
            if (isset($obj->hits->total) && $obj->hits->total > 0) {
                // return $obj->hits->hits;
                return ['total'=>$obj->hits->total, 'hits'=>$obj->hits->hits];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getId($type, $query)
    {
        // return id or false;
        $url = self::$index . self::$join . $type . '/' . '_search';
        if ($query && $type) {
            $json_data = json_encode($query);
            $rs = self::curls($url, $json_data, "GET");
            $obj = json_decode($rs);
            Util::log('getId : ' . json_encode($obj));
            //log
            if (isset($obj->error->reason)) {
                Util::log('getId' . $obj->error->reason);
            }

            if (isset($obj->hits->total) && $obj->hits->total == 1) {
                return $obj->hits->hits[0]->_id;
            } else {
                return false;
            }
        } else {
            return false;
        }
        $obj = json_decode($rs);
        if (isset($obj->hits->total) && $obj->hits->total == 1) {
            return $obj->hits->hits[0]->_id;
        } else {
            return false;
        }
    }

    // init functions.
    public function deleteIndex($type)
    {
        if ($type) {
            $url = self::$index . self::$join . $type . '?refresh=true';
            $rs = json_decode(self::curls($url, '', 'DELETE'));
            Util::log('deleteIndex : ' .json_encode($rs));
            if (isset($rs->acknowledged) && $rs->acknowledged == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function createIndex($type, $query)
    {
        if ($type && $query) {
            $url = self::$index . self::$join . $type . '?refresh=true';
            $json_data = json_encode ( $query );
            $rs = json_decode(self::curls($url, $json_data, 'PUT'));
            Util::log('createIndex : ' . json_encode($rs));
            if (isset($rs->acknowledged) && $rs->acknowledged == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
