<?php
/**
 * @version     1.7
 * @package     mod_bootstrapnav
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Brad Traversy <support@bootstrapjoomla.com> - http://www.bootstrapjoomla.com
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<?php if($use_css == 1) : ?>
    <link rel="stylesheet" href="<?php echo JURI::base(); ?>media/mod_bootstrapnav/css/bootstrap.css" type="text/css" />
<?php endif; ?>
<?php //print_r($list); ?>
<style>

.navbar, .navbar .container{
    background: <?php echo $background_color; ?> !important;
}

.navbar-nav > li > a{
     color:<?php echo $text_color; ?> !important;
     text-shadow: 0 0 0 !important;
}

.dropdown-menu .sub-menu {
    left: 100%;
    position: absolute;
    top: 0;
    visibility: hidden;
    margin-top: -1px;
}

.dropdown-menu li:hover .sub-menu {
    visibility: visible;
}

.nav-tabs .dropdown-menu, .nav-pills .dropdown-menu, .navbar .dropdown-menu {
    margin-top: 0;
}

.navbar .sub-menu:before {
    border-bottom: 7px solid transparent;
    border-left: none;
    border-right: 7px solid rgba(0, 0, 0, 0.2);
    border-top: 7px solid transparent;
    left: -7px;
    top: 10px;
}
.navbar .sub-menu:after {
    border-top: 6px solid transparent;
    border-left: none;
    border-right: 6px solid #fff;
    border-bottom: 6px solid transparent;
    left: 10px;
    top: 11px;
    left: -6px;
}

.navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus{
     background: <?php echo $active_background_color; ?> !important;
}

<?php
/**
	* Multiple Level BootStrap Snippet Styles
	* http://bootsnipp.com/snippets/featured/multi-level-navbar-menu
*/
?>

.dropdown-submenu {
    position: relative;
}

.dropdown-submenu>.dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -1px;
    margin-left: -1px;
}

.dropdown-submenu:hover>.dropdown-menu {
    display: block;
}



.dropdown-submenu>a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: #ccc;
    margin-top: 5px;
    margin-right: 5px;
}

.dropdown-submenu:hover>a:after {
    border-left-color: #fff;
}

.dropdown-submenu.pull-left {
    float: none;
}

.dropdown-submenu.pull-left>.dropdown-menu {
    left: -100%;
    margin-left: 10px;
    -webkit-border-radius: 6px 0 6px 6px;
    -moz-border-radius: 6px 0 6px 6px;
    border-radius: 6px 0 6px 6px;
}

</style>
<?php if($nav_type == 'navbar') : ?>
<div class="navbar <?php echo $fixed; ?>" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php if($brand_type == 'text') : ?>
                <a class="navbar-brand" href="index.php"><?php echo $brand_text; ?></a>
            <?php elseif($brand_type == 'image') : ?>
                <a class="navbar-brand" href="index.php"><img src="<?php echo $brand_image; ?>" /></a>
            <?php endif; ?>
        </div><!-- /.navbar-header -->
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav <?php echo $float; ?>">
			<?php
				
				$bootstrap_menu_generator = new ModBootStrapMenuGenerator();
			
				$bootstrap_menu = $bootstrap_menu_generator->Build_BootStrap_Menu($list, $path, $active_id, $show_subnav);
				echo $bootstrap_menu;
			?>
            </ul>
            <?php
                //Load Menu-Right Module
                $modules = JModuleHelper::getModules("menu-right");
                if($modules){
                    $document  = JFactory::getDocument();
                    $renderer  = $document->loadRenderer('module');
                    $attribs   = array();
                    $attribs['style'] = 'none';
                    foreach($modules as $mod){
                        echo JModuleHelper::renderModule($mod, $attribs);
                    }
                }
            ?>
        </div><!--/.nav-collapse -->
    </div><!--Container-->
    </div><!-- /.navbar -->
    <?php else : ?>
        <div class="list-group">
            <?php foreach ($list as $i => &$item) : ?>
            <?php 
                $class = $item->id; 
                $class .= ' list-group-item';
            ?>
                 <a href="<?php echo $item->flink; ?>" class="<?php echo $class; ?>"><?php echo $item->title; ?></a>                     
            <?php endforeach; ?>
        </div><!-- /.list-group-->
    <?php endif; ?>