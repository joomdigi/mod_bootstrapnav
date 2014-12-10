
<?php
/**
 * @version     1.1
 * @package     mod_bootstrapnav
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Brad Traversy <support@bootstrapjoomla.com> - http://www.bootstrapjoomla.com
 */
//No Direct Access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$list		= ModBootstrapnavHelper::getList($params);
$base		= ModBootstrapnavHelper::getBase($params);
$active		= ModBootstrapnavHelper::getActive($params);
$active_id 	= $active->id;
$path		= $base->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));
$nav_type	= $params->get('nav_type');
$color      = $params->get('color','navbar-white');
$fixed      = $params->get('fixed','navbar-default');
$float      = $params->get('float');
$brand_type = $params->get('brand_type');
$brand_text = $params->get('brand_text');
$brand_image = $params->get('brand_image');
$use_css = $params->get('use_css',0);

if (count($list))
{
	require JModuleHelper::getLayoutPath('mod_bootstrapnav', $params->get('layout', 'default'));
}
