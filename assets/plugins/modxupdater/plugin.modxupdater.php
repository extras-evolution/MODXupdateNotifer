<?php

/*
@TODO
— Мультиязычность 
— Автоматическое сохранение копий текущих файлов ядра(для того что б можно было откатиться обратно) с логикой бекапа только тех файлов что есть в новой версии так что б бекап весил порядка 5-10 мегабайт а не по полному обьему сайта. 
— Механизм по возврату к предыдущей версии если обновление некоректно работает. 
*/


if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
//lang
$_lang = array();
$plugin_path = $modx->config['base_path'] . "assets/plugins/modxupdater/";
include($plugin_path.'lang/english.php');
if (file_exists($plugin_path.'lang/' . $modx->config['manager_language'] . '.php')) {
    include($plugin_path.'lang/' . $modx->config['manager_language'] . '.php');
}

$e = &$modx->Event;
if($e->name == 'OnManagerWelcomePrerender'){
    $errorsMessage = '';
    $errors = 0;
    if (!extension_loaded('curl')){
        $errorsMessage .= '-'.$_lang['error_curl'].'<br>';
        $errors += 1;
    }
    if (!extension_loaded('zip')){
        $errorsMessage .= '-'.$_lang['error_zip'].'<br>';
        $errors += 1;
    }
    if (!extension_loaded('openssl')){
        $errorsMessage .= '-'.$_lang['error_openssl'].'<br>';
        $errors += 1;
    }
    if (!is_writable(MODX_BASE_PATH.'assets/')){
        $errorsMessage .= '-'.$_lang['error_overwrite'].'<br>';
        $errors += 1;
    }
    
    if($version == 'auto'){
        if(stristr($modx->config['settings_version'], 'd') === FALSE) {
            $version = 'modxcms/evolution';
        }else{
            $version = 'dmi3yy/modx.evo.custom';
        }
    }

    $output = '';
    require_once(MODX_MANAGER_PATH.'media/rss/rss_cache.inc');
    $cache = new RSSCache(MODX_BASE_PATH.'assets/cache/', $cache_lifetime*3600);
    if($cache->check_cache('unw') != 'HIT'){
        $ch = curl_init();
        $url = 'https://api.github.com/repos/'.$version.'/'.$type;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: updateNotify widget'));
        $info = curl_exec($ch);
        curl_close($ch);
        if (substr($info,0,1) != '[') return;
        $info = json_decode($info,true);
        $gitVersion = $info[0]['name'];
        $cache->set('unw',$gitVersion);
    } else {
        $gitVersion= $cache->get('unw');
    }
    $currentVersion = $modx->getVersionData();

    $_SESSION['updatelink'] = md5(time());
    $_SESSION['updateversion'] = $gitVersion;
    if ($gitVersion != $currentVersion['version']) {
    // get manager role
    $role = $_SESSION['mgrRole'];
    if(($role!=1) AND ($showButton == 'AdminOnly') OR ($showButton == 'hide') OR ($errors > 0)) {
        $updateButton = '';
    }  else {
    $updateButton = '<a target="_parent" href="/'.$_SESSION['updatelink'].'" class="btn btn-sm btn-default">'.$_lang['updateButton_txt'].' '.$gitVersion.'</a><br><br>';
    }   
    $output = '<li id="modxupdate_widget" data-row="7" data-col="1" data-sizex="4" data-sizey="3" class="gs-w" style="margin-top:10px">
        <div class="panel panel-default widget-wrapper">
          <div style=cursor:auto;" class="panel-headingx widget-title sectionHeader clearfix">
            <span style=cursor:auto;" class="panel-handel pull-left"><i class="fa fa-exclamation-triangle"></i> '.$_lang['system_update'].'</span>
          </div>
          <div class="panel-body widget-stage sectionBody">
              '.$_lang['cms_outdated_msg'].' <strong>'.$gitVersion.'</strong> <br><br>
               '.$updateButton.'
               <small style="color:red;font-size:10px"> '.$_lang['bkp_before_msg'].'</small>
               <small style="color:red;font-size:10px">'.$errorsMessage.'</small>
          </div>
        </div>
</li>
';
     }
    $e->output($output);
}
if($e->name == 'OnPageNotFound'){
     
    switch($_GET['q']){     
        case $_SESSION['updatelink']:
            $currentVersion = $modx->getVersionData();
            if ($_SESSION['updateversion'] != $currentVersion['version']) {
                
                file_put_contents(MODX_BASE_PATH.'updatemodx.php', '<?php
function downloadFile($url, $path)
{
    $newfname = $path;
    try {
        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }

    } catch (Exception $e) {
        $this->errors[] = array("ERROR:Download", $e->getMessage());
        return false;
    }
    if ($file) {
        fclose($file);
    }

    if ($newf) {
        fclose($newf);
    }

    return true;
}

function removeFolder($path)
{
    $dir = realpath($path);
    if (!is_dir($dir)) {
        return;
    }

    $it    = new RecursiveDirectoryIterator($dir);
    $files = new RecursiveIteratorIterator($it,
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->getFilename() === "." || $file->getFilename() === "..") {
            continue;
        }
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dir);
}

function copyFolder($src, $dest)
{
    $path    = realpath($src);
    $dest    = realpath($dest);
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {

        $startsAt = substr(dirname($name), strlen($path));
        mmkDir($dest . $startsAt);
        if ($object->isDir()) {
            mmkDir($dest . substr($name, strlen($path)));
        }

        if (is_writable($dest . $startsAt) and $object->isFile()) {
            copy((string) $name, $dest . $startsAt . DIRECTORY_SEPARATOR . basename($name));
        }
    }
}

function mmkDir($folder, $perm = 0777)
{
    if (!is_dir($folder)) {
        mkdir($folder, $perm);
    }
}

if(stristr($_GET["version"], "d") === FALSE) {
        $version = "modxcms/evolution";
}else{
        $version = "dmi3yy/modx.evo.custom";
}

downloadFile("https://github.com/".$version."/archive/" . $_GET["version"] . ".zip", "modx.zip");
$zip = new ZipArchive;
$res = $zip->open(dirname(__FILE__) . "/modx.zip");
$zip->extractTo(dirname(__FILE__) . "/temp");
$zip->close();

if ($handle = opendir(dirname(__FILE__) . "/temp")) {
    while (false !== ($name = readdir($handle))) {
        if ($name != "." && $name != "..") {
            $dir = $name;
        }
    }
    closedir($handle);
}
removeFolder(dirname(__FILE__) . "/temp/" . $dir . "/install/assets/chunks");
removeFolder(dirname(__FILE__) . "/temp/" . $dir . "/install/assets/tvs");
removeFolder(dirname(__FILE__) . "/temp/" . $dir . "/install/assets/templates");
unlink(dirname(__FILE__) . "/temp/" . $dir . "/.htaccess");
unlink(dirname(__FILE__) . "/temp/" . $dir . "/ht.access");
unlink(dirname(__FILE__) . "/temp/" . $dir . "/robots.txt");

if(is_file(dirname(__FILE__) . "/assets/cache/siteManager.php")){

    unlink(dirname(__FILE__) . "/temp/" . $dir . "/assets/cache/siteManager.php");
    include_once(dirname(__FILE__) . "/assets/cache/siteManager.php");
    if(!defined("MGR_DIR")){ define("MGR_DIR","manager"); }
    if(MGR_DIR != "manager"){
        mmkDir(dirname(__FILE__)."/temp/".$dir."/".MGR_DIR);
        copyFolder(dirname(__FILE__)."/temp/".$dir."/manager", dirname(__FILE__)."/temp/".$dir."/".MGR_DIR);
        removeFolder(dirname(__FILE__)."/temp/".$dir."/manager");
    } 
    echo dirname(__FILE__)."/temp/".$dir."/".MGR_DIR;
}
copyFolder(dirname(__FILE__)."/temp/".$dir, dirname(__FILE__)."/");
removeFolder(dirname(__FILE__)."/temp");
unlink(dirname(__FILE__)."/modx.zip");
unlink(dirname(__FILE__)."/updatemodx.php");
header("Location: /install/index.php?action=mode");
');
                header("Location: /updatemodx.php?version=".$_SESSION['updateversion']);
            }
            die();
        break;
    }
    
}
