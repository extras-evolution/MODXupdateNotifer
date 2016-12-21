//<?php
/**
 * MODX.Evolution.updateNotify
 *
 * show message about outdated CMS version
 *
 * @category    plugin
 * @version     0.4
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Dmi3yy (dmi3yy.com) 
 * @internal    @events OnManagerWelcomePrerender,OnPageNotFound
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &cache_lifetime=Cache lifetime (hours);text;24 &showButton=Show Update Button:;menu;show,hide,AdminOnly;show &version=Version:;menu;auto,modxcms/evolution,dmi3yy/modx.evo.custom;auto &type=Type:;menu;tags,releases;tags 
 * @internal    @installset base
 * @internal    @disabled 0
 */
 
require MODX_BASE_PATH.'assets/plugins/modxupdater/plugin.modxupdater.php';


