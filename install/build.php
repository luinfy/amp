<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Qin
 * Date: 13-8-31
 * Time: ����4:24
 * To change this template use File | Settings | File Templates.
 */

require('conf.php');
require('func.php');
array_shift($argv);
$app_list = array('apache', 'php', 'mysql', 'htdocs', 'temp', 'down');
foreach($app_list as $k=>$v){
	if(!file_exists($root.$v) && !is_dir($root.$v)){
		mkdir($root.$v, 755);
		batout('�ļ��� '.$root.$v.' �����ɹ�.');
	}
	$$v = $root.$v .'/';
}
if(!file_exists($htdocs . 'cgi-bin') && !is_dir($htdocs . 'cgi-bin')){
	mkdir($htdocs . 'cgi-bin', 755);
}

batout("\r\n���ڼ�鵱ǰϵͳ����, ���Ժ�...", true, 1);
if(strtolower($argv['0']) == 'amd64') {
    batout('��ǰϵͳΪ64λ, ��������64λ����...');
}elseif(strtolower($argv['0']) == 'x86'){
    batout('��ǰϵͳΪ32λ, ��������32λ����...');
}else{
    batout('���ܱ�����ϵͳ�汾,�ű�����3����˳�...', true, 3);
    exit();
}
batout("\r\n", false, 2);
$install = batput('��ѡ������32λ����64λ����  Y(32λ) N(64λ) :');
if($install == 'y'){
    $res = file_get_contents(CONF . '32bits.txt');
	define('BITS', 'x86/');
	
}else{
    if(strtolower($argv['0']) == 'x86/') {
        batout('��⵽��ǰ����ϵͳΪ32λ, ��������64λ����...', true, 1);
        batout('�Զ�����Ϊ32λ Apache MySQL PHP (PostgreSQL MongoDB)...', false, 2);
        $res = file_get_contents(CONF . '32bits.txt');
		define('BITS', 'x86/');
    }else{
        $res = file_get_contents(CONF . '64bits.txt');
		define('BITS', 'x64/');
    }    
}
$contents = file_get_contents(CONF . 'php_extension_' . substr(BITS, 0, 3) . '.txt');
$list = explode("\r\n", $contents);
foreach($list as $k=>$v){
	$v = explode("=", $v);
	$$v['0'] = $v['1'];
}
batout("", true);
batout("    **************************************************************");
batout("    **  ��Ϊ Apachelounge.com ����������й�IP������            **");
batout("    **  ���� Apache ѹ���������� sourceforge.net ��������       **");
batout("    **  Ҳ���ԴӰٶ�������������Ҫ���ļ�, ���ص�ַ�� ��װ˵��   **");
batout("    **  ���� MySQL PHP PostgreSQL MongoDB �����ùٷ���ַ����    **");
batout("    **  �벻Ҫ�޸����ص�ѹ���ļ�, �ű�����ѹ���ļ���������    **");
batout("    **************************************************************");
batout("", true, 2);

$res = explode("\r\n\r\n", $res);
$uapache = applst($res['0'], 'Apache');
$umysql = applst($res['1'], 'MySQL');
$iapache = appins($apache, $uapache);
$imysql = appins($mysql, $umysql);
$apache_dir = $iapache['1'] . '/';
$mysql_dir = $imysql['1'] . '/';
$mysql_data = $imysql['1'] . '/data/';
$bat_apache = batstr($apache_dir, 'httpd', 'Apache');
$bat_mysql = batstr($mysql_dir, 'mysqld', 'MySQL');
$ht = batput('Ĭ��htdocsĿ¼Ϊ ' . ROOT . "htdocs\r\n�Ƿ��Զ���htdocsĿ¼ Y(��) N(��):");
if($ht == 'y'){
	do{
		batout("��������ȷ��htdocsĿ¼ : ", false);
		$htdocs_dir = trim(fgets(STDIN));
	}while(empty($htdocs_dir) || is_null($htdocs_dir) || (!file_exists($htdocs_dir) && !is_dir($htdocs_dir)));
	batout("\r\nʹ���Զ���Ŀ¼ ".$htdocs_dir . " ΪhtdocsĿ¼");
}else{
	$htdocs_dir = ROOT . 'htdocs';
	batout("\r\nʹ��Ŀ¼ " . $htdocs_dir . " ΪhtdocsĿ¼");
}

batout('��ʼ���� ' . $apache_dir . 'conf/httpd.conf', true, 2);
if(!file_exists($apache_dir . 'conf/httpd-default.conf')){
	copy($apache_dir . 'conf/httpd.conf', $apache_dir . 'conf/httpd-default.conf');
}
if($iapache['0'] == '2.2'){
	$httpd = CONF . 'httpd22.txt';
}else{
	$httpd = CONF . 'httpd24.txt';
}
reconf($apache_dir . 'conf/httpd-default.conf', $httpd, $apache_dir . 'conf/httpd.conf');
batout('��ʼ���� ' . $mysql_dir . 'bin/my.ini', true, 2);
conf(CONF . 'myini.txt', $mysql_dir . 'bin/my.ini');

$ch_pgsql = batput("\r\n�Ƿ�ʹ�� PostgreSQL ����  Y(ʹ��) N(��ʹ��) :");
if($ch_pgsql == 'y'){
	if(!file_exists(ROOT . 'pgsql') && !is_dir(ROOT . 'pgsql')) mkdir(ROOT . 'pgsql');
	$pgsql = ROOT . 'pgsql/';
	$upgsql = applst($res['3'], 'PostgreSQL');
	$ipgsql = appins($pgsql, $upgsql);
	$pgsql_dir = $ipgsql['1'] . '/'; 
	if(file_exists($apache_dir . 'bin/libpq.dll')) unlink($apache_dir . 'bin/libpq.dll');
	if(file_exists($apache_dir . 'bin/libintl-8.dll')) unlink($apache_dir . 'bin/libintl-8.dll');
	copy($pgsql_dir . 'bin/libpq.dll', $apache_dir . 'bin/libpq.dll');
	if($ipgsql['0'] < 9.2) copy($pgsql_dir . 'bin/libintl-8.dll', $apache_dir . 'bin/libintl-8.dll');
	$bat_pgsql = batstr($pgsql_dir, 'pg_ctl', 'PostgreSQL');
}

$ch_mongo = batput("\r\n�Ƿ�ʹ�� MongoDB ����  Y(ʹ��) N(��ʹ��) :");
if($ch_mongo == 'y'){
	if(!file_exists(ROOT . 'mongo') && !is_dir(ROOT . 'mongo')) mkdir(ROOT . 'mongo');
	$mongo = ROOT . 'mongo/';
	$umongo = applst($res['4'], 'MongoDB');
	$imongo = appins($mongo, $umongo);
	$mongo_dir = $imongo['1'] . '/';
	$bat_mongo = batstr($mongo_dir, 'mongod', 'MongoDB');
	if(!file_exists($mongo_dir . 'data') && !is_dir($mongo_dir . 'data')) mkdir($mongo_dir . 'data', 755);
	if(file_exists($mongo_dir . 'mongo.log')) unlink($mongo_dir . 'mongo.log');
	if(file_exists($mongo_dir . 'mongod.cfg')) unlink($mongo_dir . 'mongod.cfg');
	file_put_contents($mongo_dir . 'mongod.cfg', "logpath=" . $mongo_dir . "mongo.log\r\nlogappend=true\r\ndbpath=" . $mongo_dir . "data\r\n");
}

$rmdir_php = '';
$ch_php = batput("\r\n�Ƿ����ö���汾PHP�����л�  Y(��汾) N(���汾) :");
if($ch_php == 'y'){
	$muphp = muphp($res['2'], $php, $xp);
	foreach($muphp as $k=>$v){
		$php_dir = $v['1'] . '/';
		$ver = str_replace('.', '', $v['0']);
		conf(CONF . 'phpconf.txt', $apache_dir . 'conf/extra/php-' . $ver . '.conf');
		file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "\r\n", FILE_APPEND);
		file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadFile \"" . $php_dir . "php5ts.dll\"\r\n", FILE_APPEND);
		if($iapache['0'] == 2.2) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_2.dll\"\r\n", FILE_APPEND);
		if($iapache['0'] == 2.4) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_4.dll\"\r\n", FILE_APPEND);
		reconf($php_dir . 'php.ini-production', CONF . 'phpini.txt', $php_dir . 'php.ini');
		if(isset($ipgsql)){
			php_pgsql($php_dir);
		}
		if(isset($imongo)){
			$php_mongo = 'php' . $ver . '_mongo';
			php_mongo($php_dir, $ver, $$php_mongo);		
		}
		$start_php = 'start_php' . $ver;
		$$start_php = "del " . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "copy " . windir($apache_dir) . "conf\httpd-php.conf " . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo.>>" . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo Include conf/extra/php-" . $ver . ".conf>>" . windir($apache_dir) . "conf\httpd.conf\r\n";
		$$start_php .= "echo.>>" . windir($apache_dir) . "conf/httpd.conf\r\n";
		$rmdir_php .= "rd /s /q " . windir($php_dir) . "\r\n";
		if($v['0'] >= 5.5){
			batout("\r\nΪ PHP5.5 ���ƿ������� Apache 2.2 �� php5apache2_2.dll\r\n", false, 2);
			if(!file_exists($php_dir . 'php5apache2_2.dll')) copy(EXT . BITS . 'php5apache2_2.dll', $php_dir . 'php5apache2_2.dll');
			if(!file_exists($php_dir . 'php5apache2_2_filter.dll')) copy(EXT . BITS . 'php5apache2_2_filter.dll', $php_dir . 'php5apache2_2_filter.dll');
		}
	}
}else{
	$uphp = applst($res['2'], 'PHP');
	$iphp = appins($php, $uphp);
	$php_dir = $iphp['1'] . '/';
	$ver = str_replace('.', '', $iphp['0']);
	conf(CONF . 'phpconf.txt', $apache_dir . 'conf/extra/php-' . $ver . '.conf');
	file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "\r\n", FILE_APPEND);
	file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadFile \"" . $php_dir . "php5ts.dll\"\r\n", FILE_APPEND);
	if($iapache['0'] == 2.2) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_2.dll\"\r\n", FILE_APPEND);
	if($iapache['0'] == 2.4) file_put_contents($apache_dir . 'conf/extra/php-' . $ver . '.conf', "LoadModule php5_module \"" . $php_dir . "php5apache2_4.dll\"\r\n", FILE_APPEND);
	reconf($php_dir . 'php.ini-production', CONF . 'phpini.txt', $php_dir . 'php.ini');
	if(isset($ipgsql)){
		php_pgsql($php_dir);
	}
	if(isset($imongo)){
		$php_mongo = 'php' . $ver . '_mongo';
		php_mongo($php_dir, $ver, $$php_mongo);			
	}
	file_put_contents($apache_dir . 'conf/httpd.conf', "\r\n", FILE_APPEND);
	file_put_contents($apache_dir . 'conf/httpd.conf', "Include conf/extra/php-" . $ver . ".conf", FILE_APPEND);
	$rmdir_php .= "rd /s /q " . windir($php_dir) . "\r\n";
	if($iphp['0'] >= 5.5){
		batout("\r\nΪ PHP5.5 ���ƿ������� Apache 2.2 �� php5apache2_2.dll\r\n", false, 2);
		if(!file_exists($php_dir . 'php5apache2_2.dll')) copy(EXT . BITS . 'php5apache2_2.dll', $php_dir . 'php5apache2_2.dll');
		if(!file_exists($php_dir . 'php5apache2_2_filter.dll')) copy(EXT . BITS . 'php5apache2_2_filter.dll', $php_dir . 'php5apache2_2_filter.dll');
	}
}

if(!file_exists($apache_dir . 'conf/httpd-php.conf')) copy($apache_dir . 'conf/httpd.conf', $apache_dir . 'conf/httpd-php.conf');
getpatch();


if(file_exists(INS . 'ins_service.bat')) unlink(INS . 'ins_service.bat');
rename(ROOT . '��װ����.bat', INS . 'ins_service.bat');
$cmd_start = $bat_apache['0'] . $bat_mysql['0'];
$cmd_stop = $bat_apache['1'] . $bat_mysql['1'];
$cmd_install = $bat_apache['2'] . $bat_mysql['2'];
$cmd_uninstall = $bat_apache['3'] . $bat_mysql['3'];
$cmd_rmdir = $bat_apache['4'] . $bat_mysql['4'];
if(isset($ipgsql)){
	$cmd_start .= $bat_pgsql['0'];
	$cmd_stop .= $bat_pgsql['1'];
	$cmd_install .= $bat_pgsql['2'];
	$cmd_uninstall .= $bat_pgsql['3'];
	$cmd_rmdir .= $bat_pgsql['4'];
}
if(isset($imongo)){
	$cmd_start .= $bat_mongo['0'];
	$cmd_stop .= $bat_mongo['1'];
	$cmd_install .= $bat_mongo['2'];
	$cmd_uninstall .= $bat_mongo['3'];
	$cmd_rmdir .= $bat_mongo['4'];
}
build('��װ����', $cmd_install);
build('ֹͣ����', $cmd_stop);
$rmdir_top = '';
foreach($app_list as $k=>$v){
	$rmdir_top .= "rd /s /q " . windir($root . $v) . "\r\n";
}
$del_cmd = "del ��װ����.bat\r\ndel ֹͣ����.bat\r\n";
$ch_rmdir = "echo.\r\n:rcos\r\nset /p no=   ��ȷ���Ƿ�ɾ�������ļ��� Y(ɾ��)�� N(��ɾ��):\r\nif /I \"%no%\"==\"y\" goto rdr\r\nif /I \"%no%\"==\"n\" exit\r\necho �����������������...\r\ngoto rcos\r\necho.\r\n:rdr\r\n";

if(isset($start_php53)){
	build('����PHP53', $start_php53 . $cmd_start);
	$del_cmd .= "del ����PHP53.bat\r\n";
}	
if(isset($start_php54)){
	build('����PHP54', $start_php54 . $cmd_start);
	$del_cmd .= "del ����PHP54.bat\r\n";
}
if(isset($start_php55)){
	build('����PHP55', $start_php55 . $cmd_start);
	$del_cmd .= "del ����PHP55.bat\r\n";
}
if(isset($iphp)){
	build('��������', $cmd_start);
	$del_cmd .= "del ��������.bat\r\n";
}
build('ж�ط���', $cmd_uninstall . $ch_rmdir . $cmd_rmdir . $rmdir_php . $rmdir_top . $del_cmd);
exit();
?>