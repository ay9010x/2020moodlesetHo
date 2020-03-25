<?php




defined('MOODLE_INTERNAL') || die();


    require_once($CFG->libdir.'/xmlize.php');

    
    define('NO_ERROR',                           0);
    
    define('NO_VERSION_DATA_FOUND',              1);
    
    define('NO_DATABASE_SECTION_FOUND',          2);
    
    define('NO_DATABASE_VENDORS_FOUND',          3);
    
    define('NO_DATABASE_VENDOR_MYSQL_FOUND',     4);
    
    define('NO_DATABASE_VENDOR_POSTGRES_FOUND',  5);
    
    define('NO_PHP_SECTION_FOUND',               6);
    
    define('NO_PHP_VERSION_FOUND',               7);
    
    define('NO_PHP_EXTENSIONS_SECTION_FOUND',    8);
    
    define('NO_PHP_EXTENSIONS_NAME_FOUND',       9);
    
    define('NO_DATABASE_VENDOR_VERSION_FOUND',  10);
    
    define('NO_UNICODE_SECTION_FOUND',          11);
    
    define('NO_CUSTOM_CHECK_FOUND',             12);
    
    define('CUSTOM_CHECK_FILE_MISSING',         13);
    
    define('CUSTOM_CHECK_FUNCTION_MISSING',     14);
    
    define('NO_PHP_SETTINGS_NAME_FOUND',        15);
    
    define('INCORRECT_FEEDBACK_FOR_REQUIRED',   16);
    
    define('INCORRECT_FEEDBACK_FOR_OPTIONAL',   17);

    
    define('ENV_SELECT_NEWER',                   0);
    
    define('ENV_SELECT_DATAROOT',                1);
    
    define('ENV_SELECT_RELEASE',                 2);


function check_moodle_environment($version, $env_select = ENV_SELECT_NEWER) {
    if ($env_select != ENV_SELECT_NEWER and $env_select != ENV_SELECT_DATAROOT and $env_select != ENV_SELECT_RELEASE) {
        throw new coding_exception('Incorrect value of $env_select parameter');
    }

    if (!$version = get_latest_version_available($version, $env_select)) {
        return array(false, array());
    }

    if (!$environment_results = environment_check($version, $env_select)) {
        return array(false, array());
    }

    $result = true;
    foreach ($environment_results as $environment_result) {
        if (!$environment_result->getStatus() && $environment_result->getLevel() == 'required'
          && !$environment_result->getBypassStr()) {
            $result = false;         } else if ($environment_result->getStatus() && $environment_result->getLevel() == 'required'
          && $environment_result->getRestrictStr()) {
            $result = false;         } else if ($environment_result->getErrorCode()) {
            $result = false;
        }
    }

    return array($result, $environment_results);
}



function environment_get_errors($environment_results) {
    global $CFG;
    $errors = array();

        foreach ($environment_results as $environment_result) {
        $type = $environment_result->getPart();
        $info = $environment_result->getInfo();
        $status = $environment_result->getStatus();
        $error_code = $environment_result->getErrorCode();

        $a = new stdClass();
        if ($error_code) {
            $a->error_code = $error_code;
            $errors[] = array($info, get_string('environmentxmlerror', 'admin', $a));
            return $errors;
        }

                if ($environment_result->getBypassStr() != '') {
                        continue;
        } else if ($environment_result->getRestrictStr() != '') {
                    } else {
            if ($status) {
                                continue;
            } else {
                if ($environment_result->getLevel() == 'optional') {
                                        continue;
                } else {
                                    }
            }
        }

                $rec = new stdClass();
        if ($rec->needed = $environment_result->getNeededVersion()) {
            $rec->current = $environment_result->getCurrentVersion();
            if ($environment_result->getLevel() == 'required') {
                $stringtouse = 'environmentrequireversion';
            } else {
                $stringtouse = 'environmentrecommendversion';
            }
                } else if ($environment_result->getPart() == 'custom_check') {
            if ($environment_result->getLevel() == 'required') {
                $stringtouse = 'environmentrequirecustomcheck';
            } else {
                $stringtouse = 'environmentrecommendcustomcheck';
            }
        } else if ($environment_result->getPart() == 'php_setting') {
            if ($status) {
                $stringtouse = 'environmentsettingok';
            } else if ($environment_result->getLevel() == 'required') {
                $stringtouse = 'environmentmustfixsetting';
            } else {
                $stringtouse = 'environmentshouldfixsetting';
            }
        } else {
            if ($environment_result->getLevel() == 'required') {
                $stringtouse = 'environmentrequireinstall';
            } else {
                $stringtouse = 'environmentrecommendinstall';
            }
        }
        $report = get_string($stringtouse, 'admin', $rec);

                $feedbacktext = '';
                $feedbacktext .= $environment_result->strToReport($environment_result->getFeedbackStr(), 'error');
                $feedbacktext .= $environment_result->strToReport($environment_result->getRestrictStr(), 'error');

        $report .= html_to_text($feedbacktext);

        if ($environment_result->getPart() == 'custom_check'){
            $errors[] = array($info, $report);
        } else {
            $errors[] = array(($info !== '' ? "$type $info" : $type), $report);
        }
    }

    return $errors;
}



function normalize_version($version) {

    $version = trim($version);
    $versionarr = explode(" ",$version);
    if (!empty($versionarr)) {
        $version = $versionarr[0];
    }
    $version = preg_replace('/[^\.\d]/', '.', $version);
    $version = preg_replace('/(\.{2,})/', '.', $version);
    $version = trim($version, '.');

    return $version;
}



function load_environment_xml($env_select=ENV_SELECT_NEWER) {

    global $CFG;

    static $data = array(); 
    if (isset($data[$env_select])) {
        return $data[$env_select];
    }
    $contents = false;

    if (is_numeric($env_select)) {
        $file = $CFG->dataroot.'/environment/environment.xml';
        $internalfile = $CFG->dirroot.'/'.$CFG->admin.'/environment.xml';
        switch ($env_select) {
            case ENV_SELECT_NEWER:
                if (!is_file($file) || !is_readable($file) || filemtime($file) < filemtime($internalfile) ||
                    !$contents = file_get_contents($file)) {
                                        if (!is_file($internalfile) || !is_readable($internalfile) || !$contents = file_get_contents($internalfile)) {
                        $contents = false;
                    }
                }
                break;
            case ENV_SELECT_DATAROOT:
                if (!is_file($file) || !is_readable($file) || !$contents = file_get_contents($file)) {
                    $contents = false;
                }
                break;
            case ENV_SELECT_RELEASE:
                if (!is_file($internalfile) || !is_readable($internalfile) || !$contents = file_get_contents($internalfile)) {
                    $contents = false;
                }
                break;
        }
    } else {
        if ($plugindir = core_component::get_component_directory($env_select)) {
            $pluginfile = "$plugindir/environment.xml";
            if (!is_file($pluginfile) || !is_readable($pluginfile) || !$contents = file_get_contents($pluginfile)) {
                $contents = false;
            }
        }
    }
        if ($contents !== false) {
        $contents = xmlize($contents);
    }

    $data[$env_select] = $contents;

    return $data[$env_select];
}



function get_list_of_environment_versions($contents) {
    $versions = array();

    if (isset($contents['COMPATIBILITY_MATRIX']['#']['MOODLE'])) {
        foreach ($contents['COMPATIBILITY_MATRIX']['#']['MOODLE'] as $version) {
            $versions[] = $version['@']['version'];
        }
    }

    if (isset($contents['COMPATIBILITY_MATRIX']['#']['PLUGIN'])) {
        $versions[] = 'all';
    }

    return $versions;
}



function get_latest_version_available($version, $env_select) {
    if ($env_select != ENV_SELECT_NEWER and $env_select != ENV_SELECT_DATAROOT and $env_select != ENV_SELECT_RELEASE) {
        throw new coding_exception('Incorrect value of $env_select parameter');
    }

    $version = normalize_version($version);

    if (!$contents = load_environment_xml($env_select)) {
        return false;
    }

    if (!$versions = get_list_of_environment_versions($contents)) {
        return false;
    }
    if (in_array($version, $versions)) {
        return $version;
    } else {
        $found_version = false;
                foreach ($versions as $arrversion) {
            if (version_compare($arrversion, $version, '<')) {
                $found_version = $arrversion;
            }
        }
    }

    return $found_version;
}



function get_environment_for_version($version, $env_select) {

    $version = normalize_version($version);

    if (!$contents = load_environment_xml($env_select)) {
        return false;
    }

    if (!$versions = get_list_of_environment_versions($contents)) {
        return false;
    }

                if (!is_numeric($env_select) && in_array('all', $versions)
            && environment_verify_plugin($env_select, $contents['COMPATIBILITY_MATRIX']['#']['PLUGIN'][0])) {
        return $contents['COMPATIBILITY_MATRIX']['#']['PLUGIN'][0];
    }

    if (!in_array($version, $versions)) {
        return false;
    }

    $fl_arr = array_flip($versions);

    return $contents['COMPATIBILITY_MATRIX']['#']['MOODLE'][$fl_arr[$version]];
}


function environment_verify_plugin($plugin, $pluginxml) {
    if (!isset($pluginxml['@']['name']) || $pluginxml['@']['name'] != $plugin) {
        return false;
    }
    return true;
}


function environment_check($version, $env_select) {
    global $CFG;

    if ($env_select != ENV_SELECT_NEWER and $env_select != ENV_SELECT_DATAROOT and $env_select != ENV_SELECT_RELEASE) {
        throw new coding_exception('Incorrect value of $env_select parameter');
    }

    $version = normalize_version($version);

    $results = array(); 
    if (!empty($CFG->version)) {
        $results[] = environment_check_moodle($version, $env_select);
    }
    $results[] = environment_check_unicode($version, $env_select);
    $results[] = environment_check_database($version, $env_select);
    $results[] = environment_check_php($version, $env_select);

    if ($result = environment_check_pcre_unicode($version, $env_select)) {
        $results[] = $result;
    }

    $phpext_results = environment_check_php_extensions($version, $env_select);
    $results = array_merge($results, $phpext_results);

    $phpsetting_results = environment_check_php_settings($version, $env_select);
    $results = array_merge($results, $phpsetting_results);

    $custom_results = environment_custom_checks($version, $env_select);
    $results = array_merge($results, $custom_results);

            foreach (core_component::get_plugin_types() as $plugintype => $unused) {
        foreach (core_component::get_plugin_list_with_file($plugintype, 'environment.xml') as $pluginname => $unused) {
            $plugin = $plugintype . '_' . $pluginname;

            $result = environment_check_database($version, $plugin);
            if ($result->error_code != NO_VERSION_DATA_FOUND
                and $result->error_code != NO_DATABASE_SECTION_FOUND
                and $result->error_code != NO_DATABASE_VENDORS_FOUND) {

                $result->plugin = $plugin;
                $results[] = $result;
            }

            $result = environment_check_php($version, $plugin);
            if ($result->error_code != NO_VERSION_DATA_FOUND
                and $result->error_code != NO_PHP_SECTION_FOUND
                and $result->error_code != NO_PHP_VERSION_FOUND) {

                $result->plugin = $plugin;
                $results[] = $result;
            }

            $pluginresults = environment_check_php_extensions($version, $plugin);
            foreach ($pluginresults as $result) {
                if ($result->error_code != NO_VERSION_DATA_FOUND
                    and $result->error_code != NO_PHP_EXTENSIONS_SECTION_FOUND) {

                    $result->plugin = $plugin;
                    $results[] = $result;
                }
            }

            $pluginresults = environment_check_php_settings($version, $plugin);
            foreach ($pluginresults as $result) {
                if ($result->error_code != NO_VERSION_DATA_FOUND) {
                    $result->plugin = $plugin;
                    $results[] = $result;
                }
            }

            $pluginresults = environment_custom_checks($version, $plugin);
            foreach ($pluginresults as $result) {
                if ($result->error_code != NO_VERSION_DATA_FOUND) {
                    $result->plugin = $plugin;
                    $results[] = $result;
                }
            }
        }
    }

    return $results;
}



function environment_check_php_extensions($version, $env_select) {

    $results = array();

    if (!$data = get_environment_for_version($version, $env_select)) {
            $result = new environment_results('php_extension');
        $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return array($result);
    }

    if (!isset($data['#']['PHP_EXTENSIONS']['0']['#']['PHP_EXTENSION'])) {
            $result = new environment_results('php_extension');
        $result->setStatus(false);
        $result->setErrorCode(NO_PHP_EXTENSIONS_SECTION_FOUND);
        return array($result);
    }
    foreach($data['#']['PHP_EXTENSIONS']['0']['#']['PHP_EXTENSION'] as $extension) {
        $result = new environment_results('php_extension');
            $level = get_level($extension);
            if (!isset($extension['@']['name'])) {
            $result->setStatus(false);
            $result->setErrorCode(NO_PHP_EXTENSIONS_NAME_FOUND);
        } else {
            $extension_name = $extension['@']['name'];
                    if (!extension_loaded($extension_name)) {
                $result->setStatus(false);
            } else {
                $result->setStatus(true);
            }
            $result->setLevel($level);
            $result->setInfo($extension_name);
        }

            process_environment_result($extension, $result);

            $results[] = $result;
    }


    return $results;
}


function environment_check_php_settings($version, $env_select) {

    $results = array();

    if (!$data = get_environment_for_version($version, $env_select)) {
            $result = new environment_results('php_setting');
        $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        $results[] = $result;
        return $results;
    }

    if (!isset($data['#']['PHP_SETTINGS']['0']['#']['PHP_SETTING'])) {
            return $results;
    }
    foreach($data['#']['PHP_SETTINGS']['0']['#']['PHP_SETTING'] as $setting) {
        $result = new environment_results('php_setting');
            $level = get_level($setting);
        $result->setLevel($level);
            if (!isset($setting['@']['name'])) {
            $result->setStatus(false);
            $result->setErrorCode(NO_PHP_SETTINGS_NAME_FOUND);
        } else {
            $setting_name  = $setting['@']['name'];
            $setting_value = $setting['@']['value'];
            $result->setInfo($setting_name);

            if ($setting_name == 'memory_limit') {
                $current = ini_get('memory_limit');
                if ($current == -1) {
                    $result->setStatus(true);
                } else {
                    $current  = get_real_size($current);
                    $minlimit = get_real_size($setting_value);
                    if ($current < $minlimit) {
                        @ini_set('memory_limit', $setting_value);
                        $current = ini_get('memory_limit');
                        $current = get_real_size($current);
                    }
                    $result->setStatus($current >= $minlimit);
                }

            } else {
                $current = ini_get_bool($setting_name);
                            if ($current == $setting_value) {
                    $result->setStatus(true);
                } else {
                    $result->setStatus(false);
                }
            }
        }

            process_environment_result($setting, $result);

            $results[] = $result;
    }


    return $results;
}


function environment_custom_checks($version, $env_select) {
    global $CFG;

    $results = array();

    $release = isset($CFG->release) ? $CFG->release : $version;     $current_version = normalize_version($release);

    if (!$data = get_environment_for_version($version, $env_select)) {
            return $results;
    }

    if (!isset($data['#']['CUSTOM_CHECKS']['0']['#']['CUSTOM_CHECK'])) {
            return $results;
    }

    foreach($data['#']['CUSTOM_CHECKS']['0']['#']['CUSTOM_CHECK'] as $check) {
        $result = new environment_results('custom_check');

            $level = get_level($check);

            if (isset($check['@']['function'])) {
            $function = $check['@']['function'];
            $file = null;
            if (isset($check['@']['file'])) {
                $file = $CFG->dirroot . '/' . $check['@']['file'];
                if (is_readable($file)) {
                    include_once($file);
                }
            }

            if (is_callable($function)) {
                $result->setLevel($level);
                $result->setInfo($function);
                $result = call_user_func($function, $result);
            } else if (!$file or is_readable($file)) {
                                                                if (version_compare($current_version, $version, '>=')) {
                    $result->setStatus(false);
                    $result->setInfo($function);
                    $result->setErrorCode(CUSTOM_CHECK_FUNCTION_MISSING);
                } else {
                    $result = null;
                }
            } else {
                                                                if (version_compare($current_version, $version, '>=')) {
                    $result->setStatus(false);
                    $result->setInfo($function);
                    $result->setErrorCode(CUSTOM_CHECK_FILE_MISSING);
                } else {
                    $result = null;
                }
            }
        } else {
            $result->setStatus(false);
            $result->setErrorCode(NO_CUSTOM_CHECK_FOUND);
        }

        if (!is_null($result)) {
                    process_environment_result($check, $result);

                    $results[] = $result;
        }
    }

    return $results;
}


function environment_check_moodle($version, $env_select) {

    $result = new environment_results('moodle');

    if (!$data = get_environment_for_version($version, $env_select)) {
            $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return $result;
    }

    if (!isset($data['@']['requires'])) {
        $needed_version = '1.0';     } else {
            $needed_version = $data['@']['requires'];
    }

    $release = get_config('', 'release');
    $current_version = normalize_version($release);
    if (strpos($release, 'dev') !== false) {
                $current_version = $current_version - 0.1;
    }

    if (version_compare($current_version, $needed_version, '>=')) {
        $result->setStatus(true);
    } else {
        $result->setStatus(false);
    }
    $result->setLevel('required');
    $result->setCurrentVersion($release);
    $result->setNeededVersion($needed_version);

    return $result;
}


function environment_check_php($version, $env_select) {

    $result = new environment_results('php');

    if (!$data = get_environment_for_version($version, $env_select)) {
            $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return $result;
    }

    if (!isset($data['#']['PHP'])) {
            $result->setStatus(false);
        $result->setErrorCode(NO_PHP_SECTION_FOUND);
        return $result;
    } else {
            $level = get_level($data['#']['PHP']['0']);
        if (!isset($data['#']['PHP']['0']['@']['version'])) {
            $result->setStatus(false);
            $result->setErrorCode(NO_PHP_VERSION_FOUND);
            return $result;
        } else {
            $needed_version = $data['#']['PHP']['0']['@']['version'];
        }
    }

    $current_version = normalize_version(phpversion());

    if (version_compare($current_version, $needed_version, '>=')) {
        $result->setStatus(true);
    } else {
        $result->setStatus(false);
    }
    $result->setLevel($level);
    $result->setCurrentVersion($current_version);
    $result->setNeededVersion($needed_version);

    process_environment_result($data['#']['PHP'][0], $result);

    return $result;
}


function environment_check_pcre_unicode($version, $env_select) {
    $result = new environment_results('pcreunicode');

        if (!$data = get_environment_for_version($version, $env_select)) {
                $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return $result;
    }

    if (!isset($data['#']['PCREUNICODE'])) {
        return null;
    }

    $level = get_level($data['#']['PCREUNICODE']['0']);
    $result->setLevel($level);

    if (!function_exists('preg_match')) {
                return null;

    } else if (@preg_match('/\pL/u', 'a') and @preg_match('/á/iu', 'Á')) {
        $result->setStatus(true);

    } else {
        $result->setStatus(false);
    }

        process_environment_result($data['#']['PCREUNICODE'][0], $result);

    return $result;
}


function environment_check_unicode($version, $env_select) {
    global $DB;

    $result = new environment_results('unicode');

        if (!$data = get_environment_for_version($version, $env_select)) {
            $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return $result;
    }

    
    if (!isset($data['#']['UNICODE'])) {
            $result->setStatus(false);
        $result->setErrorCode(NO_UNICODE_SECTION_FOUND);
        return $result;
    } else {
            $level = get_level($data['#']['UNICODE']['0']);
    }

    if (!$unicodedb = $DB->setup_is_unicodedb()) {
        $result->setStatus(false);
    } else {
        $result->setStatus(true);
    }

    $result->setLevel($level);

    process_environment_result($data['#']['UNICODE'][0], $result);

    return $result;
}


function environment_check_database($version, $env_select) {

    global $DB;

    $result = new environment_results('database');

    $vendors = array();  
    if (!$data = get_environment_for_version($version, $env_select)) {
            $result->setStatus(false);
        $result->setErrorCode(NO_VERSION_DATA_FOUND);
        return $result;
    }

    if (!isset($data['#']['DATABASE'])) {
            $result->setStatus(false);
        $result->setErrorCode(NO_DATABASE_SECTION_FOUND);
        return $result;
    } else {
            $level = get_level($data['#']['DATABASE']['0']);
    }

    if (!isset($data['#']['DATABASE']['0']['#']['VENDOR'])) {
            $result->setStatus(false);
        $result->setErrorCode(NO_DATABASE_VENDORS_FOUND);
        return $result;
    } else {
            foreach ($data['#']['DATABASE']['0']['#']['VENDOR'] as $vendor) {
            if (isset($vendor['@']['name']) && isset($vendor['@']['version'])) {
                $vendors[$vendor['@']['name']] = $vendor['@']['version'];
                $vendorsxml[$vendor['@']['name']] = $vendor;
            }
        }
    }
    if (empty($vendors['mysql'])) {
        $result->setStatus(false);
        $result->setErrorCode(NO_DATABASE_VENDOR_MYSQL_FOUND);
        return $result;
    }
    if (empty($vendors['postgres'])) {
        $result->setStatus(false);
        $result->setErrorCode(NO_DATABASE_VENDOR_POSTGRES_FOUND);
        return $result;
    }

    $current_vendor = $DB->get_dbvendor();

    $dbinfo = $DB->get_server_info();
    $current_version = normalize_version($dbinfo['version']);
    $needed_version = $vendors[$current_vendor];

    if (!$needed_version) {
        $result->setStatus(false);
        $result->setErrorCode(NO_DATABASE_VENDOR_VERSION_FOUND);
        return $result;
    }

    if (version_compare($current_version, $needed_version, '>=')) {
        $result->setStatus(true);
    } else {
        $result->setStatus(false);
    }
    $result->setLevel($level);
    $result->setCurrentVersion($current_version);
    $result->setNeededVersion($needed_version);
    $result->setInfo($current_vendor . ' (' . $dbinfo['description'] . ')');

    process_environment_result($vendorsxml[$current_vendor], $result);

    return $result;

}


function process_environment_bypass($xml, &$result) {

    if ($result->getStatus() || $result->getLevel() == 'optional') {
        return;
    }

    if (is_array($xml['#']) && isset($xml['#']['BYPASS'][0]['@']['function']) && isset($xml['#']['BYPASS'][0]['@']['message'])) {
        $function = $xml['#']['BYPASS'][0]['@']['function'];
        $message  = $xml['#']['BYPASS'][0]['@']['message'];
            if (function_exists($function)) {
                    if ($function($result)) {
                            if (empty($result->getBypassStr)) {
                    if (isset($xml['#']['BYPASS'][0]['@']['plugin'])) {
                        $result->setBypassStr(array($message, $xml['#']['BYPASS'][0]['@']['plugin']));
                    } else {
                        $result->setBypassStr($message);
                    }
                }
            }
        }
    }
}


function process_environment_restrict($xml, &$result) {

    if (!$result->getStatus() || $result->getLevel() == 'optional') {
        return;
    }
    if (is_array($xml['#']) && isset($xml['#']['RESTRICT'][0]['@']['function']) && isset($xml['#']['RESTRICT'][0]['@']['message'])) {
        $function = $xml['#']['RESTRICT'][0]['@']['function'];
        $message  = $xml['#']['RESTRICT'][0]['@']['message'];
            if (function_exists($function)) {
                    if ($function($result)) {
                            if (empty($result->getRestrictStr)) {
                    if (isset($xml['#']['RESTRICT'][0]['@']['plugin'])) {
                        $result->setRestrictStr(array($message, $xml['#']['RESTRICT'][0]['@']['plugin']));
                    } else {
                        $result->setRestrictStr($message);
                    }
                }
            }
        }
    }
}


function process_environment_messages($xml, &$result) {

    if (is_array($xml['#']) && isset($xml['#']['FEEDBACK'][0]['#'])) {
        $feedbackxml = $xml['#']['FEEDBACK'][0]['#'];

                if ($result->getLevel() == 'required' and isset($feedbackxml['ON_CHECK'])) {
            $result->setStatus(false);
            $result->setErrorCode(INCORRECT_FEEDBACK_FOR_REQUIRED);
        } else if ($result->getLevel() == 'optional' and isset($feedbackxml['ON_ERROR'])) {
            $result->setStatus(false);
            $result->setErrorCode(INCORRECT_FEEDBACK_FOR_OPTIONAL);
        }

        if (!$result->status and $result->getLevel() == 'required') {
            if (isset($feedbackxml['ON_ERROR'][0]['@']['message'])) {
                if (isset($feedbackxml['ON_ERROR'][0]['@']['plugin'])) {
                    $result->setFeedbackStr(array($feedbackxml['ON_ERROR'][0]['@']['message'], $feedbackxml['ON_ERROR'][0]['@']['plugin']));
                } else {
                    $result->setFeedbackStr($feedbackxml['ON_ERROR'][0]['@']['message']);
                }
            }
        } else if (!$result->status and $result->getLevel() == 'optional') {
            if (isset($feedbackxml['ON_CHECK'][0]['@']['message'])) {
                if (isset($feedbackxml['ON_CHECK'][0]['@']['plugin'])) {
                    $result->setFeedbackStr(array($feedbackxml['ON_CHECK'][0]['@']['message'], $feedbackxml['ON_CHECK'][0]['@']['plugin']));
                } else {
                    $result->setFeedbackStr($feedbackxml['ON_CHECK'][0]['@']['message']);
                }
            }
        } else {
            if (isset($feedbackxml['ON_OK'][0]['@']['message'])) {
                if (isset($feedbackxml['ON_OK'][0]['@']['plugin'])) {
                    $result->setFeedbackStr(array($feedbackxml['ON_OK'][0]['@']['message'], $feedbackxml['ON_OK'][0]['@']['plugin']));
                } else {
                    $result->setFeedbackStr($feedbackxml['ON_OK'][0]['@']['message']);
                }
            }
        }
    }
}





class environment_results {
    
    var $part;
    
    var $status;
    
    var $error_code;
    
    var $level;
    
    var $current_version;
    
    var $needed_version;
    
    var $info;
    
    var $feedback_str;
    
    var $bypass_str;
    
    var $restrict_str;
    
    var $plugin = null;
    
    public function __construct($part) {
        $this->part=$part;
        $this->status=false;
        $this->error_code=NO_ERROR;
        $this->level='required';
        $this->current_version='';
        $this->needed_version='';
        $this->info='';
        $this->feedback_str='';
        $this->bypass_str='';
        $this->restrict_str='';
    }

    
    public function environment_results($part) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($part);
    }

    
    function setStatus($testpassed) {
        $this->status = $testpassed;
        if ($testpassed) {
            $this->setErrorCode(NO_ERROR);
        }
    }

    
    function setErrorCode($error_code) {
        $this->error_code=$error_code;
    }

    
    function setLevel($level) {
        $this->level=$level;
    }

    
    function setCurrentVersion($current_version) {
        $this->current_version=$current_version;
    }

    
    function setNeededVersion($needed_version) {
        $this->needed_version=$needed_version;
    }

    
    function setInfo($info) {
        $this->info=$info;
    }

    
    function setFeedbackStr($str) {
        $this->feedback_str=$str;
    }


    
    function setBypassStr($str) {
        $this->bypass_str=$str;
    }

    
    function setRestrictStr($str) {
        $this->restrict_str=$str;
    }

    
    function getStatus() {
        return $this->status;
    }

    
    function getErrorCode() {
        return $this->error_code;
    }

    
    function getLevel() {
        return $this->level;
    }

    
    function getCurrentVersion() {
        return $this->current_version;
    }

    
    function getNeededVersion() {
        return $this->needed_version;
    }

    
    function getInfo() {
        return $this->info;
    }

    
    function getPart() {
        return $this->part;
    }

    
    function getFeedbackStr() {
        return $this->feedback_str;
    }

    
    function getBypassStr() {
        return $this->bypass_str;
    }

    
    function getRestrictStr() {
        return $this->restrict_str;
    }

    
    function strToReport($string, $class){
        if (!empty($string)){
            if (is_array($string)){
                $str = call_user_func_array('get_string', $string);
            } else {
                $str = get_string($string, 'admin');
            }
            return '<p class="'.$class.'">'.$str.'</p>';
        } else {
            return '';
        }
    }

    
    function getPluginName() {
        if ($this->plugin) {
            $manager = core_plugin_manager::instance();
            list($plugintype, $pluginname) = core_component::normalize_component($this->plugin);
            return $manager->plugintype_name($plugintype) . ' / ' . $manager->plugin_name($this->plugin);
        } else {
            return '';
        }
    }
}



function get_level($element) {
    $level = 'required';
    if (isset($element['@']['level'])) {
        $level = $element['@']['level'];
        if (!in_array($level, array('required', 'optional'))) {
            debugging('The level of a check in the environment.xml file must be "required" or "optional".', DEBUG_DEVELOPER);
            $level = 'required';
        }
    } else {
        debugging('Checks in the environment.xml file must have a level="required" or level="optional" attribute.', DEBUG_DEVELOPER);
    }
    return $level;
}


function process_environment_result($element, &$result) {
    process_environment_messages($element, $result);
    process_environment_bypass($element, $result);
    process_environment_restrict($element, $result);
}


function restrict_php_version_7(&$result) {
    return restrict_php_version($result, '7');
}


function restrict_php_version(&$result, $version) {

        $currentversion = normalize_version(phpversion());

        if (version_compare($currentversion, $version, '<')) {
                return false;
    } else {
                return true;
    }
}


function restrict_php_version_71(&$result) {
    return restrict_php_version($result, '7.1');
}
