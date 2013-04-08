php-memcache-session
====================

change php session to memcache instead of file. 


Example
----------
```
include('SessionMemcache.class.php');
SessionMemcache::$options = array('host'=>'localhost','port'=>11211,'expired'=>86400);
$mobj = new SessionMemcache();
$mobj->execute();
//execute must invoke before session_start()

session_start();
echo 'before:'.$_SESSION['ok'];
$_SESSION['ok']=time();
echo 'after:'.$_SESSION['ok'];
```