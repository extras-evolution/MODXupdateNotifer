//<?php
/**
 * MODX.EVO.Custom.updateNotify
 *
 * show message about outdated CMS version
 *
 * @category    plugin
 * @version     0.2
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Pathologic (m@xim.name) 
 * @internal    @events OnManagerWelcomePrerender
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &cache_lifetime = Время жизни кэша, часов;text;24 &message = Чанк с сообщением;text;
 * @internal    @installset base
 * @internal    @disabled 1
 */
 
$e = &$modx->Event;
if($e->name == 'OnManagerWelcomePrerender'){
$output = '';
        require_once(MODX_MANAGER_PATH.'media/rss/rss_cache.inc');
        $cache = new RSSCache(MODX_BASE_PATH.'assets/cache/', $cache_lifetime*3600);
        if($cache->check_cache('unw') != 'HIT'){
                $ch = curl_init();
                $url = 'https://api.github.com/repos/dmi3yy/modx.evo.custom/releases';
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
		$message = !empty($message) ? $modx->getChunk($message) : '<div style="font-size:20px;color:red;">Система управления сайтом устарела - возможны проблемы с безопасностью. Для обновления обратитесь к разработчикам сайта. Новая версия MODX Evolution: <strong>'.$gitVersion.'</strong></div>';
        if ($gitVersion != $currentVersion['version']) {
                $output = '<div class="sectionHeader">Внимание!</div><div class="sectionBody">'.$message.'</div>';
        }
$e->output($output);
}
