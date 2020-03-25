<?php




defined('MOODLE_INTERNAL') || die();


define('INSTALL_WELCOME',       0);

define('INSTALL_ENVIRONMENT',   1);

define('INSTALL_PATHS',         2);

define('INSTALL_DOWNLOADLANG',  3);

define('INSTALL_DATABASETYPE',  4);

define('INSTALL_DATABASE',      5);

define('INSTALL_SAVE',          6);


function install_guess_wwwroot() {
    $wwwroot = '';
    if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') {
        $wwwroot .= 'http://';
    } else {
        $wwwroot .= 'https://';
    }
    $hostport = explode(':', $_SERVER['HTTP_HOST']);
    $wwwroot .= reset($hostport);
    if ($_SERVER['SERVER_PORT'] != 80 and $_SERVER['SERVER_PORT'] != '443') {
        $wwwroot .= ':'.$_SERVER['SERVER_PORT'];
    }
    $wwwroot .= $_SERVER['SCRIPT_NAME'];

    list($wwwroot, $xtra) = explode('/install.php', $wwwroot);

    return $wwwroot;
}


function install_ini_get_bool($ini_get_arg) {
    $temp = ini_get($ini_get_arg);

    if ($temp == '1' or strtolower($temp) == 'on') {
        return true;
    }
    return false;
}


function install_init_dataroot($dataroot, $dirpermissions) {
    if (file_exists($dataroot) and !is_dir($dataroot)) {
                return false;
    }

    umask(0000);     if (!file_exists($dataroot)) {
        if (!mkdir($dataroot, $dirpermissions, true)) {
                        return false;
        }
    }
    @chmod($dataroot, $dirpermissions);

    if (!is_writable($dataroot)) {
        return false;     }

        if (!is_dir("$dataroot/temp")) {
        if (!mkdir("$dataroot/temp", $dirpermissions, true)) {
            return false;
        }
    }
    if (!is_writable("$dataroot/temp")) {
        return false;     }

        if (!is_dir("$dataroot/cache")) {
        if (!mkdir("$dataroot/cache", $dirpermissions, true)) {
            return false;
        }
    }
    if (!is_writable("$dataroot/cache")) {
        return false;     }

        if (!is_dir("$dataroot/lang")) {
        if (!mkdir("$dataroot/lang", $dirpermissions, true)) {
            return false;
        }
    }
    if (!is_writable("$dataroot/lang")) {
        return false;     }

        if (!file_exists("$dataroot/.htaccess")) {
        if ($handle = fopen("$dataroot/.htaccess", 'w')) {
            fwrite($handle, "deny from all\r\nAllowOverride None\r\nNote: this file is broken intentionally, we do not want anybody to undo it in subdirectory!\r\n");
            fclose($handle);
        } else {
            return false;
        }
    }

    return true;
}


function install_helpbutton($url, $title='') {
    if ($title == '') {
        $title = get_string('help');
    }
    echo "<a href=\"javascript:void(0)\" ";
    echo "onclick=\"return window.open('$url','Help','menubar=0,location=0,scrollbars,resizable,width=500,height=400')\"";
    echo ">";
    echo "<img src=\"pix/help.gif\" class=\"iconhelp\" alt=\"$title\" title=\"$title\"/>";
    echo "</a>\n";
}


function install_db_validate($database, $dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions) {
    try {
        try {
            $database->connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);
        } catch (moodle_exception $e) {
                        if ($database->create_database($dbhost, $dbuser, $dbpass, $dbname, $dboptions)) {
                $database->connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);
            } else {
                throw $e;
            }
        }
        return '';
    } catch (dml_exception $ex) {
        $stringmanager = get_string_manager();
        $errorstring = $ex->errorcode.'oninstall';
        $legacystring = $ex->errorcode;
        if ($stringmanager->string_exists($errorstring, $ex->module)) {
                        $returnstring = $stringmanager->get_string($errorstring, $ex->module, $ex->a);
            if ($ex->debuginfo) {
                $returnstring .= '<br />'.$ex->debuginfo;
            }

            return $returnstring;
        } else if ($stringmanager->string_exists($legacystring, $ex->module)) {
                                                $returnstring = $stringmanager->get_string($legacystring, $ex->module, $ex->a);
            if ($ex->debuginfo) {
                $returnstring .= '<br />'.$ex->debuginfo;
            }

            return $returnstring;
        }
                return $stringmanager->get_string('dmlexceptiononinstall', 'error', $ex);
    }
}


function install_generate_configphp($database, $cfg) {
    $configphp = '<?php  // Moodle configuration file' . PHP_EOL . PHP_EOL;

    $configphp .= 'unset($CFG);' . PHP_EOL;
    $configphp .= 'global $CFG;' . PHP_EOL;
    $configphp .= '$CFG = new stdClass();' . PHP_EOL . PHP_EOL; 
    $dbconfig = $database->export_dbconfig();

    foreach ($dbconfig as $key=>$value) {
        $key = str_pad($key, 9);
        $configphp .= '$CFG->'.$key.' = '.var_export($value, true) . ';' . PHP_EOL;
    }
    $configphp .= PHP_EOL;

    $configphp .= '$CFG->wwwroot   = '.var_export($cfg->wwwroot, true) . ';' . PHP_EOL ;

    $configphp .= '$CFG->dataroot  = '.var_export($cfg->dataroot, true) . ';' . PHP_EOL;

    $configphp .= '$CFG->admin     = '.var_export($cfg->admin, true) . ';' . PHP_EOL . PHP_EOL;

    if (empty($cfg->directorypermissions)) {
        $chmod = '02777';
    } else {
        $chmod = '0' . decoct($cfg->directorypermissions);
    }
    $configphp .= '$CFG->directorypermissions = ' . $chmod . ';' . PHP_EOL . PHP_EOL;

    if (isset($cfg->upgradekey) and $cfg->upgradekey !== '') {
        $configphp .= '$CFG->upgradekey = ' . var_export($cfg->upgradekey, true) . ';' . PHP_EOL . PHP_EOL;
    }

    $configphp .= 'require_once(dirname(__FILE__) . \'/lib/setup.php\');' . PHP_EOL . PHP_EOL;
    $configphp .= '// There is no php closing tag in this file,' . PHP_EOL;
    $configphp .= '// it is intentional because it prevents trailing whitespace problems!' . PHP_EOL;

    return $configphp;
}


function install_print_help_page($help) {
    global $CFG, $OUTPUT; 
    @header('Content-Type: text/html; charset=UTF-8');
    @header('X-UA-Compatible: IE=edge');
    @header('Cache-Control: no-store, no-cache, must-revalidate');
    @header('Cache-Control: post-check=0, pre-check=0', false);
    @header('Pragma: no-cache');
    @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
    @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    echo '<html dir="'.(right_to_left() ? 'rtl' : 'ltr').'">
          <head>
          <link rel="shortcut icon" href="theme/clean/pix/favicon.ico" />
          <link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/install/css.php" />
          <title>'.get_string('installation','install').'</title>
          <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
          </head><body>';
    switch ($help) {
        case 'phpversionhelp':
            print_string($help, 'install', phpversion());
            break;
        case 'memorylimithelp':
            print_string($help, 'install', @ini_get('memory_limit'));
            break;
        default:
            print_string($help, 'install');
    }
    echo $OUTPUT->close_window_button();     echo '</body></html>';
    die;
}


function install_print_header($config, $stagename, $heading, $stagetext, $stageclass = "alert-info") {
    global $CFG;

    @header('Content-Type: text/html; charset=UTF-8');
    @header('X-UA-Compatible: IE=edge');
    @header('Cache-Control: no-store, no-cache, must-revalidate');
    @header('Cache-Control: post-check=0, pre-check=0', false);
    @header('Pragma: no-cache');
    @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
    @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    echo '<html dir="'.(right_to_left() ? 'rtl' : 'ltr').'">
          <head>
          <link rel="shortcut icon" href="theme/clean/pix/favicon.ico" />';

    echo '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/install/css.php" />
          <title>'.get_string('installation','install').' - Moodle '.$CFG->target_release.'</title>
          <meta name="robots" content="noindex">
          <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
          <meta http-equiv="pragma" content="no-cache" />
          <meta http-equiv="expires" content="0" />';

    echo '</head><body class="notloggedin">
            <div id="page" class="stage'.$config->stage.'">
                <div id="page-header">
                    <div id="header" class=" clearfix">
                        <h1 class="headermain">'.get_string('installation','install').'</h1>
                        <div class="headermenu">&nbsp;</div>
                    </div>
                    <div class="navbar clearfix">
                        <nav class="breadcrumb-nav">
                            <ul class="breadcrumb"><li class="first">'.$stagename.'</li></ul>
                        </nav>
                        <div class="navbutton">&nbsp;</div>
                    </div>
                </div>
          <!-- END OF HEADER -->
          <div id="installdiv">';

    echo '<h2>'.$heading.'</h2>';

    if ($stagetext !== '') {
        echo '<div class="alert ' . $stageclass . '">';
        echo $stagetext;
        echo '</div>';
    }
        echo '<form id="installform" method="post" action="install.php"><fieldset>';
    foreach ($config as $name=>$value) {
        echo '<input type="hidden" name="'.$name.'" value="'.s($value).'" />';
    }
}


function install_print_footer($config, $reload=false) {
    global $CFG;

    if ($config->stage > INSTALL_WELCOME) {
        $first = '<input type="submit" id="previousbutton" name="previous" value="&laquo; '.s(get_string('previous')).'" />';
    } else {
        $first = '<input type="submit" id="previousbutton" name="next" value="'.s(get_string('reload')).'" />';
        $first .= '<script type="text/javascript">
//<![CDATA[
    var first = document.getElementById("previousbutton");
    first.style.visibility = "hidden";
//]]>
</script>
';
    }

    if ($reload) {
        $next = '<input type="submit" id="nextbutton" class="btn btn-primary" name="next" value="'.s(get_string('reload')).'" />';
    } else {
        $next = '<input type="submit" id="nextbutton" class="btn btn-primary" name="next" value="'.s(get_string('next')).' &raquo;" />';
    }

    echo '</fieldset><fieldset id="nav_buttons">'.$first.$next.'</fieldset>';

    $homelink  = '<div class="sitelink">'.
       '<a title="Moodle '. $CFG->target_release .'" href="http://docs.moodle.org/en/Administrator_documentation" onclick="this.target=\'_blank\'">'.
       '<img src="pix/moodlelogo.png" alt="'.get_string('moodlelogo').'" /></a></div>';

    echo '</form></div>';
    echo '<div id="page-footer">'.$homelink.'</div>';
    echo '</div></body></html>';
}


function install_cli_database(array $options, $interactive) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/environmentlib.php');
    require_once($CFG->libdir.'/upgradelib.php');

        @error_reporting(E_ALL | E_STRICT);
    @ini_set('display_errors', '1');
    $CFG->debug = (E_ALL | E_STRICT);
    $CFG->debugdisplay = true;
    $CFG->debugdeveloper = true;

    $CFG->version = '';
    $CFG->release = '';
    $CFG->branch = '';

    $version = null;
    $release = null;
    $branch = null;

        require($CFG->dirroot.'/version.php');

    if ($DB->get_tables() ) {
        cli_error(get_string('clitablesexist', 'install'));
    }

    if (empty($options['adminpass'])) {
        cli_error('Missing required admin password');
    }

        list($envstatus, $environment_results) = check_moodle_environment(normalize_version($release), ENV_SELECT_RELEASE);
    if (!$envstatus) {
        $errors = environment_get_errors($environment_results);
        cli_heading(get_string('environment', 'admin'));
        foreach ($errors as $error) {
            list($info, $report) = $error;
            echo "!! $info !!\n$report\n\n";
        }
        exit(1);
    }

    if (!$DB->setup_is_unicodedb()) {
        if (!$DB->change_db_encoding()) {
                        cli_error(get_string('unicoderequired', 'admin'));
        }
    }

    if ($interactive) {
        cli_separator();
        cli_heading(get_string('databasesetup'));
    }

        install_core($version, true);
    set_config('release', $release);
    set_config('branch', $branch);

    if (PHPUNIT_TEST) {
                set_config('phpunittest', 'na');
    }

        upgrade_noncore(true);

        $DB->set_field('user', 'password', hash_internal_user_password($options['adminpass']), array('username' => 'admin'));

        if (isset($options['adminemail'])) {
        $DB->set_field('user', 'email', $options['adminemail'], array('username' => 'admin'));
    }

        if (isset($options['adminuser']) and $options['adminuser'] !== 'admin' and $options['adminuser'] !== 'guest') {
        $DB->set_field('user', 'username', $options['adminuser'], array('username' => 'admin'));
    }

        set_config('rolesactive', 1);
    upgrade_finished();

        \core\session\manager::set_user(get_admin());

        admin_apply_default_settings(NULL, true);
    admin_apply_default_settings(NULL, true);
    set_config('registerauth', '');

        if (isset($options['shortname']) and $options['shortname'] !== '') {
        $DB->set_field('course', 'shortname', $options['shortname'], array('format' => 'site'));
    }
    if (isset($options['fullname']) and $options['fullname'] !== '') {
        $DB->set_field('course', 'fullname', $options['fullname'], array('format' => 'site'));
    }
    if (isset($options['summary'])) {
        $DB->set_field('course', 'summary', $options['summary'], array('format' => 'site'));
    }
}
