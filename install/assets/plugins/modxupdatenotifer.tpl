//<?php
/**
 * MODX.Evolution.updateNotify
 *
 * show message about outdated CMS version
 *
 * @category    plugin
 * @version     0.6
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Dmi3yy (dmi3yy.com) 
 * @internal    @events OnManagerWelcomePrerender,OnManagerWelcomeHome,OnPageNotFound,OnSiteRefresh
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &showButton=Show Update Button:;menu;show,hide,AdminOnly;AdminOnly &version=Version:;menu;auto,modxcms/evolution,dmi3yy/modx.evo.custom;auto &type=Type:;menu;tags,releases,commits;tags 
 * @internal    @installset base
 * @internal    @disabled 0
 */
 
require MODX_BASE_PATH.'assets/plugins/modxupdater/plugin.modxupdater.php';


