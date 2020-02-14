<?php



defined('MOODLE_INTERNAL') || die();


class core_collator {
    
    const SORT_REGULAR = 0;

    
    const SORT_STRING = 1;

    
    const SORT_NUMERIC = 2;

    
    const SORT_NATURAL = 6;

    
    const CASE_SENSITIVE = 64;

    
    protected static $collator = null;

    
    protected static $locale = null;

    
    private function __construct() {
    }

    
    protected static function ensure_collator_available() {
        $locale = get_string('locale', 'langconfig');
        if (is_null(self::$collator) || $locale != self::$locale) {
            self::$collator = false;
            self::$locale = $locale;
            if (class_exists('Collator', false)) {
                $collator = new Collator($locale);
                if (!empty($collator) && $collator instanceof Collator) {
                                                                                $errorcode = $collator->getErrorCode();
                    $errormessage = $collator->getErrorMessage();
                                        if ($errorcode !== 0) {
                                                $localeinuse = $collator->getLocale(Locale::ACTUAL_LOCALE);
                                                                                                                                                                                                                                                                        if ($errorcode === -127 || $errorcode === -128) {
                                                                                    if ($localeinuse !== 'root' && strpos($locale, $localeinuse) !== 0) {
                                                                                                debugging('Locale warning (not fatal) '.$errormessage.': '.
                                    'Requested locale "'.$locale.'" not found, locale "'.$localeinuse.'" used instead. '.
                                    'The most specific locale supported by ICU relatively to the requested locale is "'.
                                    $collator->getLocale(Locale::VALID_LOCALE).'".');
                            } else {
                                                                                                                                                            }
                        } else {
                                                                                    debugging('Problem with locale: '.$errormessage.'. '.
                                'Requested locale: "'.$locale.'", actual locale "'.$localeinuse.'". '.
                                'The most specific locale supported by ICU relatively to the requested locale is "'.
                                $collator->getLocale(Locale::VALID_LOCALE).'".');
                        }
                    }
                                        self::$collator = $collator;
                } else {
                                        debugging('Error instantiating collator for locale: "' . $locale . '", with error [' .
                    intl_get_error_code() . '] ' . intl_get_error_message($collator));
                }
            }
        }
        return (self::$collator instanceof Collator);
    }

    
    protected static function restore_array(array &$arr, array &$original) {
        foreach ($arr as $key => $ignored) {
            $arr[$key] = $original[$key];
        }
    }

    
    protected static function naturalise($string) {
        return preg_replace_callback('/[0-9]+/', array('core_collator', 'callback_naturalise'), $string);
    }

    
    public static function callback_naturalise($matches) {
        return str_pad($matches[0], 20, '0', STR_PAD_LEFT);
    }

    
    public static function asort(array &$arr, $sortflag = core_collator::SORT_STRING) {
        if (empty($arr)) {
                        return true;
        }

        $original = null;

        $casesensitive = (bool)($sortflag & core_collator::CASE_SENSITIVE);
        $sortflag = ($sortflag & ~core_collator::CASE_SENSITIVE);
        if ($sortflag != core_collator::SORT_NATURAL and $sortflag != core_collator::SORT_STRING) {
            $casesensitive = false;
        }

        if (self::ensure_collator_available()) {
            if ($sortflag == core_collator::SORT_NUMERIC) {
                $flag = Collator::SORT_NUMERIC;

            } else if ($sortflag == core_collator::SORT_REGULAR) {
                $flag = Collator::SORT_REGULAR;

            } else {
                $flag = Collator::SORT_STRING;
            }

            if ($sortflag == core_collator::SORT_NATURAL) {
                $original = $arr;
                if ($sortflag == core_collator::SORT_NATURAL) {
                    foreach ($arr as $key => $value) {
                        $arr[$key] = self::naturalise((string)$value);
                    }
                }
            }
            if ($casesensitive) {
                self::$collator->setAttribute(Collator::CASE_FIRST, Collator::UPPER_FIRST);
            } else {
                self::$collator->setAttribute(Collator::CASE_FIRST, Collator::OFF);
            }
            $result = self::$collator->asort($arr, $flag);
            if ($original) {
                self::restore_array($arr, $original);
            }
            return $result;
        }

        
        if ($sortflag == core_collator::SORT_NUMERIC) {
            return asort($arr, SORT_NUMERIC);

        } else if ($sortflag == core_collator::SORT_REGULAR) {
            return asort($arr, SORT_REGULAR);
        }

        if (!$casesensitive) {
            $original = $arr;
            foreach ($arr as $key => $value) {
                $arr[$key] = core_text::strtolower($value);
            }
        }

        if ($sortflag == core_collator::SORT_NATURAL) {
            $result = natsort($arr);

        } else {
            $result = asort($arr, SORT_LOCALE_STRING);
        }

        if ($original) {
            self::restore_array($arr, $original);
        }

        return $result;
    }

    
    public static function asort_objects_by_property(array &$objects, $property, $sortflag = core_collator::SORT_STRING) {
        $original = $objects;
        foreach ($objects as $key => $object) {
            $objects[$key] = $object->$property;
        }
        $result = self::asort($objects, $sortflag);
        self::restore_array($objects, $original);
        return $result;
    }

    
    public static function asort_objects_by_method(array &$objects, $method, $sortflag = core_collator::SORT_STRING) {
        $original = $objects;
        foreach ($objects as $key => $object) {
            $objects[$key] = $object->{$method}();
        }
        $result = self::asort($objects, $sortflag);
        self::restore_array($objects, $original);
        return $result;
    }

    
    public static function asort_array_of_arrays_by_key(array &$array, $key, $sortflag = core_collator::SORT_STRING) {
        $original = $array;
        foreach ($array as $initkey => $item) {
            $array[$initkey] = $item[$key];
        }
        $result = self::asort($array, $sortflag);
        self::restore_array($array, $original);
        return $result;
    }

    
    public static function ksort(array &$arr, $sortflag = core_collator::SORT_STRING) {
        $keys = array_keys($arr);
        if (!self::asort($keys, $sortflag)) {
            return false;
        }
                $original = $arr;
        $arr = array();         foreach ($keys as $key) {
            $arr[$key] = $original[$key];
        }

        return true;
    }
}
