<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Qin
 * Date: 13-8-31
 * Time: обнГ3:30
 * To change this template use File | Settings | File Templates.
 */
$root = getcwd().'/';
$root = str_replace('\\', '/', $root);
$temp = $root . 'temp/';
$update = 'https://raw.github.com/shaoqin/amppm2win/public/';
#$httpd_url = 'http://superb-dca3.dl.sourceforge.net/project/appmm/';

define('ROOT', $root);
define('INS', ROOT . 'install/');
define('DOWN', ROOT . 'down/');
define('TEMP', ROOT . 'temp/');
define('CONF', INS . 'conf/');
define('EXT', INS . 'ext/');
define('SCRIPTS', INS . 'scripts/');
define('PATCH', SCRIPTS . 'patch/');
define('BIN', INS . 'bin/');
define('FETCH', BIN . 'wget.exe --no-check-certificate');
define('UNZIP', BIN . '7z.exe');
define('DOWNTIME', 3);

if(php_uname('r')<6){
	$xp = true;
}else{
	$xp = false;
}
?>