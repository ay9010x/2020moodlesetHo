<?php




require_once(dirname(__FILE__).'/locallib.php');



$options = lightboxgallery_edit_types(true);

$disableplugins = new admin_setting_configmulticheckbox('disabledplugins', get_string('configdisabledplugins', 'lightboxgallery'),
                    get_string('configdisabledpluginsdesc', 'lightboxgallery'), array(), $options);
$disableplugins->plugin = 'lightboxgallery';

$settings->add($disableplugins);



$description = get_string('configenablerssfeedsdesc', 'lightboxgallery');

if (empty($CFG->enablerssfeeds)) {
    $description .= ' (' . get_string('configenablerssfeedsdisabled2', 'admin') . ')';
}

$enablerss = new admin_setting_configcheckbox('enablerssfeeds', get_string('configenablerssfeeds', 'lightboxgallery'),
                $description, 0);
$enablerss->plugin = 'lightboxgallery';

$settings->add($enablerss);
