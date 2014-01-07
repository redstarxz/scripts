<?php
class CurlMulti
{
    private static $ins = null;
    private static $res = null;
    public static function getInstance()
    {
        if (!self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    private function __construct()
    {
        $this->mh = curl_multi_init();
    }

    public function addHandler($ch)
    {
        $this->chs[] = $ch;
        curl_multi_add_handle($this->mh, $ch);  
    }
    
    public function beginMulti()
    {
        Register::set('curl_multi', true);
    }

    public function endMulti()
    {
        Register::del('curl_multi');
        Register::del('curl_multi_complete');
        $this->clear();
    }

    public function execMulti()
    {
        $active = false;
        do {
            $mrc = curl_multi_exec($this->mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($this->mh) != -1) {
                do {
                    $mrc = curl_multi_exec($this->mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        if ($mrc != CURLM_OK) {           
            //todo 
            error_log("CURL Data Error");       
        }  
        $result = array();
        foreach ($this->chs as $ch) {           
            $result[] = $this->dealResult($ch);
            curl_multi_remove_handle($this->mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($this->mh);
        Register::set('curl_multi_complete', true);
        self::$res = $result;
        return $result;
    }

    private function dealResult($ch)
    {
        if (($err = curl_error($ch)) == '') {
            $result = curl_multi_getcontent($ch);
        } else {               
            $result = $err;
        }
        return $result;
    }

    public function getHandler($key = '')
    {
        if ($key) {
            return isset($this->chs[$key]) ? $this->chs[$key] : false;
        } else {
            return $this->chs;
        }
    }

    public function clear()
    {
        self::$ins = null;
        self::$res = null;
    }

    public function getResult()
    {
        if (count(self::$res)) {
            return array_shift(self::$res);
        }
        return array();
    }

}
