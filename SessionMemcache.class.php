<?php 
// +----------------------------------------------------------------------
// | Memcache  session for php                                                           
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://bigpu.cn All rights reserved.      
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dodgepudding <dodgepudding@gmail.com>                                  
// +----------------------------------------------------------------------
// 

class SessionMemcache
{
    /**
     +----------------------------------------------------------
     * Session life time, for seconds
     +----------------------------------------------------------
     * @var int
     * @access protected
     +----------------------------------------------------------
     */
   protected $lifeTime=3600; 

    /**
     +----------------------------------------------------------
     * according to session_name setting.
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
   protected $sessionName='';

    /**
     +----------------------------------------------------------
     * memcache handle
     +----------------------------------------------------------
     * @var array of Memcache
     * @access private
     +----------------------------------------------------------
     */
   private static $mHandle; 

   /**
     +----------------------------------------------------------
     * session静态缓存
     +----------------------------------------------------------
     * @var array
     * @access private
     +----------------------------------------------------------
     */
   private static $staticCache;
   /**
     +----------------------------------------------------------
     * memcache options array
     +----------------------------------------------------------
     * @var array
     * @access public
     +----------------------------------------------------------
     */
   
   public static $options;
    /**
     +----------------------------------------------------------
     * 打开Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $savePath 
     * @param mixed $sessName  
     +----------------------------------------------------------
     */
    public function open($savePath, $sessName) { 
        //get session-lifetime 
	    $this->sessionName = $sessName;
        $options = self::$options;
        $this->lifeTime = isset($options['expired'])?$options['expired'] : $this->lifeTime; 
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $timeout = isset($options['timeout'])?$options['timeout']:60;
        $host = isset($options['host']) ? $options['host'] : 'localhost';
        $port = isset($options['port']) ? $options['port'] : '11211';
        self::$mHandle = new Memcache;
        $re =  self::$mHandle->$func($host, $port, $timeout);
        return $re;
    } 

    /**
     +----------------------------------------------------------
     * 关闭Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
   public function close() { 
       $this->gc(ini_get('session.gc_maxlifetime')); 
       self::$mHandle->close();
       self::$mHandle = null;
       self::$staticCache = null;
       return true; 
   } 

    /**
     +----------------------------------------------------------
     * 读取Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sessID 
     +----------------------------------------------------------
     */
   public function read($sessID) { 
   	if (isset(self::$staticCache[$this->sessionName.$sessID])) {
   		$re = self::$staticCache[$this->sessionName.$sessID];
   	} else {
		$re = self::$mHandle->get($this->sessionName.$sessID);
		if ($re) self::$staticCache[$this->sessionName.$sessID] = $re;
   	}
	return $re;
   } 

    /**
     +----------------------------------------------------------
     * 写入Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sessID 
     * @param String $sessData  
     +----------------------------------------------------------
     */
   public function write($sessID,$sessData) { 
	self::$staticCache[$this->sessionName.$sessID] = $sessData;
	return self::$mHandle->set($this->sessionName.$sessID, $sessData, 0, $this->lifeTime);
   } 

    /**
     +----------------------------------------------------------
     * 删除Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sessID 
     +----------------------------------------------------------
     */
   public function destroy($sessID) { 
       // delete session-data 
		if (isset(self::$staticCache[$this->sessionName.$sessID])) unset(self::$staticCache[$this->sessionName.$sessID]);
	   	return self::$mHandle->delete($this->sessionName.$sessID);
   } 

    /**
     +----------------------------------------------------------
     * Session 垃圾回收
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $sessMaxLifeTime 
     +----------------------------------------------------------
     */
   public function gc($sessMaxLifeTime) { 
       //TODO: memcache will aoto gc. 
       return true; 
   } 

    /**
     +----------------------------------------------------------
     * 打开Session 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $savePath 
     * @param mixed $sessName  
     +----------------------------------------------------------
     */
    public function execute() 
    {
    	session_set_save_handler(array(&$this,"open"), 
                         array(&$this,"close"), 
                         array(&$this,"read"), 
                         array(&$this,"write"), 
                         array(&$this,"destroy"), 
                         array(&$this,"gc")); 
    }
}
?>