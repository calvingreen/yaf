<?php

/**
 * 业务相关缓存层基类
 *
 * @package    Cache
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     hqlong <qinglong@staff.sina.com.cn>
 * @version    2011-8-14
 */
abstract class Abstract_Cache {
    protected $configs = array();
    /**
     * 定义key前缀，参考configs/cache_key.php
     */
    protected $key_prefix = '';
    protected $cache_obj = NULL;
    /**
     * 缓存池，必须指定
     * @var string
     */
    protected $cache_pool = '';
    protected static $cache = array();

    public function __construct($pool = NULL) {
        $pool = $pool ? $pool : $this->cache_pool;
        if (empty($pool)) {
            throw new Cache_Exception(get_class($this) . ' property cache_pool must be assigned');
        }
        $this->set_pool($pool);
    }

    /**
     * 重新设定$cache变量,防止大量设定cache值造成的缓存溢出错误(批量处理用)
     */
    public static function reset_cache_data() {
        self::$cache = array();
    }

    /**
     * 动态设置缓存池
     * @param string $pool
     */
    public function set_pool($pool) {
        $this->cache_obj = Comm_Mc::init($this->cache_pool);
    }

    /**
     * 将缓存池设置成默认
     */
    public function reset_pool() {
        $this->set_pool($this->cache_pool);
    }

    /**
     * 清除cache对象
     */
    public function clear_cache_obj() {
        $this->cache_obj = NULL;
    }

    /**
     * 获取缓存单元key
     *
     * @param $name
     * @return string
     */
    public function key($name) {
        $args = func_get_args();
        $id = @join('_', $args);
        if (isset(self::$cache['key'][$id])) {
            return self::$cache['key'][$id];
        }

        if (empty($name)) {
            throw new Cache_Exception('key name does not empty');
        }
        if (!isset($this->configs[$name][0])) {
            throw new Cache_Exception('Key name ' . $name . ' illegal');
        }

        if (isset(self::$cache['key_prefix'][$this->key_prefix])) {
            $args[0] = self::$cache['key_prefix'][$this->key_prefix];
        } else {
            $args[0] = Comm_Config::getUseStatic('cache.' . $this->key_prefix);
            self::$cache['key_prefix'][$this->key_prefix] = $args[0];
        }
        return self::$cache['key'][$id] = vsprintf($this->configs[$name][0], $args);
    }

    /**
     * 获取缓存单元缓存时间，未指定，默认缓存时间为60秒
     *
     * @param $name
     * @return int
     */
    public function livetime($name) {
        if (empty($name)) {
            throw new Cache_Exception('key name does not empty');
        }
        if (isset($this->configs[$name][1]) && !is_integer($this->configs[$name][1])) {
            throw new Cache_Exception('live time must be is valid integer');
        }
        return isset($this->configs[$name][1]) ? $this->configs[$name][1] : 60;
    }

    /**
     * 将key映射成多份，随机返回一个key，主要解决一个端口负载过大的问题
     *
     * @see key_hash()
     * @param $key
     * @param $rand 命中概率 1/$rand
     * @return array
     */
    public function key_rand($key, $rand = 10) {
        if ($rand <= 1) {
            return $key;
        }
        $rnd = rand(1, $rand);
        return $key . '_' . $rnd;
    }

    /**
     * 将key映射成多份，解决公共缓存占用一个端口，造成端口负载过大的问题
     *
     * @see key_rand()
     * @param $key
     * @param $hash 将key映射成这么多份
     * @return array;
     */
    public function key_hash($key, $hash = 10) {
        if ($hash <= 1) {
            return array($key);
        }
        $keys = array();
        for($i = 1; $i <= $hash; $i++) {
            $keys[] = $key . '_' . $i;
        }
        return $keys;
    }

    /**
     * 创建多份缓存，解决公共缓存占用一个端口，造成端口负载过大的问题
     *
     * @see key_rand()
     * @param $key
     * @param $data
     * @param $livetime
     * @param $hash 将key映射成这么多份
     * @return array;
     */
    public function set_hash($key, $data, $livetime, $hash = 10) {
        $values = array();
        if ($hash <= 1) {
            $values[$key] = $data;
        } else {
            for($i = 1; $i <= $hash; $i++) {
                $values[$key . '_' . $i] = $data;
            }
        }
        return $this->cache_obj->mset($values, $livetime);
    }
}