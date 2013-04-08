<?php
include('SessionMemcache.class.php');
SessionMemcache::$options = array('host'=>'localhost','port'=>11211,'expired'=>86400);
$mobj = new SessionMemcache();
$mobj->execute();
session_start();
echo 'before:'.$_SESSION['ok'];
$_SESSION['ok']=time();
echo 'after:'.$_SESSION['ok'];