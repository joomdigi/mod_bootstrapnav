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
class ModBootstrapnavHelper
{
    /**
     * Get a list of the menu items.
     *
     * @param  JRegistry   $params  The module options.
     * @return  array
     */
    public static function getList(&$params)
    {
        $app  = JFactory::getApplication();
        $menu = $app->getMenu();
        
        // Get active menu item
        $base   = self::getBase($params);
        $user   = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        asort($levels);
        $key   = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
        $cache = JFactory::getCache('mod_bootstrapnav', '');
        if (!($items = $cache->get($key))) {
            $path    = $base->tree;
            $start   = 1;
            $end     = 0;
            $showAll = 1;
            $items   = $menu->getItems('menutype', $params->get('menutype'));
            
            $lastitem = 0;
            
            if ($items) {
                foreach ($items as $i => $item) {
                    if (($start && $start > $item->level) || ($end && $item->level > $end) || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path)) || ($start > 1 && !in_array($item->tree[$start - 2], $path))) {
                        unset($items[$i]);
                        continue;
                    }
                    
                    $item->deeper     = false;
                    $item->shallower  = false;
                    $item->level_diff = 0;
                    
                    if (isset($items[$lastitem])) {
                        $items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
                        $items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
                        $items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
                    }
                    
                    $item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
                    
                    $lastitem     = $i;
                    $item->active = false;
                    $item->flink  = $item->link;
                    
                    switch ($item->type) {
                        case 'separator':
                        case 'heading':
                            // No further action needed.
                            continue;
                        
                        case 'url':
                            if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
                                // If this is an internal Joomla link, ensure the Itemid is set.
                                $item->flink = $item->link . '&Itemid=' . $item->id;
                            }
                            break;
                        
                        case 'alias':
                            $item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                            break;
                        
                        default:
                            $router = $app::getRouter();
                            if ($router->getMode() == JROUTER_MODE_SEF) {
                                $item->flink = 'index.php?Itemid=' . $item->id;
                            } else {
                                $item->flink .= '&Itemid=' . $item->id;
                            }
                            break;
                    }
                    
                    if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
                        $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                    } else {
                        $item->flink = JRoute::_($item->flink);
                    }
                    
                    $item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
                }
                
                if (isset($items[$lastitem])) {
                    $items[$lastitem]->deeper     = (($start ? $start : 1) > $items[$lastitem]->level);
                    $items[$lastitem]->shallower  = (($start ? $start : 1) < $items[$lastitem]->level);
                    $items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ? $start : 1));
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
    public static function getBase(&$params)
    {
        if ($params->get('base')) {
            $base = JFactory::getApplication()->getMenu()->getItem($params->get('base'));
        } else {
            $base = false;
        }
        
        if (!$base) {
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
    
    
    /**
     * Allow for complete recursion on the BootStrap Menu
     * @param array $list
     * @param string $path
     * @param integer $active_id
     * @param boolean $show_subnav
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    public static function Build_BootStrap_Menu($list, $path, $active_id, $show_subnav = TRUE)
    {
        
        $bootstrap_menu = array();
        
        foreach ($list as $i => &$item) {
            
            $class = '';
            
            if ($item->id == $active_id) {
                //$class .= ' current';
            }
            if (in_array($item->id, $path)) {
                $class .= ' active';
            }
            
            $bootstrap_menu[] = self::Build_BootStrap_MenuItem($item, $class, $list, $show_subnav);
            
        }
        
        // Render our List
        $rendered_bootstrap_menu = implode('', $bootstrap_menu);
        
        // Return the List
        return $rendered_bootstrap_menu;
        
    }
    
    
    /**
     * Allow for Sub Items; to also have other Sub Items
     * @param stdClass Joomla/Registry $item
     * @param string $item_class
     * @param array $list
     * @param boolean $show_subnav
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    public static function Build_BootStrap_MenuItem($item, $item_class, $list, $show_subnav = TRUE)
    {
        
        $menu_item = array();
        
        if (!$item->parent) {
            
            if ($item->level == 1) {
                $menu_item[] = "<li class=\"{$item_class}\"><a href=\"{$item->flink}\">{$item->title}</a></li>";
            }
            
        } else {
            
            if (!$show_subnav) {
                
                if ($item->level == 1) {
                    
                    $menu_item[] = "<li class=\"{$item_class}\"><a href=\"{$item->flink}\">{$item->title}</a></li>";
                    
                }
                
            } else {
                
                $menu_item[] = "<li class=\"dropdown\">";
                $menu_item[] = "<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$item->title}<span class=\"caret_spacer\"></span><b class=\"caret\"></b></a>";
                $menu_item[] = "<ul class=\"dropdown-menu\">";
                foreach ($list as $i => &$subitem) {
                    if ($subitem->parent_id == $item->id) {
                        if ($subitem->parent) {
                            $new_sub_item = self::Build_BootStrap_MenuItem($subitem, $list, $class, $show_subnav);
                            $menu_item[]  = $new_sub_item;
                        } else {
                            $menu_item[] = "<li class=\"{$item_class}\"><a href=\"{$item->flink}\">{$item->title}</a></li>";
                        }
                    }
                }
                
                $menu_item[] = "</ul>";
                $menu_item[] = "</li>";
                
            }
        }
        
        $rendered_menu_item = implode('', $menu_item);
        return $rendered_menu_item;
    }
    
}
