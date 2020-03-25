<?php




class phpunit_autoloader implements PHPUnit_Runner_TestSuiteLoader {
    public function load($suiteClassName, $suiteClassFile = '') {
        global $CFG;

                if ($suiteClassFile) {
                        if (strpos($suiteClassName, '/') !== false) {
                                                return $this->guess_class_from_path($suiteClassFile);
            }
            if (strpos($suiteClassName, '\\') !== false and strpos($suiteClassFile, $suiteClassName.'.php') !== false) {
                                return $this->guess_class_from_path($suiteClassFile);
            }
        }

        if (class_exists($suiteClassName, false)) {
            $class = new ReflectionClass($suiteClassName);
            return $class;
        }

        if ($suiteClassFile) {
            PHPUnit_Util_Fileloader::checkAndLoad($suiteClassFile);
            if (class_exists($suiteClassName, false)) {
                $class = new ReflectionClass($suiteClassName);
                return $class;
            }

            throw new PHPUnit_Framework_Exception(
                sprintf("Class '%s' could not be found in '%s'.", $suiteClassName, $suiteClassFile)
            );
        }

        

        $parts = explode('_', $suiteClassName);
        $suffix = end($parts);
        $component = '';

        if ($suffix === 'testcase') {
            unset($parts[key($parts)]);
            while($parts) {
                if (!$component) {
                    $component = array_shift($parts);
                } else {
                    $component = $component . '_' . array_shift($parts);
                }
                                if ($fulldir = core_component::get_component_directory($component)) {
                    $testfile = implode('_', $parts);
                    $fullpath = "{$fulldir}/tests/{$testfile}_test.php";
                    if (is_readable($fullpath)) {
                        include_once($fullpath);
                        if (class_exists($suiteClassName, false)) {
                            $class = new ReflectionClass($suiteClassName);
                            return $class;
                        }
                    }
                }
            }
                        $xmlfile = "$CFG->dirroot/phpunit.xml";
            if (is_readable($xmlfile) and $xml = file_get_contents($xmlfile)) {
                $dom = new DOMDocument();
                $dom->loadXML($xml);
                $nodes = $dom->getElementsByTagName('testsuite');
                foreach ($nodes as $node) {
                    
                    $suitename = trim($node->attributes->getNamedItem('name')->nodeValue);
                    if (strpos($suitename, 'core') !== 0 or strpos($suitename, ' ') !== false) {
                        continue;
                    }
                                                            if (strpos($suiteClassName, $suitename) !== 0) {
                        continue;
                    }
                    foreach ($node->childNodes as $dirnode) {
                        
                        $dir = trim($dirnode->textContent);
                        if (!$dir) {
                            continue;
                        }
                        $dir = $CFG->dirroot.'/'.$dir;
                        $parts = explode('_', $suitename);
                        $prefix = '';
                        while ($parts) {
                            if ($prefix) {
                                $prefix = $prefix.'_'.array_shift($parts);
                            } else {
                                $prefix = array_shift($parts);
                            }
                            $filename = substr($suiteClassName, strlen($prefix)+1);
                            $filename = preg_replace('/testcase$/', 'test', $filename);
                            if (is_readable("$dir/$filename.php")) {
                                include_once("$dir/$filename.php");
                                if (class_exists($suiteClassName, false)) {
                                    $class = new ReflectionClass($suiteClassName);
                                    return $class;
                                }
                            }
                        }
                    }
                }
            }
        }

        throw new PHPUnit_Framework_Exception(
            sprintf("Class '%s' could not be found in '%s'.", $suiteClassName, $suiteClassFile)
        );
    }

    protected function guess_class_from_path($file) {
                
        $classes = get_declared_classes();
        PHPUnit_Util_Fileloader::checkAndLoad($file);
        $includePathFilename = stream_resolve_include_path($file);
        $loadedClasses = array_diff(get_declared_classes(), $classes);

        $candidates = array();

        foreach ($loadedClasses as $loadedClass) {
            $class = new ReflectionClass($loadedClass);

            if ($class->isSubclassOf('PHPUnit_Framework_TestCase') and !$class->isAbstract()) {
                if (realpath($includePathFilename) === realpath($class->getFileName())) {
                    $candidates[] = $loadedClass;
                }
            }
        }

        if (count($candidates) == 0) {
            throw new PHPUnit_Framework_Exception(
                sprintf("File '%s' does not contain any test cases.", $file)
            );
        }

        if (count($candidates) > 1) {
            throw new PHPUnit_Framework_Exception(
                sprintf("File '%s' contains multiple test cases: ".implode(', ', $candidates), $file)
            );
        }

        $classname = reset($candidates);
        return new ReflectionClass($classname);
    }

    public function reload(ReflectionClass $aClass) {
        return $aClass;
    }
}
