<?php



require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/gdlib.php');
require_once('picture_form.php');

define ('PIX_FILE_UPDATED', 0);
define ('PIX_FILE_ERROR',   1);
define ('PIX_FILE_SKIPPED', 2);

admin_externalpage_setup('tooluploaduserpictures');

require_login();

require_capability('tool/uploaduser:uploaduserpictures', context_system::instance());

$site = get_site();

if (!$adminuser = get_admin()) {
    print_error('noadmins', 'error');
}

$strfile = get_string('file');
$struser = get_string('user');
$strusersupdated = get_string('usersupdated', 'tool_uploaduser');
$struploadpictures = get_string('uploadpictures','tool_uploaduser');

$userfields = array (
    0 => 'username',
    1 => 'idnumber',
    2 => 'id' );

$userfield = optional_param('userfield', 0, PARAM_INT);
$overwritepicture = optional_param('overwritepicture', 0, PARAM_BOOL);

echo $OUTPUT->header();

echo $OUTPUT->heading_with_help($struploadpictures, 'uploadpictures', 'tool_uploaduser');

$mform = new admin_uploadpicture_form(null, $userfields);
if ($formdata = $mform->get_data()) {
    if (!array_key_exists($userfield, $userfields)) {
        echo $OUTPUT->notification(get_string('uploadpicture_baduserfield', 'tool_uploaduser'));
    } else {
                                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

                        $zipdir = my_mktempdir($CFG->tempdir.'/', 'usrpic');
        $dstfile = $zipdir.'/images.zip';

        if (!$mform->save_file('userpicturesfile', $dstfile, true)) {
            echo $OUTPUT->notification(get_string('uploadpicture_cannotmovezip', 'tool_uploaduser'));
            @remove_dir($zipdir);
        } else {
            $fp = get_file_packer('application/zip');
            $unzipresult = $fp->extract_to_pathname($dstfile, $zipdir);
            if (!$unzipresult) {
                echo $OUTPUT->notification(get_string('uploadpicture_cannotunzip', 'tool_uploaduser'));
                @remove_dir($zipdir);
            } else {
                                                @unlink($dstfile);

                $results = array ('errors' => 0,'updated' => 0);

                process_directory($zipdir, $userfields[$userfield], $overwritepicture, $results);


                                remove_dir($zipdir);
                echo $OUTPUT->notification(get_string('usersupdated', 'tool_uploaduser') . ": " . $results['updated'], 'notifysuccess');
                echo $OUTPUT->notification(get_string('errors', 'tool_uploaduser') . ": " . $results['errors'], ($results['errors'] ? 'notifyproblem' : 'notifysuccess'));
                echo '<hr />';
            }
        }
    }
}
$mform->display();
echo $OUTPUT->footer();
exit;



function my_mktempdir($dir, $prefix='') {
    global $CFG;

    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }

    do {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (file_exists($path));

    check_dir_exists($path);

    return $path;
}


function process_directory ($dir, $userfield, $overwrite, &$results) {
    global $OUTPUT;
    if(!($handle = opendir($dir))) {
        echo $OUTPUT->notification(get_string('uploadpicture_cannotprocessdir', 'tool_uploaduser'));
        return;
    }

    while (false !== ($item = readdir($handle))) {
        if ($item != '.' && $item != '..') {
            if (is_dir($dir.'/'.$item)) {
                process_directory($dir.'/'.$item, $userfield, $overwrite, $results);
            } else if (is_file($dir.'/'.$item))  {
                $result = process_file($dir.'/'.$item, $userfield, $overwrite);
                switch ($result) {
                    case PIX_FILE_ERROR:
                        $results['errors']++;
                        break;
                    case PIX_FILE_UPDATED:
                        $results['updated']++;
                        break;
                }
            }
                                }
    }
    closedir($handle);
}


function process_file ($file, $userfield, $overwrite) {
    global $DB, $OUTPUT;

            $path_parts = pathinfo(cleardoubleslashes($file));
    $basename  = $path_parts['basename'];
    $extension = $path_parts['extension'];

            $uservalue = substr($basename, 0,
                        strlen($basename) -
                        strlen($extension) - 1);

        if (!($user = $DB->get_record('user', array ($userfield => $uservalue, 'deleted' => 0)))) {
        $a = new stdClass();
        $a->userfield = clean_param($userfield, PARAM_CLEANHTML);
        $a->uservalue = clean_param($uservalue, PARAM_CLEANHTML);
        echo $OUTPUT->notification(get_string('uploadpicture_usernotfound', 'tool_uploaduser', $a));
        return PIX_FILE_ERROR;
    }

    $haspicture = $DB->get_field('user', 'picture', array('id'=>$user->id));
    if ($haspicture && !$overwrite) {
        echo $OUTPUT->notification(get_string('uploadpicture_userskipped', 'tool_uploaduser', $user->username));
        return PIX_FILE_SKIPPED;
    }

    if ($newrev = my_save_profile_image($user->id, $file)) {
        $DB->set_field('user', 'picture', $newrev, array('id'=>$user->id));
        echo $OUTPUT->notification(get_string('uploadpicture_userupdated', 'tool_uploaduser', $user->username), 'notifysuccess');
        return PIX_FILE_UPDATED;
    } else {
        echo $OUTPUT->notification(get_string('uploadpicture_cannotsave', 'tool_uploaduser', $user->username));
        return PIX_FILE_ERROR;
    }
}


function my_save_profile_image($id, $originalfile) {
    $context = context_user::instance($id);
    return process_new_icon($context, 'user', 'icon', 0, $originalfile);
}


