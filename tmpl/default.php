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

.dropdown:hover .dropdown-menu {
    display: block;
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
                <?php foreach ($list as $i => &$item) : ?>
                <?php 
                $class = $item->id;
                if($item->id == $active_id){
                    //$class .= ' current';
                }
                if (in_array($item->id, $path)){
                    $class .= ' active';
                }
                ?>
                    <?php if(!$item->parent) : ?>
                        <?php if($item->level == 1) : ?>
                            <li class="<?php echo $class; ?>"><a href="<?php echo $item->flink; ?>"><?php echo $item->title; ?></a></li>
                        <?php endif; ?>
                    <?php elseif($item->parent) : ?>
                         <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $item->title; ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <?php foreach ($list as $i => &$subitem) : ?>                                   
                                <?php if($subitem->parent_id == $item->id) : ?>
                                    <li><a href="<?php echo $subitem->flink; ?>"><?php echo $subitem->title; ?></a></li>
                                <?php endif; ?>       
                        <?php endforeach; ?>
                    </ul>
                </li>
                    <?php endif; ?>
                <?php endforeach; ?>                                                                                           
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