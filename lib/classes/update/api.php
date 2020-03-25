<?php



namespace core\update;

use curl;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');


class api {

    
    const APIROOT = 'https://download.moodle.org/api';

    
    const APIVER = '1.3';

    
    public static function client() {
        return new static();
    }

    
    protected function __construct() {
    }

    
    public function get_plugin_info($component, $version) {

        $params = array(
            'plugin' => $component.'@'.$version,
            'format' => 'json',
        );

        return $this->call_pluginfo_service($params);
    }

    
    public function find_plugin($component, $reqversion=ANY_VERSION, $branch=null) {
        global $CFG;

        $params = array(
            'plugin' => $component,
            'format' => 'json',
        );

        if ($reqversion === ANY_VERSION) {
            $params['minversion'] = 0;
        } else {
            $params['minversion'] = $reqversion;
        }

        if ($branch === null) {
            $branch = $CFG->branch;
        }

        $params['branch'] = $this->convert_branch_numbering_format($branch);

        return $this->call_pluginfo_service($params);
    }

    
    public function validate_pluginfo_format($data) {

        if (empty($data) or !is_object($data)) {
            return false;
        }

        $output = new remote_info();

        $rootproperties = array('id' => 1, 'name' => 1, 'component' => 1, 'source' => 0, 'doc' => 0,
            'bugs' => 0, 'discussion' => 0, 'version' => 0);
        foreach ($rootproperties as $property => $required) {
            if (!property_exists($data, $property)) {
                return false;
            }
            if ($required and empty($data->$property)) {
                return false;
            }
            $output->$property = $data->$property;
        }

        if (!empty($data->version)) {
            if (!is_object($data->version)) {
                return false;
            }
            $versionproperties = array('id' => 1, 'version' => 1, 'release' => 0, 'maturity' => 0,
                'downloadurl' => 1, 'downloadmd5' => 1, 'vcssystem' => 0, 'vcssystemother' => 0,
                'vcsrepositoryurl' => 0, 'vcsbranch' => 0, 'vcstag' => 0, 'supportedmoodles' => 0);
            foreach ($versionproperties as $property => $required) {
                if (!property_exists($data->version, $property)) {
                    return false;
                }
                if ($required and empty($data->version->$property)) {
                    return false;
                }
            }
            if (!preg_match('|^https?://|i', $data->version->downloadurl)) {
                return false;
            }

            if (!empty($data->version->supportedmoodles)) {
                if (!is_array($data->version->supportedmoodles)) {
                    return false;
                }
                foreach ($data->version->supportedmoodles as $supportedmoodle) {
                    if (!is_object($supportedmoodle)) {
                        return false;
                    }
                    if (empty($supportedmoodle->version) or empty($supportedmoodle->release)) {
                        return false;
                    }
                }
            }
        }

        return $output;
    }

    
    protected function call_pluginfo_service(array $params) {

        $serviceurl = $this->get_serviceurl_pluginfo();
        $response = $this->call_service($serviceurl, $params);

        if ($response) {
            if ($response->info['http_code'] == 404) {
                                return false;

            } else if ($response->info['http_code'] == 200 and isset($response->data->status)
                    and $response->data->status === 'OK' and $response->data->apiver == self::APIVER
                    and isset($response->data->pluginfo)) {
                    return $this->validate_pluginfo_format($response->data->pluginfo);

            } else {
                debugging('cURL: Unexpected response', DEBUG_DEVELOPER);
                return false;
            }
        }

        return false;
    }

    
    protected function call_service($serviceurl, array $params=array()) {

        $response = (object)array(
            'data' => null,
            'info' => null,
            'status' => null,
        );

        $curl = new curl();

        $response->data = json_decode($curl->get($serviceurl, $params, array(
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_SSL_VERIFYPEER' => true,
        )));

        $curlerrno = $curl->get_errno();

        if (!empty($curlerrno)) {
            debugging('cURL: Error '.$curlerrno.' when calling '.$serviceurl, DEBUG_DEVELOPER);
            return false;
        }

        $response->info = $curl->get_info();

        if (isset($response->info['ssl_verify_result']) and $response->info['ssl_verify_result'] != 0) {
            debugging('cURL/SSL: Unable to verify remote service response when calling '.$serviceurl, DEBUG_DEVELOPER);
            return false;
        }

                $response->status = array_shift($curl->response);

        return $response;
    }

    
    protected function convert_branch_numbering_format($branch) {

        $branch = (string)$branch;

        if (strpos($branch, '.') === false) {
            $branch = substr($branch, 0, -1).'.'.substr($branch, -1);
        }

        return $branch;
    }

    
    protected function get_serviceurl_pluginfo() {
        global $CFG;

        if (!empty($CFG->config_php_settings['alternativepluginfoserviceurl'])) {
            return $CFG->config_php_settings['alternativepluginfoserviceurl'];
        } else {
            return self::APIROOT.'/'.self::APIVER.'/pluginfo.php';
        }
    }
}
