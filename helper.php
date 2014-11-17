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

/**
 * Helper for mod_bootstrapnav
 *
 * @package     Joomla.Site
 * @subpackage  mod_bootstrapnav
 * @since       1.5
 */
class ModBootstrapnavHelper{
	/**
	 * Get a list of the menu items.
	 *
	 * @param  JRegistry   $params  The module options.
	 * @return  array
	 */
	public static function getList(&$params){
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu item
		$base = self::getBase($params);
		$user = JFactory::getUser();
		$levels = $user->getAuthorisedViewLevels();
		asort($levels);
		$key = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
		$cache = JFactory::getCache('mod_bootstrapnav', '');
		if (!($items = $cache->get($key))){
			$path    = $base->tree;
			$start   = 1;
			$end     = 0;
			$showAll = 1;
			$items   = $menu->getItems('menutype', $params->get('menutype'));

			$lastitem = 0;

			if ($items){
				foreach ($items as $i => $item){
					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start - 2], $path)))
					{
						unset($items[$i]);
						continue;
					}

					$item->deeper     = false;
					$item->shallower  = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem])){
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

					$lastitem     = $i;
					$item->active = false;
					$item->flink  = $item->link;

					switch ($item->type){
						case 'separator':
						case 'heading':
							// No further action needed.
							continue;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							$router = $app::getRouter();
							if ($router->getMode() == JROUTER_MODE_SEF){
								$item->flink = 'index.php?Itemid=' . $item->id;
							}
							else{
								$item->flink .= '&Itemid=' . $item->id;
							}
							break;
					}

					if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)){
						$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
					}
					else{
						$item->flink = JRoute::_($item->flink);
					}

					$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
				}

				if (isset($items[$lastitem])){
					$items[$lastitem]->deeper     = (($start?$start:1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start?$start:1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start?$start:1));
				}
			}

			$cache->store($items, $key);
		}
		return $items;
	}

	/**
	 * Get base menu item.
	 * @param   JRegistry  $params  The module options.
	 * @return   object
	 *
	 */
	public static function getBase(&$params){
		if ($params->get('base')){
			$base = JFactory::getApplication()->getMenu()->getItem($params->get('base'));
		}
		else{
			$base = false;
		}

		if (!$base){
			$base = self::getActive($params);
		}

		return $base;
	}

	/**
	 * Get active menu item.
	 * @param   JRegistry  $params  The module options.
	 * @return  object
	 *
	 */
	public static function getActive(&$params)
	{
		$menu = JFactory::getApplication()->getMenu();

		return $menu->getActive() ? $menu->getActive() : $menu->getDefault();
	}

}
