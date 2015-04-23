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
    
    
    
    
}
/**
 * @version     1.1
 * @package     mod_bootstrapnav
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Michael Jones <mikegrahamjones@gmail.com>
 */
class ModBootStrapMenuGenerator
{
    
    
    /**
     * Track individual menu items; by the $item->id
     * @type array (KVP)
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    private $menu_items_created = false;
    
    
    /**
     * Debug tracked items; as the menu is generated
     * @type boolean
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    private $debug_created_menus = false;
    
    
    /**
     * Allow for complete recursion on the BootStrap Menu
     * @param array $list
     * @param string $path
     * @param integer $active_id
     * @param boolean $show_subnav
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    public function Build_BootStrap_Menu($item_list, $path, $active_id, $show_subnav = TRUE, $bootstrap_menu = array())
    {
        $this->menu_items_created = array();
        
        if (!count($item_list) || !is_array($item_list)) {
            return "";
        }
        
        $bootstrap_menu_item_keys = array_keys($bootstrap_menu);
        
        foreach ($item_list as $item_list_index => &$item) {
            
            $class = '';
            
            if ($item->id == $active_id) {
                //$class .= ' current';
            }
            if (in_array($item->id, $path)) {
                $class .= ' active';
            }
            
            if ($this->Is_Joomla_Item_New($item, 'first_level')) {
                
                $rendered_menu_item = $this->Build_BootStrap_MenuItem($item, $class, $item_list, $show_subnav);
                
                $bootstrap_menu[] = $rendered_menu_item;
                
            }
            
            unset($item_list[$item_list_index]);
        }
        
        
        // Render our List
        $rendered_bootstrap_menu = implode('', $bootstrap_menu);
        
        // Return the List
        return $rendered_bootstrap_menu;
        
    }
    
    /**
     * Create a <li></li> menu item
     * @param object stdClass $item
     * @param string $item_class
     * @param string $link_class
     * @param string $link_title
     * @param string $link_data
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    protected function Create_BootStrap_Menu_Item($item, $item_class = '', $link_class = '', $link_title = '', $link_data = '')
    {
        
        if ($this->Is_Joomla_Item_New($item, 'created')) {
            
            $properties['li-class'] = $item_class ? "class=\"$item_class\"" : "";
            $properties['li-id']    = "id=\"bootstrap_li_menu_item_{$item->id}\"";
            
            $joomla_link_item = $this->Create_BootStrap_Menu_Link($item->title, $item->flink, $link_class, "bootstrap_a_menu_item_{$item->id}", $link_title, $link_data);
            
            $joomla_menu_item_rendered = "<li {$properties['li-class']} {$properties['li-id']} >{$joomla_link_item}</li>";
            
            $this->Track_BootStrap_Menu_Item($item);
            
            return $joomla_menu_item_rendered;
            
        }
        
        return "";
        
    }
    
    /**
     * Create a <a></a> menu item; inherts Create_BootStrap_Menu_Item
     * @param object $link_content
     * @param string $link_href
     * @param string $link_class
     * @param string $link_id
     * @param string $link_title
     * @param string $link_data
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    protected function Create_BootStrap_Menu_Link($link_content = '', $link_href = '', $link_class = '', $link_id = '', $link_title = '', $link_data = '')
    {
        
        $properties['class'] = $link_class ? "class=\"$link_class\"" : "";
        $properties['id']    = $link_id ? "id=\"{$link_id}\"" : "";
        $properties['title'] = $link_title ? "title=\"$link_title\"" : "";
        $properties['href']  = $link_href ? "href=\"{$link_href}\"" : "href=\"#\"";
        
        $joomla_link_rendered = "<a {$properties['class']} {$properties['id']} {$properties['title']} {$link_data} {$properties['href']} >{$link_content}</a>";
        
        return $joomla_link_rendered;
        
    }
    
    /**
     * Track a Item; so it does not get re-created
     * @param object stdClass $item
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    protected function Track_BootStrap_Menu_Item($item)
    {
        
        $this->menu_items_created[$item->id] = $item;
        
        return true;
        
    }
    
    /**
     * Track a Item; so it does not get re-created
     * @param object stdClass $item
     * @param string $scan_event
     * @return  html
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    protected function Is_Joomla_Item_New($item, $scan_event = '')
    {
        
        if (count($this->menu_items_created) > 0 && is_array($this->menu_items_created)) {
            
            $item_is_new = !isset($this->menu_items_created[$item->id]);
            
            if ($this->debug_created_menus) {
                
                // echo "Checking item {$item->id} in menu_items_created from <b>{$scan_event}</b><br/>";
                // echo "The item at INDEX <b>{$item->id}</b> " . ($item_is_new ? "New" : "Created") . "<br/>";
                
                // echo "<pre>";
                // print_r(array_keys($this->menu_items_created));
                // echo "</pre>";
                
            }
            
            return $item_is_new;
        }
        
        return true;
    }
    
    
    /**
     * Allow for Sub Items; to also have other Sub Items
     * @param stdClass Joomla/Registry $item
     * @param string $item_class
     * @param array $list
     * @param boolean $show_subnav
     * @return  html
     * http://bootsnipp.com/snippets/featured/multi-level-navbar-menu
     * @author Michael Jones <mikegrahamjones@gmail.com>
     */
    protected function Build_BootStrap_MenuItem($item, $item_class, $list, $show_subnav = TRUE, $subnav_class = NULL)
    {
        
        $menu_item = array();
        
        if (!$this->Is_Joomla_Item_New($item, 'menu_item')) {
            return "";
        }
        
        if ($item_class) {
            $item_class     = trim($item_class);
            $css_item_class = "class=\"{$item_class}\"";
        } else {
            $css_item_class = "";
        }
        
        if (!$item->parent) {
            
            if ($item->level == 1) {
                $menu_item[] = $this->Create_BootStrap_Menu_Item($item, $item_class);
            }
            
        } else {
            
            if (!$show_subnav) {
                
                if ($item->level == 1) {
                    
                    $menu_item[] = $this->Create_BootStrap_Menu_Item($item, $item_class, 'top-level-menu-item');
                    
                }
                
            } else {
                
                if (!count($list)) {
                    return "";
                }
                
                if ($item->level == 1) {
                    $dropdown_properties['multi-level'] = "class=\"dropdown-menu multi-level\"";
                    $dropdown_properties['sub-level']   = $item_class ? $css_item_class : "";
                    $dropdown_properties['caret']       = "<span class=\"caret_spacer\"></span><b class=\"caret\"></b>";
                } else {
                    $dropdown_properties['multi-level'] = "class=\"dropdown-menu\"";
                    $dropdown_properties['sub-level']   = "class=\"dropdown-submenu $item_class\"";
                    $dropdown_properties['caret']       = "";
                }
                
                $menu_item[] = "<li {$dropdown_properties['sub-level']} >";
                $menu_item[] = $this->Create_BootStrap_Menu_Link("{$item->title}{$dropdown_properties['caret']}", "#", "$subnav_class dropdown-toggle", "", "", "data-toggle=\"dropdown\"");
                $menu_item[] = "<ul {$dropdown_properties['multi-level']} >";
                
                foreach ($list as $list_index => $subitem) {
                    
                    if ($subitem->parent_id == $item->id && $this->Is_Joomla_Item_New($subitem, 'sub_menu')) {
                        
                        if ($subitem->parent) {
                            
                            $item_class .= ' dropdown-sub-menu-item';
                            
                            $new_sub_item = $this->Build_BootStrap_MenuItem($subitem, $item_class, $list, $show_subnav, 'sub-menu-item');
                            $menu_item[]  = $new_sub_item;
                            
                        } else {
                            
                            $menu_item[] = $this->Create_BootStrap_Menu_Item($subitem, $item_class, $subnav_class);
                            
                        }
                        
                    }
                }
                
                $menu_item[] = "</ul>";
                $menu_item[] = "</li>";
                
            }
        }
        
        $this->Track_BootStrap_Menu_Item($item);
        
        $rendered_menu_item = implode('', $menu_item);
        
        return $rendered_menu_item;
    }
    
}
?>