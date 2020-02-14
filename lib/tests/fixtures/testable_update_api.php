<?php



namespace core\update;

defined('MOODLE_INTERNAL') || die();


class testable_api extends api {

    
    public function convert_branch_numbering_format($branch) {
        return parent::convert_branch_numbering_format($branch);
    }

    
    protected function get_serviceurl_pluginfo() {
        return 'http://testab.le/api/pluginfo.php';
    }

    
    protected function call_service($serviceurl, array $params=array()) {

        $response = (object)array(
            'data' => null,
            'info' => null,
            'status' => null,
        );

        $foobarinfo = (object)array(
            'status' => 'OK',
            'apiver' => '1.3',
            'pluginfo' => (object)array(
                'id' => 42,
                'name' => 'Foo bar',
                'component' => 'foo_bar',
                'source' => '',
                'doc' => '',
                'bugs' => '',
                'discussion' => '',
                'version' => false,
            ),
        );

        $version2015093000info = (object)array(
            'id' => '6765',
            'version' => '2015093000',
            'release' => '1.0',
            'maturity' => '200',
            'downloadurl' => 'http://mood.le/plugins/foo_bar/2015093000.zip',
            'downloadmd5' => 'd41d8cd98f00b204e9800998ecf8427e',
            'vcssystem' => '',
            'vcssystemother' => '',
            'vcsrepositoryurl' => '',
            'vcsbranch' => '',
            'vcstag' => '',
            'supportedmoodles' => array(
                (object)array(
                    'version' => '2015041700',
                    'release' => '2.9'
                ),
                (object)array(
                    'version' => '2015110900',
                    'release' => '3.0'
                ),
            )
        );

        $version2015100400info = (object)array(
            'id' => '6796',
            'version' => '2015100400',
            'release' => '1.1',
            'maturity' => '200',
            'downloadurl' => 'http://mood.le/plugins/foo_bar/2015100400.zip',
            'downloadmd5' => 'd41d8cd98f00b204e9800998ecf8427e',
            'vcssystem' => '',
            'vcssystemother' => '',
            'vcsrepositoryurl' => '',
            'vcsbranch' => '',
            'vcstag' => '',
            'supportedmoodles' => array(
                (object)array(
                    'version' => '2015110900',
                    'release' => '3.0'
                ),
            )
        );

        $version2015100500info = (object)array(
            'id' => '6799',
            'version' => '2015100500',
            'release' => '2.0beta',
            'maturity' => '100',
            'downloadurl' => 'http://mood.le/plugins/foo_bar/2015100500.zip',
            'downloadmd5' => 'd41d8cd98f00b204e9800998ecf8427e',
            'vcssystem' => '',
            'vcssystemother' => '',
            'vcsrepositoryurl' => '',
            'vcsbranch' => '',
            'vcstag' => '',
            'supportedmoodles' => array(
                (object)array(
                    'version' => '2015110900',
                    'release' => '3.0'
                ),
            )
        );

        if ($serviceurl === 'http://testab.le/api/pluginfo.php') {
            if (strpos($params['plugin'], 'foo_bar@') === 0) {
                $response->data = $foobarinfo;
                $response->info = array(
                    'http_code' => 200,
                );
                $response->status = '200 OK';

                if (substr($params['plugin'], -11) === '@2015093000') {
                    $response->data->pluginfo->version = $version2015093000info;
                }

                if (substr($params['plugin'], -11) === '@2015100400') {
                    $response->data->pluginfo->version = $version2015100400info;
                }

                if (substr($params['plugin'], -11) === '@2015100500') {
                    $response->data->pluginfo->version = $version2015100500info;
                }

            } else if ($params['plugin'] === 'foo_bar' and isset($params['branch']) and isset($params['minversion'])) {
                $response->data = $foobarinfo;
                $response->info = array(
                    'http_code' => 200,
                );
                $response->status = '200 OK';

                if ($params['minversion'] <= 2015100400) {
                                                            $response->data->pluginfo->version = $version2015100400info;

                } else if ($params['minversion'] <= 2015100500) {
                                                                                $response->data->pluginfo->version = $version2015100500info;
                }

            } else {
                $response->info = array(
                    'http_code' => 404,
                );
                $response->status = '404 Not Found (unknown plugin)';
            }

            return $response;

        } else {
            return 'This should not happen';
        }
    }
}
