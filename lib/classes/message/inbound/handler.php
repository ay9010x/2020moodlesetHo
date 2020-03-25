<?php


namespace core\message\inbound;


abstract class handler {

    
    private $id = null;

    
    private $component = '';

    
    private $defaultexpiration = WEEKSECS;

    
    private $validateaddress = true;

    
    private $enabled = false;

    
    private $accessibleproperties = array(
        'id' => true,
        'component' => true,
        'defaultexpiration' => true,
        'validateaddress' => true,
        'enabled' => true,
    );

    
    public function __get($key) {
                $getter = 'get_' . $key;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

                if (isset($this->accessibleproperties[$key])) {
            return $this->$key;
        }

                throw new \coding_exception('unknown_property ' . $key);
    }

    
    public function set_id($id) {
        return $this->id = $id;
    }

    
    public function set_component($component) {
        return $this->component = $component;
    }

    
    public function can_change_validateaddress() {
        return true;
    }

    
    public function set_validateaddress($validateaddress) {
        return $this->validateaddress = $validateaddress;
    }

    
    public function can_change_defaultexpiration() {
        return true;
    }

    
    public function can_change_enabled() {
        return true;
    }

    
    public function set_enabled($enabled) {
        return $this->enabled = $enabled;
    }

    
    public function set_defaultexpiration($period) {
        return $this->defaultexpiration = $period;
    }

    
    private function get_classname() {
        $classname = get_class($this);
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }

        return $classname;
    }

    
    protected abstract function get_description();

    
    protected abstract function get_name();

    
    public abstract function process_message(\stdClass $record, \stdClass $messagedata);

    
    public function get_success_message(\stdClass $messagedata, $handlerresult) {
        return false;
    }

    
    protected static function remove_quoted_text($messagedata) {
        if (!empty($messagedata->plain)) {
            $text = $messagedata->plain;
        } else {
            $text = html_to_text($messagedata->html);
        }
        $messageformat = FORMAT_PLAIN;

        $splitted = preg_split("/\n|\r/", $text);
        if (empty($splitted)) {
            return array($text, $messageformat);
        }

        $i = 0;
        $flag = false;
        foreach ($splitted as $i => $element) {
            if (stripos($element, ">") === 0) {
                                $flag = true;
                                for ($j = $i - 1; ($j >= 0); $j--) {
                    $element = $splitted[$j];
                    if (!empty($element)) {
                        unset($splitted[$j]);
                        break;
                    }
                }
                break;
            }
        }
        if ($flag) {
                                    $splitted = array_slice($splitted, 0, $i-1);

                        $reverse = array_reverse($splitted);
            foreach ($reverse as $i => $line) {
                if (empty($line)) {
                    unset($reverse[$i]);
                } else {
                                        break;
                }
            }

            $replaced = implode(PHP_EOL, array_reverse($reverse));
            $message = trim($replaced);
        } else {
                        if (!empty($messagedata->html)) {
                $message = $messagedata->html;
                $messageformat = FORMAT_HTML;
            } else {
                $message = $messagedata->plain;
            }
        }
        return array($message, $messageformat);
    }
}
