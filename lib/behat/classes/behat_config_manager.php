<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/behat_command.php');
require_once(__DIR__ . '/../../testing/classes/tests_finder.php');


class behat_config_manager {

    
    public static $autoprofileconversion = false;

    
    public static function update_config_file($component = '', $testsrunner = true, $tags = '') {
        global $CFG;

                if ($testsrunner === true) {
            $configfilepath = behat_command::get_behat_dir() . '/behat.yml';
        } else {
                        $configfilepath = self::get_steps_list_config_filepath();
        }

                $features = array();
        $components = tests_finder::get_components_with_tests('features');
        if ($components) {
            foreach ($components as $componentname => $path) {
                $path = self::clean_path($path) . self::get_behat_tests_path();
                if (empty($featurespaths[$path]) && file_exists($path)) {

                                        $uniquekey = str_replace('\\', '/', $path);
                    $featurespaths[$uniquekey] = $path;
                }
            }
            foreach ($featurespaths as $path) {
                $additional = glob("$path/*.feature");
                $features = array_merge($features, $additional);
            }
        }

                if (!empty($CFG->behat_additionalfeatures)) {
            $features = array_merge($features, array_map("realpath", $CFG->behat_additionalfeatures));
        }

                $stepsdefinitions = array();
        $steps = self::get_components_steps_definitions();
        if ($steps) {
            foreach ($steps as $key => $filepath) {
                if ($component == '' || $component === $key) {
                    $stepsdefinitions[$key] = $filepath;
                }
            }
        }

                if (!$testsrunner) {
            unset($stepsdefinitions['behat_deprecated']);
        }

                        $contents = self::get_config_file_contents(self::get_features_with_tags($features, $tags), $stepsdefinitions);

                if (!file_put_contents($configfilepath, $contents)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'File ' . $configfilepath . ' can not be created');
        }

    }

    
    public static function get_features_with_tags($features, $tags) {
        if (empty($tags)) {
            return $features;
        }
        $newfeaturelist = array();
                $tags = explode('&&', $tags);
        $andtags = array();
        $ortags = array();
        foreach ($tags as $tag) {
                        $ortags = array_merge($ortags, explode(',', $tag));
                        $andtags[] = preg_replace('/,.*/', '', $tag);
        }

        foreach ($features as $featurefile) {
            $contents = file_get_contents($featurefile);
            $includefeature = true;
            foreach ($andtags as $tag) {
                                if (strpos($tag, '~') !== false) {
                    $tag = substr($tag, 1);
                    if ($contents && strpos($contents, $tag) !== false) {
                        $includefeature = false;
                        break;
                    }
                } else if ($contents && strpos($contents, $tag) === false) {
                    $includefeature = false;
                    break;
                }
            }

                        if (!$includefeature && !empty($ortags)) {
                foreach ($ortags as $tag) {
                    if ($contents && (strpos($tag, '~') === false) && (strpos($contents, $tag) !== false)) {
                        $includefeature = true;
                        break;
                    }
                }
            }

            if ($includefeature) {
                $newfeaturelist[] = $featurefile;
            }
        }
        return $newfeaturelist;
    }

    
    public static function get_components_steps_definitions() {

        $components = tests_finder::get_components_with_tests('stepsdefinitions');
        if (!$components) {
            return false;
        }

        $stepsdefinitions = array();
        foreach ($components as $componentname => $componentpath) {
            $componentpath = self::clean_path($componentpath);

            if (!file_exists($componentpath . self::get_behat_tests_path())) {
                continue;
            }
            $diriterator = new DirectoryIterator($componentpath . self::get_behat_tests_path());
            $regite = new RegexIterator($diriterator, '|behat_.*\.php$|');

                        foreach ($regite as $file) {
                $key = $file->getBasename('.php');
                $stepsdefinitions[$key] = $file->getPathname();
            }
        }

        return $stepsdefinitions;
    }

    
    public static function get_steps_list_config_filepath() {
        global $USER;

                $userdir = behat_command::get_behat_dir() . '/users/' . $USER->id;
        make_writable_directory($userdir);

        return $userdir . '/behat.yml';
    }

    
    public static function get_behat_cli_config_filepath($runprocess = 0) {
        global $CFG;

        if ($runprocess) {
            if (isset($CFG->behat_parallel_run[$runprocess - 1 ]['behat_dataroot'])) {
                $command = $CFG->behat_parallel_run[$runprocess - 1]['behat_dataroot'];
            } else {
                $command = $CFG->behat_dataroot . $runprocess;
            }
        } else {
            $command = $CFG->behat_dataroot;
        }
        $command .= DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR . 'behat.yml';

                if (testing_is_cygwin()) {
            $command = str_replace('\\', '/', $command);
        }

        return $command;
    }

    
    public final static function get_parallel_test_file_path($runprocess = 0) {
        return behat_command::get_behat_dir($runprocess) . '/parallel_environment_enabled.txt';
    }

    
    public final static function get_parallel_test_runs($runprocess = 0) {

        $parallelrun = 0;
                $parallelrunconfigfile = self::get_parallel_test_file_path($runprocess);
        if (file_exists($parallelrunconfigfile)) {
            if ($parallel = file_get_contents($parallelrunconfigfile)) {
                $parallelrun = (int) $parallel;
            }
        }

        return $parallelrun;
    }

    
    public final static function drop_parallel_site_links() {
        global $CFG;

                $parallelrun = self::get_parallel_test_runs(1);

        if (empty($parallelrun)) {
            return false;
        }

                clearstatcache();
        for ($i = 1; $i <= $parallelrun; $i++) {
                        if (!empty($CFG->behat_parallel_run['behat_wwwroot'][$i - 1]['behat_wwwroot'])) {
                continue;
            }
            $link = $CFG->dirroot . '/' . BEHAT_PARALLEL_SITE_NAME . $i;
            if (file_exists($link) && is_link($link)) {
                @unlink($link);
            }
        }
        return true;
    }

    
    public final static function create_parallel_site_links($fromrun, $torun) {
        global $CFG;

                clearstatcache();
        for ($i = $fromrun; $i <= $torun; $i++) {
                        if (!empty($CFG->behat_parallel_run['behat_wwwroot'][$i - 1]['behat_wwwroot'])) {
                continue;
            }
            $link = $CFG->dirroot.'/'.BEHAT_PARALLEL_SITE_NAME.$i;
            clearstatcache();
            if (file_exists($link)) {
                if (!is_link($link) || !is_dir($link)) {
                    echo "File exists at link location ($link) but is not a link or directory!" . PHP_EOL;
                    return false;
                }
            } else if (!symlink($CFG->dirroot, $link)) {
                                echo "Unable to create behat site symlink ($link)" . PHP_EOL;
                return false;
            }
        }
        return true;
    }

    
    protected static function get_config_file_contents($features, $stepsdefinitions) {
        global $CFG;

                require_once($CFG->dirroot . '/vendor/autoload.php');

        $selenium2wdhost = array('wd_host' => 'http://localhost:4444/wd/hub');

        $parallelruns = self::get_parallel_test_runs();
                if (!empty($CFG->behatrunprocess) && !empty($parallelruns)) {
                        if ($alloc = self::profile_guided_allocate($features, max(1, $parallelruns), $CFG->behatrunprocess)) {
                $features = $alloc;
            } else {
                                srand(crc32(floor(time() / 3600 / 24) . var_export($features, true)));
                shuffle($features);
                                if (count($features)) {
                    $features = array_chunk($features, ceil(count($features) / max(1, $parallelruns)));
                                        if (!empty($features[$CFG->behatrunprocess - 1])) {
                        $features = $features[$CFG->behatrunprocess - 1];
                    } else {
                        $features = null;
                    }
                }
            }
                        if (!empty($CFG->behat_parallel_run[$CFG->behatrunprocess - 1]['wd_host'])) {
                $selenium2wdhost = array('wd_host' => $CFG->behat_parallel_run[$CFG->behatrunprocess - 1]['wd_host']);
            }
        }

                if (empty($CFG->behat_wwwroot)) {
            $CFG->behat_wwwroot = 'http://itwillnotbeused.com';
        }

                        $config = array(
            'default' => array(
                'formatters' => array(
                    'moodle_progress' => array(
                        'output_styles' => array(
                            'comment' => array('magenta'))
                        )
                ),
                'suites' => array(
                    'default' => array(
                        'paths' => $features,
                        'contexts' => array_keys($stepsdefinitions)
                    )
                ),
                'extensions' => array(
                    'Behat\MinkExtension' => array(
                        'base_url' => $CFG->behat_wwwroot,
                        'goutte' => null,
                        'selenium2' => $selenium2wdhost
                    ),
                    'Moodle\BehatExtension' => array(
                        'moodledirroot' => $CFG->dirroot,
                        'steps_definitions' => $stepsdefinitions
                    )
                )
            )
        );

                if (!empty($CFG->behat_config)) {
            foreach ($CFG->behat_config as $profile => $values) {
                $config = self::merge_config($config, self::merge_behat_config($profile, $values));
            }
        }
                if (!empty($CFG->behat_profiles) && is_array($CFG->behat_profiles)) {
            foreach ($CFG->behat_profiles as $profile => $values) {
                $config = self::merge_config($config, self::get_behat_profile($profile, $values));
            }
        }

        return Symfony\Component\Yaml\Yaml::dump($config, 10, 2);
    }

    
    protected static function merge_behat_config($profile, $values) {
                                if (!isset($values['filters']['tags']) && !isset($values['extensions']['Behat\MinkExtension\Extension'])) {
            return array($profile => $values);
        }

                $oldconfigvalues = array();
        if (isset($values['extensions']['Behat\MinkExtension\Extension'])) {
            $extensionvalues = $values['extensions']['Behat\MinkExtension\Extension'];
            if (isset($extensionvalues['selenium2']['browser'])) {
                $oldconfigvalues['browser'] = $extensionvalues['selenium2']['browser'];
            }
            if (isset($extensionvalues['selenium2']['wd_host'])) {
                $oldconfigvalues['wd_host'] = $extensionvalues['selenium2']['wd_host'];
            }
            if (isset($extensionvalues['capabilities'])) {
                $oldconfigvalues['capabilities'] = $extensionvalues['capabilities'];
            }
        }

        if (isset($values['filters']['tags'])) {
            $oldconfigvalues['tags'] = $values['filters']['tags'];
        }

        if (!empty($oldconfigvalues)) {
            self::$autoprofileconversion = true;
            return self::get_behat_profile($profile, $oldconfigvalues);
        }

                return array();
    }

    
    protected static function get_behat_profile($profile, $values) {
                if (!is_array($values)) {
            return array();
        }

                $behatprofilesuites = array();
                if (isset($values['tags'])) {
            $behatprofilesuites = array(
                'suites' => array(
                    'default' => array(
                        'filters' => array(
                            'tags' => $values['tags'],
                        )
                    )
                )
            );
        }

                $behatprofileextension = array();
        $seleniumconfig = array();
        if (isset($values['browser'])) {
            $seleniumconfig['browser'] = $values['browser'];
        }
        if (isset($values['wd_host'])) {
            $seleniumconfig['wd_host'] = $values['wd_host'];
        }
        if (isset($values['capabilities'])) {
            $seleniumconfig['capabilities'] = $values['capabilities'];
        }
        if (!empty($seleniumconfig)) {
            $behatprofileextension = array(
                'extensions' => array(
                    'Behat\MinkExtension' => array(
                        'selenium2' => $seleniumconfig,
                    )
                )
            );
        }

        return array($profile => array_merge($behatprofilesuites, $behatprofileextension));
    }

    
    protected static function profile_guided_allocate($features, $nbuckets, $instance) {

        $behattimingfile = defined('BEHAT_FEATURE_TIMING_FILE') &&
            @filesize(BEHAT_FEATURE_TIMING_FILE) ? BEHAT_FEATURE_TIMING_FILE : false;

        if (!$behattimingfile || !$behattimingdata = @json_decode(file_get_contents($behattimingfile), true)) {
                        $stepfile = "";
            if (defined('BEHAT_FEATURE_STEP_FILE') && BEHAT_FEATURE_STEP_FILE) {
                $stepfile = BEHAT_FEATURE_STEP_FILE;
            }
                        if (empty($stepfile) || !$behattimingdata = @json_decode(file_get_contents($stepfile), true)) {
                return false;
            }
        }

        arsort($behattimingdata); 
        $realroot = realpath(__DIR__.'/../../../').'/';
        $defaultweight = array_sum($behattimingdata) / count($behattimingdata);
        $weights = array_fill(0, $nbuckets, 0);
        $buckets = array_fill(0, $nbuckets, array());
        $totalweight = 0;

                foreach ($features as $k => $file) {
            $key = str_replace($realroot, '', $file);
            $features[$key] = $file;
            unset($features[$k]);
            if (!isset($behattimingdata[$key])) {
                $behattimingdata[$key] = $defaultweight;
            }
        }

                $behattimingorder = array();
        foreach ($features as $key => $file) {
            $behattimingorder[$key] = $behattimingdata[$key];
        }
        arsort($behattimingorder);

                foreach ($behattimingorder as $key => $weight) {
            $file = $features[$key];
            $lightbucket = array_search(min($weights), $weights);
            $weights[$lightbucket] += $weight;
            $buckets[$lightbucket][] = $file;
            $totalweight += $weight;
        }

        if ($totalweight && !defined('BEHAT_DISABLE_HISTOGRAM') && $instance == $nbuckets) {
            echo "Bucket weightings:\n";
            foreach ($weights as $k => $weight) {
                echo $k + 1 . ": " . str_repeat('*', 70 * $nbuckets * $weight / $totalweight) . PHP_EOL;
            }
        }

                return $buckets[$instance - 1];
    }

    
    protected static function merge_config($config, $localconfig) {

        if (!is_array($config) && !is_array($localconfig)) {
            return $localconfig;
        }

                if (is_array($config) && !is_array($localconfig)) {
            return $localconfig;
        }

        foreach ($localconfig as $key => $value) {

                        if (!is_array($config)) {
                unset($config);
            }

                        if (empty($config[$key])) {
                $config[$key] = $value;
            } else {
                $config[$key] = self::merge_config($config[$key], $localconfig[$key]);
            }
        }

        return $config;
    }

    
    protected final static function clean_path($path) {

        $path = rtrim($path, DIRECTORY_SEPARATOR);

        $parttoremove = DIRECTORY_SEPARATOR . 'tests';

        $substr = substr($path, strlen($path) - strlen($parttoremove));
        if ($substr == $parttoremove) {
            $path = substr($path, 0, strlen($path) - strlen($parttoremove));
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    
    protected final static function get_behat_tests_path() {
        return DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'behat';
    }

}
