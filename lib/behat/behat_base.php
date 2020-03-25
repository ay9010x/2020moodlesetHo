<?php




use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Element\NodeElement as NodeElement;


class behat_base extends Behat\MinkExtension\Context\RawMinkContext {

    
    const REDUCED_TIMEOUT = 2;

    
    const TIMEOUT = 6;

    
    const EXTENDED_TIMEOUT = 10;

    
    const PAGE_READY_JS = '(typeof M !== "undefined" && M.util && M.util.pending_js && !Boolean(M.util.pending_js.length)) && (document.readyState === "complete")';

    
    protected function locate_path($path) {
        $starturl = rtrim($this->getMinkParameter('base_url'), '/') . '/';
        return 0 !== strpos($path, 'http') ? $starturl . ltrim($path, '/') : $path;
    }

    
    protected function find($selector, $locator, $exception = false, $node = false, $timeout = false) {

                if ($selector === 'named') {
            $exception = 'Using the "named" selector is deprecated as of 3.1. '
                .' Use the "named_partial" or use the "named_exact" selector instead.';
            throw new ExpectationException($exception, $this->getSession());
        }

                $items = $this->find_all($selector, $locator, $exception, $node, $timeout);
        return count($items) ? reset($items) : null;
    }

    
    protected function find_all($selector, $locator, $exception = false, $node = false, $timeout = false) {

                if ($selector === 'named') {
            $exception = 'Using the "named" selector is deprecated as of 3.1. '
                .' Use the "named_partial" or use the "named_exact" selector instead.';
            throw new ExpectationException($exception, $this->getSession());
        }

                if (!$exception) {

                        if (($selector == 'named_exact') || ($selector == 'named_partial')) {
                $exceptiontype = $locator[0];
                $exceptionlocator = $locator[1];

                                if ($this->running_javascript()) {
                    $locator[1] = html_entity_decode($locator[1], ENT_NOQUOTES);
                }

            } else {
                $exceptiontype = $selector;
                $exceptionlocator = $locator;
            }

            $exception = new ElementNotFoundException($this->getSession(), $exceptiontype, null, $exceptionlocator);
        }

        $params = array('selector' => $selector, 'locator' => $locator);
                if ($node) {
            $params['node'] = $node;
        }

                if (!$timeout) {
            $timeout = self::TIMEOUT;
            $microsleep = false;
        } else {
                                                $microsleep = true;
        }

                return $this->spin(
            function($context, $args) {

                                if (empty($args['node'])) {
                    return $context->getSession()->getPage()->findAll($args['selector'], $args['locator']);
                }

                                                $elementxpath = $context->getSession()->getSelectorsHandler()->selectorToXpath($args['selector'], $args['locator']);

                                $unions = explode('|', $elementxpath);
                foreach ($unions as $key => $union) {
                    $union = trim($union);

                                        if (strpos($union, '.') === 0) {
                        $union = substr($union, 1);
                    } else if (strpos($union, '/') !== 0) {
                                                $union = '/' . $union;
                    }
                    $unions[$key] = $args['node']->getXpath() . $union;
                }

                                return $context->getSession()->getDriver()->find(implode('|', $unions));
            },
            $params,
            $timeout,
            $exception,
            $microsleep
        );
    }

    
    public function __call($name, $arguments) {

        if (substr($name, 0, 5) !== 'find_') {
            throw new coding_exception('The "' . $name . '" method does not exist');
        }

                $cleanname = substr($name, 5);

                if (count($arguments) !== 1) {
            throw new coding_exception('The "' . $cleanname . '" named selector needs the locator as it\'s single argument');
        }

                        return $this->find('named_partial',
            array(
                $cleanname,
                behat_context_helper::escape($arguments[0])
            )
        );
    }

    
    public function escape($string) {
        return str_replace('"', '\"', $string);
    }

    
    protected function spin($lambda, $args = false, $timeout = false, $exception = false, $microsleep = false) {

                if (!$timeout) {
            $timeout = self::TIMEOUT;
        }
        if ($microsleep) {
                        $loops = $timeout * 10;
        } else {
                        $loops = $timeout;
        }

                if (!$this->running_javascript()) {
            $loops = 1;
        }

        for ($i = 0; $i < $loops; $i++) {
                        try {
                                                                if ($return = call_user_func($lambda, $this, $args)) {
                    return $return;
                }
            } catch (Exception $e) {
                                if (!$exception) {
                    $exception = $e;
                }
                                continue;
            }

            if ($this->running_javascript()) {
                if ($microsleep) {
                    usleep(100000);
                } else {
                    sleep(1);
                }
            }
        }

                if (!$exception) {
            $exception = new coding_exception('spin method requires an exception if the callback does not throw an exception');
        }

                throw $exception;
    }

    
    protected function get_selected_node($selectortype, $element) {

                list($selector, $locator) = $this->transform_selector($selectortype, $element);

                return $this->find($selector, $locator);
    }

    
    protected function get_text_selector_node($selectortype, $element) {

                list($selector, $locator) = $this->transform_text_selector($selectortype, $element);

                return $this->find($selector, $locator);
    }

    
    protected function get_node_in_container($selectortype, $element, $containerselectortype, $containerelement) {

                $containernode = $this->get_text_selector_node($containerselectortype, $containerelement);

        list($selector, $locator) = $this->transform_selector($selectortype, $element);

                $locatorexceptionmsg = $element . '" in the "' . $containerelement. '" "' . $containerselectortype. '"';
        $exception = new ElementNotFoundException($this->getSession(), $selectortype, null, $locatorexceptionmsg);

                return $this->find($selector, $locator, $exception, $containernode);
    }

    
    protected function transform_selector($selectortype, $element) {

                $selectors = behat_selectors::get_allowed_selectors();
        if (!isset($selectors[$selectortype])) {
            throw new ExpectationException('The "' . $selectortype . '" selector type does not exist', $this->getSession());
        }

        return behat_selectors::get_behat_selector($selectortype, $element, $this->getSession());
    }

    
    protected function transform_text_selector($selectortype, $element) {

        $selectors = behat_selectors::get_allowed_text_selectors();
        if (empty($selectors[$selectortype])) {
            throw new ExpectationException('The "' . $selectortype . '" selector can not be used to select text nodes', $this->getSession());
        }

        return $this->transform_selector($selectortype, $element);
    }

    
    protected function running_javascript() {
        return get_class($this->getSession()->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }

    
    protected function ensure_element_exists($element, $selectortype) {

                list($selector, $locator) = $this->transform_selector($selectortype, $element);

                $msg = 'The "' . $element . '" element does not exist and should exist';
        $exception = new ExpectationException($msg, $this->getSession());

                $this->spin(
            function($context, $args) {
                                if ($context->getSession()->getPage()->find($args['selector'], $args['locator'])) {
                    return true;
                }
                return false;
            },
            array('selector' => $selector, 'locator' => $locator),
            self::EXTENDED_TIMEOUT,
            $exception,
            true
        );

    }

    
    protected function ensure_element_does_not_exist($element, $selectortype) {

                list($selector, $locator) = $this->transform_selector($selectortype, $element);

                $msg = 'The "' . $element . '" element exists and should not exist';
        $exception = new ExpectationException($msg, $this->getSession());

                $this->spin(
            function($context, $args) {
                                if (!$context->getSession()->getPage()->find($args['selector'], $args['locator'])) {
                    return true;
                }
                return false;
            },
            array('selector' => $selector, 'locator' => $locator),
            self::EXTENDED_TIMEOUT,
            $exception,
            true
        );
    }

    
    protected function ensure_node_is_visible($node) {

        if (!$this->running_javascript()) {
            return;
        }

                $msg = 'The "' . $node->getXPath() . '" xpath node is not visible and it should be visible';
        $exception = new ExpectationException($msg, $this->getSession());

                $this->spin(
            function($context, $args) {
                if ($args->isVisible()) {
                    return true;
                }
                return false;
            },
            $node,
            self::EXTENDED_TIMEOUT,
            $exception,
            true
        );
    }

    
    protected function ensure_element_is_visible($element, $selectortype) {

        if (!$this->running_javascript()) {
            return;
        }

        $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);

        return $node;
    }

    
    protected function ensure_editors_are_loaded() {
        global $CFG;

        if (empty($CFG->behat_usedeprecated)) {
            debugging('Function behat_base::ensure_editors_are_loaded() is deprecated. It is no longer required.');
        }
        return;
    }

    
    protected function resize_window($windowsize, $viewport = false) {
                if (!$this->running_javascript()) {
            return;
        }

        switch ($windowsize) {
            case "small":
                $width = 640;
                $height = 480;
                break;
            case "medium":
                $width = 1024;
                $height = 768;
                break;
            case "large":
                $width = 2560;
                $height = 1600;
                break;
            default:
                preg_match('/^(\d+x\d+)$/', $windowsize, $matches);
                if (empty($matches) || (count($matches) != 2)) {
                    throw new ExpectationException("Invalid screen size, can't resize", $this->getSession());
                }
                $size = explode('x', $windowsize);
                $width = (int) $size[0];
                $height = (int) $size[1];
        }
        if ($viewport) {
                                                                                    $offset = $this->getSession()->getDriver()->evaluateScript(
                    'return (function() { var before = document.body.style.overflowY;' .
                    'document.body.style.overflowY = "scroll";' .
                    'var result = {};' .
                    'result.x = window.outerWidth - document.body.offsetWidth;' .
                    'result.y = window.outerHeight - window.innerHeight;' .
                    'document.body.style.overflowY = before;' .
                    'return result; })();');
            $width += $offset['x'];
            $height += $offset['y'];
        }

        $this->getSession()->getDriver()->resizeWindow($width, $height);
    }

    
    public function wait_for_pending_js() {
                if (!$this->running_javascript()) {
            return;
        }

                        for ($i = 0; $i < self::EXTENDED_TIMEOUT * 10; $i++) {
            $pending = '';
            try {
                $jscode = '
                    return function() {
                        if (typeof M === "undefined") {
                            if (document.readyState === "complete") {
                                return "";
                            } else {
                                return "incomplete";
                            }
                        } else if (' . self::PAGE_READY_JS . ') {
                            return "";
                        } else if (typeof M.util !== "undefined") {
                            return M.util.pending_js.join(":");
                        } else {
                            return "incomplete"
                        }
                    }();';
                $pending = $this->getSession()->evaluateScript($jscode);
            } catch (NoSuchWindow $nsw) {
                                                $pending = '';
            } catch (UnknownError $e) {
                                if (strstr($e->getMessage(), 'M is not defined') != false) {
                    $pending = '';
                }
            }

                        if ($pending === '') {
                return true;
            }

                        usleep(100000);
        }

                                        throw new \Exception('Javascript code and/or AJAX requests are not ready after ' . self::EXTENDED_TIMEOUT .
            ' seconds. There is a Javascript error or the code is extremely slow.');
    }

    
    public function look_for_exceptions() {
                try {

                        $exceptionsxpath = "//div[@data-rel='fatalerror']";
                        $debuggingxpath = "//div[@data-rel='debugging']";
                        $phperrorxpath = "//div[@data-rel='phpdebugmessage']";
                        $othersxpath = "(//*[contains(., ': call to ')])[1]";

            $xpaths = array($exceptionsxpath, $debuggingxpath, $phperrorxpath, $othersxpath);
            $joinedxpath = implode(' | ', $xpaths);

                                    if (!$this->getSession()->getDriver()->find($joinedxpath)) {
                                $phperrors = behat_get_shutdown_process_errors();
                if (!empty($phperrors)) {
                    foreach ($phperrors as $error) {
                        $errnostring = behat_get_error_string($error['type']);
                        $msgs[] = $errnostring . ": " .$error['message'] . " at " . $error['file'] . ": " . $error['line'];
                    }
                    $msg = "PHP errors found:\n" . implode("\n", $msgs);
                    throw new \Exception(htmlentities($msg));
                }

                return;
            }

                        if ($errormsg = $this->getSession()->getPage()->find('xpath', $exceptionsxpath)) {

                                $errorinfoboxes = $this->getSession()->getPage()->findAll('css', 'div.alert-error');
                                if (empty($errorinfoboxes)) {
                    $errorinfoboxes = $this->getSession()->getPage()->findAll('css', 'div.notifytiny');
                }

                                if (empty($errorinfoboxes)) {
                    $errorinfoboxes = $this->getSession()->getPage()->findAll('css', 'div.moodle-exception-message');

                                        if ($errorinfoboxes) {
                        $errorinfo = $this->get_debug_text($errorinfoboxes[0]->getHtml());
                    }

                } else {
                    $errorinfo = $this->get_debug_text($errorinfoboxes[0]->getHtml()) . "\n" .
                        $this->get_debug_text($errorinfoboxes[1]->getHtml());
                }

                $msg = "Moodle exception: " . $errormsg->getText() . "\n" . $errorinfo;
                throw new \Exception(html_entity_decode($msg));
            }

                        if ($debuggingmessages = $this->getSession()->getPage()->findAll('xpath', $debuggingxpath)) {
                $msgs = array();
                foreach ($debuggingmessages as $debuggingmessage) {
                    $msgs[] = $this->get_debug_text($debuggingmessage->getHtml());
                }
                $msg = "debugging() message/s found:\n" . implode("\n", $msgs);
                throw new \Exception(html_entity_decode($msg));
            }

                        if ($phpmessages = $this->getSession()->getPage()->findAll('xpath', $phperrorxpath)) {

                $msgs = array();
                foreach ($phpmessages as $phpmessage) {
                    $msgs[] = $this->get_debug_text($phpmessage->getHtml());
                }
                $msg = "PHP debug message/s found:\n" . implode("\n", $msgs);
                throw new \Exception(html_entity_decode($msg));
            }

                                                if ($this->getSession()->getDriver()->find($othersxpath)) {
                $backtracespattern = '/(line [0-9]* of [^:]*: call to [\->&;:a-zA-Z_\x7f-\xff][\->&;:a-zA-Z0-9_\x7f-\xff]*)/';
                if (preg_match_all($backtracespattern, $this->getSession()->getPage()->getContent(), $backtraces)) {
                    $msgs = array();
                    foreach ($backtraces[0] as $backtrace) {
                        $msgs[] = $backtrace . '()';
                    }
                    $msg = "Other backtraces found:\n" . implode("\n", $msgs);
                    throw new \Exception(htmlentities($msg));
                }
            }

        } catch (NoSuchWindow $e) {
                    }
    }

    
    protected function get_debug_text($html) {

                $notags = preg_replace('/<+\s*\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>*/i', "\n", $html);
        return preg_replace("/(\n)+/s", "\n", $notags);
    }

    
    protected function execute($contextapi, $params = array()) {
        if (!is_array($params)) {
            $params = array($params);
        }

                $contextapi = explode("::", $contextapi);
        $context = behat_context_helper::get($contextapi[0]);
        call_user_func_array(array($context, $contextapi[1]), $params);

                
                $this->wait_for_pending_js();

                $this->look_for_exceptions();
    }
}
