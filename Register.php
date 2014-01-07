<?php
class Register
{
    private static $store = array();
    private static $ins = null;

    private function __construct()
    {

    }
    public static function getInstance()
    {
        if (!self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function append($key, $val) 
    {
        if (!isset(self::$store[$key])) {
            $this->set($key, array($val));
        } else {
            $arr = array_merge(self::$store[$key] , array($val));
            $this->set($key, $arr);
        }
    }

    public function set($key, $val)
    {
        self::$store[$key] = $val;
    }

    public function get($key)
    {
        if (!isset(self::$store[$key])) {
            return '';
        }
        return self::$store[$key];
    }

    public function del($key)
    {
        if (isset(self::$store[$key])) {
            unset(self::$store[$key]);
            return true;
        }
        return false;
    }

    public function clear()
    {
        self::$store = array();
        return true;
    }

}
