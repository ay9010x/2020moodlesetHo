<?php



defined('MOODLE_INTERNAL') || die();



define('YEARSECS', 31536000);


define('WEEKSECS', 604800);


define('DAYSECS', 86400);


define('HOURSECS', 3600);


define('MINSECS', 60);


define('DAYMINS', 1440);


define('HOURMINS', 60);



define('PARAM_ALPHA',    'alpha');


define('PARAM_ALPHAEXT', 'alphaext');


define('PARAM_ALPHANUM', 'alphanum');


define('PARAM_ALPHANUMEXT', 'alphanumext');


define('PARAM_AUTH',  'auth');


define('PARAM_BASE64',   'base64');


define('PARAM_BOOL',     'bool');


define('PARAM_CAPABILITY',   'capability');


define('PARAM_CLEANHTML', 'cleanhtml');


define('PARAM_EMAIL',   'email');


define('PARAM_FILE',   'file');


define('PARAM_FLOAT',  'float');


define('PARAM_HOST',     'host');


define('PARAM_INT',      'int');


define('PARAM_LANG',  'lang');


define('PARAM_LOCALURL', 'localurl');


define('PARAM_NOTAGS',   'notags');


define('PARAM_PATH',     'path');


define('PARAM_PEM',      'pem');


define('PARAM_PERMISSION',   'permission');


define('PARAM_RAW', 'raw');


define('PARAM_RAW_TRIMMED', 'raw_trimmed');


define('PARAM_SAFEDIR',  'safedir');


define('PARAM_SAFEPATH',  'safepath');


define('PARAM_SEQUENCE',  'sequence');


define('PARAM_TAG',   'tag');


define('PARAM_TAGLIST',   'taglist');


define('PARAM_TEXT',  'text');


define('PARAM_THEME',  'theme');


define('PARAM_URL',      'url');


define('PARAM_USERNAME',    'username');


define('PARAM_STRINGID',    'stringid');


define('PARAM_CLEAN',    'clean');


define('PARAM_INTEGER',  'int');


define('PARAM_NUMBER',  'float');


define('PARAM_ACTION',   'alphanumext');


define('PARAM_FORMAT',   'alphanumext');


define('PARAM_MULTILANG',  'text');


define('PARAM_TIMEZONE', 'timezone');


define('PARAM_CLEANFILE', 'file');


define('PARAM_COMPONENT', 'component');


define('PARAM_AREA', 'area');


define('PARAM_PLUGIN', 'plugin');




define('VALUE_REQUIRED', 1);


define('VALUE_OPTIONAL', 2);


define('VALUE_DEFAULT', 0);


define('NULL_NOT_ALLOWED', false);


define('NULL_ALLOWED', true);



define('PAGE_COURSE_VIEW', 'course-view');


define('GETREMOTEADDR_SKIP_HTTP_CLIENT_IP', '1');

define('GETREMOTEADDR_SKIP_HTTP_X_FORWARDED_FOR', '2');

define ('BLOG_USER_LEVEL', 1);
define ('BLOG_GROUP_LEVEL', 2);
define ('BLOG_COURSE_LEVEL', 3);
define ('BLOG_SITE_LEVEL', 4);
define ('BLOG_GLOBAL_LEVEL', 5);



define('TAG_MAX_LENGTH', 50);

define ('PASSWORD_LOWER', 'abcdefghijklmnopqrstuvwxyz');
define ('PASSWORD_UPPER', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define ('PASSWORD_DIGITS', '0123456789');
define ('PASSWORD_NONALPHANUM', '.,;:!?_-+/*@#&$');



define('FEATURE_GRADE_HAS_GRADE', 'grade_has_grade');

define('FEATURE_GRADE_OUTCOMES', 'outcomes');

define('FEATURE_ADVANCED_GRADING', 'grade_advanced_grading');

define('FEATURE_CONTROLS_GRADE_VISIBILITY', 'controlsgradevisbility');

define('FEATURE_PLAGIARISM', 'plagiarism');


define('FEATURE_COMPLETION_TRACKS_VIEWS', 'completion_tracks_views');

define('FEATURE_COMPLETION_HAS_RULES', 'completion_has_rules');


define('FEATURE_NO_VIEW_LINK', 'viewlink');

define('FEATURE_IDNUMBER', 'idnumber');

define('FEATURE_GROUPS', 'groups');

define('FEATURE_GROUPINGS', 'groupings');

define('FEATURE_GROUPMEMBERSONLY', 'groupmembersonly');


define('FEATURE_MOD_ARCHETYPE', 'mod_archetype');

define('FEATURE_MOD_INTRO', 'mod_intro');

define('FEATURE_MODEDIT_DEFAULT_COMPLETION', 'modedit_default_completion');

define('FEATURE_COMMENT', 'comment');

define('FEATURE_RATE', 'rate');

define('FEATURE_BACKUP_MOODLE2', 'backup_moodle2');


define('FEATURE_SHOW_DESCRIPTION', 'showdescription');


define('FEATURE_USES_QUESTIONS', 'usesquestions');


define('MOD_ARCHETYPE_OTHER', 0);

define('MOD_ARCHETYPE_RESOURCE', 1);

define('MOD_ARCHETYPE_ASSIGNMENT', 2);

define('MOD_ARCHETYPE_SYSTEM', 3);


define('MOD_SUBTYPE_NO_CHILDREN', 'modsubtypenochildren');


define('EXTERNAL_TOKEN_PERMANENT', 0);


define('EXTERNAL_TOKEN_EMBEDDED', 1);


define('HOMEPAGE_SITE', 0);

define('HOMEPAGE_MY', 1);

define('HOMEPAGE_USER', 2);


define('HUB_HUBDIRECTORYURL', "http://hubdirectory.moodle.com.tw");



define('HUB_MOODLEORGHUBURL', "http://hub.moodle.com.tw");


define('MOODLE_OFFICIAL_MOBILE_SERVICE', 'moodle_mobile_app');


define('MODSET_OFFICIAL_SERVICE', 'moodleset_api');


define('USER_CAN_IGNORE_FILE_SIZE_LIMITS', -1);


define('COURSE_DISPLAY_SINGLEPAGE', 0);

define('COURSE_DISPLAY_MULTIPAGE', 1);


define('AUTH_PASSWORD_NOT_CACHED', 'not cached');



function required_param($parname, $type) {
    if (func_num_args() != 2 or empty($parname) or empty($type)) {
        throw new coding_exception('required_param() requires $parname and $type to be specified (parameter: '.$parname.')');
    }
        if (isset($_POST[$parname])) {
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        print_error('missingparam', '', '', $parname);
    }

    if (is_array($param)) {
        debugging('Invalid array parameter detected in required_param(): '.$parname);
                return required_param_array($parname, $type);
    }

    return clean_param($param, $type);
}


function required_param_array($parname, $type) {
    if (func_num_args() != 2 or empty($parname) or empty($type)) {
        throw new coding_exception('required_param_array() requires $parname and $type to be specified (parameter: '.$parname.')');
    }
        if (isset($_POST[$parname])) {
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        print_error('missingparam', '', '', $parname);
    }
    if (!is_array($param)) {
        print_error('missingparam', '', '', $parname);
    }

    $result = array();
    foreach ($param as $key => $value) {
        if (!preg_match('/^[a-z0-9_-]+$/i', $key)) {
            debugging('Invalid key name in required_param_array() detected: '.$key.', parameter: '.$parname);
            continue;
        }
        $result[$key] = clean_param($value, $type);
    }

    return $result;
}


function optional_param($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param requires $parname, $default + $type to be specified (parameter: '.$parname.')');
    }

        if (isset($_POST[$parname])) {
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }

    if (is_array($param)) {
        debugging('Invalid array parameter detected in required_param(): '.$parname);
                return optional_param_array($parname, $default, $type);
    }

    return clean_param($param, $type);
}


function optional_param_array($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param_array requires $parname, $default + $type to be specified (parameter: '.$parname.')');
    }

        if (isset($_POST[$parname])) {
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }
    if (!is_array($param)) {
        debugging('optional_param_array() expects array parameters only: '.$parname);
        return $default;
    }

    $result = array();
    foreach ($param as $key => $value) {
        if (!preg_match('/^[a-z0-9_-]+$/i', $key)) {
            debugging('Invalid key name in optional_param_array() detected: '.$key.', parameter: '.$parname);
            continue;
        }
        $result[$key] = clean_param($value, $type);
    }

    return $result;
}


function validate_param($param, $type, $allownull=NULL_NOT_ALLOWED, $debuginfo='') {
    if (is_null($param)) {
        if ($allownull == NULL_ALLOWED) {
            return null;
        } else {
            throw new invalid_parameter_exception($debuginfo);
        }
    }
    if (is_array($param) or is_object($param)) {
        throw new invalid_parameter_exception($debuginfo);
    }

    $cleaned = clean_param($param, $type);

    if ($type == PARAM_FLOAT) {
                if (is_float($param) or is_int($param)) {
                    } else if (!is_numeric($param) or !preg_match('/^[\+-]?[0-9]*\.?[0-9]*(e[-+]?[0-9]+)?$/i', (string)$param)) {
            throw new invalid_parameter_exception($debuginfo);
        }
    } else if ((string)$param !== (string)$cleaned) {
                throw new invalid_parameter_exception($debuginfo);
    }

    return $cleaned;
}


function clean_param_array(array $param = null, $type, $recursive = false) {
        $param = (array)$param;
    foreach ($param as $key => $value) {
        if (is_array($value)) {
            if ($recursive) {
                $param[$key] = clean_param_array($value, $type, true);
            } else {
                throw new coding_exception('clean_param_array can not process multidimensional arrays when $recursive is false.');
            }
        } else {
            $param[$key] = clean_param($value, $type);
        }
    }
    return $param;
}


function clean_param($param, $type) {
    global $CFG;

    if (is_array($param)) {
        throw new coding_exception('clean_param() can not process arrays, please use clean_param_array() instead.');
    } else if (is_object($param)) {
        if (method_exists($param, '__toString')) {
            $param = $param->__toString();
        } else {
            throw new coding_exception('clean_param() can not process objects, please use clean_param_array() instead.');
        }
    }

    switch ($type) {
        case PARAM_RAW:
                        $param = fix_utf8($param);
            return $param;

        case PARAM_RAW_TRIMMED:
                        $param = fix_utf8($param);
            return trim($param);

        case PARAM_CLEAN:
                                    if (is_numeric($param)) {
                return $param;
            }
            $param = fix_utf8($param);
                        return clean_text($param);

        case PARAM_CLEANHTML:
                        $param = fix_utf8($param);
                        $param = clean_text($param, FORMAT_HTML);
            return trim($param);

        case PARAM_INT:
                        return (int)$param;

        case PARAM_FLOAT:
                        return (float)$param;

        case PARAM_ALPHA:
                        return preg_replace('/[^a-zA-Z]/i', '', $param);

        case PARAM_ALPHAEXT:
                        return preg_replace('/[^a-zA-Z_-]/i', '', $param);

        case PARAM_ALPHANUM:
                        return preg_replace('/[^A-Za-z0-9]/i', '', $param);

        case PARAM_ALPHANUMEXT:
                        return preg_replace('/[^A-Za-z0-9_-]/i', '', $param);

        case PARAM_SEQUENCE:
                        return preg_replace('/[^0-9,]/i', '', $param);

        case PARAM_BOOL:
                        $tempstr = strtolower($param);
            if ($tempstr === 'on' or $tempstr === 'yes' or $tempstr === 'true') {
                $param = 1;
            } else if ($tempstr === 'off' or $tempstr === 'no'  or $tempstr === 'false') {
                $param = 0;
            } else {
                $param = empty($param) ? 0 : 1;
            }
            return $param;

        case PARAM_NOTAGS:
                        $param = fix_utf8($param);
            return strip_tags($param);

        case PARAM_TEXT:
                        $param = fix_utf8($param);
                                    do {
                if (strpos($param, '</lang>') !== false) {
                                        $param = strip_tags($param, '<lang>');
                    if (!preg_match_all('/<.*>/suU', $param, $matches)) {
                        break;
                    }
                    $open = false;
                    foreach ($matches[0] as $match) {
                        if ($match === '</lang>') {
                            if ($open) {
                                $open = false;
                                continue;
                            } else {
                                break 2;
                            }
                        }
                        if (!preg_match('/^<lang lang="[a-zA-Z0-9_-]+"\s*>$/u', $match)) {
                            break 2;
                        } else {
                            $open = true;
                        }
                    }
                    if ($open) {
                        break;
                    }
                    return $param;

                } else if (strpos($param, '</span>') !== false) {
                                        $param = strip_tags($param, '<span>');
                    if (!preg_match_all('/<.*>/suU', $param, $matches)) {
                        break;
                    }
                    $open = false;
                    foreach ($matches[0] as $match) {
                        if ($match === '</span>') {
                            if ($open) {
                                $open = false;
                                continue;
                            } else {
                                break 2;
                            }
                        }
                        if (!preg_match('/^<span(\s+lang="[a-zA-Z0-9_-]+"|\s+class="multilang"){2}\s*>$/u', $match)) {
                            break 2;
                        } else {
                            $open = true;
                        }
                    }
                    if ($open) {
                        break;
                    }
                    return $param;
                }
            } while (false);
                        return strip_tags($param);

        case PARAM_COMPONENT:
                                    if (!preg_match('/^[a-z]+(_[a-z][a-z0-9_]*)?[a-z0-9]+$/', $param)) {
                return '';
            }
            if (strpos($param, '__') !== false) {
                return '';
            }
            if (strpos($param, 'mod_') === 0) {
                                if (substr_count($param, '_') != 1) {
                    return '';
                }
            }
            return $param;

        case PARAM_PLUGIN:
        case PARAM_AREA:
                        if (!is_valid_plugin_name($param)) {
                return '';
            }
            return $param;

        case PARAM_SAFEDIR:
                        return preg_replace('/[^a-zA-Z0-9_-]/i', '', $param);

        case PARAM_SAFEPATH:
                        return preg_replace('/[^a-zA-Z0-9\/_-]/i', '', $param);

        case PARAM_FILE:
                        $param = fix_utf8($param);
            $param = preg_replace('~[[:cntrl:]]|[&<>"`\|\':\\\\/]~u', '', $param);
            if ($param === '.' || $param === '..') {
                $param = '';
            }
            return $param;

        case PARAM_PATH:
                        $param = fix_utf8($param);
            $param = str_replace('\\', '/', $param);

                        $breadcrumb = explode('/', $param);
            foreach ($breadcrumb as $key => $crumb) {
                if ($crumb === '.' && $key === 0) {
                                    } else {
                    $crumb = clean_param($crumb, PARAM_FILE);
                }
                $breadcrumb[$key] = $crumb;
            }
            $param = implode('/', $breadcrumb);

                        $param = preg_replace('~//+~', '/', $param);
            $param = preg_replace('~/(\./)+~', '/', $param);
            return $param;

        case PARAM_HOST:
                        $param = preg_replace('/[^\.\d\w-]/', '', $param );
                        if (preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/', $param, $match)) {
                                if ( $match[0] > 255
                     || $match[1] > 255
                     || $match[3] > 255
                     || $match[4] > 255 ) {
                                        $param = '';
                }
            } else if ( preg_match('/^[\w\d\.-]+$/', $param)                        && !preg_match('/^[\.-]/',  $param)                        && !preg_match('/[\.-]$/',  $param)                        ) {
                            } else {
                                $param='';
            }
            return $param;

        case PARAM_URL:                      $param = fix_utf8($param);
            include_once($CFG->dirroot . '/lib/validateurlsyntax.php');
            if (!empty($param) && validateUrlSyntax($param, 's?H?S?F?E?u-P-a?I?p?f?q?r?')) {
                            } else {
                                $param ='';
            }
            return $param;

        case PARAM_LOCALURL:
                        $param = clean_param($param, PARAM_URL);
            if (!empty($param)) {

                                $httpswwwroot = str_replace('http://', 'https://', $CFG->wwwroot);

                if ($param === $CFG->wwwroot) {
                                    } else if (!empty($CFG->loginhttps) && $param === $httpswwwroot) {
                                    } else if (preg_match(':^/:', $param)) {
                                    } else if (preg_match('/^' . preg_quote($CFG->wwwroot . '/', '/') . '/i', $param)) {
                                    } else if (!empty($CFG->loginhttps) && preg_match('/^' . preg_quote($httpswwwroot . '/', '/') . '/i', $param)) {
                                    } else {
                                        if (validateUrlSyntax('/' . $param, 's-u-P-a-p-f+q?r?')) {
                                            } else {
                        $param = '';
                    }
                }
            }
            return $param;

        case PARAM_PEM:
            $param = trim($param);
                                                                        if (preg_match('/^-----BEGIN CERTIFICATE-----([\s\w\/\+=]+)-----END CERTIFICATE-----$/', trim($param), $matches)) {
                list($wholething, $body) = $matches;
                unset($wholething, $matches);
                $b64 = clean_param($body, PARAM_BASE64);
                if (!empty($b64)) {
                    return "-----BEGIN CERTIFICATE-----\n$b64\n-----END CERTIFICATE-----\n";
                } else {
                    return '';
                }
            }
            return '';

        case PARAM_BASE64:
            if (!empty($param)) {
                                                                                if (0 >= preg_match('/^([\s\w\/\+=]+)$/', trim($param))) {
                    return '';
                }
                $lines = preg_split('/[\s]+/', $param, -1, PREG_SPLIT_NO_EMPTY);
                                                for ($i=0, $j=count($lines); $i < $j; $i++) {
                    if ($i + 1 == $j) {
                        if (64 < strlen($lines[$i])) {
                            return '';
                        }
                        continue;
                    }

                    if (64 != strlen($lines[$i])) {
                        return '';
                    }
                }
                return implode("\n", $lines);
            } else {
                return '';
            }

        case PARAM_TAG:
            $param = fix_utf8($param);
                                                $param = preg_replace('~[[:cntrl:]]|[<>`]~u', '', $param);
                        $param = preg_replace('/\s+/u', ' ', $param);
            $param = core_text::substr(trim($param), 0, TAG_MAX_LENGTH);
            return $param;

        case PARAM_TAGLIST:
            $param = fix_utf8($param);
            $tags = explode(',', $param);
            $result = array();
            foreach ($tags as $tag) {
                $res = clean_param($tag, PARAM_TAG);
                if ($res !== '') {
                    $result[] = $res;
                }
            }
            if ($result) {
                return implode(',', $result);
            } else {
                return '';
            }

        case PARAM_CAPABILITY:
            if (get_capability_info($param)) {
                return $param;
            } else {
                return '';
            }

        case PARAM_PERMISSION:
            $param = (int)$param;
            if (in_array($param, array(CAP_INHERIT, CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT))) {
                return $param;
            } else {
                return CAP_INHERIT;
            }

        case PARAM_AUTH:
            $param = clean_param($param, PARAM_PLUGIN);
            if (empty($param)) {
                return '';
            } else if (exists_auth_plugin($param)) {
                return $param;
            } else {
                return '';
            }

        case PARAM_LANG:
            $param = clean_param($param, PARAM_SAFEDIR);
            if (get_string_manager()->translation_exists($param)) {
                return $param;
            } else {
                                return '';
            }

        case PARAM_THEME:
            $param = clean_param($param, PARAM_PLUGIN);
            if (empty($param)) {
                return '';
            } else if (file_exists("$CFG->dirroot/theme/$param/config.php")) {
                return $param;
            } else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/$param/config.php")) {
                return $param;
            } else {
                                return '';
            }

        case PARAM_USERNAME:
            $param = fix_utf8($param);
            $param = trim($param);
                        $param = core_text::strtolower($param);
            if (empty($CFG->extendedusernamechars)) {
                $param = str_replace(" " , "", $param);
                                                $param = preg_replace('/[^-\.@_a-z0-9]/', '', $param);
            }
            return $param;

        case PARAM_EMAIL:
            $param = fix_utf8($param);
            if (validate_email($param)) {
                return $param;
            } else {
                return '';
            }

        case PARAM_STRINGID:
            if (preg_match('|^[a-zA-Z][a-zA-Z0-9\.:/_-]*$|', $param)) {
                return $param;
            } else {
                return '';
            }

        case PARAM_TIMEZONE:
                        $param = fix_utf8($param);
            $timezonepattern = '/^(([+-]?(0?[0-9](\.[5|0])?|1[0-3](\.0)?|1[0-2]\.5))|(99)|[[:alnum:]]+(\/?[[:alpha:]_-])+)$/';
            if (preg_match($timezonepattern, $param)) {
                return $param;
            } else {
                return '';
            }

        default:
                        print_error("unknownparamtype", '', '', $type);
    }
}


function fix_utf8($value) {
    if (is_null($value) or $value === '') {
        return $value;

    } else if (is_string($value)) {
        if ((string)(int)$value === $value) {
                        return $value;
        }
                $value = str_replace("\0", '', $value);

                static $buggyiconv = null;
        if ($buggyiconv === null) {
            $buggyiconv = (!function_exists('iconv') or @iconv('UTF-8', 'UTF-8//IGNORE', '100'.chr(130).'€') !== '100€');
        }

        if ($buggyiconv) {
            if (function_exists('mb_convert_encoding')) {
                $subst = mb_substitute_character();
                mb_substitute_character('');
                $result = mb_convert_encoding($value, 'utf-8', 'utf-8');
                mb_substitute_character($subst);

            } else {
                                $result = $value;
            }

        } else {
            $result = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        }

        return $result;

    } else if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = fix_utf8($v);
        }
        return $value;

    } else if (is_object($value)) {
                $value = clone($value);
        foreach ($value as $k => $v) {
            $value->$k = fix_utf8($v);
        }
        return $value;

    } else {
                return $value;
    }
}


function is_number($value) {
    if (is_int($value)) {
        return true;
    } else if (is_string($value)) {
        return ((string)(int)$value) === $value;
    } else {
        return false;
    }
}


function get_host_from_url($url) {
    preg_match('|^[a-z]+://([a-zA-Z0-9-.]+)|i', $url, $matches);
    if ($matches) {
        return $matches[1];
    }
    return null;
}


function html_is_blank($string) {
    return trim(strip_tags($string, '<img><object><applet><input><select><textarea><hr>')) == '';
}


function set_config($name, $value, $plugin=null) {
    global $CFG, $DB;

    if (empty($plugin)) {
        if($name == 'mnet_dispatcher_mode'){
            $value = $value == 'on' ? 'strict' : $value;
        }
        if($name == 'enablewebservices'){
            $value = $value == 'on' ? 1 : $value;
        }
        if (!array_key_exists($name, $CFG->config_php_settings)) {
                        if (is_null($value)) {
                unset($CFG->$name);
            } else {
                                $CFG->$name = (string)$value;
            }
        }

        if ($DB->get_field('config', 'name', array('name' => $name))) {
            if ($value === null) {
                $DB->delete_records('config', array('name' => $name));
            } else {
                $DB->set_field('config', 'value', $value, array('name' => $name));
            }
        } else {
            if ($value !== null) {
                $config = new stdClass();
                $config->name  = $name;
                $config->value = $value;
                $DB->insert_record('config', $config, false);
            }
        }
        if ($name === 'siteidentifier') {
            cache_helper::update_site_identifier($value);
        }
        cache_helper::invalidate_by_definition('core', 'config', array(), 'core');
    } else {
                if ($id = $DB->get_field('config_plugins', 'id', array('name' => $name, 'plugin' => $plugin))) {
            if ($value===null) {
                $DB->delete_records('config_plugins', array('name' => $name, 'plugin' => $plugin));
            } else {
                $DB->set_field('config_plugins', 'value', $value, array('id' => $id));
            }
        } else {
            if ($value !== null) {
                $config = new stdClass();
                $config->plugin = $plugin;
                $config->name   = $name;
                $config->value  = $value;
                $DB->insert_record('config_plugins', $config, false);
            }
        }
        cache_helper::invalidate_by_definition('core', 'config', array(), $plugin);
    }

    return true;
}


function get_config($plugin, $name = null) {
    global $CFG, $DB;

    static $siteidentifier = null;
    
    $plugin = $plugin === 'modset' ? 'moodle' : $plugin;

    if ($plugin === 'moodle' || $plugin === 'core' || empty($plugin)) {
        $forced =& $CFG->config_php_settings;
        $iscore = true;
        $plugin = 'core';
    } else {
        if (array_key_exists($plugin, $CFG->forced_plugin_settings)) {
            $forced =& $CFG->forced_plugin_settings[$plugin];
        } else {
            $forced = array();
        }
        $iscore = false;
    }

    if ($siteidentifier === null) {
        try {
                                                $siteidentifier = $DB->get_field('config', 'value', array('name' => 'siteidentifier'));
        } catch (dml_exception $ex) {
                        $siteidentifier = false;
            throw $ex;
        }
    }

    if (!empty($name)) {
        if (array_key_exists($name, $forced)) {
            return (string)$forced[$name];
        } else if ($name === 'siteidentifier' && $plugin == 'core') {
            return $siteidentifier;
        }
    }

    $cache = cache::make('core', 'config');
    $result = $cache->get($plugin);
    if ($result === false) {
                if (!$iscore) {
            $result = $DB->get_records_menu('config_plugins', array('plugin' => $plugin), '', 'name,value');
        } else {
                        $result = $DB->get_records_menu('config', array(), '', 'name,value');;
        }
        $cache->set($plugin, $result);
    }

    if (!empty($name)) {
        if (array_key_exists($name, $result)) {
            return $result[$name];
        }
        return false;
    }

    if ($plugin === 'core') {
        $result['siteidentifier'] = $siteidentifier;
    }

    foreach ($forced as $key => $value) {
        if (is_null($value) or is_array($value) or is_object($value)) {
                        unset($result[$key]);
        } else {
                        $result[$key] = (string)$value;
        }
    }

    return (object)$result;
}


function unset_config($name, $plugin=null) {
    global $CFG, $DB;

    if (empty($plugin)) {
        unset($CFG->$name);
        $DB->delete_records('config', array('name' => $name));
        cache_helper::invalidate_by_definition('core', 'config', array(), 'core');
    } else {
        $DB->delete_records('config_plugins', array('name' => $name, 'plugin' => $plugin));
        cache_helper::invalidate_by_definition('core', 'config', array(), $plugin);
    }

    return true;
}


function unset_all_config_for_plugin($plugin) {
    global $DB;
        $DB->delete_records('config_plugins', array('plugin' => $plugin));
        $like = $DB->sql_like('name', '?', true, true, false, '|');
    $params = array($DB->sql_like_escape($plugin.'_', '|') . '%');
    $DB->delete_records_select('config', $like, $params);
        cache_helper::invalidate_by_definition('core', 'config', array(), array('core', $plugin));

    return true;
}


function get_users_from_config($value, $capability, $includeadmins = true) {
    if (empty($value) or $value === '$@NONE@$') {
        return array();
    }

                $users = get_users_by_capability(context_system::instance(), $capability);
    if ($includeadmins) {
        $admins = get_admins();
        foreach ($admins as $admin) {
            $users[$admin->id] = $admin;
        }
    }

    if ($value === '$@ALL@$') {
        return $users;
    }

    $result = array();     $allowed = explode(',', $value);
    foreach ($allowed as $uid) {
        if (isset($users[$uid])) {
            $user = $users[$uid];
            $result[$user->id] = $user;
        }
    }

    return $result;
}



function purge_all_caches() {
    global $CFG, $DB;

    reset_text_filters_cache();
    js_reset_all_caches();
    theme_reset_all_caches();
    get_string_manager()->reset_caches();
    core_text::reset_caches();
    if (class_exists('core_plugin_manager')) {
        core_plugin_manager::reset_caches();
    }

        try {
        increment_revision_number('course', 'cacherev', '');
    } catch (moodle_exception $e) {
            }

    $DB->reset_caches();
    cache_helper::purge_all();

        clearstatcache();
    remove_dir($CFG->cachedir.'', true);

        make_cache_directory('');

            remove_dir($CFG->localcachedir, true);
    set_config('localcachedirpurged', time());
    make_localcache_directory('', true);
    \core\task\manager::clear_static_caches();
}


function get_cache_flags($type, $changedsince = null) {
    global $DB;

    $params = array('type' => $type, 'expiry' => time());
    $sqlwhere = "flagtype = :type AND expiry >= :expiry";
    if ($changedsince !== null) {
        $params['changedsince'] = $changedsince;
        $sqlwhere .= " AND timemodified > :changedsince";
    }
    $cf = array();
    if ($flags = $DB->get_records_select('cache_flags', $sqlwhere, $params, '', 'name,value')) {
        foreach ($flags as $flag) {
            $cf[$flag->name] = $flag->value;
        }
    }
    return $cf;
}


function get_cache_flag($type, $name, $changedsince=null) {
    global $DB;

    $params = array('type' => $type, 'name' => $name, 'expiry' => time());

    $sqlwhere = "flagtype = :type AND name = :name AND expiry >= :expiry";
    if ($changedsince !== null) {
        $params['changedsince'] = $changedsince;
        $sqlwhere .= " AND timemodified > :changedsince";
    }

    return $DB->get_field_select('cache_flags', 'value', $sqlwhere, $params);
}


function set_cache_flag($type, $name, $value, $expiry = null) {
    global $DB;

    $timemodified = time();
    if ($expiry === null || $expiry < $timemodified) {
        $expiry = $timemodified + 24 * 60 * 60;
    } else {
        $expiry = (int)$expiry;
    }

    if ($value === null) {
        unset_cache_flag($type, $name);
        return true;
    }

    if ($f = $DB->get_record('cache_flags', array('name' => $name, 'flagtype' => $type), '*', IGNORE_MULTIPLE)) {
                if ($f->value == $value and $f->expiry == $expiry and $f->timemodified == $timemodified) {
            return true;         }
        $f->value        = $value;
        $f->expiry       = $expiry;
        $f->timemodified = $timemodified;
        $DB->update_record('cache_flags', $f);
    } else {
        $f = new stdClass();
        $f->flagtype     = $type;
        $f->name         = $name;
        $f->value        = $value;
        $f->expiry       = $expiry;
        $f->timemodified = $timemodified;
        $DB->insert_record('cache_flags', $f);
    }
    return true;
}


function unset_cache_flag($type, $name) {
    global $DB;
    $DB->delete_records('cache_flags', array('name' => $name, 'flagtype' => $type));
    return true;
}


function gc_cache_flags() {
    global $DB;
    $DB->delete_records_select('cache_flags', 'expiry < ?', array(time()));
    return true;
}



function check_user_preferences_loaded(stdClass $user, $cachelifetime = 120) {
    global $DB;
        static $loadedusers = array();

    if (!isset($user->id)) {
        throw new coding_exception('Invalid $user parameter in check_user_preferences_loaded() call, missing id field');
    }

    if (empty($user->id) or isguestuser($user->id)) {
                if (!isset($user->preference)) {
            $user->preference = array();
        }
        return;
    }

    $timenow = time();

    if (isset($loadedusers[$user->id]) and isset($user->preference) and isset($user->preference['_lastloaded'])) {
                if ($user->preference['_lastloaded'] + $cachelifetime > $timenow) {
                        return;

        } else if (!get_cache_flag('userpreferenceschanged', $user->id, $user->preference['_lastloaded'])) {
                        $user->preference['_lastloaded'] = $timenow;
            return;
        }
    }

        $loadedusers[$user->id] = true;
    $user->preference = $DB->get_records_menu('user_preferences', array('userid' => $user->id), '', 'name,value');     $user->preference['_lastloaded'] = $timenow;
}


function mark_user_preferences_changed($userid) {
    global $CFG;

    if (empty($userid) or isguestuser($userid)) {
                return;
    }

    set_cache_flag('userpreferenceschanged', $userid, 1, time() + $CFG->sessiontimeout);
}


function set_user_preference($name, $value, $user = null) {
    global $USER, $DB;

    if (empty($name) or is_numeric($name) or $name === '_lastloaded') {
        throw new coding_exception('Invalid preference name in set_user_preference() call');
    }

    if (is_null($value)) {
                return unset_user_preference($name, $user);
    } else if (is_object($value)) {
        throw new coding_exception('Invalid value in set_user_preference() call, objects are not allowed');
    } else if (is_array($value)) {
        throw new coding_exception('Invalid value in set_user_preference() call, arrays are not allowed');
    }
        $value = (string)$value;
    if (core_text::strlen($value) > 1333) {
        throw new coding_exception('Invalid value in set_user_preference() call, value is is too long for the value column');
    }

    if (is_null($user)) {
        $user = $USER;
    } else if (isset($user->id)) {
            } else if (is_numeric($user)) {
        $user = (object)array('id' => (int)$user);
    } else {
        throw new coding_exception('Invalid $user parameter in set_user_preference() call');
    }

    check_user_preferences_loaded($user);

    if (empty($user->id) or isguestuser($user->id)) {
                $user->preference[$name] = $value;
        return true;
    }

    if ($preference = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => $name))) {
        if ($preference->value === $value and isset($user->preference[$name]) and $user->preference[$name] === $value) {
                        return true;
        }
        $DB->set_field('user_preferences', 'value', $value, array('id' => $preference->id));

    } else {
        $preference = new stdClass();
        $preference->userid = $user->id;
        $preference->name   = $name;
        $preference->value  = $value;
        $DB->insert_record('user_preferences', $preference);
    }

        $user->preference[$name] = $value;

        mark_user_preferences_changed($user->id);

    return true;
}


function set_user_preferences(array $prefarray, $user = null) {
    foreach ($prefarray as $name => $value) {
        set_user_preference($name, $value, $user);
    }
    return true;
}


function unset_user_preference($name, $user = null) {
    global $USER, $DB;

    if (empty($name) or is_numeric($name) or $name === '_lastloaded') {
        throw new coding_exception('Invalid preference name in unset_user_preference() call');
    }

    if (is_null($user)) {
        $user = $USER;
    } else if (isset($user->id)) {
            } else if (is_numeric($user)) {
        $user = (object)array('id' => (int)$user);
    } else {
        throw new coding_exception('Invalid $user parameter in unset_user_preference() call');
    }

    check_user_preferences_loaded($user);

    if (empty($user->id) or isguestuser($user->id)) {
                unset($user->preference[$name]);
        return true;
    }

        $DB->delete_records('user_preferences', array('userid' => $user->id, 'name' => $name));

        unset($user->preference[$name]);

        mark_user_preferences_changed($user->id);

    return true;
}


function get_user_preferences($name = null, $default = null, $user = null) {
    global $USER;

    if (is_null($name)) {
            } else if (is_numeric($name) or $name === '_lastloaded') {
        throw new coding_exception('Invalid preference name in get_user_preferences() call');
    }

    if (is_null($user)) {
        $user = $USER;
    } else if (isset($user->id)) {
            } else if (is_numeric($user)) {
        $user = (object)array('id' => (int)$user);
    } else {
        throw new coding_exception('Invalid $user parameter in get_user_preferences() call');
    }

    check_user_preferences_loaded($user);

    if (empty($name)) {
                return $user->preference;
    } else if (isset($user->preference[$name])) {
                return $user->preference[$name];
    } else {
                return $default;
    }
}



function make_timestamp($year, $month=1, $day=1, $hour=0, $minute=0, $second=0, $timezone=99, $applydst=true) {
    $date = new DateTime('now', core_date::get_user_timezone_object($timezone));
    $date->setDate((int)$year, (int)$month, (int)$day);
    $date->setTime((int)$hour, (int)$minute, (int)$second);

    $time = $date->getTimestamp();

        if (!$applydst) {
        $time += dst_offset_on($time, $timezone);
    }

    return $time;

}


function format_time($totalsecs, $str = null) {

    $totalsecs = abs($totalsecs);

    if (!$str) {
                $str = new stdClass();
        $str->day   = get_string('day');
        $str->days  = get_string('days');
        $str->hour  = get_string('hour');
        $str->hours = get_string('hours');
        $str->min   = get_string('min');
        $str->mins  = get_string('mins');
        $str->sec   = get_string('sec');
        $str->secs  = get_string('secs');
        $str->year  = get_string('year');
        $str->years = get_string('years');
    }

    $years     = floor($totalsecs/YEARSECS);
    $remainder = $totalsecs - ($years*YEARSECS);
    $days      = floor($remainder/DAYSECS);
    $remainder = $totalsecs - ($days*DAYSECS);
    $hours     = floor($remainder/HOURSECS);
    $remainder = $remainder - ($hours*HOURSECS);
    $mins      = floor($remainder/MINSECS);
    $secs      = $remainder - ($mins*MINSECS);

    $ss = ($secs == 1)  ? $str->sec  : $str->secs;
    $sm = ($mins == 1)  ? $str->min  : $str->mins;
    $sh = ($hours == 1) ? $str->hour : $str->hours;
    $sd = ($days == 1)  ? $str->day  : $str->days;
    $sy = ($years == 1)  ? $str->year  : $str->years;

    $oyears = '';
    $odays = '';
    $ohours = '';
    $omins = '';
    $osecs = '';

    if ($years) {
        $oyears  = $years .' '. $sy;
    }
    if ($days) {
        $odays  = $days .' '. $sd;
    }
    if ($hours) {
        $ohours = $hours .' '. $sh;
    }
    if ($mins) {
        $omins  = $mins .' '. $sm;
    }
    if ($secs) {
        $osecs  = $secs .' '. $ss;
    }

    if ($years) {
        return trim($oyears .' '. $odays);
    }
    if ($days) {
        return trim($odays .' '. $ohours);
    }
    if ($hours) {
        return trim($ohours .' '. $omins);
    }
    if ($mins) {
        return trim($omins .' '. $osecs);
    }
    if ($secs) {
        return $osecs;
    }
    return get_string('now');
}


function userdate($date, $format = '', $timezone = 99, $fixday = true, $fixhour = true) {
    $calendartype = \core_calendar\type_factory::get_calendar_instance();
    return $calendartype->timestamp_to_date_string($date, $format, $timezone, $fixday, $fixhour);
}


function date_format_string($date, $format, $tz = 99) {
    global $CFG;

    $localewincharset = null;
        if ($CFG->ostype == 'WINDOWS') {
        $calendartype = \core_calendar\type_factory::get_calendar_instance();
        $localewincharset = $calendartype->locale_win_charset();
    }

    if ($localewincharset) {
        $format = core_text::convert($format, 'utf-8', $localewincharset);
    }

    date_default_timezone_set(core_date::get_user_timezone($tz));
    $datestring = strftime($format, $date);
    core_date::set_default_server_timezone();

    if ($localewincharset) {
        $datestring = core_text::convert($datestring, $localewincharset, 'utf-8');
    }

    return $datestring;
}


function usergetdate($time, $timezone=99) {
    date_default_timezone_set(core_date::get_user_timezone($timezone));
    $result = getdate($time);
    core_date::set_default_server_timezone();

    return $result;
}


function usertime($date, $timezone=99) {
    $userdate = new DateTime('@' . $date);
    $userdate->setTimezone(core_date::get_user_timezone_object($timezone));
    $dst = dst_offset_on($date, $timezone);

    return $date - $userdate->getOffset() + $dst;
}


function usergetmidnight($date, $timezone=99) {

    $userdate = usergetdate($date, $timezone);

        return make_timestamp($userdate['year'], $userdate['mon'], $userdate['mday'], 0, 0, 0, $timezone);

}


function usertimezone($timezone=99) {
    $tz = core_date::get_user_timezone($timezone);
    return core_date::get_localised_timezone($tz);
}


function get_user_timezone($tz = 99) {
    global $USER, $CFG;

    $timezones = array(
        $tz,
        isset($CFG->forcetimezone) ? $CFG->forcetimezone : 99,
        isset($USER->timezone) ? $USER->timezone : 99,
        isset($CFG->timezone) ? $CFG->timezone : 99,
        );

    $tz = 99;

        while (((empty($tz) && !is_numeric($tz)) || $tz == 99) && $next = each($timezones)) {
        $tz = $next['value'];
    }
    return is_numeric($tz) ? (float) $tz : $tz;
}


function dst_offset_on($time, $strtimezone = null) {
    $tz = core_date::get_user_timezone($strtimezone);
    $date = new DateTime('@' . $time);
    $date->setTimezone(new DateTimeZone($tz));
    if ($date->format('I') == '1') {
        if ($tz === 'Australia/Lord_Howe') {
            return 1800;
        }
        return 3600;
    }
    return 0;
}


function find_day_in_month($startday, $weekday, $month, $year) {
    $calendartype = \core_calendar\type_factory::get_calendar_instance();

    $daysinmonth = days_in_month($month, $year);
    $daysinweek = count($calendartype->get_weekdays());

    if ($weekday == -1) {
                                return ($startday == -1) ? $daysinmonth : abs($startday);
    }

            if ($startday == -1) {
        $startday = -1 * $daysinmonth;
    }

        if ($startday < 1) {
        $startday = abs($startday);
        $lastmonthweekday = dayofweek($daysinmonth, $month, $year);

                $lastinmonth = $daysinmonth + $weekday - $lastmonthweekday;
        if ($lastinmonth > $daysinmonth) {
            $lastinmonth -= $daysinweek;
        }

                while ($lastinmonth > $startday) {
            $lastinmonth -= $daysinweek;
        }

        return $lastinmonth;
    } else {
        $indexweekday = dayofweek($startday, $month, $year);

        $diff = $weekday - $indexweekday;
        if ($diff < 0) {
            $diff += $daysinweek;
        }

                $firstfromindex = $startday + $diff;

        return $firstfromindex;
    }
}


function days_in_month($month, $year) {
    $calendartype = \core_calendar\type_factory::get_calendar_instance();
    return $calendartype->get_num_days_in_month($year, $month);
}


function dayofweek($day, $month, $year) {
    $calendartype = \core_calendar\type_factory::get_calendar_instance();
    return $calendartype->get_weekday($year, $month, $day);
}



function get_login_url() {
    global $CFG;

    $url = "$CFG->wwwroot/login/index.php";

    if (!empty($CFG->loginhttps)) {
        $url = str_replace('http:', 'https:', $url);
    }

    return $url;
}


function require_login($courseorid = null, $autologinguest = true, $cm = null, $setwantsurltome = true, $preventredirect = false) {
    global $CFG, $SESSION, $USER, $PAGE, $SITE, $DB, $OUTPUT;
    
        if (!empty($_SERVER['HTTP_RANGE'])) {
        $preventredirect = true;
    }

    if (AJAX_SCRIPT) {
                $preventredirect = true;
    }

        if (!empty($courseorid)) {
        if (is_object($courseorid)) {
            $course = $courseorid;
        } else if ($courseorid == SITEID) {
            $course = clone($SITE);
        } else {
            $course = $DB->get_record('course', array('id' => $courseorid), '*', MUST_EXIST);
        }
        if ($cm) {
            if ($cm->course != $course->id) {
                throw new coding_exception('course and cm parameters in require_login() call do not match!!');
            }
                        if (!($cm instanceof cm_info)) {
                                                                $modinfo = get_fast_modinfo($course);
                $cm = $modinfo->get_cm($cm->id);
            }
        }
    } else {
                        $course = $SITE;
        if ($cm) {
            throw new coding_exception('cm parameter in require_login() requires valid course parameter!');
        }
    }

                if ($setwantsurltome && defined('AJAX_SCRIPT') && AJAX_SCRIPT) {
        $setwantsurltome = false;
    }

        if ((!isloggedin() or isguestuser()) && !empty($SESSION->has_timed_out) && !empty($CFG->dbsessions)) {
        if ($preventredirect) {
            throw new require_login_session_timeout_exception();
        } else {
            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }
            redirect(get_login_url());
        }
    }

        if (!isloggedin()) {
        if ($autologinguest and !empty($CFG->guestloginbutton) and !empty($CFG->autologinguests)) {
            if (!$guest = get_complete_user_data('id', $CFG->siteguest)) {
                                redirect(get_login_url());
                exit;             }
            $lang = isset($SESSION->lang) ? $SESSION->lang : $CFG->lang;
            complete_user_login($guest);
            $USER->autologinguest = true;
            $SESSION->lang = $lang;
        } else {
                        if ($preventredirect) {
                throw new require_login_exception('You are not logged in');
            }

            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }

            $referer = get_local_referer(false);
            if (!empty($referer)) {
                $SESSION->fromurl = $referer;
            }

                        $authsequence = get_enabled_auth_plugins(true);             foreach($authsequence as $authname) {
                $authplugin = get_auth_plugin($authname);
                $authplugin->pre_loginpage_hook();
                if (isloggedin()) {
                    break;
                }
            }

                        if (!isloggedin()) {
                redirect(get_login_url());
                exit;             }
        }
    }

        if ($course->id != SITEID and \core\session\manager::is_loggedinas()) {
        if ($USER->loginascontext->contextlevel == CONTEXT_COURSE) {
            if ($USER->loginascontext->instanceid != $course->id) {
                print_error('loginasonecourse', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
            }
        }
    }

        if (get_user_preferences('auth_forcepasswordchange') && !\core\session\manager::is_loggedinas()) {
        $userauth = get_auth_plugin($USER->auth);
        if ($userauth->can_change_password() and !$preventredirect) {
            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }
            if ($changeurl = $userauth->change_password_url()) {
                                redirect($changeurl);
            } else {
                                if (empty($CFG->loginhttps)) {
                    redirect($CFG->wwwroot .'/login/change_password.php');
                } else {
                    $wwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
                    redirect($wwwroot .'/login/change_password.php');
                }
            }
        } else {
            print_error('nopasswordchangeforced', 'auth');
        }
    }

            
    if ($preventredirect) {
        $usernotfullysetup = user_not_fully_set_up($USER, false);
    } else {
        $usernotfullysetup = user_not_fully_set_up($USER, true);
    }

    if ($usernotfullysetup) {
        if ($preventredirect) {
            throw new require_login_exception('User not fully set-up');
        }
        if ($setwantsurltome) {
            $SESSION->wantsurl = qualified_me();
        }
        redirect($CFG->wwwroot .'/user/edit.php?id='. $USER->id .'&amp;course='. SITEID);
    }

        sesskey();

        if (is_siteadmin()) {
                if ($cm) {
            $PAGE->set_cm($cm, $course);
            $PAGE->set_pagelayout('incourse');
        } else if (!empty($courseorid)) {
            $PAGE->set_course($course);
        }
                user_accesstime_log($course->id);
        return;
    }

        if (!$USER->policyagreed and !is_siteadmin()) {
        if (!empty($CFG->sitepolicy) and !isguestuser()) {
            if ($preventredirect) {
                throw new require_login_exception('Policy not agreed');
            }
            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }
            redirect($CFG->wwwroot .'/user/policy.php');
        } else if (!empty($CFG->sitepolicyguest) and isguestuser()) {
            if ($preventredirect) {
                throw new require_login_exception('Policy not agreed');
            }
            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }
            redirect($CFG->wwwroot .'/user/policy.php');
        }
    }

        $sysctx = context_system::instance();
    $coursecontext = context_course::instance($course->id, MUST_EXIST);
    if ($cm) {
        $cmcontext = context_module::instance($cm->id, MUST_EXIST);
    } else {
        $cmcontext = null;
    }

        if (!empty($CFG->maintenance_enabled) and !has_capability('moodle/site:config', $sysctx)) {
        if ($preventredirect) {
            throw new require_login_exception('Maintenance in progress');
        }
        $PAGE->set_context(null);
        print_maintenance_message();
    }

        if ($course->id == SITEID) {
            } else {
        if (is_role_switched($course->id)) {
                    } else {
            if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                                                if ($preventredirect) {
                    throw new require_login_exception('Course is hidden');
                }
                $PAGE->set_context(null);
                                                navigation_node::override_active_url(new moodle_url('/'));
                notice(get_string('coursehidden'), $CFG->wwwroot .'/');
            }
        }
    }

        if ($course->id == SITEID) {
            } else {
        if (\core\session\manager::is_loggedinas()) {
                        $realuser = \core\session\manager::get_realuser();
            if (!is_enrolled($coursecontext, $realuser->id, '', true) and
                !is_viewing($coursecontext, $realuser->id) and !is_siteadmin($realuser->id)) {
                if ($preventredirect) {
                    throw new require_login_exception('Invalid course login-as access');
                }
                $PAGE->set_context(null);
                echo $OUTPUT->header();
                notice(get_string('studentnotallowed', '', fullname($USER, true)), $CFG->wwwroot .'/');
            }
        }

        $access = false;

        if (is_role_switched($course->id)) {
                        $access = true;

        } else if (is_viewing($coursecontext, $USER)) {
                        $access = true;

        } else {
            if (isset($USER->enrol['enrolled'][$course->id])) {
                if ($USER->enrol['enrolled'][$course->id] > time()) {
                    $access = true;
                    if (isset($USER->enrol['tempguest'][$course->id])) {
                        unset($USER->enrol['tempguest'][$course->id]);
                        remove_temp_course_roles($coursecontext);
                    }
                } else {
                                        unset($USER->enrol['enrolled'][$course->id]);
                }
            }
            if (isset($USER->enrol['tempguest'][$course->id])) {
                if ($USER->enrol['tempguest'][$course->id] == 0) {
                    $access = true;
                } else if ($USER->enrol['tempguest'][$course->id] > time()) {
                    $access = true;
                } else {
                                        unset($USER->enrol['tempguest'][$course->id]);
                    remove_temp_course_roles($coursecontext);
                }
            }

            if (!$access) {
                                $until = enrol_get_enrolment_end($coursecontext->instanceid, $USER->id);
                if ($until !== false) {
                                        if ($until == 0) {
                        $until = ENROL_MAX_TIMESTAMP;
                    }
                    $USER->enrol['enrolled'][$course->id] = $until;
                    $access = true;

                } else {
                    $params = array('courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED);
                    $instances = $DB->get_records('enrol', $params, 'sortorder, id ASC');
                    $enrols = enrol_get_plugins(true);
                                        foreach ($instances as $instance) {
                        if (!isset($enrols[$instance->enrol])) {
                            continue;
                        }
                                                $until = $enrols[$instance->enrol]->try_autoenrol($instance);
                        if ($until !== false) {
                            if ($until == 0) {
                                $until = ENROL_MAX_TIMESTAMP;
                            }
                            $USER->enrol['enrolled'][$course->id] = $until;
                            $access = true;
                            break;
                        }
                    }
                                        if (!$access) {
                        foreach ($instances as $instance) {
                            if (!isset($enrols[$instance->enrol])) {
                                continue;
                            }
                                                        $until = $enrols[$instance->enrol]->try_guestaccess($instance);
                            if ($until !== false and $until > time()) {
                                $USER->enrol['tempguest'][$course->id] = $until;
                                $access = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!$access) {
            if ($preventredirect) {
                throw new require_login_exception('Not enrolled');
            }
            if ($setwantsurltome) {
                $SESSION->wantsurl = qualified_me();
            }
            redirect($CFG->wwwroot .'/enrol/index.php?id='. $course->id);
        }
    }

        if ($cm && !$cm->uservisible) {
        if ($preventredirect) {
            throw new require_login_exception('Activity is hidden');
        }
        if ($course->id != SITEID) {
            $url = new moodle_url('/course/view.php', array('id' => $course->id));
        } else {
            $url = new moodle_url('/');
        }
        redirect($url, get_string('activityiscurrentlyhidden'));
    }

        if ($cm) {
        $PAGE->set_cm($cm, $course);
        $PAGE->set_pagelayout('incourse');
    } else if (!empty($courseorid)) {
        $plugins = get_plugin_list('local');
        if(array_key_exists("mooccourse", $plugins)){
            $access = true;
            if(isset($course->forbiddens) && !empty($course->forbiddens) && ($course->enddate < time())){
                $forbiddens = explode(',', $course->forbiddens);
                $context = context_course::instance($course->id);
                foreach($forbiddens as $forbidden){
                    if(empty($forbidden)){
                        continue;
                    }
                    $roles = get_user_roles($context, $USER->id);
                    foreach($roles as $role){
                        if($role->archetype == $forbidden){
                            $access = false;
                        }else{
                            $PAGE->set_course($course);
                            break;
                        }
                    }
                }
                if(!$access){
                    $PAGE->set_context($context);
                    echo $OUTPUT->header();
                    notice(get_string('studentnotallowed', '', fullname($USER, true)), $CFG->wwwroot .'/');
                }
            }else{
                $PAGE->set_course($course);
            }
        }else{
            $PAGE->set_course($course);
        }
    }

        user_accesstime_log($course->id);
}



function require_logout() {
    global $USER, $DB;

    if (!isloggedin()) {
                \core\session\manager::terminate_current();
        return;
    }

        $authplugins = array();
    $authsequence = get_enabled_auth_plugins();
    foreach ($authsequence as $authname) {
        $authplugins[$authname] = get_auth_plugin($authname);
        $authplugins[$authname]->prelogout_hook();
    }

        $sid = session_id();
    $event = \core\event\user_loggedout::create(
        array(
            'userid' => $USER->id,
            'objectid' => $USER->id,
            'other' => array('sessionid' => $sid),
        )
    );
    if ($session = $DB->get_record('sessions', array('sid'=>$sid))) {
        $event->add_record_snapshot('sessions', $session);
    }

        $user = fullclone($USER);

        \core\session\manager::terminate_current();

        $event->trigger();

        foreach ($authplugins as $authplugin) {
        $authplugin->postlogout_hook($user);
    }
}


function require_course_login($courseorid, $autologinguest = true, $cm = null, $setwantsurltome = true, $preventredirect = false) {
    global $CFG, $PAGE, $SITE;
    $issite = ((is_object($courseorid) and $courseorid->id == SITEID)
          or (!is_object($courseorid) and $courseorid == SITEID));
    if ($issite && !empty($cm) && !($cm instanceof cm_info)) {
                                if (is_object($courseorid)) {
            $course = $courseorid;
        } else {
            $course = clone($SITE);
        }
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($cm->id);
    }
    if (!empty($CFG->forcelogin)) {
                require_login($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect);

    } else if ($issite && !empty($cm) and !$cm->uservisible) {
                require_login($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect);

    } else if ($issite) {
                        if (!empty($courseorid)) {
            if (is_object($courseorid)) {
                $course = $courseorid;
            } else {
                $course = clone $SITE;
            }
            if ($cm) {
                if ($cm->course != $course->id) {
                    throw new coding_exception('course and cm parameters in require_course_login() call do not match!!');
                }
                $PAGE->set_cm($cm, $course);
                $PAGE->set_pagelayout('incourse');
            } else {
                $PAGE->set_course($course);
            }
        } else {
                        $PAGE->set_course($PAGE->course);
        }
        user_accesstime_log(SITEID);
        return;

    } else {
                require_login($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect);
    }
}


function require_user_key_login($script, $instance=null) {
    global $DB;

    if (!NO_MOODLE_COOKIES) {
        print_error('sessioncookiesdisable');
    }

        \core\session\manager::write_close();

    $keyvalue = required_param('key', PARAM_ALPHANUM);

    if (!$key = $DB->get_record('user_private_key', array('script' => $script, 'value' => $keyvalue, 'instance' => $instance))) {
        print_error('invalidkey');
    }

    if (!empty($key->validuntil) and $key->validuntil < time()) {
        print_error('expiredkey');
    }

    if ($key->iprestriction) {
        $remoteaddr = getremoteaddr(null);
        if (empty($remoteaddr) or !address_in_subnet($remoteaddr, $key->iprestriction)) {
            print_error('ipmismatch');
        }
    }

    if (!$user = $DB->get_record('user', array('id' => $key->userid))) {
        print_error('invaliduserid');
    }

        enrol_check_plugins($user);
    \core\session\manager::set_user($user);

        if (!defined('USER_KEY_LOGIN')) {
        define('USER_KEY_LOGIN', true);
    }

        return $key->instance;
}


function create_user_key($script, $userid, $instance=null, $iprestriction=null, $validuntil=null) {
    global $DB;

    $key = new stdClass();
    $key->script        = $script;
    $key->userid        = $userid;
    $key->instance      = $instance;
    $key->iprestriction = $iprestriction;
    $key->validuntil    = $validuntil;
    $key->timecreated   = time();

        $key->value         = md5($userid.'_'.time().random_string(40));
    while ($DB->record_exists('user_private_key', array('value' => $key->value))) {
                $key->value     = md5($userid.'_'.time().random_string(40));
    }
    $DB->insert_record('user_private_key', $key);
    return $key->value;
}


function delete_user_key($script, $userid) {
    global $DB;
    $DB->delete_records('user_private_key', array('script' => $script, 'userid' => $userid));
}


function get_user_key($script, $userid, $instance=null, $iprestriction=null, $validuntil=null) {
    global $DB;

    if ($key = $DB->get_record('user_private_key', array('script' => $script, 'userid' => $userid,
                                                         'instance' => $instance, 'iprestriction' => $iprestriction,
                                                         'validuntil' => $validuntil))) {
        return $key->value;
    } else {
        return create_user_key($script, $userid, $instance, $iprestriction, $validuntil);
    }
}



function update_user_login_times() {
    global $USER, $DB;

    if (isguestuser()) {
                return true;
    }

    $now = time();

    $user = new stdClass();
    $user->id = $USER->id;

        if ($USER->firstaccess == 0) {
        $USER->firstaccess = $user->firstaccess = $now;
    }

        $USER->lastlogin = $user->lastlogin = $USER->currentlogin;

    $USER->currentlogin = $user->currentlogin = $now;

        $USER->lastaccess = $user->lastaccess = $now;
    $USER->lastip = $user->lastip = getremoteaddr();

            $DB->update_record('user', $user);
    return true;
}


function user_not_fully_set_up($user, $strict = true) {
    global $CFG;
    require_once($CFG->dirroot.'/user/profile/lib.php');

    if (isguestuser($user)) {
        return false;
    }

    if (empty($user->firstname) or empty($user->lastname) or empty($user->email) or over_bounce_threshold($user)) {
        return true;
    }

    if ($strict) {
        if (empty($user->id)) {
                        return true;
        }
        if (!profile_has_required_custom_fields_set($user->id)) {
            return true;
        }
    }

    return false;
}


function over_bounce_threshold($user) {
    global $CFG, $DB;

    if (empty($CFG->handlebounces)) {
        return false;
    }

    if (empty($user->id)) {
                return false;
    }

        if (empty($CFG->minbounces)) {
        $CFG->minbounces = 10;
    }
    if (empty($CFG->bounceratio)) {
        $CFG->bounceratio = .20;
    }
    $bouncecount = 0;
    $sendcount = 0;
    if ($bounce = $DB->get_record('user_preferences', array ('userid' => $user->id, 'name' => 'email_bounce_count'))) {
        $bouncecount = $bounce->value;
    }
    if ($send = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => 'email_send_count'))) {
        $sendcount = $send->value;
    }
    return ($bouncecount >= $CFG->minbounces && $bouncecount/$sendcount >= $CFG->bounceratio);
}


function set_send_count($user, $reset=false) {
    global $DB;

    if (empty($user->id)) {
                return;
    }

    if ($pref = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => 'email_send_count'))) {
        $pref->value = (!empty($reset)) ? 0 : $pref->value+1;
        $DB->update_record('user_preferences', $pref);
    } else if (!empty($reset)) {
                $pref = new stdClass();
        $pref->name   = 'email_send_count';
        $pref->value  = 1;
        $pref->userid = $user->id;
        $DB->insert_record('user_preferences', $pref, false);
    }
}


function set_bounce_count($user, $reset=false) {
    global $DB;

    if ($pref = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => 'email_bounce_count'))) {
        $pref->value = (!empty($reset)) ? 0 : $pref->value+1;
        $DB->update_record('user_preferences', $pref);
    } else if (!empty($reset)) {
                $pref = new stdClass();
        $pref->name   = 'email_bounce_count';
        $pref->value  = 1;
        $pref->userid = $user->id;
        $DB->insert_record('user_preferences', $pref, false);
    }
}


function ismoving($courseid) {
    global $USER;

    if (!empty($USER->activitycopy)) {
        return ($USER->activitycopycourse == $courseid);
    }
    return false;
}


function fullname($user, $override=false) {
    global $CFG, $SESSION;

    if (!isset($user->firstname) and !isset($user->lastname)) {
        return '';
    }

        $allnames = get_all_user_name_fields();
    if ($CFG->debugdeveloper) {
        foreach ($allnames as $allname) {
            if (!property_exists($user, $allname)) {
                                debugging('You need to update your sql to include additional name fields in the user object.', DEBUG_DEVELOPER);
                                break;
            }
        }
    }

    if (!$override) {
        if (!empty($CFG->forcefirstname)) {
            $user->firstname = $CFG->forcefirstname;
        }
        if (!empty($CFG->forcelastname)) {
            $user->lastname = $CFG->forcelastname;
        }
    }

    if (!empty($SESSION->fullnamedisplay)) {
        $CFG->fullnamedisplay = $SESSION->fullnamedisplay;
    }

    $template = null;
        if (isset($CFG->fullnamedisplay)) {
        $template = $CFG->fullnamedisplay;
    }
        if ((empty($template) || $template == 'language') && !$override) {
        return get_string('fullnamedisplay', null, $user);
    }

        if ($override) {
        if (empty($CFG->alternativefullnameformat) || $CFG->alternativefullnameformat == 'language') {
                        return get_string('fullnamedisplay', null, $user);
        } else {
                        $template = $CFG->alternativefullnameformat;
        }
    }

    $requirednames = array();
        foreach ($allnames as $allname) {
        if (strpos($template, $allname) !== false) {
            $requirednames[] = $allname;
        }
    }

    $displayname = $template;
        foreach ($requirednames as $altname) {
        if (isset($user->$altname)) {
                        if ((string)$user->$altname == '') {
                $displayname = str_replace($altname, 'EMPTY', $displayname);
            } else {
                $displayname = str_replace($altname, $user->$altname, $displayname);
            }
        } else {
            $displayname = str_replace($altname, 'EMPTY', $displayname);
        }
    }
                $patterns = array();
                $patterns[] = '/[[:punct:]「」]*EMPTY[[:punct:]「」]*/u';
        $patterns[] = '/\s{2,}/u';
    foreach ($patterns as $pattern) {
        $displayname = preg_replace($pattern, ' ', $displayname);
    }

        $displayname = trim($displayname);
    if (empty($displayname)) {
                        $displayname = $user->firstname;
    }
    return $displayname;
}


function get_all_user_name_fields($returnsql = false, $tableprefix = null, $prefix = null, $fieldprefix = null, $order = false) {
            $alternatenames = array('firstnamephonetic' => 'firstnamephonetic',
                            'lastnamephonetic' => 'lastnamephonetic',
                            'middlename' => 'middlename',
                            'alternatename' => 'alternatename',
                            'firstname' => 'firstname',
                            'lastname' => 'lastname');

        if ($prefix) {
        foreach ($alternatenames as $key => $altname) {
            $alternatenames[$key] = $prefix . $altname;
        }
    }

        if ($order) {
                for ($i = 0; $i < 2; $i++) {
                        $lastelement = end($alternatenames);
                        unset($alternatenames[$lastelement]);
                        $alternatenames = array_merge(array($lastelement => $lastelement), $alternatenames);
        }
    }

        if ($returnsql) {
        if ($tableprefix) {
            if ($fieldprefix) {
                foreach ($alternatenames as $key => $altname) {
                    $alternatenames[$key] = $tableprefix . '.' . $altname . ' AS ' . $fieldprefix . $altname;
                }
            } else {
                foreach ($alternatenames as $key => $altname) {
                    $alternatenames[$key] = $tableprefix . '.' . $altname;
                }
            }
        }
        $alternatenames = implode(',', $alternatenames);
    }
    return $alternatenames;
}


function username_load_fields_from_object($addtoobject, $secondobject, $prefix = null, $additionalfields = null) {
    $fields = get_all_user_name_fields(false, null, $prefix);
    if ($additionalfields) {
                        foreach ($additionalfields as $key => $value) {
            if (is_numeric($key)) {
                $additionalfields[$value] = $prefix . $value;
                unset($additionalfields[$key]);
            } else {
                $additionalfields[$key] = $prefix . $value;
            }
        }
        $fields = array_merge($fields, $additionalfields);
    }
    foreach ($fields as $key => $field) {
                $addtoobject->$key = '';
        if (isset($secondobject->$field)) {
            $addtoobject->$key = $secondobject->$field;
        }
    }
    return $addtoobject;
}


function order_in_string($values, $stringformat) {
    $valuearray = array();
    foreach ($values as $value) {
        $pattern = "/$value\b/";
                if (preg_match($pattern, $stringformat)) {
            $replacement = "thing";
                        $newformat = preg_replace($pattern, $replacement, $stringformat);
            $position = strpos($newformat, $replacement);
            $valuearray[$position] = $value;
        }
    }
    ksort($valuearray);
    return $valuearray;
}


function get_extra_user_fields($context, $already = array()) {
    global $CFG;

        if (!has_capability('moodle/site:viewuseridentity', $context)) {
        return array();
    }

        if (empty($CFG->showuseridentity)) {
                $extra = array();
    } else {
        $extra =  explode(',', $CFG->showuseridentity);
    }
    $renumber = false;
    foreach ($extra as $key => $field) {
        if (in_array($field, $already)) {
            unset($extra[$key]);
            $renumber = true;
        }
    }
    if ($renumber) {
                        $extra = array_merge($extra);
    }
    return $extra;
}


function get_extra_user_fields_sql($context, $alias='', $prefix='', $already = array()) {
    $fields = get_extra_user_fields($context, $already);
    $result = '';
        if ($alias !== '') {
        $alias .= '.';
    }
    foreach ($fields as $field) {
        $result .= ', ' . $alias . $field;
        if ($prefix) {
            $result .= ' AS ' . $prefix . $field;
        }
    }
    return $result;
}


function get_user_field_name($field) {
        switch ($field) {
        case 'url' : {
            return get_string('webpage');
        }
        case 'icq' : {
            return get_string('icqnumber');
        }
        case 'skype' : {
            return get_string('skypeid');
        }
        case 'aim' : {
            return get_string('aimid');
        }
        case 'yahoo' : {
            return get_string('yahooid');
        }
        case 'msn' : {
            return get_string('msnid');
        }
    }
        return get_string($field);
}


function exists_auth_plugin($auth) {
    global $CFG;

    if (file_exists("{$CFG->dirroot}/auth/$auth/auth.php")) {
        return is_readable("{$CFG->dirroot}/auth/$auth/auth.php");
    }
    return false;
}


function is_enabled_auth($auth) {
    if (empty($auth)) {
        return false;
    }

    $enabled = get_enabled_auth_plugins();

    return in_array($auth, $enabled);
}


function get_auth_plugin($auth) {
    global $CFG;

        if (! exists_auth_plugin($auth)) {
        print_error('authpluginnotfound', 'debug', '', $auth);
    }

        require_once("{$CFG->dirroot}/auth/$auth/auth.php");
    $class = "auth_plugin_$auth";
    return new $class;
}


function get_enabled_auth_plugins($fix=false) {
    global $CFG;

    $default = array('manual', 'nologin');

    if (empty($CFG->auth)) {
        $auths = array();
    } else {
        $auths = explode(',', $CFG->auth);
    }

    if ($fix) {
        $auths = array_unique($auths);
        foreach ($auths as $k => $authname) {
            if (!exists_auth_plugin($authname) or in_array($authname, $default)) {
                unset($auths[$k]);
            }
        }
        $newconfig = implode(',', $auths);
        if (!isset($CFG->auth) or $newconfig != $CFG->auth) {
            set_config('auth', $newconfig);
        }
    }

    return (array_merge($default, $auths));
}


function is_internal_auth($auth) {
        $authplugin = get_auth_plugin($auth);
    return $authplugin->is_internal();
}


function is_restored_user($username) {
    global $CFG, $DB;

    return $DB->record_exists('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id, 'password' => 'restored'));
}


function get_user_fieldnames() {
    global $DB;

    $fieldarray = $DB->get_columns('user');
    unset($fieldarray['id']);
    $fieldarray = array_keys($fieldarray);

    return $fieldarray;
}


function create_user_record($username, $password, $auth = 'manual') {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/user/profile/lib.php');
    require_once($CFG->dirroot.'/user/lib.php');

        $username = trim(core_text::strtolower($username));

    $authplugin = get_auth_plugin($auth);
    $customfields = $authplugin->get_custom_user_profile_fields();
    $newuser = new stdClass();
    if ($newinfo = $authplugin->get_userinfo($username)) {
        $newinfo = truncate_userinfo($newinfo);
        foreach ($newinfo as $key => $value) {
            if (in_array($key, $authplugin->userfields) || (in_array($key, $customfields))) {
                $newuser->$key = $value;
            }
        }
    }

    if (!empty($newuser->email)) {
        if (email_is_not_allowed($newuser->email)) {
            unset($newuser->email);
        }
    }

    if (!isset($newuser->city)) {
        $newuser->city = '';
    }

    $newuser->auth = $auth;
    $newuser->username = $username;

                if (empty($newuser->lang) || !get_string_manager()->translation_exists($newuser->lang)) {
        $newuser->lang = $CFG->lang;
    }
    $newuser->confirmed = 1;
    $newuser->lastip = getremoteaddr();
    $newuser->timecreated = time();
    $newuser->timemodified = $newuser->timecreated;
    $newuser->mnethostid = $CFG->mnet_localhost_id;

    $newuser->id = user_create_user($newuser, false, false);

        profile_save_data($newuser);

    $user = get_complete_user_data('id', $newuser->id);
    if (!empty($CFG->{'auth_'.$newuser->auth.'_forcechangepassword'})) {
        set_user_preference('auth_forcepasswordchange', 1, $user);
    }
        update_internal_user_password($user, $password);

        \core\event\user_created::create_from_userid($newuser->id)->trigger();

    return $user;
}


function update_user_record($username) {
    global $DB, $CFG;
        $username = trim(core_text::strtolower($username));

    $oldinfo = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id), '*', MUST_EXIST);
    return update_user_record_by_id($oldinfo->id);
}


function update_user_record_by_id($id) {
    global $DB, $CFG;
    require_once($CFG->dirroot."/user/profile/lib.php");
    require_once($CFG->dirroot.'/user/lib.php');

    $params = array('mnethostid' => $CFG->mnet_localhost_id, 'id' => $id, 'deleted' => 0);
    $oldinfo = $DB->get_record('user', $params, '*', MUST_EXIST);

    $newuser = array();
    $userauth = get_auth_plugin($oldinfo->auth);

    if ($newinfo = $userauth->get_userinfo($oldinfo->username)) {
        $newinfo = truncate_userinfo($newinfo);
        $customfields = $userauth->get_custom_user_profile_fields();

        foreach ($newinfo as $key => $value) {
            $iscustom = in_array($key, $customfields);
            if (!$iscustom) {
                $key = strtolower($key);
            }
            if ((!property_exists($oldinfo, $key) && !$iscustom) or $key === 'username' or $key === 'id'
                    or $key === 'auth' or $key === 'mnethostid' or $key === 'deleted') {
                                continue;
            }
            $confval = $userauth->config->{'field_updatelocal_' . $key};
            $lockval = $userauth->config->{'field_lock_' . $key};
            if (empty($confval) || empty($lockval)) {
                continue;
            }
            if ($confval === 'onlogin') {
                                                                                                                if (!(empty($value) && $lockval === 'unlockedifempty')) {
                    if ($iscustom || (in_array($key, $userauth->userfields) &&
                            ((string)$oldinfo->$key !== (string)$value))) {
                        $newuser[$key] = (string)$value;
                    }
                }
            }
        }
        if ($newuser) {
            $newuser['id'] = $oldinfo->id;
            $newuser['timemodified'] = time();
            user_update_user((object) $newuser, false, false);

                        profile_save_data((object) $newuser);

                        \core\event\user_updated::create_from_userid($newuser['id'])->trigger();
        }
    }

    return get_complete_user_data('id', $oldinfo->id);
}


function truncate_userinfo(array $info) {
        $limit = array(
        'username'    => 100,
        'idnumber'    => 255,
        'firstname'   => 100,
        'lastname'    => 100,
        'email'       => 100,
        'icq'         =>  15,
        'phone1'      =>  20,
        'phone2'      =>  20,
        'institution' => 255,
        'department'  => 255,
        'address'     => 255,
        'city'        => 120,
        'country'     =>   2,
        'url'         => 255,
    );

        foreach (array_keys($info) as $key) {
        if (!empty($limit[$key])) {
            $info[$key] = trim(core_text::substr($info[$key], 0, $limit[$key]));
        }
    }

    return $info;
}


function delete_user(stdClass $user) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/grouplib.php');
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->dirroot.'/message/lib.php');
    require_once($CFG->dirroot.'/user/lib.php');

        if (!property_exists($user, 'id') or !property_exists($user, 'username')) {
        throw new coding_exception('Invalid $user parameter in delete_user() detected');
    }

        if (!$user = $DB->get_record('user', array('id' => $user->id))) {
        debugging('Attempt to delete unknown user account.');
        return false;
    }

            if ($user->username === 'guest' or isguestuser($user)) {
        debugging('Guest user account can not be deleted.');
        return false;
    }

            if ($user->auth === 'manual' and is_siteadmin($user)) {
        debugging('Local administrator accounts can not be deleted.');
        return false;
    }

        if ($pluginsfunction = get_plugins_with_function('pre_user_delete')) {
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($user);
            }
        }
    }

        $olduser = clone $user;

        $usercontext = context_user::instance($user->id);

        grade_user_delete($user->id);

        message_move_userfrom_unread2read($user->id);

    
        core_tag_tag::remove_all_item_tags('core', 'user', $user->id);

        enrol_user_delete($user);

            role_unassign_all(array('userid' => $user->id));

    
        $DB->delete_records('cohort_members', array('userid' => $user->id));

        $DB->delete_records('groups_members', array('userid' => $user->id));

        $DB->delete_records('user_enrolments', array('userid' => $user->id));

        $DB->delete_records('user_preferences', array('userid' => $user->id));

        $DB->delete_records('user_info_data', array('userid' => $user->id));

        $DB->delete_records('user_password_history', array('userid' => $user->id));

        $DB->delete_records('user_lastaccess', array('userid' => $user->id));
        $DB->delete_records('external_tokens', array('userid' => $user->id));

        $DB->delete_records('external_services_users', array('userid' => $user->id));

        $DB->delete_records('user_private_key', array('userid' => $user->id));

        $DB->delete_records('my_pages', array('userid' => $user->id, 'private' => 1));

        \core\session\manager::kill_user_sessions($user->id);

        $delemail = !empty($user->email) ? $user->email : $user->username . '.' . $user->id . '@unknownemail.invalid';
    $delname = clean_param($delemail . "." . time(), PARAM_USERNAME);

        while ($DB->record_exists('user', array('username' => $delname))) {         $delname++;
    }

        $updateuser = new stdClass();
    $updateuser->id           = $user->id;
    $updateuser->deleted      = 1;
    $updateuser->username     = $delname;                $updateuser->email        = md5($user->username);    $updateuser->idnumber     = '';                      $updateuser->picture      = 0;
    $updateuser->timemodified = time();

        user_update_user($updateuser, false, false);

        context_helper::delete_instance(CONTEXT_USER, $user->id);

            $event = \core\event\user_deleted::create(
            array(
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => $usercontext,
                'other' => array(
                    'username' => $user->username,
                    'email' => $user->email,
                    'idnumber' => $user->idnumber,
                    'picture' => $user->picture,
                    'mnethostid' => $user->mnethostid
                    )
                )
            );
    $event->add_record_snapshot('user', $olduser);
    $event->trigger();

            $user->timemodified = $updateuser->timemodified;

        $authplugin = get_auth_plugin($user->auth);
    $authplugin->user_delete($user);

    return true;
}


function guest_user() {
    global $CFG, $DB;

    if ($newuser = $DB->get_record('user', array('id' => $CFG->siteguest))) {
        $newuser->confirmed = 1;
        $newuser->lang = $CFG->lang;
        $newuser->lastip = getremoteaddr();
    }

    return $newuser;
}


function authenticate_user_login($username, $password, $ignorelockout=false, &$failurereason=null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/authlib.php");

    if ($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id)) {
        
    } else if (!empty($CFG->authloginviaemail)) {
        if ($email = clean_param($username, PARAM_EMAIL)) {
            $select = "mnethostid = :mnethostid AND LOWER(email) = LOWER(:email) AND deleted = 0";
            $params = array('mnethostid' => $CFG->mnet_localhost_id, 'email' => $email);
            $users = $DB->get_records_select('user', $select, $params, 'id', 'id', 0, 2);
            if (count($users) === 1) {
                                $user = reset($users);
                $user = get_complete_user_data('id', $user->id);
                $username = $user->username;
            }
            unset($users);
        }
    }

    $authsenabled = get_enabled_auth_plugins();

    if ($user) {
                $auth = empty($user->auth) ? 'manual' : $user->auth;
        if (!empty($user->suspended)) {
            $failurereason = AUTH_LOGIN_SUSPENDED;

                        $event = \core\event\user_login_failed::create(array('userid' => $user->id,
                    'other' => array('username' => $username, 'reason' => $failurereason)));
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Suspended Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
        if ($auth=='nologin' or !is_enabled_auth($auth)) {
                        $failurereason = AUTH_LOGIN_SUSPENDED;

                        $event = \core\event\user_login_failed::create(array('userid' => $user->id,
                    'other' => array('username' => $username, 'reason' => $failurereason)));
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Disabled Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
        $auths = array($auth);

    } else {
                if ($DB->get_field('user', 'id', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id,  'deleted' => 1))) {
            $failurereason = AUTH_LOGIN_NOUSER;

                        $event = \core\event\user_login_failed::create(array('other' => array('username' => $username,
                    'reason' => $failurereason)));
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Deleted Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }

                $auths = $authsenabled;
        $user = new stdClass();
        $user->id = 0;
    }

    if ($ignorelockout) {
                    } else if ($user->id) {
                if (login_is_lockedout($user)) {
            $failurereason = AUTH_LOGIN_LOCKOUT;

                        $event = \core\event\user_login_failed::create(array('userid' => $user->id,
                    'other' => array('username' => $username, 'reason' => $failurereason)));
            $event->trigger();

            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Login lockout:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
    } else {
            }

    foreach ($auths as $auth) {
        $authplugin = get_auth_plugin($auth);

                if (!$authplugin->user_login($username, $password)) {
            continue;
        }

                if ($user->id) {
                        if (empty($user->auth)) {
                                $DB->set_field('user', 'auth', $auth, array('id' => $user->id));
                $user->auth = $auth;
            }

                                    update_internal_user_password($user, $password);

            if ($authplugin->is_synchronised_with_external()) {
                                $user = update_user_record_by_id($user->id);
            }
        } else {
                        if (!empty($CFG->authpreventaccountcreation)) {
                $failurereason = AUTH_LOGIN_UNAUTHORISED;

                                $event = \core\event\user_login_failed::create(array('other' => array('username' => $username,
                        'reason' => $failurereason)));
                $event->trigger();

                error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Unknown user, can not create new accounts:  $username  ".
                        $_SERVER['HTTP_USER_AGENT']);
                return false;
            } else {
                $user = create_user_record($username, $password, $auth);
            }
        }

        $authplugin->sync_roles($user);

        foreach ($authsenabled as $hau) {
            $hauth = get_auth_plugin($hau);
            $hauth->user_authenticated_hook($user, $username, $password);
        }

        if (empty($user->id)) {
            $failurereason = AUTH_LOGIN_NOUSER;
                        $event = \core\event\user_login_failed::create(array('other' => array('username' => $username,
                    'reason' => $failurereason)));
            $event->trigger();
            return false;
        }

        if (!empty($user->suspended)) {
                        $failurereason = AUTH_LOGIN_SUSPENDED;
                        $event = \core\event\user_login_failed::create(array('userid' => $user->id,
                    'other' => array('username' => $username, 'reason' => $failurereason)));
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Suspended Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }

        login_attempt_valid($user);
        $failurereason = AUTH_LOGIN_OK;
        return $user;
    }

        if (debugging('', DEBUG_ALL)) {
        error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Failed Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
    }

    if ($user->id) {
        login_attempt_failed($user);
        $failurereason = AUTH_LOGIN_FAILED;
                $event = \core\event\user_login_failed::create(array('userid' => $user->id,
                'other' => array('username' => $username, 'reason' => $failurereason)));
        $event->trigger();
    } else {
        $failurereason = AUTH_LOGIN_NOUSER;
                $event = \core\event\user_login_failed::create(array('other' => array('username' => $username,
                'reason' => $failurereason)));
        $event->trigger();
    }

    return false;
}


function complete_user_login($user) {
    global $CFG, $USER, $SESSION;

    \core\session\manager::login_user($user);

        unset($USER->preference);
    check_user_preferences_loaded($USER);

        update_user_login_times();

        set_login_session_preferences();

        $event = \core\event\user_loggedin::create(
        array(
            'userid' => $USER->id,
            'objectid' => $USER->id,
            'other' => array('username' => $USER->username),
        )
    );
    $event->trigger();

    if (isguestuser()) {
                return $USER;
    }

    if (CLI_SCRIPT) {
                return $USER;
    }

        $userauth = get_auth_plugin($USER->auth);

        if (get_user_preferences('auth_forcepasswordchange', false)) {
        if ($userauth->can_change_password()) {
            if ($changeurl = $userauth->change_password_url()) {
                redirect($changeurl);
            } else {
                require_once($CFG->dirroot . '/login/lib.php');
                $SESSION->wantsurl = core_login_get_return_url();
                redirect($CFG->httpswwwroot.'/login/change_password.php');
            }
        } else {
            print_error('nopasswordchangeforced', 'auth');
        }
    }
    return $USER;
}


function password_is_legacy_hash($password) {
    return (bool) preg_match('/^[0-9a-f]{32}$/', $password);
}


function validate_internal_user_password($user, $password) {
    global $CFG;
    require_once($CFG->libdir.'/password_compat/lib/password.php');

    if ($user->password === AUTH_PASSWORD_NOT_CACHED) {
                return false;
    }

        if (!password_is_legacy_hash($user->password)) {
        return password_verify($password, $user->password);
    }

        
    $sitesalt = isset($CFG->passwordsaltmain) ? $CFG->passwordsaltmain : '';
    $validated = false;

    if ($user->password === md5($password.$sitesalt)
            or $user->password === md5($password)
            or $user->password === md5(addslashes($password).$sitesalt)
            or $user->password === md5(addslashes($password))) {
                        $validated = true;

    } else {
        for ($i=1; $i<=20; $i++) {             $alt = 'passwordsaltalt'.$i;
            if (!empty($CFG->$alt)) {
                if ($user->password === md5($password.$CFG->$alt) or $user->password === md5(addslashes($password).$CFG->$alt)) {
                    $validated = true;
                    break;
                }
            }
        }
    }

    if ($validated) {
                        update_internal_user_password($user, $password);
    }

    return $validated;
}


function hash_internal_user_password($password, $fasthash = false) {
    global $CFG;
    require_once($CFG->libdir.'/password_compat/lib/password.php');

        $options = ($fasthash) ? array('cost' => 4) : array();

    $generatedhash = password_hash($password, PASSWORD_DEFAULT, $options);

    if ($generatedhash === false || $generatedhash === null) {
        throw new moodle_exception('Failed to generate password hash.');
    }

    return $generatedhash;
}


function update_internal_user_password($user, $password, $fasthash = false) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/password_compat/lib/password.php');

        if (!isset($user->auth)) {
        debugging('User record in update_internal_user_password() must include field auth',
                DEBUG_DEVELOPER);
        $user->auth = $DB->get_field('user', 'auth', array('id' => $user->id));
    }
    $authplugin = get_auth_plugin($user->auth);
    if ($authplugin->prevent_local_passwords()) {
        $hashedpassword = AUTH_PASSWORD_NOT_CACHED;
    } else {
        $hashedpassword = hash_internal_user_password($password, $fasthash);
    }

    $algorithmchanged = false;

    if ($hashedpassword === AUTH_PASSWORD_NOT_CACHED) {
                $passwordchanged = ($user->password !== $hashedpassword);

    } else if (isset($user->password)) {
                $passwordchanged = !password_verify($password, $user->password);
        $algorithmchanged = password_needs_rehash($user->password, PASSWORD_DEFAULT);
    } else {
                        $passwordchanged = true;
    }

    if ($passwordchanged || $algorithmchanged) {
        $DB->set_field('user', 'password',  $hashedpassword, array('id' => $user->id));
        $user->password = $hashedpassword;

                $user = $DB->get_record('user', array('id' => $user->id));
        \core\event\user_password_updated::create_from_user($user)->trigger();

                require_once($CFG->dirroot.'/webservice/lib.php');
        webservice::delete_user_ws_tokens($user->id);
    }

    return true;
}


function get_complete_user_data($field, $value, $mnethostid = null) {
    global $CFG, $DB;

    if (!$field || !$value) {
        return false;
    }

        $params = array('fieldval' => $value);
    $constraints = "$field = :fieldval AND deleted <> 1";

            if ($field != 'id') {
        if (empty($mnethostid)) {
                        $mnethostid = $CFG->mnet_localhost_id;
        }
    }
    if (!empty($mnethostid)) {
        $params['mnethostid'] = $mnethostid;
        $constraints .= " AND mnethostid = :mnethostid";
    }

        if (! $user = $DB->get_record_select('user', $constraints, $params)) {
        return false;
    }

    
        check_user_preferences_loaded($user);

        $user->lastcourseaccess    = array();     $user->currentcourseaccess = array();     if ($lastaccesses = $DB->get_records('user_lastaccess', array('userid' => $user->id))) {
        foreach ($lastaccesses as $lastaccess) {
            $user->lastcourseaccess[$lastaccess->courseid] = $lastaccess->timeaccess;
        }
    }

    $sql = "SELECT g.id, g.courseid
              FROM {groups} g, {groups_members} gm
             WHERE gm.groupid=g.id AND gm.userid=?";

        $user->groupmember = array();
    if (!isguestuser($user)) {
        if ($groups = $DB->get_records_sql($sql, array($user->id))) {
            foreach ($groups as $group) {
                if (!array_key_exists($group->courseid, $user->groupmember)) {
                    $user->groupmember[$group->courseid] = array();
                }
                $user->groupmember[$group->courseid][$group->id] = $group->id;
            }
        }
    }

        $user->profile = array();
    if (!isguestuser($user)) {
        require_once($CFG->dirroot.'/user/profile/lib.php');
        profile_load_custom_fields($user);
    }

        if (!empty($user->description)) {
                $user->description = true;
    }
    if (isguestuser($user)) {
                $user->lang = $CFG->lang;
                $user->firstname = get_string('guestuser');
        $user->lastname = ' ';
    }

    return $user;
}


function check_password_policy($password, &$errmsg) {
    global $CFG;

    if (empty($CFG->passwordpolicy)) {
        return true;
    }

    $errmsg = '';
    if (core_text::strlen($password) < $CFG->minpasswordlength) {
        $errmsg .= '<div>'. get_string('errorminpasswordlength', 'auth', $CFG->minpasswordlength) .'</div>';

    }
    if (preg_match_all('/[[:digit:]]/u', $password, $matches) < $CFG->minpassworddigits) {
        $errmsg .= '<div>'. get_string('errorminpassworddigits', 'auth', $CFG->minpassworddigits) .'</div>';

    }
    if (preg_match_all('/[[:lower:]]/u', $password, $matches) < $CFG->minpasswordlower) {
        $errmsg .= '<div>'. get_string('errorminpasswordlower', 'auth', $CFG->minpasswordlower) .'</div>';

    }
    if (preg_match_all('/[[:upper:]]/u', $password, $matches) < $CFG->minpasswordupper) {
        $errmsg .= '<div>'. get_string('errorminpasswordupper', 'auth', $CFG->minpasswordupper) .'</div>';

    }
    if (preg_match_all('/[^[:upper:][:lower:][:digit:]]/u', $password, $matches) < $CFG->minpasswordnonalphanum) {
        $errmsg .= '<div>'. get_string('errorminpasswordnonalphanum', 'auth', $CFG->minpasswordnonalphanum) .'</div>';
    }
    if (!check_consecutive_identical_characters($password, $CFG->maxconsecutiveidentchars)) {
        $errmsg .= '<div>'. get_string('errormaxconsecutiveidentchars', 'auth', $CFG->maxconsecutiveidentchars) .'</div>';
    }

    if ($errmsg == '') {
        return true;
    } else {
        return false;
    }
}



function set_login_session_preferences() {
    global $SESSION;

    $SESSION->justloggedin = true;

    unset($SESSION->lang);
    unset($SESSION->forcelang);
    unset($SESSION->load_navigation_admin);
}



function delete_course($courseorid, $showfeedback = true) {
    global $DB;

    if (is_object($courseorid)) {
        $courseid = $courseorid->id;
        $course   = $courseorid;
    } else {
        $courseid = $courseorid;
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            return false;
        }
    }
    $context = context_course::instance($courseid);

        if ($courseid == SITEID) {
        return false;
    }

        if ($pluginsfunction = get_plugins_with_function('pre_course_delete')) {
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($course);
            }
        }
    }

        remove_course_contents($courseid, $showfeedback);

        context_helper::delete_instance(CONTEXT_COURSE, $courseid);

    $DB->delete_records("course", array("id" => $courseid));
    $DB->delete_records("course_format_options", array("courseid" => $courseid));

        if (class_exists('format_base', false)) {
        format_base::reset_course_cache($courseid);
    }

        $event = \core\event\course_deleted::create(array(
        'objectid' => $course->id,
        'context' => $context,
        'other' => array(
            'shortname' => $course->shortname,
            'fullname' => $course->fullname,
            'idnumber' => $course->idnumber
            )
    ));
    $event->add_record_snapshot('course', $course);
    $event->trigger();

    return true;
}


function remove_course_contents($courseid, $showfeedback = true, array $options = null) {
    global $CFG, $DB, $OUTPUT;

    require_once($CFG->libdir.'/badgeslib.php');
    require_once($CFG->libdir.'/completionlib.php');
    require_once($CFG->libdir.'/questionlib.php');
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->dirroot.'/group/lib.php');
    require_once($CFG->dirroot.'/comment/lib.php');
    require_once($CFG->dirroot.'/rating/lib.php');
    require_once($CFG->dirroot.'/notes/lib.php');

        badges_handle_course_deletion($courseid);

        $strdeleted = get_string('deleted').' - ';

        $options = (array)$options;

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $coursecontext = context_course::instance($courseid);
    $fs = get_file_storage();

        $cc = new completion_info($course);
    $cc->clear_criteria();
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.get_string('completion', 'completion'), 'notifysuccess');
    }

                remove_course_grades($courseid, $showfeedback);
    remove_grade_letters($coursecontext, $showfeedback);

            $childcontexts = $coursecontext->get_child_contexts();     foreach ($childcontexts as $childcontext) {
        blocks_delete_all_for_context($childcontext->id);
    }
    unset($childcontexts);
    blocks_delete_all_for_context($coursecontext->id);
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.get_string('type_block_plural', 'plugin'), 'notifysuccess');
    }

            $locations = core_component::get_plugin_list('mod');
    foreach ($locations as $modname => $moddir) {
        if ($modname === 'NEWMODULE') {
            continue;
        }
        if ($module = $DB->get_record('modules', array('name' => $modname))) {
            include_once("$moddir/lib.php");                             $moddelete = $modname .'_delete_instance';                   $moddeletecourse = $modname .'_delete_course';   
            if ($instances = $DB->get_records($modname, array('course' => $course->id))) {
                foreach ($instances as $instance) {
                    if ($cm = get_coursemodule_from_instance($modname, $instance->id, $course->id)) {
                                                question_delete_activity($cm,  $showfeedback);

                                                \core_competency\api::hook_course_module_deleted($cm);
                    }
                    if (function_exists($moddelete)) {
                                                $moddelete($instance->id);
                    } else {
                                                debugging("Defective module '$modname' detected when deleting course contents: missing function $moddelete()!");
                        $DB->delete_records($modname, array('id' => $instance->id));
                    }

                    if ($cm) {
                                                context_helper::delete_instance(CONTEXT_MODULE, $cm->id);
                        $DB->delete_records('course_modules', array('id' => $cm->id));
                    }
                }
            }
            if (function_exists($moddeletecourse)) {
                                $moddeletecourse($course, $showfeedback);
            }
            if ($instances and $showfeedback) {
                echo $OUTPUT->notification($strdeleted.get_string('pluginname', $modname), 'notifysuccess');
            }
        } else {
                    }
    }

    
                $DB->delete_records_select('course_modules_completion',
           'coursemoduleid IN (SELECT id from {course_modules} WHERE course=?)',
           array($courseid));

        $cms = $DB->get_records('course_modules', array('course' => $course->id));
    foreach ($cms as $cm) {
        if ($module = $DB->get_record('modules', array('id' => $cm->module))) {
            try {
                $DB->delete_records($module->name, array('id' => $cm->instance));
            } catch (Exception $e) {
                            }
        }
        context_helper::delete_instance(CONTEXT_MODULE, $cm->id);
        $DB->delete_records('course_modules', array('id' => $cm->id));
    }

    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.get_string('type_mod_plural', 'plugin'), 'notifysuccess');
    }

        $cleanuplugintypes = array('report', 'coursereport', 'format');
    $callbacks = get_plugins_with_function('delete_course', 'lib.php');
    foreach ($cleanuplugintypes as $type) {
        if (!empty($callbacks[$type])) {
            foreach ($callbacks[$type] as $pluginfunction) {
                $pluginfunction($course->id, $showfeedback);
            }
        }
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('type_'.$type.'_plural', 'plugin'), 'notifysuccess');
        }
    }

        question_delete_course($course, $showfeedback);
    if ($showfeedback) {
        echo $OUTPUT->notification($strdeleted.get_string('questions', 'question'), 'notifysuccess');
    }

        $childcontexts = $coursecontext->get_child_contexts();     foreach ($childcontexts as $childcontext) {
        $childcontext->delete();
    }
    unset($childcontexts);

        if (empty($options['keep_roles_and_enrolments'])) {
                role_unassign_all(array('contextid' => $coursecontext->id, 'component' => ''), true);
        enrol_course_delete($course);
        if ($showfeedback) {
            echo $OUTPUT->notification($strdeleted.get_string('type_enrol_plural', 'plugin'), 'notifysuccess');
        }
    }

        if (empty($options['keep_groups_and_groupings'])) {
        groups_delete_groupings($course->id, $showfeedback);
        groups_delete_groups($course->id, $showfeedback);
    }

        filter_delete_all_for_context($coursecontext->id);

        note_delete_all($course->id);

        comment::delete_comments($coursecontext->id);

        $delopt = new stdclass();
    $delopt->contextid = $coursecontext->id;
    $rm = new rating_manager();
    $rm->delete_ratings($delopt);

        core_tag_tag::remove_all_item_tags('core', 'course', $course->id);

        \core_competency\api::hook_course_deleted($course);

        $DB->delete_records('event', array('courseid' => $course->id));
    $fs->delete_area_files($coursecontext->id, 'calendar');

                $tablestoclear = array(
        'backup_courses' => 'courseid',          'user_lastaccess' => 'courseid',     );
    foreach ($tablestoclear as $table => $col) {
        $DB->delete_records($table, array($col => $course->id));
    }

        $fs->delete_area_files($coursecontext->id, 'backup');

        $oldcourse = new stdClass();
    $oldcourse->id               = $course->id;
    $oldcourse->summary          = '';
    $oldcourse->cacherev         = 0;
    $oldcourse->legacyfiles      = 0;
    if (!empty($options['keep_groups_and_groupings'])) {
        $oldcourse->defaultgroupingid = 0;
    }
    $DB->update_record('course', $oldcourse);

        $DB->delete_records('course_sections', array('course' => $course->id));

        $fs->delete_area_files($coursecontext->id, 'course'); 
        if (empty($options['keep_roles_and_enrolments']) and empty($options['keep_groups_and_groupings'])) {
                $coursecontext->delete_content();
    } else {
                            }

            fulldelete($CFG->dataroot.'/'.$course->id);

        $cachemodinfo = cache::make('core', 'coursemodinfo');
    $cachemodinfo->delete($courseid);

        $event = \core\event\course_content_deleted::create(array(
        'objectid' => $course->id,
        'context' => $coursecontext,
        'other' => array('shortname' => $course->shortname,
                         'fullname' => $course->fullname,
                         'options' => $options)     ));
    $event->add_record_snapshot('course', $course);
    $event->trigger();

    return true;
}


function shift_course_mod_dates($modname, $fields, $timeshift, $courseid, $modid = 0) {
    global $CFG, $DB;
    include_once($CFG->dirroot.'/mod/'.$modname.'/lib.php');

    $return = true;
    $params = array($timeshift, $courseid);
    foreach ($fields as $field) {
        $updatesql = "UPDATE {".$modname."}
                          SET $field = $field + ?
                        WHERE course=? AND $field<>0";
        if ($modid) {
            $updatesql .= ' AND id=?';
            $params[] = $modid;
        }
        $return = $DB->execute($updatesql, $params) && $return;
    }

    $refreshfunction = $modname.'_refresh_events';
    if (function_exists($refreshfunction)) {
        $refreshfunction($courseid);
    }

    return $return;
}


function reset_course_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->libdir.'/completionlib.php');
    require_once($CFG->dirroot.'/completion/criteria/completion_criteria_date.php');
    require_once($CFG->dirroot.'/group/lib.php');

    $data->courseid = $data->id;
    $context = context_course::instance($data->courseid);

    $eventparams = array(
        'context' => $context,
        'courseid' => $data->id,
        'other' => array(
            'reset_options' => (array) $data
        )
    );
    $event = \core\event\course_reset_started::create($eventparams);
    $event->trigger();

        if (!empty($data->reset_start_date)) {
                $data->timeshift = $data->reset_start_date - usergetmidnight($data->reset_start_date_old);
    } else {
        $data->timeshift = 0;
    }

        $status = array();

        $componentstr = get_string('general');

        if (!empty($data->reset_start_date) and $data->timeshift) {
                $DB->set_field('course', 'startdate', $data->reset_start_date, array('id' => $data->courseid));
                $updatesql = "UPDATE {event}
                         SET timestart = timestart + ?
                       WHERE courseid=? AND instance=0";
        $DB->execute($updatesql, array($data->timeshift, $data->courseid));

                if ($CFG->enableavailability) {
            \availability_date\condition::update_all_dates($data->courseid, $data->timeshift);
        }

                if ($CFG->enablecompletion) {
            $modinfo = get_fast_modinfo($data->courseid);
            $changed = false;
            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->completion && !empty($cm->completionexpected)) {
                    $DB->set_field('course_modules', 'completionexpected', $cm->completionexpected + $data->timeshift,
                        array('id' => $cm->id));
                    $changed = true;
                }
            }

                        if ($changed) {
                rebuild_course_cache($data->courseid, true);
            }

                        \completion_criteria_date::update_date($data->courseid, $data->timeshift);
        }

        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    if (!empty($data->reset_events)) {
        $DB->delete_records('event', array('courseid' => $data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteevents', 'calendar'), 'error' => false);
    }

    if (!empty($data->reset_notes)) {
        require_once($CFG->dirroot.'/notes/lib.php');
        note_delete_all($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('deletenotes', 'notes'), 'error' => false);
    }

    if (!empty($data->delete_blog_associations)) {
        require_once($CFG->dirroot.'/blog/lib.php');
        blog_remove_associations_for_course($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteblogassociations', 'blog'), 'error' => false);
    }

    if (!empty($data->reset_completion)) {
                $course = $DB->get_record('course', array('id' => $data->courseid));
        $cc = new completion_info($course);
        $cc->delete_all_completion_data();
        $status[] = array('component' => $componentstr,
                'item' => get_string('deletecompletiondata', 'completion'), 'error' => false);
    }

    if (!empty($data->reset_competency_ratings)) {
        \core_competency\api::hook_course_reset_competency_ratings($data->courseid);
        $status[] = array('component' => $componentstr,
            'item' => get_string('deletecompetencyratings', 'core_competency'), 'error' => false);
    }

    $componentstr = get_string('roles');

    if (!empty($data->reset_roles_overrides)) {
        $children = $context->get_child_contexts();
        foreach ($children as $child) {
            $DB->delete_records('role_capabilities', array('contextid' => $child->id));
        }
        $DB->delete_records('role_capabilities', array('contextid' => $context->id));
                $context->mark_dirty();
        $status[] = array('component' => $componentstr, 'item' => get_string('deletecourseoverrides', 'role'), 'error' => false);
    }

    if (!empty($data->reset_roles_local)) {
        $children = $context->get_child_contexts();
        foreach ($children as $child) {
            role_unassign_all(array('contextid' => $child->id));
        }
                $context->mark_dirty();
        $status[] = array('component' => $componentstr, 'item' => get_string('deletelocalroles', 'role'), 'error' => false);
    }

        $data->unenrolled = array();
    if (!empty($data->unenrol_users)) {
        $plugins = enrol_get_plugins(true);
        $instances = enrol_get_instances($data->courseid, true);
        foreach ($instances as $key => $instance) {
            if (!isset($plugins[$instance->enrol])) {
                unset($instances[$key]);
                continue;
            }
        }

        foreach ($data->unenrol_users as $withroleid) {
            if ($withroleid) {
                $sql = "SELECT ue.*
                          FROM {user_enrolments} ue
                          JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                          JOIN {context} c ON (c.contextlevel = :courselevel AND c.instanceid = e.courseid)
                          JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = :roleid AND ra.userid = ue.userid)";
                $params = array('courseid' => $data->courseid, 'roleid' => $withroleid, 'courselevel' => CONTEXT_COURSE);

            } else {
                                $sql = "SELECT ue.*
                          FROM {user_enrolments} ue
                          JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                          JOIN {context} c ON (c.contextlevel = :courselevel AND c.instanceid = e.courseid)
                     LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid)
                         WHERE ra.id IS null";
                $params = array('courseid' => $data->courseid, 'courselevel' => CONTEXT_COURSE);
            }

            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (!isset($instances[$ue->enrolid])) {
                    continue;
                }
                $instance = $instances[$ue->enrolid];
                $plugin = $plugins[$instance->enrol];
                if (!$plugin->allow_unenrol($instance) and !$plugin->allow_unenrol_user($instance, $ue)) {
                    continue;
                }

                $plugin->unenrol_user($instance, $ue->userid);
                $data->unenrolled[$ue->userid] = $ue->userid;
            }
            $rs->close();
        }
    }
    if (!empty($data->unenrolled)) {
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('unenrol', 'enrol').' ('.count($data->unenrolled).')',
            'error' => false
        );
    }

    $componentstr = get_string('groups');

        if (!empty($data->reset_groups_members)) {
        groups_delete_group_members($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('removegroupsmembers', 'group'), 'error' => false);
    }

        if (!empty($data->reset_groups_remove)) {
        groups_delete_groups($data->courseid, false);
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteallgroups', 'group'), 'error' => false);
    }

        if (!empty($data->reset_groupings_members)) {
        groups_delete_groupings_groups($data->courseid, false);
        $status[] = array('component' => $componentstr, 'item' => get_string('removegroupingsmembers', 'group'), 'error' => false);
    }

        if (!empty($data->reset_groupings_remove)) {
        groups_delete_groupings($data->courseid, false);
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteallgroupings', 'group'), 'error' => false);
    }

        $unsupportedmods = array();
    if ($allmods = $DB->get_records('modules') ) {
        foreach ($allmods as $mod) {
            $modname = $mod->name;
            $modfile = $CFG->dirroot.'/mod/'. $modname.'/lib.php';
            $moddeleteuserdata = $modname.'_reset_userdata';               if (file_exists($modfile)) {
                if (!$DB->count_records($modname, array('course' => $data->courseid))) {
                    continue;                 }
                include_once($modfile);
                if (function_exists($moddeleteuserdata)) {
                    $modstatus = $moddeleteuserdata($data);
                    if (is_array($modstatus)) {
                        $status = array_merge($status, $modstatus);
                    } else {
                        debugging('Module '.$modname.' returned incorrect staus - must be an array!');
                    }
                } else {
                    $unsupportedmods[] = $mod;
                }
            } else {
                debugging('Missing lib.php in '.$modname.' module!');
            }
        }
    }

        if (!empty($unsupportedmods)) {
        foreach ($unsupportedmods as $mod) {
            $status[] = array(
                'component' => get_string('modulenameplural', $mod->name),
                'item' => '',
                'error' => get_string('resetnotimplemented')
            );
        }
    }

    $componentstr = get_string('gradebook', 'grades');
        if (!empty($data->reset_gradebook_items)) {
        remove_course_grades($data->courseid, false);
        grade_grab_course_grades($data->courseid);
        grade_regrade_final_grades($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('removeallcourseitems', 'grades'), 'error' => false);

    } else if (!empty($data->reset_gradebook_grades)) {
        grade_course_reset($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('removeallcoursegrades', 'grades'), 'error' => false);
    }
        if (!empty($data->reset_comments)) {
        require_once($CFG->dirroot.'/comment/lib.php');
        comment::reset_course_page_comments($context);
    }

    $event = \core\event\course_reset_ended::create($eventparams);
    $event->trigger();

    return $status;
}


function generate_email_processing_address($modid, $modargs) {
    global $CFG;

    $header = $CFG->mailprefix . substr(base64_encode(pack('C', $modid)), 0, 2).$modargs;
    return $header . substr(md5($header.get_site_identifier()), 0, 16).'@'.$CFG->maildomain;
}


function moodle_process_email($modargs, $body) {
    global $DB;

        switch ($modargs{0}) {
        case 'B': {             list(, $userid) = unpack('V', base64_decode(substr($modargs, 1, 8)));
            if ($user = $DB->get_record("user", array('id' => $userid), "id,email")) {
                                $md5check = substr(md5($user->email), 0, 16);
                if ($md5check == substr($modargs, -16)) {
                    set_bounce_count($user);
                }
                            }
        }
        break;
            }
}



function get_mailer($action='get') {
    global $CFG;

    
    static $mailer  = null;
    static $counter = 0;

    if (!isset($CFG->smtpmaxbulk)) {
        $CFG->smtpmaxbulk = 1;
    }

    if ($action == 'get') {
        $prevkeepalive = false;

        if (isset($mailer) and $mailer->Mailer == 'smtp') {
            if ($counter < $CFG->smtpmaxbulk and !$mailer->isError()) {
                $counter++;
                                $mailer->Priority         = 3;
                $mailer->CharSet          = 'UTF-8';                 $mailer->ContentType      = "text/plain";
                $mailer->Encoding         = "8bit";
                $mailer->From             = "root@localhost";
                $mailer->FromName         = "Root User";
                $mailer->Sender           = "";
                $mailer->Subject          = "";
                $mailer->Body             = "";
                $mailer->AltBody          = "";
                $mailer->ConfirmReadingTo = "";

                $mailer->clearAllRecipients();
                $mailer->clearReplyTos();
                $mailer->clearAttachments();
                $mailer->clearCustomHeaders();
                return $mailer;
            }

            $prevkeepalive = $mailer->SMTPKeepAlive;
            get_mailer('flush');
        }

        require_once($CFG->libdir.'/phpmailer/moodle_phpmailer.php');
        $mailer = new moodle_phpmailer();

        $counter = 1;

        if ($CFG->smtphosts == 'qmail') {
                        $mailer->isQmail();

        } else if (empty($CFG->smtphosts)) {
                        $mailer->isMail();

        } else {
                        $mailer->isSMTP();
            if (!empty($CFG->debugsmtp)) {
                $mailer->SMTPDebug = true;
            }
                        $mailer->Host          = $CFG->smtphosts;
                        $mailer->SMTPSecure    = $CFG->smtpsecure;
                        $mailer->SMTPKeepAlive = $prevkeepalive;

            if ($CFG->smtpuser) {
                                $mailer->SMTPAuth = true;
                $mailer->Username = $CFG->smtpuser;
                $mailer->Password = $CFG->smtppass;
            }
        }

        return $mailer;
    }

    $nothing = null;

        if ($action == 'buffer') {
        if (!empty($CFG->smtpmaxbulk)) {
            get_mailer('flush');
            $m = get_mailer();
            if ($m->Mailer == 'smtp') {
                $m->SMTPKeepAlive = true;
            }
        }
        return $nothing;
    }

        if ($action == 'flush') {
        if (isset($mailer) and $mailer->Mailer == 'smtp') {
            if (!empty($mailer->SMTPDebug)) {
                echo '<pre>'."\n";
            }
            $mailer->SmtpClose();
            if (!empty($mailer->SMTPDebug)) {
                echo '</pre>';
            }
        }
        return $nothing;
    }

        if ($action == 'close') {
        if (isset($mailer) and $mailer->Mailer == 'smtp') {
            get_mailer('flush');
            $mailer->SMTPKeepAlive = false;
        }
        $mailer = null;         return $nothing;
    }
}


function email_should_be_diverted($email) {
    global $CFG;

    if (empty($CFG->divertallemailsto)) {
        return false;
    }

    if (empty($CFG->divertallemailsexcept)) {
        return true;
    }

    $patterns = array_map('trim', explode(',', $CFG->divertallemailsexcept));
    foreach ($patterns as $pattern) {
        if (preg_match("/$pattern/", $email)) {
            return false;
        }
    }

    return true;
}


function generate_email_messageid($localpart = null) {
    global $CFG;

    $urlinfo = parse_url($CFG->wwwroot);
    $base = '@' . $urlinfo['host'];

                    if (isset($urlinfo['path'])) {
        $base = $urlinfo['path'] . $base;
    }

    if (empty($localpart)) {
        $localpart = uniqid('', true);
    }

            $localpart = str_replace('/', '%2F', $localpart);

    return '<' . $localpart . $base . '>';
}


function email_to_user($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
                       $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

    global $CFG, $PAGE, $SITE;

    if (empty($user) or empty($user->id)) {
        debugging('Can not send email to null user', DEBUG_DEVELOPER);
        return false;
    }

    if (empty($user->email)) {
        debugging('Can not send email to user without email: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (!empty($user->deleted)) {
        debugging('Can not send email to deleted user: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
                return true;
    }

    if (!empty($CFG->noemailever)) {
                debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
        return true;
    }

    if (email_should_be_diverted($user->email)) {
        $subject = "[DIVERTED {$user->email}] $subject";
        $user = clone($user);
        $user->email = $CFG->divertallemailsto;
    }

        if ((isset($user->auth) && $user->auth=='nologin') or (isset($user->suspended) && $user->suspended)) {
        return true;
    }

    if (!validate_email($user->email)) {
                debugging("email_to_user: User $user->id (".fullname($user).") email ($user->email) is invalid! Not sending.");
        return false;
    }

    if (over_bounce_threshold($user)) {
        debugging("email_to_user: User $user->id (".fullname($user).") is over bounce threshold! Not sending.");
        return false;
    }

            if (substr($user->email, -8) == '.invalid') {
        debugging("email_to_user: User $user->id (".fullname($user).") email domain ($user->email) is invalid! Not sending.");
        return true;     }

                if (is_mnet_remote_user($user)) {
        require_once($CFG->dirroot.'/mnet/lib.php');

        $jumpurl = mnet_get_idp_jump_url($user);
        $callback = partial('mnet_sso_apply_indirection', $jumpurl);

        $messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
                $callback,
                $messagetext);
        $messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
                $callback,
                $messagehtml);
    }
    $mail = get_mailer();

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    $supportuser = core_user::get_support_user();
    $noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
    $noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

    if (!validate_email($noreplyaddress)) {
        debugging('email_to_user: Invalid noreply-email '.s($noreplyaddress));
        $noreplyaddress = $noreplyaddressdefault;
    }

    if (!validate_email($supportuser->email)) {
        debugging('email_to_user: Invalid support-email '.s($supportuser->email));
        $supportuser->email = $noreplyaddress;
    }

        if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V', $user->id)).substr(md5($user->email), 0, 16);
        $mail->Sender = generate_email_processing_address(0, $modargs);
    } else {
        $mail->Sender = $supportuser->email;
    }

    if (!empty($CFG->emailonlyfromnoreplyaddress)) {
        $usetrueaddress = false;
        if (empty($replyto) && $from->maildisplay) {
            $replyto = $from->email;
            $replytoname = fullname($from);
        }
    }

        if (!empty($replyto) && !validate_email($replyto)) {
        debugging('email_to_user: Invalid replyto-email '.s($replyto));
        $replyto = $noreplyaddress;
    }

    if (is_string($from)) {         $mail->From     = $noreplyaddress;
        $mail->FromName = $from;
    } else if ($usetrueaddress and $from->maildisplay) {
        if (!validate_email($from->email)) {
            debugging('email_to_user: Invalid from-email '.s($from->email).' - not sending');
                        return false;
        }
        $mail->From     = $from->email;
        $mail->FromName = fullname($from);
    } else {
        $mail->From     = $noreplyaddress;
        $mail->FromName = fullname($from);
        if (empty($replyto)) {
            $tempreplyto[] = array($noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }

    $temprecipients[] = array($user->email, fullname($user));

        $mail->WordWrap = $wordwrapwidth;

    if (!empty($from->customheaders)) {
                if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($from->customheaders);
        }
    }

                if (ini_get('mail.add_x_header')) {

        $stack = debug_backtrace(false);
        $origin = $stack[0];

        foreach ($stack as $depth => $call) {
            if ($call['function'] == 'message_send') {
                $origin = $call;
            }
        }

        $originheader = $CFG->wwwroot . ' => ' . gethostname() . ':'
             . str_replace($CFG->dirroot . '/', '', $origin['file']) . ':' . $origin['line'];
        $mail->addCustomHeader('X-Moodle-Originating-Script: ' . $originheader);
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    $renderer = $PAGE->get_renderer('core');
    $context = array(
        'sitefullname' => $SITE->fullname,
        'siteshortname' => $SITE->shortname,
        'sitewwwroot' => $CFG->wwwroot,
        'subject' => $subject,
        'to' => $user->email,
        'toname' => fullname($user),
        'from' => $mail->From,
        'fromname' => $mail->FromName,
    );
    if (!empty($tempreplyto[0])) {
        $context['replyto'] = $tempreplyto[0][0];
        $context['replytoname'] = $tempreplyto[0][1];
    }
    if ($user->id > 0) {
        $context['touserid'] = $user->id;
        $context['tousername'] = $user->username;
    }

    if (!empty($user->mailformat) && $user->mailformat == 1) {
        
        if ($messagehtml) {
                        $context['body'] = $messagehtml;
            $messagehtml = $renderer->render_from_template('core/email_html', $context);

        } else {
                                    $autohtml = trim(text_to_html($messagetext));
            $context['body'] = $autohtml;
            $temphtml = $renderer->render_from_template('core/email_html', $context);
            if ($autohtml != $temphtml) {
                $messagehtml = $temphtml;
            }
        }
    }

    $context['body'] = $messagetext;
    $mail->Subject = $renderer->render_from_template('core/email_subject', $context);
    $mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
    $messagetext = $renderer->render_from_template('core/email_text', $context);

        if (empty($mail->MessageID)) {
        $mail->MessageID = generate_email_messageid();
    }

    if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
                $mail->isHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  "\n$messagetext\n";
    } else {
        $mail->IsHTML(false);
        $mail->Body =  "\n$messagetext\n";
    }

    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" , $attachment )) {
                        $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);

            $attachmentpath = $attachment;

                        $attachpath = str_replace('\\', '/', $attachmentpath);
                        $temppath = str_replace('\\', '/', realpath($CFG->tempdir));

                                    if (strpos($attachpath, $temppath) !== 0) {
                $attachmentpath = $CFG->dataroot . '/' . $attachmentpath;
            }

            $mail->addAttachment($attachmentpath, $attachname, 'base64', $mimetype);
        }
    }

        if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

                $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }

                $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }

    foreach ($temprecipients as $values) {
        $mail->addAddress($values[0], $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
    }

    if ($mail->send()) {
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
                $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => $from->id,
            'relateduserid' => $user->id,
            'other' => array(
                'subject' => $subject,
                'message' => $messagetext,
                'errorinfo' => $mail->ErrorInfo
            )
        ));
        $event->trigger();
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
        }
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }
}


function generate_email_signoff() {
    global $CFG;

    $signoff = "\n";
    if (!empty($CFG->supportname)) {
        $signoff .= $CFG->supportname."\n";
    }
    if (!empty($CFG->supportemail)) {
        $signoff .= $CFG->supportemail."\n";
    }
    if (!empty($CFG->supportpage)) {
        $signoff .= $CFG->supportpage."\n";
    }
    return $signoff;
}


function setnew_password_and_mail($user, $fasthash = false) {
    global $CFG, $DB;

                $lang = empty($user->lang) ? $CFG->lang : $user->lang;

    $site  = get_site();

    $supportuser = core_user::get_support_user();

    $newpassword = generate_password();

    update_internal_user_password($user, $newpassword, $fasthash);

    $a = new stdClass();
    $a->firstname   = fullname($user, true);
    $a->sitename    = format_string($site->fullname);
    $a->username    = $user->username;
    $a->newpassword = $newpassword;
    $a->link        = $CFG->wwwroot .'/login/';
    $a->signoff     = generate_email_signoff();

    $message = (string)new lang_string('newusernewpasswordtext', '', $a, $lang);

    $subject = format_string($site->fullname) .': '. (string)new lang_string('newusernewpasswordsubj', '', $a, $lang);

        return email_to_user($user, $supportuser, $subject, $message);

}


function reset_password_and_mail($user) {
    global $CFG;

    $site  = get_site();
    $supportuser = core_user::get_support_user();

    $userauth = get_auth_plugin($user->auth);
    if (!$userauth->can_reset_password() or !is_enabled_auth($user->auth)) {
        trigger_error("Attempt to reset user password for user $user->username with Auth $user->auth.");
        return false;
    }

    $newpassword = generate_password();

    if (!$userauth->user_update_password($user, $newpassword)) {
        print_error("cannotsetpassword");
    }

    $a = new stdClass();
    $a->firstname   = $user->firstname;
    $a->lastname    = $user->lastname;
    $a->sitename    = format_string($site->fullname);
    $a->username    = $user->username;
    $a->newpassword = $newpassword;
    $a->link        = $CFG->httpswwwroot .'/login/change_password.php';
    $a->signoff     = generate_email_signoff();

    $message = get_string('newpasswordtext', '', $a);

    $subject  = format_string($site->fullname) .': '. get_string('changedpassword');

    unset_user_preference('create_password', $user); 
        return email_to_user($user, $supportuser, $subject, $message);
}


function send_confirmation_email($user) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username);     $data->link  = $CFG->wwwroot .'/login/confirm.php?data='. $user->secret .'/'. $username;
    $message     = get_string('emailconfirmation', '', $data);
    $messagehtml = text_to_html(get_string('emailconfirmation', '', $data), false, false, true);

    $user->mailformat = 1;  
        return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
}


function send_password_change_confirmation_email($user, $resetrecord) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();
    $pwresetmins = isset($CFG->pwresettime) ? floor($CFG->pwresettime / MINSECS) : 30;

    $data = new stdClass();
    $data->firstname = $user->firstname;
    $data->lastname  = $user->lastname;
    $data->username  = $user->username;
    $data->sitename  = format_string($site->fullname);
    $data->link      = $CFG->httpswwwroot .'/login/forgot_password.php?token='. $resetrecord->token;
    $data->admin     = generate_email_signoff();
    $data->resetminutes = $pwresetmins;

    $message = get_string('emailresetconfirmation', '', $data);
    $subject = get_string('emailresetconfirmationsubject', '', format_string($site->fullname));

        return email_to_user($user, $supportuser, $subject, $message);

}


function send_password_change_info($user) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();
    $systemcontext = context_system::instance();

    $data = new stdClass();
    $data->firstname = $user->firstname;
    $data->lastname  = $user->lastname;
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $userauth = get_auth_plugin($user->auth);

    if (!is_enabled_auth($user->auth) or $user->auth == 'nologin') {
        $message = get_string('emailpasswordchangeinfodisabled', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));
                return email_to_user($user, $supportuser, $subject, $message);
    }

    if ($userauth->can_change_password() and $userauth->change_password_url()) {
                $data->link .= $userauth->change_password_url();

    } else {
                $data->link = '';
    }

    if (!empty($data->link) and has_capability('moodle/user:changeownpassword', $systemcontext, $user->id)) {
        $message = get_string('emailpasswordchangeinfo', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));
    } else {
        $message = get_string('emailpasswordchangeinfofail', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));
    }

        return email_to_user($user, $supportuser, $subject, $message);

}


function email_is_not_allowed($email) {
    global $CFG;

    if (!empty($CFG->allowemailaddresses)) {
        $allowed = explode(' ', $CFG->allowemailaddresses);
        foreach ($allowed as $allowedpattern) {
            $allowedpattern = trim($allowedpattern);
            if (!$allowedpattern) {
                continue;
            }
            if (strpos($allowedpattern, '.') === 0) {
                if (strpos(strrev($email), strrev($allowedpattern)) === 0) {
                                        return false;
                }

            } else if (strpos(strrev($email), strrev('@'.$allowedpattern)) === 0) {
                return false;
            }
        }
        return get_string('emailonlyallowed', '', $CFG->allowemailaddresses);

    } else if (!empty($CFG->denyemailaddresses)) {
        $denied = explode(' ', $CFG->denyemailaddresses);
        foreach ($denied as $deniedpattern) {
            $deniedpattern = trim($deniedpattern);
            if (!$deniedpattern) {
                continue;
            }
            if (strpos($deniedpattern, '.') === 0) {
                if (strpos(strrev($email), strrev($deniedpattern)) === 0) {
                                        return get_string('emailnotallowed', '', $CFG->denyemailaddresses);
                }

            } else if (strpos(strrev($email), strrev('@'.$deniedpattern)) === 0) {
                return get_string('emailnotallowed', '', $CFG->denyemailaddresses);
            }
        }
    }

    return false;
}



function get_file_storage() {
    global $CFG;

    static $fs = null;

    if ($fs) {
        return $fs;
    }

    require_once("$CFG->libdir/filelib.php");

    if (isset($CFG->filedir)) {
        $filedir = $CFG->filedir;
    } else {
        $filedir = $CFG->dataroot.'/filedir';
    }

    if (isset($CFG->trashdir)) {
        $trashdirdir = $CFG->trashdir;
    } else {
        $trashdirdir = $CFG->dataroot.'/trashdir';
    }

    $fs = new file_storage($filedir, $trashdirdir, "$CFG->tempdir/filestorage", $CFG->directorypermissions, $CFG->filepermissions);

    return $fs;
}


function get_file_browser() {
    global $CFG;

    static $fb = null;

    if ($fb) {
        return $fb;
    }

    require_once("$CFG->libdir/filelib.php");

    $fb = new file_browser();

    return $fb;
}


function get_file_packer($mimetype='application/zip') {
    global $CFG;

    static $fp = array();

    if (isset($fp[$mimetype])) {
        return $fp[$mimetype];
    }

    switch ($mimetype) {
        case 'application/zip':
        case 'application/vnd.moodle.profiling':
            $classname = 'zip_packer';
            break;

        case 'application/x-gzip' :
            $classname = 'tgz_packer';
            break;

        case 'application/vnd.moodle.backup':
            $classname = 'mbz_packer';
            break;

        default:
            return false;
    }

    require_once("$CFG->libdir/filestorage/$classname.php");
    $fp[$mimetype] = new $classname();

    return $fp[$mimetype];
}


function valid_uploaded_file($newfile) {
    if (empty($newfile)) {
        return '';
    }
    if (is_uploaded_file($newfile['tmp_name']) and $newfile['size'] > 0) {
        return $newfile['tmp_name'];
    } else {
        return '';
    }
}


function get_max_upload_file_size($sitebytes=0, $coursebytes=0, $modulebytes=0, $unused = false) {

    if (! $filesize = ini_get('upload_max_filesize')) {
        $filesize = '5M';
    }
    $minimumsize = get_real_size($filesize);

    if ($postsize = ini_get('post_max_size')) {
        $postsize = get_real_size($postsize);
        if ($postsize < $minimumsize) {
            $minimumsize = $postsize;
        }
    }

    if (($sitebytes > 0) and ($sitebytes < $minimumsize)) {
        $minimumsize = $sitebytes;
    }

    if (($coursebytes > 0) and ($coursebytes < $minimumsize)) {
        $minimumsize = $coursebytes;
    }

    if (($modulebytes > 0) and ($modulebytes < $minimumsize)) {
        $minimumsize = $modulebytes;
    }

    return $minimumsize;
}


function get_user_max_upload_file_size($context, $sitebytes = 0, $coursebytes = 0, $modulebytes = 0, $user = null,
        $unused = false) {
    global $USER;

    if (empty($user)) {
        $user = $USER;
    }

    if (has_capability('moodle/course:ignorefilesizelimits', $context, $user)) {
        return USER_CAN_IGNORE_FILE_SIZE_LIMITS;
    }

    return get_max_upload_file_size($sitebytes, $coursebytes, $modulebytes);
}


function get_max_upload_sizes($sitebytes = 0, $coursebytes = 0, $modulebytes = 0, $custombytes = null) {
    global $CFG;

    if (!$maxsize = get_max_upload_file_size($sitebytes, $coursebytes, $modulebytes)) {
        return array();
    }

    if ($sitebytes == 0) {
                $sitebytes = get_max_upload_file_size();
    }

    $filesize = array();
    $sizelist = array(10240, 51200, 102400, 512000, 1048576, 2097152,
                      5242880, 10485760, 20971520, 52428800, 104857600);

        if (is_number($custombytes) and $custombytes > 0) {
        $custombytes = (int)$custombytes;
        if (!in_array($custombytes, $sizelist)) {
            $sizelist[] = $custombytes;
        }
    } else if (is_array($custombytes)) {
        $sizelist = array_unique(array_merge($sizelist, $custombytes));
    }

        if (isset($CFG->maxbytes) && !in_array(get_real_size($CFG->maxbytes), $sizelist)) {
                $sizelist[] = get_real_size($CFG->maxbytes);
    }

    foreach ($sizelist as $sizebytes) {
        if ($sizebytes < $maxsize && $sizebytes > 0) {
            $filesize[(string)intval($sizebytes)] = display_size($sizebytes);
        }
    }

    $limitlevel = '';
    $displaysize = '';
    if ($modulebytes &&
        (($modulebytes < $coursebytes || $coursebytes == 0) &&
         ($modulebytes < $sitebytes || $sitebytes == 0))) {
        $limitlevel = get_string('activity', 'core');
        $displaysize = display_size($modulebytes);
        $filesize[$modulebytes] = $displaysize; 
    } else if ($coursebytes && ($coursebytes < $sitebytes || $sitebytes == 0)) {
        $limitlevel = get_string('course', 'core');
        $displaysize = display_size($coursebytes);
        $filesize[$coursebytes] = $displaysize; 
    } else if ($sitebytes) {
        $limitlevel = get_string('site', 'core');
        $displaysize = display_size($sitebytes);
        $filesize[$sitebytes] = $displaysize;     }

    krsort($filesize, SORT_NUMERIC);
    if ($limitlevel) {
        $params = (object) array('contextname' => $limitlevel, 'displaysize' => $displaysize);
        $filesize  = array('0' => get_string('uploadlimitwithsize', 'core', $params)) + $filesize;
    }

    return $filesize;
}


function get_directory_list($rootdir, $excludefiles='', $descend=true, $getdirs=false, $getfiles=true) {

    $dirs = array();

    if (!$getdirs and !$getfiles) {           return $dirs;
    }

    if (!is_dir($rootdir)) {                  return $dirs;
    }

    if (!$dir = opendir($rootdir)) {          return $dirs;
    }

    if (!is_array($excludefiles)) {
        $excludefiles = array($excludefiles);
    }

    while (false !== ($file = readdir($dir))) {
        $firstchar = substr($file, 0, 1);
        if ($firstchar == '.' or $file == 'CVS' or in_array($file, $excludefiles)) {
            continue;
        }
        $fullfile = $rootdir .'/'. $file;
        if (filetype($fullfile) == 'dir') {
            if ($getdirs) {
                $dirs[] = $file;
            }
            if ($descend) {
                $subdirs = get_directory_list($fullfile, $excludefiles, $descend, $getdirs, $getfiles);
                foreach ($subdirs as $subdir) {
                    $dirs[] = $file .'/'. $subdir;
                }
            }
        } else if ($getfiles) {
            $dirs[] = $file;
        }
    }
    closedir($dir);

    asort($dirs);

    return $dirs;
}



function get_directory_size($rootdir, $excludefile='') {
    global $CFG;

        if (!empty($CFG->pathtodu) && is_executable(trim($CFG->pathtodu))) {
        $command = trim($CFG->pathtodu).' -sk '.escapeshellarg($rootdir);
        $output = null;
        $return = null;
        exec($command, $output, $return);
        if (is_array($output)) {
                        return get_real_size(intval($output[0]).'k');
        }
    }

    if (!is_dir($rootdir)) {
                return 0;
    }

    if (!$dir = @opendir($rootdir)) {
                return 0;
    }

    $size = 0;

    while (false !== ($file = readdir($dir))) {
        $firstchar = substr($file, 0, 1);
        if ($firstchar == '.' or $file == 'CVS' or $file == $excludefile) {
            continue;
        }
        $fullfile = $rootdir .'/'. $file;
        if (filetype($fullfile) == 'dir') {
            $size += get_directory_size($fullfile, $excludefile);
        } else {
            $size += filesize($fullfile);
        }
    }
    closedir($dir);

    return $size;
}


function display_size($size) {

    static $gb, $mb, $kb, $b;

    if ($size === USER_CAN_IGNORE_FILE_SIZE_LIMITS) {
        return get_string('unlimited');
    }

    if (empty($gb)) {
        $gb = get_string('sizegb');
        $mb = get_string('sizemb');
        $kb = get_string('sizekb');
        $b  = get_string('sizeb');
    }

    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 10) / 10 . $gb;
    } else if ($size >= 1048576) {
        $size = round($size / 1048576 * 10) / 10 . $mb;
    } else if ($size >= 1024) {
        $size = round($size / 1024 * 10) / 10 . $kb;
    } else {
        $size = intval($size) .' '. $b;     }
    return $size;
}


function clean_filename($string) {
    return clean_param($string, PARAM_FILE);
}




function current_language() {
    global $CFG, $USER, $SESSION, $COURSE;

    if (!empty($SESSION->forcelang)) {
                                        $return = $SESSION->forcelang;

    } else if (!empty($COURSE->id) and $COURSE->id != SITEID and !empty($COURSE->lang)) {
                $return = $COURSE->lang;

    } else if (!empty($SESSION->lang)) {
                $return = $SESSION->lang;

    } else if (!empty($USER->lang)) {
        $return = $USER->lang;

    } else if (isset($CFG->lang)) {
        $return = $CFG->lang;

    } else {
        $return = 'en';
    }

        $return = str_replace('_utf8', '', $return);

    return $return;
}


function get_parent_language($lang=null) {

        if (!empty($lang)) {
        $oldforcelang = force_current_language($lang);
    }

    $parentlang = get_string('parentlanguage', 'langconfig');
    if ($parentlang === 'en') {
        $parentlang = '';
    }

        if (!empty($lang)) {
        force_current_language($oldforcelang);
    }

    return $parentlang;
}


function force_current_language($language) {
    global $SESSION;
    $sessionforcelang = isset($SESSION->forcelang) ? $SESSION->forcelang : '';
    if ($language !== $sessionforcelang) {
                if (empty($language) || get_string_manager()->translation_exists($language, false)) {
            $SESSION->forcelang = $language;
            moodle_setlocale();
        }
    }
    return $sessionforcelang;
}


function get_string_manager($forcereload=false) {
    global $CFG;

    static $singleton = null;

    if ($forcereload) {
        $singleton = null;
    }
    if ($singleton === null) {
        if (empty($CFG->early_install_lang)) {

            if (empty($CFG->langlist)) {
                 $translist = array();
            } else {
                $translist = explode(',', $CFG->langlist);
            }

            if (!empty($CFG->config_php_settings['customstringmanager'])) {
                $classname = $CFG->config_php_settings['customstringmanager'];

                if (class_exists($classname)) {
                    $implements = class_implements($classname);

                    if (isset($implements['core_string_manager'])) {
                        $singleton = new $classname($CFG->langotherroot, $CFG->langlocalroot, $translist);
                        return $singleton;

                    } else {
                        debugging('Unable to instantiate custom string manager: class '.$classname.
                            ' does not implement the core_string_manager interface.');
                    }

                } else {
                    debugging('Unable to instantiate custom string manager: class '.$classname.' can not be found.');
                }
            }

            $singleton = new core_string_manager_standard($CFG->langotherroot, $CFG->langlocalroot, $translist);

        } else {
            $singleton = new core_string_manager_install();
        }
    }

    return $singleton;
}


function get_string($identifier, $component = '', $a = null, $lazyload = false) {
    global $CFG;

                    if ($lazyload === true) {
        return new lang_string($identifier, $component, $a);
    }

    if ($CFG->debugdeveloper && clean_param($identifier, PARAM_STRINGID) === '') {
        throw new coding_exception('Invalid string identifier. The identifier cannot be empty. Please fix your get_string() call.', DEBUG_DEVELOPER);
    }

            if (!is_bool($lazyload) && !empty($lazyload)) {
        debugging('extralocations parameter in get_string() is not supported any more, please use standard lang locations only.');
    }

    if (strpos($component, '/') !== false) {
        debugging('The module name you passed to get_string is the deprecated format ' .
                'like mod/mymod or block/myblock. The correct form looks like mymod, or block_myblock.' , DEBUG_DEVELOPER);
        $componentpath = explode('/', $component);

        switch ($componentpath[0]) {
            case 'mod':
                $component = $componentpath[1];
                break;
            case 'blocks':
            case 'block':
                $component = 'block_'.$componentpath[1];
                break;
            case 'enrol':
                $component = 'enrol_'.$componentpath[1];
                break;
            case 'format':
                $component = 'format_'.$componentpath[1];
                break;
            case 'grade':
                $component = 'grade'.$componentpath[1].'_'.$componentpath[2];
                break;
        }
    }

    $result = get_string_manager()->get_string($identifier, $component, $a);

        if (isset($CFG->debugstringids) && $CFG->debugstringids && optional_param('strings', 0, PARAM_INT)) {
        $result .= ' {' . $identifier . '/' . $component . '}';
    }
    return $result;
}


function get_strings($array, $component = '') {
    $string = new stdClass;
    foreach ($array as $item) {
        $string->$item = get_string($item, $component);
    }
    return $string;
}


function print_string($identifier, $component = '', $a = null) {
    echo get_string($identifier, $component, $a);
}


function get_list_of_charsets() {

    $charsets = array(
        'EUC-JP'     => 'EUC-JP',
        'ISO-2022-JP'=> 'ISO-2022-JP',
        'ISO-8859-1' => 'ISO-8859-1',
        'SHIFT-JIS'  => 'SHIFT-JIS',
        'GB2312'     => 'GB2312',
        'GB18030'    => 'GB18030',         'UTF-8'      => 'UTF-8');

    asort($charsets);

    return $charsets;
}


function get_list_of_themes() {
    global $CFG;

    $themes = array();

    if (!empty($CFG->themelist)) {               $themelist = explode(',', $CFG->themelist);
    } else {
        $themelist = array_keys(core_component::get_plugin_list("theme"));
    }

    foreach ($themelist as $key => $themename) {
        $theme = theme_config::load($themename);
        $themes[$themename] = $theme;
    }

    core_collator::asort_objects_by_method($themes, 'get_theme_name');

    return $themes;
}


function get_emoticon_manager() {
    static $singleton = null;

    if (is_null($singleton)) {
        $singleton = new emoticon_manager();
    }

    return $singleton;
}


class emoticon_manager {

    
    public function get_emoticons() {
        global $CFG;

        if (empty($CFG->emoticons)) {
            return array();
        }

        $emoticons = $this->decode_stored_config($CFG->emoticons);

        if (!is_array($emoticons)) {
                        debugging('Invalid format of emoticons setting, please resave the emoticons settings form', DEBUG_NORMAL);
            return array();
        }

        return $emoticons;
    }

    
    public function prepare_renderable_emoticon(stdClass $emoticon, array $attributes = array()) {
        $stringmanager = get_string_manager();
        if ($stringmanager->string_exists($emoticon->altidentifier, $emoticon->altcomponent)) {
            $alt = get_string($emoticon->altidentifier, $emoticon->altcomponent);
        } else {
            $alt = s($emoticon->text);
        }
        return new pix_emoticon($emoticon->imagename, $alt, $emoticon->imagecomponent, $attributes);
    }

    
    public function encode_stored_config(array $emoticons) {
        return json_encode($emoticons);
    }

    
    public function decode_stored_config($encoded) {
        $decoded = json_decode($encoded);
        if (!is_array($decoded)) {
            return null;
        }
        return $decoded;
    }

    
    public function default_emoticons() {
        return array(
            $this->prepare_emoticon_object(":-)", 's/smiley', 'smiley'),
            $this->prepare_emoticon_object(":)", 's/smiley', 'smiley'),
            $this->prepare_emoticon_object(":-D", 's/biggrin', 'biggrin'),
            $this->prepare_emoticon_object(";-)", 's/wink', 'wink'),
            $this->prepare_emoticon_object(":-/", 's/mixed', 'mixed'),
            $this->prepare_emoticon_object("V-.", 's/thoughtful', 'thoughtful'),
            $this->prepare_emoticon_object(":-P", 's/tongueout', 'tongueout'),
            $this->prepare_emoticon_object(":-p", 's/tongueout', 'tongueout'),
            $this->prepare_emoticon_object("B-)", 's/cool', 'cool'),
            $this->prepare_emoticon_object("^-)", 's/approve', 'approve'),
            $this->prepare_emoticon_object("8-)", 's/wideeyes', 'wideeyes'),
            $this->prepare_emoticon_object(":o)", 's/clown', 'clown'),
            $this->prepare_emoticon_object(":-(", 's/sad', 'sad'),
            $this->prepare_emoticon_object(":(", 's/sad', 'sad'),
            $this->prepare_emoticon_object("8-.", 's/shy', 'shy'),
            $this->prepare_emoticon_object(":-I", 's/blush', 'blush'),
            $this->prepare_emoticon_object(":-X", 's/kiss', 'kiss'),
            $this->prepare_emoticon_object("8-o", 's/surprise', 'surprise'),
            $this->prepare_emoticon_object("P-|", 's/blackeye', 'blackeye'),
            $this->prepare_emoticon_object("8-[", 's/angry', 'angry'),
            $this->prepare_emoticon_object("(grr)", 's/angry', 'angry'),
            $this->prepare_emoticon_object("xx-P", 's/dead', 'dead'),
            $this->prepare_emoticon_object("|-.", 's/sleepy', 'sleepy'),
            $this->prepare_emoticon_object("}-]", 's/evil', 'evil'),
            $this->prepare_emoticon_object("(h)", 's/heart', 'heart'),
            $this->prepare_emoticon_object("(heart)", 's/heart', 'heart'),
            $this->prepare_emoticon_object("(y)", 's/yes', 'yes', 'core'),
            $this->prepare_emoticon_object("(n)", 's/no', 'no', 'core'),
            $this->prepare_emoticon_object("(martin)", 's/martin', 'martin'),
            $this->prepare_emoticon_object("( )", 's/egg', 'egg'),
        );
    }

    
    protected function prepare_emoticon_object($text, $imagename, $altidentifier = null,
                                               $altcomponent = 'core_pix', $imagecomponent = 'core') {
        return (object)array(
            'text'           => $text,
            'imagename'      => $imagename,
            'imagecomponent' => $imagecomponent,
            'altidentifier'  => $altidentifier,
            'altcomponent'   => $altcomponent,
        );
    }
}



function rc4encrypt($data) {
    return endecrypt(get_site_identifier(), $data, '');
}


function rc4decrypt($data) {
    return endecrypt(get_site_identifier(), $data, 'de');
}


function endecrypt ($pwd, $data, $case) {

    if ($case == 'de') {
        $data = urldecode($data);
    }

    $key[] = '';
    $box[] = '';
    $pwdlength = strlen($pwd);

    for ($i = 0; $i <= 255; $i++) {
        $key[$i] = ord(substr($pwd, ($i % $pwdlength), 1));
        $box[$i] = $i;
    }

    $x = 0;

    for ($i = 0; $i <= 255; $i++) {
        $x = ($x + $box[$i] + $key[$i]) % 256;
        $tempswap = $box[$i];
        $box[$i] = $box[$x];
        $box[$x] = $tempswap;
    }

    $cipher = '';

    $a = 0;
    $j = 0;

    for ($i = 0; $i < strlen($data); $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $temp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $temp;
        $k = $box[(($box[$a] + $box[$j]) % 256)];
        $cipherby = ord(substr($data, $i, 1)) ^ $k;
        $cipher .= chr($cipherby);
    }

    if ($case == 'de') {
        $cipher = urldecode(urlencode($cipher));
    } else {
        $cipher = urlencode($cipher);
    }

    return $cipher;
}



function is_valid_plugin_name($name) {
        return core_component::is_valid_plugin_name('tool', $name);
}


function get_plugin_list_with_function($plugintype, $function, $file = 'lib.php') {
    global $CFG;

        $plugins = get_plugins_with_function($function, $file, false);

    if (empty($plugins[$plugintype])) {
        return array();
    }

    $allplugins = core_component::get_plugin_list($plugintype);

        $pluginfunctions = array();
    foreach ($plugins[$plugintype] as $pluginname => $functionname) {

                if (!empty($allplugins[$pluginname])) {

            $filepath = $allplugins[$pluginname] . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filepath)) {
                include_once($filepath);
                $pluginfunctions[$plugintype . '_' . $pluginname] = $functionname;
            }
        }
    }

    return $pluginfunctions;
}


function get_plugins_with_function($function, $file = 'lib.php', $include = true) {
    global $CFG;

    $cache = \cache::make('core', 'plugin_functions');

            $key = $function . '_' . clean_param($file, PARAM_ALPHA);

    if ($pluginfunctions = $cache->get($key)) {

                foreach ($pluginfunctions as $plugintype => $plugins) {

            $allplugins = \core_component::get_plugin_list($plugintype);
            foreach ($plugins as $plugin => $fullpath) {

                                if (empty($allplugins[$plugin])) {
                    unset($pluginfunctions[$plugintype][$plugin]);
                    continue;
                }

                $fileexists = file_exists($allplugins[$plugin] . DIRECTORY_SEPARATOR . $file);
                if ($include && $fileexists) {
                                        include_once($allplugins[$plugin] . DIRECTORY_SEPARATOR . $file);
                } else if (!$fileexists) {
                                        unset($pluginfunctions[$plugintype][$plugin]);
                }
            }
        }
        return $pluginfunctions;
    }

    $pluginfunctions = array();

        $plugintypes = \core_component::get_plugin_types();
    foreach ($plugintypes as $plugintype => $unused) {

                $pluginswithfile = \core_component::get_plugin_list_with_file($plugintype, $file, true);
        foreach ($pluginswithfile as $plugin => $notused) {

            $fullfunction = $plugintype . '_' . $plugin . '_' . $function;

            $pluginfunction = false;
            if (function_exists($fullfunction)) {
                                $pluginfunction = $fullfunction;

            } else if ($plugintype === 'mod') {
                                $shortfunction = $plugin . '_' . $function;
                if (function_exists($shortfunction)) {
                    $pluginfunction = $shortfunction;
                }
            }

            if ($pluginfunction) {
                if (empty($pluginfunctions[$plugintype])) {
                    $pluginfunctions[$plugintype] = array();
                }
                $pluginfunctions[$plugintype][$plugin] = $pluginfunction;
            }

        }
    }
    $cache->set($key, $pluginfunctions);

    return $pluginfunctions;

}


function get_list_of_plugins($directory='mod', $exclude='', $basedir='') {
    global $CFG;
    $plugins = array();

    if (empty($basedir)) {
        $basedir = $CFG->dirroot .'/'. $directory;

    } else {
        $basedir = $basedir .'/'. $directory;
    }

    if ($CFG->debugdeveloper and empty($exclude)) {
        
        $subtypes = core_component::get_plugin_types();
        if (in_array($basedir, $subtypes)) {
            debugging('get_list_of_plugins() should not be used to list real plugins, use core_component::get_plugin_list() instead!', DEBUG_DEVELOPER);
        }
        unset($subtypes);
    }

    if (file_exists($basedir) && filetype($basedir) == 'dir') {
        if (!$dirhandle = opendir($basedir)) {
            debugging("Directory permission error for plugin ({$directory}). Directory exists but cannot be read.", DEBUG_DEVELOPER);
            return array();
        }
        while (false !== ($dir = readdir($dirhandle))) {
                        if (strpos($dir, '.') === 0 or $dir === 'CVS' or $dir === '_vti_cnf' or $dir === 'simpletest' or $dir === 'yui' or
                $dir === 'tests' or $dir === 'classes' or $dir === $exclude) {
                continue;
            }
            if (filetype($basedir .'/'. $dir) != 'dir') {
                continue;
            }
            $plugins[] = $dir;
        }
        closedir($dirhandle);
    }
    if ($plugins) {
        asort($plugins);
    }
        return $plugins;
}


function plugin_callback($type, $name, $feature, $action, $params = null, $default = null) {
    return component_callback($type . '_' . $name, $feature . '_' . $action, (array) $params, $default);
}


function component_callback($component, $function, array $params = array(), $default = null) {

    $functionname = component_callback_exists($component, $function);

    if ($functionname) {
                $ret = call_user_func_array($functionname, $params);
        if (is_null($ret)) {
            return $default;
        } else {
            return $ret;
        }
    }
    return $default;
}


function component_callback_exists($component, $function) {
    global $CFG; 
    $cleancomponent = clean_param($component, PARAM_COMPONENT);
    if (empty($cleancomponent)) {
        throw new coding_exception('Invalid component used in plugin/component_callback():' . $component);
    }
    $component = $cleancomponent;

    list($type, $name) = core_component::normalize_component($component);
    $component = $type . '_' . $name;

    $oldfunction = $name.'_'.$function;
    $function = $component.'_'.$function;

    $dir = core_component::get_component_directory($component);
    if (empty($dir)) {
        throw new coding_exception('Invalid component used in plugin/component_callback():' . $component);
    }

        if (file_exists($dir.'/lib.php')) {
        require_once($dir.'/lib.php');
    }

    if (!function_exists($function) and function_exists($oldfunction)) {
        if ($type !== 'mod' and $type !== 'core') {
            debugging("Please use new function name $function instead of legacy $oldfunction", DEBUG_DEVELOPER);
        }
        $function = $oldfunction;
    }

    if (function_exists($function)) {
        return $function;
    }
    return false;
}


function plugin_supports($type, $name, $feature, $default = null) {
    global $CFG;

    if ($type === 'mod' and $name === 'NEWMODULE') {
                return false;
    }

    $component = clean_param($type . '_' . $name, PARAM_COMPONENT);
    if (empty($component)) {
        throw new coding_exception('Invalid component used in plugin_supports():' . $type . '_' . $name);
    }

    $function = null;

    if ($type === 'mod') {
                        if (file_exists("$CFG->dirroot/mod/$name/lib.php")) {
            include_once("$CFG->dirroot/mod/$name/lib.php");
            $function = $component.'_supports';
            if (!function_exists($function)) {
                                $function = $name.'_supports';
            }
        }

    } else {
        if (!$path = core_component::get_plugin_directory($type, $name)) {
                        return false;
        }
        if (file_exists("$path/lib.php")) {
            include_once("$path/lib.php");
            $function = $component.'_supports';
        }
    }

    if ($function and function_exists($function)) {
        $supports = $function($feature);
        if (is_null($supports)) {
                        return $default;
        } else {
            return $supports;
        }
    }

        return $default;
}


function check_php_version($version='5.2.4') {
    return (version_compare(phpversion(), $version) >= 0);
}


function moodle_needs_upgrading() {
    global $CFG;

    if (empty($CFG->version)) {
        return true;
    }

            
    if (empty($CFG->allversionshash)) {
        return true;
    }

    $hash = core_component::get_all_versions_hash();

    return ($hash !== $CFG->allversionshash);
}


function moodle_major_version($fromdisk = false) {
    global $CFG;

    if ($fromdisk) {
        $release = null;
        require($CFG->dirroot.'/version.php');
        if (empty($release)) {
            return false;
        }

    } else {
        if (empty($CFG->release)) {
            return false;
        }
        $release = $CFG->release;
    }

    if (preg_match('/^[0-9]+\.[0-9]+/', $release, $matches)) {
        return $matches[0];
    } else {
        return false;
    }
}



function moodle_setlocale($locale='') {
    global $CFG;

    static $currentlocale = ''; 
    $oldlocale = $currentlocale;

        if ($CFG->ostype == 'WINDOWS') {
        $stringtofetch = 'localewin';
    } else {
        $stringtofetch = 'locale';
    }

        if (!empty($locale)) {
        $currentlocale = $locale;
    } else if (!empty($CFG->locale)) {         $currentlocale = $CFG->locale;
    } else {
        $currentlocale = get_string($stringtofetch, 'langconfig');
    }

        if ($oldlocale == $currentlocale) {
        return;
    }

            
        $monetary= setlocale (LC_MONETARY, 0);
    $numeric = setlocale (LC_NUMERIC, 0);
    $ctype   = setlocale (LC_CTYPE, 0);
    if ($CFG->ostype != 'WINDOWS') {
        $messages= setlocale (LC_MESSAGES, 0);
    }
        $result = setlocale (LC_ALL, $currentlocale);
            if ($result === false) {
        if (stripos($currentlocale, '.UTF-8') !== false) {
            $newlocale = str_ireplace('.UTF-8', '.UTF8', $currentlocale);
            setlocale (LC_ALL, $newlocale);
        } else if (stripos($currentlocale, '.UTF8') !== false) {
            $newlocale = str_ireplace('.UTF8', '.UTF-8', $currentlocale);
            setlocale (LC_ALL, $newlocale);
        }
    }
        setlocale (LC_MONETARY, $monetary);
    setlocale (LC_NUMERIC, $numeric);
    if ($CFG->ostype != 'WINDOWS') {
        setlocale (LC_MESSAGES, $messages);
    }
    if ($currentlocale == 'tr_TR' or $currentlocale == 'tr_TR.UTF-8') {
                setlocale (LC_CTYPE, $ctype);
    }
}


function count_words($string) {
    $string = strip_tags($string);
        $string = html_entity_decode($string);
        $string = preg_replace('/_/u', ' ', $string);
        $string = preg_replace('/[\'"’-]/u', '', $string);
        $string = preg_replace('/([0-9])[.,]([0-9])/u', '$1$2', $string);

    return count(preg_split('/\w\b/u', $string)) - 1;
}


function count_letters($string) {
    $string = strip_tags($string);     $string = preg_replace('/[[:space:]]*/', '', $string); 
    return core_text::strlen($string);
}


function random_string($length=15) {
    $randombytes = random_bytes_emulate($length);
    $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pool .= 'abcdefghijklmnopqrstuvwxyz';
    $pool .= '0123456789';
    $poollen = strlen($pool);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $rand = ord($randombytes[$i]);
        $string .= substr($pool, ($rand%($poollen)), 1);
    }
    return $string;
}


function complex_random_string($length=null) {
    $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $pool .= '`~!@#%^&*()_+-=[];,./<>?:{} ';
    $poollen = strlen($pool);
    if ($length===null) {
        $length = floor(rand(24, 32));
    }
    $randombytes = random_bytes_emulate($length);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $rand = ord($randombytes[$i]);
        $string .= $pool[($rand%$poollen)];
    }
    return $string;
}


function random_bytes_emulate($length) {
    global $CFG;
    if ($length <= 0) {
        debugging('Invalid random bytes length', DEBUG_DEVELOPER);
        return '';
    }
    if (function_exists('random_bytes')) {
                $hash = @random_bytes($length);
        if ($hash !== false) {
            return $hash;
        }
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
                $hash = openssl_random_pseudo_bytes($length);
        if ($hash !== false) {
            return $hash;
        }
    }

        $staticdata = serialize($CFG) . serialize($_SERVER);
    $hash = '';
    do {
        $hash .= sha1($staticdata . microtime(true) . uniqid('', true), true);
    } while (strlen($hash) < $length);
    return substr($hash, 0, $length);
}


function shorten_text($text, $ideal=30, $exact = false, $ending='...') {
        if (core_text::strlen(preg_replace('/<.*?>/', '', $text)) <= $ideal) {
        return $text;
    }

            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

    $totallength = core_text::strlen($ending);
    $truncate = '';

                    $tagdetails = array();

    foreach ($lines as $linematchings) {
                if (!empty($linematchings[1])) {
                        if (!preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $linematchings[1])) {
                if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $linematchings[1], $tagmatchings)) {
                                        $tagdetails[] = (object) array(
                            'open' => false,
                            'tag'  => core_text::strtolower($tagmatchings[1]),
                            'pos'  => core_text::strlen($truncate),
                        );

                } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $linematchings[1], $tagmatchings)) {
                                        $tagdetails[] = (object) array(
                            'open' => true,
                            'tag'  => core_text::strtolower($tagmatchings[1]),
                            'pos'  => core_text::strlen($truncate),
                        );
                } else if (preg_match('/^<!--\[if\s.*?\]>$/s', $linematchings[1], $tagmatchings)) {
                    $tagdetails[] = (object) array(
                            'open' => true,
                            'tag'  => core_text::strtolower('if'),
                            'pos'  => core_text::strlen($truncate),
                    );
                } else if (preg_match('/^<!--<!\[endif\]-->$/s', $linematchings[1], $tagmatchings)) {
                    $tagdetails[] = (object) array(
                            'open' => false,
                            'tag'  => core_text::strtolower('if'),
                            'pos'  => core_text::strlen($truncate),
                    );
                }
            }
                        $truncate .= $linematchings[1];
        }

                $contentlength = core_text::strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $linematchings[2]));
        if ($totallength + $contentlength > $ideal) {
                        $left = $ideal - $totallength;
            $entitieslength = 0;
                        if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $linematchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                                foreach ($entities[0] as $entity) {
                    if ($entity[1]+1-$entitieslength <= $left) {
                        $left--;
                        $entitieslength += core_text::strlen($entity[0]);
                    } else {
                                                break;
                    }
                }
            }
            $breakpos = $left + $entitieslength;

                        if (!$exact) {
                                for (; $breakpos > 0; $breakpos--) {
                    if ($char = core_text::substr($linematchings[2], $breakpos, 1)) {
                        if ($char === '.' or $char === ' ') {
                            $breakpos += 1;
                            break;
                        } else if (strlen($char) > 2) {
                                                        $breakpos += 1;
                            break;
                        }
                    }
                }
            }
            if ($breakpos == 0) {
                                $breakpos = $left + $entitieslength;
            } else if ($breakpos > $left + $entitieslength) {
                                $breakpos = $left + $entitieslength;
            }

            $truncate .= core_text::substr($linematchings[2], 0, $breakpos);
                        break;
        } else {
            $truncate .= $linematchings[2];
            $totallength += $contentlength;
        }

                if ($totallength >= $ideal) {
            break;
        }
    }

        $truncate .= $ending;

        $opentags = array();
    foreach ($tagdetails as $taginfo) {
        if ($taginfo->open) {
                        array_unshift($opentags, $taginfo->tag);
        } else {
                        $pos = array_search($taginfo->tag, array_reverse($opentags, true));
            if ($pos !== false) {
                unset($opentags[$pos]);
            }
        }
    }

        foreach ($opentags as $tag) {
        if ($tag === 'if') {
            $truncate .= '<!--<![endif]-->';
        } else {
            $truncate .= '</' . $tag . '>';
        }
    }

    return $truncate;
}



function getweek ($startdate, $thedate) {
    if ($thedate < $startdate) {
        return 0;
    }

    return floor(($thedate - $startdate) / WEEKSECS) + 1;
}


function generate_password($maxlen=10) {
    global $CFG;

    if (empty($CFG->passwordpolicy)) {
        $fillers = PASSWORD_DIGITS;
        $wordlist = file($CFG->wordlist);
        $word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
        $word2 = trim($wordlist[rand(0, count($wordlist) - 1)]);
        $filler1 = $fillers[rand(0, strlen($fillers) - 1)];
        $password = $word1 . $filler1 . $word2;
    } else {
        $minlen = !empty($CFG->minpasswordlength) ? $CFG->minpasswordlength : 0;
        $digits = $CFG->minpassworddigits;
        $lower = $CFG->minpasswordlower;
        $upper = $CFG->minpasswordupper;
        $nonalphanum = $CFG->minpasswordnonalphanum;
        $total = $lower + $upper + $digits + $nonalphanum;
                $minlen = $minlen < $total ? $total : $minlen;
                $maxlen = $minlen > $maxlen ? $minlen : $maxlen;
        $additional = $maxlen - $total;

                        $passworddigits = PASSWORD_DIGITS;
        while ($digits > strlen($passworddigits)) {
            $passworddigits .= PASSWORD_DIGITS;
        }
        $passwordlower = PASSWORD_LOWER;
        while ($lower > strlen($passwordlower)) {
            $passwordlower .= PASSWORD_LOWER;
        }
        $passwordupper = PASSWORD_UPPER;
        while ($upper > strlen($passwordupper)) {
            $passwordupper .= PASSWORD_UPPER;
        }
        $passwordnonalphanum = PASSWORD_NONALPHANUM;
        while ($nonalphanum > strlen($passwordnonalphanum)) {
            $passwordnonalphanum .= PASSWORD_NONALPHANUM;
        }

                $password = str_shuffle (substr(str_shuffle ($passwordlower), 0, $lower) .
                                 substr(str_shuffle ($passwordupper), 0, $upper) .
                                 substr(str_shuffle ($passworddigits), 0, $digits) .
                                 substr(str_shuffle ($passwordnonalphanum), 0 , $nonalphanum) .
                                 substr(str_shuffle ($passwordlower .
                                                     $passwordupper .
                                                     $passworddigits .
                                                     $passwordnonalphanum), 0 , $additional));
    }

    return substr ($password, 0, $maxlen);
}


function format_float($float, $decimalpoints=1, $localized=true, $stripzeros=false) {
    if (is_null($float)) {
        return '';
    }
    if ($localized) {
        $separator = get_string('decsep', 'langconfig');
    } else {
        $separator = '.';
    }
    $result = number_format($float, $decimalpoints, $separator, '');
    if ($stripzeros) {
                $result = preg_replace('~(' . preg_quote($separator) . ')?0+$~', '', $result);
    }
    return $result;
}


function unformat_float($localefloat, $strict = false) {
    $localefloat = trim($localefloat);

    if ($localefloat == '') {
        return null;
    }

    $localefloat = str_replace(' ', '', $localefloat);     $localefloat = str_replace(get_string('decsep', 'langconfig'), '.', $localefloat);

    if ($strict && !is_numeric($localefloat)) {
        return false;
    }

    return (float)$localefloat;
}


function swapshuffle($array) {

    $last = count($array) - 1;
    for ($i = 0; $i <= $last; $i++) {
        $from = rand(0, $last);
        $curr = $array[$i];
        $array[$i] = $array[$from];
        $array[$from] = $curr;
    }
    return $array;
}


function swapshuffle_assoc($array) {

    $newarray = array();
    $newkeys = swapshuffle(array_keys($array));

    foreach ($newkeys as $newkey) {
        $newarray[$newkey] = $array[$newkey];
    }
    return $newarray;
}


function draw_rand_array($array, $draws) {

    $return = array();

    $last = count($array);

    if ($draws > $last) {
        $draws = $last;
    }

    while ($draws > 0) {
        $last--;

        $keys = array_keys($array);
        $rand = rand(0, $last);

        $return[$keys[$rand]] = $array[$keys[$rand]];
        unset($array[$keys[$rand]]);

        $draws--;
    }

    return $return;
}


function microtime_diff($a, $b) {
    list($adec, $asec) = explode(' ', $a);
    list($bdec, $bsec) = explode(' ', $b);
    return $bsec - $asec + $bdec - $adec;
}


function make_menu_from_list($list, $separator=',') {

    $array = array_reverse(explode($separator, $list), true);
    foreach ($array as $key => $item) {
        $outarray[$key+1] = trim($item);
    }
    return $outarray;
}


function make_grades_menu($gradingtype) {
    global $DB;

    $grades = array();
    if ($gradingtype < 0) {
        if ($scale = $DB->get_record('scale', array('id'=> (-$gradingtype)))) {
            return make_menu_from_list($scale->scale);
        }
    } else if ($gradingtype > 0) {
        for ($i=$gradingtype; $i>=0; $i--) {
            $grades[$i] = $i .' / '. $gradingtype;
        }
        return $grades;
    }
    return $grades;
}


function make_unique_id_code($extra = '') {

    $hostname = 'unknownhost';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    } else if (!empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    } else if (!empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    }

    $date = gmdate("ymdHis");

    $random =  random_string(6);

    if ($extra) {
        return $hostname .'+'. $date .'+'. $random .'+'. $extra;
    } else {
        return $hostname .'+'. $date .'+'. $random;
    }
}



function address_in_subnet($addr, $subnetstr) {

    if ($addr == '0.0.0.0') {
        return false;
    }
    $subnets = explode(',', $subnetstr);
    $found = false;
    $addr = trim($addr);
    $addr = cleanremoteaddr($addr, false);     if ($addr === null) {
        return false;
    }
    $addrparts = explode(':', $addr);

    $ipv6 = strpos($addr, ':');

    foreach ($subnets as $subnet) {
        $subnet = trim($subnet);
        if ($subnet === '') {
            continue;
        }

        if (strpos($subnet, '/') !== false) {
                        list($ip, $mask) = explode('/', $subnet);
            $mask = trim($mask);
            if (!is_number($mask)) {
                continue;             }
            $ip = cleanremoteaddr($ip, false);             if ($ip === null) {
                continue;
            }
            if (strpos($ip, ':') !== false) {
                                if (!$ipv6) {
                    continue;
                }
                if ($mask > 128 or $mask < 0) {
                    continue;                 }
                if ($mask == 0) {
                    return true;                 }
                if ($mask == 128) {
                    if ($ip === $addr) {
                        return true;
                    }
                    continue;
                }
                $ipparts = explode(':', $ip);
                $modulo  = $mask % 16;
                $ipnet   = array_slice($ipparts, 0, ($mask-$modulo)/16);
                $addrnet = array_slice($addrparts, 0, ($mask-$modulo)/16);
                if (implode(':', $ipnet) === implode(':', $addrnet)) {
                    if ($modulo == 0) {
                        return true;
                    }
                    $pos     = ($mask-$modulo)/16;
                    $ipnet   = hexdec($ipparts[$pos]);
                    $addrnet = hexdec($addrparts[$pos]);
                    $mask    = 0xffff << (16 - $modulo);
                    if (($addrnet & $mask) == ($ipnet & $mask)) {
                        return true;
                    }
                }

            } else {
                                if ($ipv6) {
                    continue;
                }
                if ($mask > 32 or $mask < 0) {
                    continue;                 }
                if ($mask == 0) {
                    return true;
                }
                if ($mask == 32) {
                    if ($ip === $addr) {
                        return true;
                    }
                    continue;
                }
                $mask = 0xffffffff << (32 - $mask);
                if (((ip2long($addr) & $mask) == (ip2long($ip) & $mask))) {
                    return true;
                }
            }

        } else if (strpos($subnet, '-') !== false) {
                        $parts = explode('-', $subnet);
            if (count($parts) != 2) {
                continue;
            }

            if (strpos($subnet, ':') !== false) {
                                if (!$ipv6) {
                    continue;
                }
                $ipstart = cleanremoteaddr(trim($parts[0]), false);                 if ($ipstart === null) {
                    continue;
                }
                $ipparts = explode(':', $ipstart);
                $start = hexdec(array_pop($ipparts));
                $ipparts[] = trim($parts[1]);
                $ipend = cleanremoteaddr(implode(':', $ipparts), false);                 if ($ipend === null) {
                    continue;
                }
                $ipparts[7] = '';
                $ipnet = implode(':', $ipparts);
                if (strpos($addr, $ipnet) !== 0) {
                    continue;
                }
                $ipparts = explode(':', $ipend);
                $end = hexdec($ipparts[7]);

                $addrend = hexdec($addrparts[7]);

                if (($addrend >= $start) and ($addrend <= $end)) {
                    return true;
                }

            } else {
                                if ($ipv6) {
                    continue;
                }
                $ipstart = cleanremoteaddr(trim($parts[0]), false);                 if ($ipstart === null) {
                    continue;
                }
                $ipparts = explode('.', $ipstart);
                $ipparts[3] = trim($parts[1]);
                $ipend = cleanremoteaddr(implode('.', $ipparts), false);                 if ($ipend === null) {
                    continue;
                }

                if ((ip2long($addr) >= ip2long($ipstart)) and (ip2long($addr) <= ip2long($ipend))) {
                    return true;
                }
            }

        } else {
                        if (strpos($subnet, ':') !== false) {
                                if (!$ipv6) {
                    continue;
                }
                $parts = explode(':', $subnet);
                $count = count($parts);
                if ($parts[$count-1] === '') {
                    unset($parts[$count-1]);                     $count--;
                    $subnet = implode('.', $parts);
                }
                $isip = cleanremoteaddr($subnet, false);                 if ($isip !== null) {
                    if ($isip === $addr) {
                        return true;
                    }
                    continue;
                } else if ($count > 8) {
                    continue;
                }
                $zeros = array_fill(0, 8-$count, '0');
                $subnet = $subnet.':'.implode(':', $zeros).'/'.($count*16);
                if (address_in_subnet($addr, $subnet)) {
                    return true;
                }

            } else {
                                if ($ipv6) {
                    continue;
                }
                $parts = explode('.', $subnet);
                $count = count($parts);
                if ($parts[$count-1] === '') {
                    unset($parts[$count-1]);                     $count--;
                    $subnet = implode('.', $parts);
                }
                if ($count == 4) {
                    $subnet = cleanremoteaddr($subnet, false);                     if ($subnet === $addr) {
                        return true;
                    }
                    continue;
                } else if ($count > 4) {
                    continue;
                }
                $zeros = array_fill(0, 4-$count, '0');
                $subnet = $subnet.'.'.implode('.', $zeros).'/'.($count*8);
                if (address_in_subnet($addr, $subnet)) {
                    return true;
                }
            }
        }
    }

    return false;
}


function mtrace($string, $eol="\n", $sleep=0) {

    if (defined('STDOUT') && !PHPUNIT_TEST && !defined('BEHAT_TEST')) {
        fwrite(STDOUT, $string.$eol);
    } else {
        echo $string . $eol;
    }

    flush();

        if ($sleep) {
        sleep($sleep);
    }
}


function cleardoubleslashes ($path) {
    return preg_replace('/(\/|\\\){1,}/', '/', $path);
}


function remoteip_in_list($list) {
    $inlist = false;
    $clientip = getremoteaddr(null);

    if (!$clientip) {
                return true;
    }

    $list = explode("\n", $list);
    foreach ($list as $subnet) {
        $subnet = trim($subnet);
        if (address_in_subnet($clientip, $subnet)) {
            $inlist = true;
            break;
        }
    }
    return $inlist;
}


function getremoteaddr($default='0.0.0.0') {
    global $CFG;

    if (empty($CFG->getremoteaddrconf)) {
                        $variablestoskip = 0;
    } else {
        $variablestoskip = $CFG->getremoteaddrconf;
    }
    if (!($variablestoskip & GETREMOTEADDR_SKIP_HTTP_CLIENT_IP)) {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $address = cleanremoteaddr($_SERVER['HTTP_CLIENT_IP']);
            return $address ? $address : $default;
        }
    }
    if (!($variablestoskip & GETREMOTEADDR_SKIP_HTTP_X_FORWARDED_FOR)) {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwardedaddresses = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $address = $forwardedaddresses[0];

            if (substr_count($address, ":") > 1) {
                                if (preg_match("/\[(.*)\]:/", $address, $matches)) {
                    $address = $matches[1];
                }
            } else {
                                if (substr_count($address, ":") == 1) {
                    $parts = explode(":", $address);
                    $address = $parts[0];
                }
            }

            $address = cleanremoteaddr($address);
            return $address ? $address : $default;
        }
    }
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $address = cleanremoteaddr($_SERVER['REMOTE_ADDR']);
        return $address ? $address : $default;
    } else {
        return $default;
    }
}


function cleanremoteaddr($addr, $compress=false) {
    $addr = trim($addr);

    
    if (strpos($addr, ':') !== false) {
                $parts = explode(':', $addr);
        $count = count($parts);

        if (strpos($parts[$count-1], '.') !== false) {
                        $last = array_pop($parts);
            $ipv4 = cleanremoteaddr($last, true);
            if ($ipv4 === null) {
                return null;
            }
            $bits = explode('.', $ipv4);
            $parts[] = dechex($bits[0]).dechex($bits[1]);
            $parts[] = dechex($bits[2]).dechex($bits[3]);
            $count = count($parts);
            $addr = implode(':', $parts);
        }

        if ($count < 3 or $count > 8) {
            return null;         }

        if ($count != 8) {
            if (strpos($addr, '::') === false) {
                return null;             }
                        $insertat = array_search('', $parts, true);
            $missing = array_fill(0, 1 + 8 - $count, '0');
            array_splice($parts, $insertat, 1, $missing);
            foreach ($parts as $key => $part) {
                if ($part === '') {
                    $parts[$key] = '0';
                }
            }
        }

        $adr = implode(':', $parts);
        if (!preg_match('/^([0-9a-f]{1,4})(:[0-9a-f]{1,4})*$/i', $adr)) {
            return null;         }

                $parts = array_map('hexdec', $parts);
        $parts = array_map('dechex', $parts);

        $result = implode(':', $parts);

        if (!$compress) {
            return $result;
        }

        if ($result === '0:0:0:0:0:0:0:0') {
            return '::';         }

        $compressed = preg_replace('/(:0)+:0$/', '::', $result, 1);
        if ($compressed !== $result) {
            return $compressed;
        }

        $compressed = preg_replace('/^(0:){2,7}/', '::', $result, 1);
        if ($compressed !== $result) {
            return $compressed;
        }

        $compressed = preg_replace('/(:0){2,6}:/', '::', $result, 1);
        if ($compressed !== $result) {
            return $compressed;
        }

        return $result;
    }

        $parts = array();
    if (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $addr, $parts)) {
        return null;
    }
    unset($parts[0]);

    foreach ($parts as $key => $match) {
        if ($match > 255) {
            return null;
        }
        $parts[$key] = (int)$match;     }

    return implode('.', $parts);
}


function fullclone($thing) {
    return unserialize(serialize($thing));
}

 
function message_popup_window() {
    global $USER, $DB, $PAGE, $CFG;

    if (!$PAGE->get_popup_notification_allowed() || empty($CFG->messaging)) {
        return;
    }

    if (!isloggedin() || isguestuser()) {
        return;
    }

    if (!isset($USER->message_lastpopup)) {
        $USER->message_lastpopup = 0;
    } else if ($USER->message_lastpopup > (time()-120)) {
                return;
    }

        $messagecount = $DB->count_records('message', array('useridto' => $USER->id));
    if ($messagecount < 1) {
        return;
    }

        $messagesql = "SELECT m.id, c.blocked
                     FROM {message} m
                     JOIN {message_working} mw ON m.id=mw.unreadmessageid
                     JOIN {message_processors} p ON mw.processorid=p.id
                     LEFT JOIN {message_contacts} c ON c.contactid = m.useridfrom
                                                   AND c.userid = m.useridto
                    WHERE m.useridto = :userid
                      AND p.name='popup'";

            $lastnotifiedlongago = $USER->message_lastpopup < (time()-3600);
    if (!$lastnotifiedlongago) {
        $messagesql .= 'AND m.timecreated > :lastpopuptime';
    }

    $waitingmessages = $DB->get_records_sql($messagesql, array('userid' => $USER->id, 'lastpopuptime' => $USER->message_lastpopup));

    $validmessages = 0;
    foreach ($waitingmessages as $messageinfo) {
        if ($messageinfo->blocked) {
                                    $messageobject = $DB->get_record('message', array('id' => $messageinfo->id));
            message_mark_message_read($messageobject, time());
        } else {
            $validmessages++;
        }
    }

    if ($validmessages > 0) {
        $strmessages = get_string('unreadnewmessages', 'message', $validmessages);
        $strgomessage = get_string('gotomessages', 'message');
        $strstaymessage = get_string('ignore', 'admin');

        $notificationsound = null;
        $beep = get_user_preferences('message_beepnewmessage', '');
        if (!empty($beep)) {
                        $sourcetags =  html_writer::empty_tag('source', array('src' => $CFG->wwwroot.'/message/bell.wav', 'type' => 'audio/wav'));
            $sourcetags .= html_writer::empty_tag('source', array('src' => $CFG->wwwroot.'/message/bell.ogg', 'type' => 'audio/ogg'));
            $sourcetags .= html_writer::empty_tag('source', array('src' => $CFG->wwwroot.'/message/bell.mp3', 'type' => 'audio/mpeg'));
            $sourcetags .= html_writer::empty_tag('embed',  array('src' => $CFG->wwwroot.'/message/bell.wav', 'autostart' => 'true', 'hidden' => 'true'));

            $notificationsound = html_writer::tag('audio', $sourcetags, array('preload' => 'auto', 'autoplay' => 'autoplay'));
        }

        $url = $CFG->wwwroot.'/message/index.php';
        $content =  html_writer::start_tag('div', array('id' => 'newmessageoverlay', 'class' => 'mdl-align')).
                        html_writer::start_tag('div', array('id' => 'newmessagetext')).
                            $strmessages.
                        html_writer::end_tag('div').

                        $notificationsound.
                        html_writer::start_tag('div', array('id' => 'newmessagelinks')).
                        html_writer::link($url, $strgomessage, array('id' => 'notificationyes')).'&nbsp;&nbsp;&nbsp;'.
                        html_writer::link('', $strstaymessage, array('id' => 'notificationno')).
                        html_writer::end_tag('div');
                    html_writer::end_tag('div');

        $PAGE->requires->js_init_call('M.core_message.init_notification', array('', $content, $url));

        $USER->message_lastpopup = time();
    }
}


function bounded_number($min, $value, $max) {
    if ($value < $min) {
        return $min;
    }
    if ($value > $max) {
        return $max;
    }
    return $value;
}


function array_is_nested($array) {
    foreach ($array as $value) {
        if (is_array($value)) {
            return true;
        }
    }
    return false;
}


function get_performance_info() {
    global $CFG, $PERF, $DB, $PAGE;

    $info = array();
    $info['html'] = '';             $info['txt']  = me() . ' '; 
    $info['realtime'] = microtime_diff($PERF->starttime, microtime());

    $info['html'] .= '<span class="timeused">'.$info['realtime'].' secs</span> ';
    $info['txt'] .= 'time: '.$info['realtime'].'s ';

    if (function_exists('memory_get_usage')) {
        $info['memory_total'] = memory_get_usage();
        $info['memory_growth'] = memory_get_usage() - $PERF->startmemory;
        $info['html'] .= '<span class="memoryused">RAM: '.display_size($info['memory_total']).'</span> ';
        $info['txt']  .= 'memory_total: '.$info['memory_total'].'B (' . display_size($info['memory_total']).') memory_growth: '.
            $info['memory_growth'].'B ('.display_size($info['memory_growth']).') ';
    }

    if (function_exists('memory_get_peak_usage')) {
        $info['memory_peak'] = memory_get_peak_usage();
        $info['html'] .= '<span class="memoryused">RAM peak: '.display_size($info['memory_peak']).'</span> ';
        $info['txt']  .= 'memory_peak: '.$info['memory_peak'].'B (' . display_size($info['memory_peak']).') ';
    }

    $inc = get_included_files();
    $info['includecount'] = count($inc);
    $info['html'] .= '<span class="included">Included '.$info['includecount'].' files</span> ';
    $info['txt']  .= 'includecount: '.$info['includecount'].' ';

    if (!empty($CFG->early_install_lang) or empty($PAGE)) {
                return $info;
    }

    $filtermanager = filter_manager::instance();
    if (method_exists($filtermanager, 'get_performance_summary')) {
        list($filterinfo, $nicenames) = $filtermanager->get_performance_summary();
        $info = array_merge($filterinfo, $info);
        foreach ($filterinfo as $key => $value) {
            $info['html'] .= "<span class='$key'>$nicenames[$key]: $value </span> ";
            $info['txt'] .= "$key: $value ";
        }
    }

    $stringmanager = get_string_manager();
    if (method_exists($stringmanager, 'get_performance_summary')) {
        list($filterinfo, $nicenames) = $stringmanager->get_performance_summary();
        $info = array_merge($filterinfo, $info);
        foreach ($filterinfo as $key => $value) {
            $info['html'] .= "<span class='$key'>$nicenames[$key]: $value </span> ";
            $info['txt'] .= "$key: $value ";
        }
    }

    if (!empty($PERF->logwrites)) {
        $info['logwrites'] = $PERF->logwrites;
        $info['html'] .= '<span class="logwrites">Log DB writes '.$info['logwrites'].'</span> ';
        $info['txt'] .= 'logwrites: '.$info['logwrites'].' ';
    }

    $info['dbqueries'] = $DB->perf_get_reads().'/'.($DB->perf_get_writes() - $PERF->logwrites);
    $info['html'] .= '<span class="dbqueries">DB reads/writes: '.$info['dbqueries'].'</span> ';
    $info['txt'] .= 'db reads/writes: '.$info['dbqueries'].' ';

    $info['dbtime'] = round($DB->perf_get_queries_time(), 5);
    $info['html'] .= '<span class="dbtime">DB queries time: '.$info['dbtime'].' secs</span> ';
    $info['txt'] .= 'db queries time: ' . $info['dbtime'] . 's ';

    if (function_exists('posix_times')) {
        $ptimes = posix_times();
        if (is_array($ptimes)) {
            foreach ($ptimes as $key => $val) {
                $info[$key] = $ptimes[$key] -  $PERF->startposixtimes[$key];
            }
            $info['html'] .= "<span class=\"posixtimes\">ticks: $info[ticks] user: $info[utime] sys: $info[stime] cuser: $info[cutime] csys: $info[cstime]</span> ";
            $info['txt'] .= "ticks: $info[ticks] user: $info[utime] sys: $info[stime] cuser: $info[cutime] csys: $info[cstime] ";
        }
    }

                if (is_readable('/proc/loadavg') && $loadavg = @file('/proc/loadavg')) {
        list($serverload) = explode(' ', $loadavg[0]);
        unset($loadavg);
    } else if ( function_exists('is_executable') && is_executable('/usr/bin/uptime') && $loadavg = `/usr/bin/uptime` ) {
        if (preg_match('/load averages?: (\d+[\.,:]\d+)/', $loadavg, $matches)) {
            $serverload = $matches[1];
        } else {
            trigger_error('Could not parse uptime output!');
        }
    }
    if (!empty($serverload)) {
        $info['serverload'] = $serverload;
        $info['html'] .= '<span class="serverload">Load average: '.$info['serverload'].'</span> ';
        $info['txt'] .= "serverload: {$info['serverload']} ";
    }

        if ($si = \core\session\manager::get_performance_info()) {
        $info['sessionsize'] = $si['size'];
        $info['html'] .= $si['html'];
        $info['txt'] .= $si['txt'];
    }

    if ($stats = cache_helper::get_stats()) {
        $html = '<span class="cachesused">';
        $html .= '<span class="cache-stats-heading">Caches used (hits/misses/sets)</span>';
        $text = 'Caches used (hits/misses/sets): ';
        $hits = 0;
        $misses = 0;
        $sets = 0;
        foreach ($stats as $definition => $details) {
            switch ($details['mode']) {
                case cache_store::MODE_APPLICATION:
                    $modeclass = 'application';
                    $mode = ' <span title="application cache">[a]</span>';
                    break;
                case cache_store::MODE_SESSION:
                    $modeclass = 'session';
                    $mode = ' <span title="session cache">[s]</span>';
                    break;
                case cache_store::MODE_REQUEST:
                    $modeclass = 'request';
                    $mode = ' <span title="request cache">[r]</span>';
                    break;
            }
            $html .= '<span class="cache-definition-stats cache-mode-'.$modeclass.'">';
            $html .= '<span class="cache-definition-stats-heading">'.$definition.$mode.'</span>';
            $text .= "$definition {";
            foreach ($details['stores'] as $store => $data) {
                $hits += $data['hits'];
                $misses += $data['misses'];
                $sets += $data['sets'];
                if ($data['hits'] == 0 and $data['misses'] > 0) {
                    $cachestoreclass = 'nohits';
                } else if ($data['hits'] < $data['misses']) {
                    $cachestoreclass = 'lowhits';
                } else {
                    $cachestoreclass = 'hihits';
                }
                $text .= "$store($data[hits]/$data[misses]/$data[sets]) ";
                $html .= "<span class=\"cache-store-stats $cachestoreclass\">$store: $data[hits] / $data[misses] / $data[sets]</span>";
            }
            $html .= '</span>';
            $text .= '} ';
        }
        $html .= "<span class='cache-total-stats'>Total: $hits / $misses / $sets</span>";
        $html .= '</span> ';
        $info['cachesused'] = "$hits / $misses / $sets";
        $info['html'] .= $html;
        $info['txt'] .= $text.'. ';
    } else {
        $info['cachesused'] = '0 / 0 / 0';
        $info['html'] .= '<span class="cachesused">Caches used (hits/misses/sets): 0/0/0</span>';
        $info['txt'] .= 'Caches used (hits/misses/sets): 0/0/0 ';
    }

    $info['html'] = '<div class="performanceinfo siteinfo">'.$info['html'].'</div>';
    return $info;
}


function remove_dir($dir, $contentonly=false) {
    if (!file_exists($dir)) {
                return true;
    }
    if (!$handle = opendir($dir)) {
        return false;
    }
    $result = true;
    while (false!==($item = readdir($handle))) {
        if ($item != '.' && $item != '..') {
            if (is_dir($dir.'/'.$item)) {
                $result = remove_dir($dir.'/'.$item) && $result;
            } else {
                $result = unlink($dir.'/'.$item) && $result;
            }
        }
    }
    closedir($handle);
    if ($contentonly) {
        clearstatcache();         return $result;
    }
    $result = rmdir($dir);     clearstatcache();     return $result;
}


function object_property_exists( $obj, $property ) {
    if (is_string( $obj )) {
        $properties = get_class_vars( $obj );
    } else {
        $properties = get_object_vars( $obj );
    }
    return array_key_exists( $property, $properties );
}


function convert_to_array($var) {
    $result = array();

        foreach ($var as $key => $value) {
                if (is_object($value) || is_array($value)) {
            $result[$key] = convert_to_array($value);
        } else {
                        $result[$key] = $value;
        }
    }
    return $result;
}


function custom_script_path() {
    global $CFG, $SCRIPT;

    if ($SCRIPT === null) {
                return false;
    }

    $scriptpath = $CFG->customscripts . $SCRIPT;

        if (file_exists($scriptpath) and is_file($scriptpath)) {
        return $scriptpath;
    } else {
        return false;
    }
}


function is_mnet_remote_user($user) {
    global $CFG;

    if (!isset($CFG->mnet_localhost_id)) {
        include_once($CFG->dirroot . '/mnet/lib.php');
        $env = new mnet_environment();
        $env->init();
        unset($env);
    }

    return (!empty($user->mnethostid) && $user->mnethostid != $CFG->mnet_localhost_id);
}


function setup_lang_from_browser() {
    global $CFG, $SESSION, $USER;

    if (!empty($SESSION->lang) or !empty($USER->lang) or empty($CFG->autolang)) {
                return;
    }

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {         return;
    }

        $rawlangs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $rawlangs = str_replace('-', '_', $rawlangs);             $rawlangs = explode(',', $rawlangs);                      $langs = array();

    $order = 1.0;
    foreach ($rawlangs as $lang) {
        if (strpos($lang, ';') === false) {
            $langs[(string)$order] = $lang;
            $order = $order-0.01;
        } else {
            $parts = explode(';', $lang);
            $pos = strpos($parts[1], '=');
            $langs[substr($parts[1], $pos+1)] = $parts[0];
        }
    }
    krsort($langs, SORT_NUMERIC);

        foreach ($langs as $lang) {
                $lang = strtolower(clean_param($lang, PARAM_SAFEDIR));
        if (get_string_manager()->translation_exists($lang, false)) {
                        $SESSION->lang = $lang;
                        break;
        }
    }
    return;
}


function is_proxybypass( $url ) {
    global $CFG;

        if (empty($CFG->proxyhost) or empty($CFG->proxybypass)) {
        return false;
    }

        if (!$host = parse_url( $url, PHP_URL_HOST )) {
        return false;
    }

        $matches = explode( ',', $CFG->proxybypass );

                foreach ($matches as $match) {
        $match = trim($match);

                $lhs = substr($host, 0, strlen($match));
        if (strcasecmp($match, $lhs)==0) {
            return true;
        }

                $rhs = substr($host, -strlen($match));
        if (strcasecmp($match, $rhs)==0) {
            return true;
        }
    }

        return false;
}


function is_newnav($navigation) {
    if (is_array($navigation) && !empty($navigation['newnav'])) {
        return true;
    } else {
        return false;
    }
}


function in_object_vars($var, $object) {
    $classvars = get_class_vars(get_class($object));
    $classvars = array_keys($classvars);
    return in_array($var, $classvars);
}


function object_array_unique($array, $keepkeyassoc = true) {
    $duplicatekeys = array();
    $tmp         = array();

    foreach ($array as $key => $val) {
                if (is_object($val)) {
            $val = (array)$val;
        }

        if (!in_array($val, $tmp)) {
            $tmp[] = $val;
        } else {
            $duplicatekeys[] = $key;
        }
    }

    foreach ($duplicatekeys as $key) {
        unset($array[$key]);
    }

    return $keepkeyassoc ? $array : array_values($array);
}


function is_primary_admin($userid) {
    $primaryadmin =  get_admin();

    if ($userid == $primaryadmin->id) {
        return true;
    } else {
        return false;
    }
}


function get_site_identifier() {
    global $CFG;
        if (empty($CFG->siteidentifier)) {
        set_config('siteidentifier', random_string(32) . $_SERVER['HTTP_HOST']);
    }
        return $CFG->siteidentifier;
}


function check_consecutive_identical_characters($password, $maxchars) {

    if ($maxchars < 1) {
        return true;     }
    if (strlen($password) <= $maxchars) {
        return true;     }

    $previouschar = '';
    $consecutivecount = 1;
    foreach (str_split($password) as $char) {
        if ($char != $previouschar) {
            $consecutivecount = 1;
        } else {
            $consecutivecount++;
            if ($consecutivecount > $maxchars) {
                return false;             }
        }

        $previouschar = $char;
    }

    return true;
}


function partial() {
    if (!class_exists('partial')) {
        
        class partial{
            
            public $values = array();
            
            public $func;
            
            public function __construct($func, $args) {
                $this->values = $args;
                $this->func = $func;
            }
            
            public function method() {
                $args = func_get_args();
                return call_user_func_array($this->func, array_merge($this->values, $args));
            }
        }
    }
    $args = func_get_args();
    $func = array_shift($args);
    $p = new partial($func, $args);
    return array($p, 'method');
}


function get_mnet_environment() {
    global $CFG;
    require_once($CFG->dirroot . '/mnet/lib.php');
    static $instance = null;
    if (empty($instance)) {
        $instance = new mnet_environment();
        $instance->init();
    }
    return $instance;
}


function get_mnet_remote_client() {
    if (!defined('MNET_SERVER')) {
        debugging(get_string('notinxmlrpcserver', 'mnet'));
        return false;
    }
    global $MNET_REMOTE_CLIENT;
    if (isset($MNET_REMOTE_CLIENT)) {
        return $MNET_REMOTE_CLIENT;
    }
    return false;
}


function set_mnet_remote_client($client) {
    if (!defined('MNET_SERVER')) {
        throw new moodle_exception('notinxmlrpcserver', 'mnet');
    }
    global $MNET_REMOTE_CLIENT;
    $MNET_REMOTE_CLIENT = $client;
}


function mnet_get_idp_jump_url($user) {
    global $CFG;

    static $mnetjumps = array();
    if (!array_key_exists($user->mnethostid, $mnetjumps)) {
        $idp = mnet_get_peer_host($user->mnethostid);
        $idpjumppath = mnet_get_app_jumppath($idp->applicationid);
        $mnetjumps[$user->mnethostid] = $idp->wwwroot . $idpjumppath . '?hostwwwroot=' . $CFG->wwwroot . '&wantsurl=';
    }
    return $mnetjumps[$user->mnethostid];
}


function get_home_page() {
    global $CFG;

    if (isloggedin() && !isguestuser() && !empty($CFG->defaulthomepage)) {
        if ($CFG->defaulthomepage == HOMEPAGE_MY) {
            return HOMEPAGE_MY;
        } else {
            return (int)get_user_preferences('user_home_page_preference', HOMEPAGE_MY);
        }
    }
    return HOMEPAGE_SITE;
}


function get_course_display_name_for_list($course) {
    global $CFG;
    if (!empty($CFG->courselistshortnames)) {
        if (!($course instanceof stdClass)) {
            $course = (object)convert_to_array($course);
        }
        return get_string('courseextendednamedisplay', '', $course);
    } else {
        return $course->fullname;
    }
}


class lang_string {

    
    protected $identifier;
    
    protected $component = '';
    
    protected $a = null;
    
    protected $lang = null;

    
    protected $string = null;

    
    protected $forcedstring = false;

    
    public function __construct($identifier, $component = '', $a = null, $lang = null) {
        if (empty($component)) {
            $component = 'moodle';
        }

        $this->identifier = $identifier;
        $this->component = $component;
        $this->lang = $lang;

                                        if (!empty($a)) {
            if (is_scalar($a)) {
                $this->a = $a;
            } else if ($a instanceof lang_string) {
                $this->a = $a->out();
            } else if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $this->a = array();
                foreach ($a as $key => $value) {
                                        if (is_array($value)) {
                        $this->a[$key] = '';
                    } else if (is_object($value)) {
                        if (method_exists($value, '__toString')) {
                            $this->a[$key] = $value->__toString();
                        } else {
                            $this->a[$key] = '';
                        }
                    } else {
                        $this->a[$key] = (string)$value;
                    }
                }
            }
        }

        if (debugging(false, DEBUG_DEVELOPER)) {
            if (clean_param($this->identifier, PARAM_STRINGID) == '') {
                throw new coding_exception('Invalid string identifier. Most probably some illegal character is part of the string identifier. Please check your string definition');
            }
            if (!empty($this->component) && clean_param($this->component, PARAM_COMPONENT) == '') {
                throw new coding_exception('Invalid string compontent. Please check your string definition');
            }
            if (!get_string_manager()->string_exists($this->identifier, $this->component)) {
                debugging('String does not exist. Please check your string definition for '.$this->identifier.'/'.$this->component, DEBUG_DEVELOPER);
            }
        }
    }

    
    protected function get_string() {
        global $CFG;

                if ($this->string === null) {
                        if ($CFG->debugdeveloper && clean_param($this->identifier, PARAM_STRINGID) === '') {
                throw new coding_exception('Invalid string identifier. Most probably some illegal character is part of the string identifier. Please check your string definition', DEBUG_DEVELOPER);
            }

                        $this->string = get_string_manager()->get_string($this->identifier, $this->component, $this->a, $this->lang);
                        if (isset($CFG->debugstringids) && $CFG->debugstringids && optional_param('strings', 0, PARAM_INT)) {
                $this->string .= ' {' . $this->identifier . '/' . $this->component . '}';
            }
        }
                return $this->string;
    }

    
    public function out($lang = null) {
        if ($lang !== null && $lang != $this->lang && ($this->lang == null && $lang != current_language())) {
            if ($this->forcedstring) {
                debugging('lang_string objects that have been used cannot be printed in another language. ('.$this->lang.' used)', DEBUG_DEVELOPER);
                return $this->get_string();
            }
            $translatedstring = new lang_string($this->identifier, $this->component, $this->a, $lang);
            return $translatedstring->out();
        }
        return $this->get_string();
    }

    
    public function __toString() {
        return $this->get_string();
    }

    
    public function __set_state() {
        return $this->get_string();
    }

    
    public function __sleep() {
        $this->get_string();
        $this->forcedstring = true;
        return array('forcedstring', 'string', 'lang');
    }
}
