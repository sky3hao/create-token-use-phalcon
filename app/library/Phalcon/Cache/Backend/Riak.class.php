<?php

namespace Phalcon\Cache\Backend;

class Riak extends \Phalcon\Cache\Backend implements \Phalcon\Cache\BackendInterface
{

    protected $_connection;
    protected $_bucket;

    public function __construct($frontend, $options = array())
    {
        if (!is_array($options)) {
            $options = (array)$options;
        }
        if (!isset($options['host'])) {
            $options['host'] = '127.0.0.1';
        }
        if (!isset($options['port'])) {
            $options['port'] = 8087;
        }
        if (!isset($options['bucket'])) {
            $options['bucket'] = 'cache';
        }
        parent::__construct($frontend, $options);
    }

    protected function _connect()
    {
        $options = $this->_options;
        $connection = new \Riak\Connection($options['host'], $options['port']);
        $bucket = new \Riak\Bucket($connection, $options['bucket']);
        $newProps = new \Riak\BucketPropertyList();
        $newProps->setAllowMult(false);
        $bucket->setPropertyList($newProps);

        $this->_connection = $connection;
        $this->_bucket = $bucket;
    }

    public function get($keyName, $lifetime = 0)
    {
        $frontend = $this->_frontend;
        $bucket = $this->_bucket;
        if (!is_object($bucket)) {
            $this->_connect();
            $bucket = $this->_bucket;
        }

        $response = $bucket->get($keyName);

        if ($response->hasObject()) {
            $cachedContent = $response->getFirstObject()->getContent();
            return $frontend->afterRetrieve($cachedContent);
        }
        return null;
    }
    
    public function save($keyName =null, $content = null, $lifetime = null, $stopBuffer = null)
    {
        $frontend = $this->_frontend;
        $bucket = $this->_bucket;
        if (!is_object($bucket)) {
            $this->_connect();
            $bucket = $this->_bucket;
        }

        if (null == $content) {
            $cachedContent = $frontend->getContent();
        } else {
            $cachedContent = $content;
        }
        if (null == $stopBuffer) {
            $stopBuffer = true;
        }

        $preparedContent = $frontend->beforeStore($cachedContent);
        $obj = new \Riak\Object($keyName);
        $obj->setContent($preparedContent);
        if (true != $bucket->put($obj)) {
            throw new Phalcon\Cache\Exception("Failed storing data in Riak");
        }

        if ($stopBuffer) {
            $frontend->stop();
        }
        $this->_started = false;
    }
    
    public function delete($keyName)
    {
        $bucket = $this->_bucket;
        if (!is_object($bucket)) {
            $this->_connect();
            $bucket = $this->_bucket;
        }

        $response = $bucket->get($keyName);
        if ($response->hasObject()) {
            return $bucket->delete($response->getObject());
        }
        return true;
    }
    
    public function queryKeys($prefix = null)
    {
        throw new Phalcon\Cache\Exception("WARNING!! do not use this function in production");
    }
    
    public function exists($keyName = null, $lifetime = null)
    {
        $bucket = $this->_bucket;
        if (!is_object($bucket)) {
            $this->_connect();
            $bucket = $this->_bucket;
        }

        $response = $bucket->get($keyName);
        if ($response->hasObject()) {
            return true;
        }
        return false;
    }
}
