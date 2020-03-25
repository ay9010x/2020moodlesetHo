<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchElement as NoSuchElement,
    WebDriver\Exception\StaleElementReference as StaleElementReference,
    Behat\Gherkin\Node\TableNode as TableNode;


class behat_general extends behat_base {

    
    const MAIN_WINDOW_NAME = '__moodle_behat_main_window_name';

    
    const PAGE_LOAD_DETECTION_STRING = 'new_page_not_loaded_since_behat_started_watching';

    
    private $pageloaddetectionrunning = false;

    
    public function i_am_on_homepage() {
        $this->getSession()->visit($this->locate_path('/'));
    }

    
    public function i_am_on_site_homepage() {
        $this->getSession()->visit($this->locate_path('/?redirect=0'));
    }

    
    public function reload() {
        $this->getSession()->reload();
    }

    
    public function i_wait_to_be_redirected() {

                        if (!$metarefresh = $this->getSession()->getPage()->find('xpath', "//head/descendant::meta[@http-equiv='refresh']")) {
                        return true;
        }

                try {
            $content = $metarefresh->getAttribute('content');
        } catch (NoSuchElement $e) {
            return true;
        } catch (StaleElementReference $e) {
            return true;
        }

                if (strstr($content, 'url') != false) {

            list($waittime, $url) = explode(';', $content);

                        $url = trim(substr($url, strpos($url, 'http')));

        } else {
                        $waittime = $content;
        }


                if ($this->running_javascript()) {
            $this->getSession()->wait($waittime * 1000, false);

        } else if (!empty($url)) {
                        $this->getSession()->getDriver()->getClient()->request('get', $url);

        } else {
                        $this->getSession()->getDriver()->reload();
        }
    }

    
    public function switch_to_iframe($iframename) {

                                $this->spin(
            function($context, $iframename) {
                $context->getSession()->switchToIFrame($iframename);

                                return true;
            },
            $iframename,
            self::EXTENDED_TIMEOUT
        );
    }

    
    public function switch_to_the_main_frame() {
        $this->getSession()->switchToIFrame();
    }

    
    public function switch_to_window($windowname) {
                                                        $this->getSession()->executeScript(
                'if (window.name == "") window.name = "' . self::MAIN_WINDOW_NAME . '"');

        $this->getSession()->switchToWindow($windowname);
    }

    
    public function switch_to_the_main_window() {
        $this->getSession()->switchToWindow(self::MAIN_WINDOW_NAME);
    }

    
    public function accept_currently_displayed_alert_dialog() {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    
    public function dismiss_currently_displayed_alert_dialog() {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    
    public function click_link($link) {

        $linknode = $this->find_link($link);
        $this->ensure_node_is_visible($linknode);
        $linknode->click();
    }

    
    public function i_wait_seconds($seconds) {
        if ($this->running_javascript()) {
            $this->getSession()->wait($seconds * 1000, false);
        } else {
            sleep($seconds);
        }
    }

    
    public function wait_until_the_page_is_ready() {

                if (!$this->running_javascript()) {
            return;
        }

        $this->getSession()->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);
    }

    
    public function wait_until_exists($element, $selectortype) {
        $this->ensure_element_exists($element, $selectortype);
    }

    
    public function wait_until_does_not_exists($element, $selectortype) {
        $this->ensure_element_does_not_exist($element, $selectortype);
    }

    
    public function i_hover($element, $selectortype) {

                $node = $this->get_selected_node($selectortype, $element);
        $node->mouseOver();
    }

    
    public function i_click_on($element, $selectortype) {

                $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    
    public function i_take_focus_off_field($element, $selectortype) {
        if (!$this->running_javascript()) {
            throw new ExpectationException('Can\'t take focus off from "' . $element . '" in non-js mode', $this->getSession());
        }
                $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);

                $node->focus();
        $node->blur();
    }

    
    public function i_click_on_confirming_the_dialogue($element, $selectortype) {
        $this->i_click_on($element, $selectortype);
        $this->accept_currently_displayed_alert_dialog();
    }

    
    public function i_click_on_dismissing_the_dialogue($element, $selectortype) {
        $this->i_click_on($element, $selectortype);
        $this->dismiss_currently_displayed_alert_dialog();
    }

    
    public function i_click_on_in_the($element, $selectortype, $nodeelement, $nodeselectortype) {

        $node = $this->get_node_in_container($selectortype, $element, $nodeselectortype, $nodeelement);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    
    public function i_drag_and_i_drop_it_in($element, $selectortype, $containerelement, $containerselectortype) {

        list($sourceselector, $sourcelocator) = $this->transform_selector($selectortype, $element);
        $sourcexpath = $this->getSession()->getSelectorsHandler()->selectorToXpath($sourceselector, $sourcelocator);

        list($containerselector, $containerlocator) = $this->transform_selector($containerselectortype, $containerelement);
        $destinationxpath = $this->getSession()->getSelectorsHandler()->selectorToXpath($containerselector, $containerlocator);

        $node = $this->get_selected_node("xpath_element", $sourcexpath);
        if (!$node->isVisible()) {
            throw new ExpectationException('"' . $sourcexpath . '" "xpath_element" is not visible', $this->getSession());
        }
        $node = $this->get_selected_node("xpath_element", $destinationxpath);
        if (!$node->isVisible()) {
            throw new ExpectationException('"' . $destinationxpath . '" "xpath_element" is not visible', $this->getSession());
        }

        $this->getSession()->getDriver()->dragTo($sourcexpath, $destinationxpath);
    }

    
    public function should_be_visible($element, $selectortype) {

        if (!$this->running_javascript()) {
            throw new DriverException('Visible checks are disabled in scenarios without Javascript support');
        }

        $node = $this->get_selected_node($selectortype, $element);
        if (!$node->isVisible()) {
            throw new ExpectationException('"' . $element . '" "' . $selectortype . '" is not visible', $this->getSession());
        }
    }

    
    public function should_not_be_visible($element, $selectortype) {

        try {
            $this->should_be_visible($element, $selectortype);
        } catch (ExpectationException $e) {
                        return;
        }
        throw new ExpectationException('"' . $element . '" "' . $selectortype . '" is visible', $this->getSession());
    }

    
    public function in_the_should_be_visible($element, $selectortype, $nodeelement, $nodeselectortype) {

        if (!$this->running_javascript()) {
            throw new DriverException('Visible checks are disabled in scenarios without Javascript support');
        }

        $node = $this->get_node_in_container($selectortype, $element, $nodeselectortype, $nodeelement);
        if (!$node->isVisible()) {
            throw new ExpectationException(
                '"' . $element . '" "' . $selectortype . '" in the "' . $nodeelement . '" "' . $nodeselectortype . '" is not visible',
                $this->getSession()
            );
        }
    }

    
    public function in_the_should_not_be_visible($element, $selectortype, $nodeelement, $nodeselectortype) {

        try {
            $this->in_the_should_be_visible($element, $selectortype, $nodeelement, $nodeselectortype);
        } catch (ExpectationException $e) {
                        return;
        }
        throw new ExpectationException(
            '"' . $element . '" "' . $selectortype . '" in the "' . $nodeelement . '" "' . $nodeselectortype . '" is visible',
            $this->getSession()
        );
    }

    
    public function assert_page_contains_text($text) {

                        $xpathliteral = behat_context_helper::escape($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

        try {
            $nodes = $this->find_all('xpath', $xpath);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $text . '" text was not found in the page', $this->getSession());
        }

                        if (!$this->running_javascript()) {
            return;
        }

                                $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        return true;
                    }
                }

                                throw new ExpectationException('"' . $args['text'] . '" text was found but was not visible', $context->getSession());
            },
            array('nodes' => $nodes, 'text' => $text),
            false,
            false,
            true
        );

    }

    
    public function assert_page_not_contains_text($text) {

                        $xpathliteral = behat_context_helper::escape($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

                                try {
            $nodes = $this->find_all('xpath', $xpath, false, false, self::REDUCED_TIMEOUT);
        } catch (ElementNotFoundException $e) {
                        return;
        }

                        if (!$this->running_javascript()) {
            throw new ExpectationException('"' . $text . '" text was found in the page', $this->getSession());
        }

                $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        throw new ExpectationException('"' . $args['text'] . '" text was found in the page', $context->getSession());
                    }
                }

                                return true;
            },
            array('nodes' => $nodes, 'text' => $text),
            self::REDUCED_TIMEOUT,
            false,
            true
        );

    }

    
    public function assert_element_contains_text($text, $element, $selectortype) {

                $container = $this->get_selected_node($selectortype, $element);

                        $xpathliteral = behat_context_helper::escape($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

                try {
            $nodes = $this->find_all('xpath', $xpath, false, $container);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $text . '" text was not found in the "' . $element . '" element', $this->getSession());
        }

                        if (!$this->running_javascript()) {
            return;
        }

                        $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        return true;
                    }
                }

                throw new ExpectationException('"' . $args['text'] . '" text was found in the "' . $args['element'] . '" element but was not visible', $context->getSession());
            },
            array('nodes' => $nodes, 'text' => $text, 'element' => $element),
            false,
            false,
            true
        );
    }

    
    public function assert_element_not_contains_text($text, $element, $selectortype) {

                $container = $this->get_selected_node($selectortype, $element);

                        $xpathliteral = behat_context_helper::escape($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

                        try {
            $nodes = $this->find_all('xpath', $xpath, false, $container, self::REDUCED_TIMEOUT);
        } catch (ElementNotFoundException $e) {
                        return;
        }

                        if (!$this->running_javascript()) {
            throw new ExpectationException('"' . $text . '" text was found in the "' . $element . '" element', $this->getSession());
        }

                $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        throw new ExpectationException('"' . $args['text'] . '" text was found in the "' . $args['element'] . '" element', $context->getSession());
                    }
                }

                                return true;
            },
            array('nodes' => $nodes, 'text' => $text, 'element' => $element),
            self::REDUCED_TIMEOUT,
            false,
            true
        );
    }

    
    public function should_appear_before($preelement, $preselectortype, $postelement, $postselectortype) {

                list($preselector, $prelocator) = $this->transform_selector($preselectortype, $preelement);
        list($postselector, $postlocator) = $this->transform_selector($postselectortype, $postelement);

        $prexpath = $this->find($preselector, $prelocator)->getXpath();
        $postxpath = $this->find($postselector, $postlocator)->getXpath();

                $msg = '"'.$preelement.'" "'.$preselectortype.'" does not appear before "'.$postelement.'" "'.$postselectortype.'"';
        $xpath = $prexpath.'/following::*[contains(., '.$postxpath.')]';
        if (!$this->getSession()->getDriver()->find($xpath)) {
            throw new ExpectationException($msg, $this->getSession());
        }
    }

    
    public function should_appear_after($postelement, $postselectortype, $preelement, $preselectortype) {

                list($postselector, $postlocator) = $this->transform_selector($postselectortype, $postelement);
        list($preselector, $prelocator) = $this->transform_selector($preselectortype, $preelement);

        $postxpath = $this->find($postselector, $postlocator)->getXpath();
        $prexpath = $this->find($preselector, $prelocator)->getXpath();

                $msg = '"'.$postelement.'" "'.$postselectortype.'" does not appear after "'.$preelement.'" "'.$preselectortype.'"';
        $xpath = $postxpath.'/preceding::*[contains(., '.$prexpath.')]';
        if (!$this->getSession()->getDriver()->find($xpath)) {
            throw new ExpectationException($msg, $this->getSession());
        }
    }

    
    public function the_element_should_be_disabled($element, $selectortype) {

                $node = $this->get_selected_node($selectortype, $element);

        if (!$node->hasAttribute('disabled')) {
            throw new ExpectationException('The element "' . $element . '" is not disabled', $this->getSession());
        }
    }

    
    public function the_element_should_be_enabled($element, $selectortype) {

                $node = $this->get_selected_node($selectortype, $element);

        if ($node->hasAttribute('disabled')) {
            throw new ExpectationException('The element "' . $element . '" is not enabled', $this->getSession());
        }
    }

    
    public function the_element_should_be_readonly($element, $selectortype) {
                $node = $this->get_selected_node($selectortype, $element);

        if (!$node->hasAttribute('readonly')) {
            throw new ExpectationException('The element "' . $element . '" is not readonly', $this->getSession());
        }
    }

    
    public function the_element_should_not_be_readonly($element, $selectortype) {
                $node = $this->get_selected_node($selectortype, $element);

        if ($node->hasAttribute('readonly')) {
            throw new ExpectationException('The element "' . $element . '" is readonly', $this->getSession());
        }
    }

    
    public function should_exist($element, $selectortype) {

                list($selector, $locator) = $this->transform_selector($selectortype, $element);

                $this->find($selector, $locator);
    }

    
    public function should_not_exist($element, $selectortype) {

                list($selector, $locator) = $this->transform_selector($selectortype, $element);

        try {

                                    $params = array('selector' => $selector, 'locator' => $locator);
                        $exception = new ElementNotFoundException($this->getSession(), $selectortype, null, $element);

                        $this->spin(
                function($context, $args) {
                    return $context->getSession()->getPage()->findAll($args['selector'], $args['locator']);
                },
                $params,
                self::REDUCED_TIMEOUT,
                $exception,
                false
            );
        } catch (ElementNotFoundException $e) {
                        return;
        }

        throw new ExpectationException('The "' . $element . '" "' . $selectortype .
                '" exists in the current page', $this->getSession());
    }

    
    public function i_trigger_cron() {
        $this->getSession()->visit($this->locate_path('/admin/cron.php'));
    }

    
    public function i_run_the_scheduled_task($taskname) {
        $task = \core\task\manager::get_scheduled_task($taskname);
        if (!$task) {
            throw new DriverException('The "' . $taskname . '" scheduled task does not exist');
        }

                raise_memory_limit(MEMORY_EXTRA);
        cron_setup_user();

                $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');
        if (!$cronlock = $cronlockfactory->get_lock('core_cron', 10)) {
            throw new DriverException('Unable to obtain core_cron lock for scheduled task');
        }
        if (!$lock = $cronlockfactory->get_lock('\\' . get_class($task), 10)) {
            $cronlock->release();
            throw new DriverException('Unable to obtain task lock for scheduled task');
        }
        $task->set_lock($lock);
        if (!$task->is_blocking()) {
            $cronlock->release();
        } else {
            $task->set_cron_lock($cronlock);
        }

        try {
                        ob_start();
            $task->execute();
            ob_end_clean();

                        \core\task\manager::scheduled_task_complete($task);
        } catch (Exception $e) {
                        \core\task\manager::scheduled_task_failed($task);
            throw new DriverException('The "' . $taskname . '" scheduled task failed', 0, $e);
        }
    }

    
    public function should_exist_in_the($element, $selectortype, $containerelement, $containerselectortype) {
                $containernode = $this->get_selected_node($containerselectortype, $containerelement);

        list($selector, $locator) = $this->transform_selector($selectortype, $element);

                $locatorexceptionmsg = $element . '" in the "' . $containerelement. '" "' . $containerselectortype. '"';
        $exception = new ElementNotFoundException($this->getSession(), $selectortype, null, $locatorexceptionmsg);

                $this->find($selector, $locator, $exception, $containernode);
    }

    
    public function should_not_exist_in_the($element, $selectortype, $containerelement, $containerselectortype) {

                        $containernode = $this->get_selected_node($containerselectortype, $containerelement);

        list($selector, $locator) = $this->transform_selector($selectortype, $element);

                        try {
                                                $this->find($selector, $locator, false, $containernode, self::REDUCED_TIMEOUT);
        } catch (ElementNotFoundException $e) {
                        return;
        }
        throw new ExpectationException('The "' . $element . '" "' . $selectortype . '" exists in the "' .
                $containerelement . '" "' . $containerselectortype . '"', $this->getSession());
    }

    
    public function i_change_window_size_to($windowviewport, $windowsize) {
        $this->resize_window($windowsize, $windowviewport === 'viewport');
    }

    
    public function the_attribute_of_should_contain($attribute, $element, $selectortype, $text) {
                $containernode = $this->get_selected_node($selectortype, $element);
        $value = $containernode->getAttribute($attribute);
        if ($value == null) {
            throw new ExpectationException('The attribute "' . $attribute. '" does not exist',
                    $this->getSession());
        } else if (strpos($value, $text) === false) {
            throw new ExpectationException('The attribute "' . $attribute .
                    '" does not contain "' . $text . '" (actual value: "' . $value . '")',
                    $this->getSession());
        }
    }

    
    public function the_attribute_of_should_not_contain($attribute, $element, $selectortype, $text) {
                $containernode = $this->get_selected_node($selectortype, $element);
        $value = $containernode->getAttribute($attribute);
        if ($value == null) {
            throw new ExpectationException('The attribute "' . $attribute. '" does not exist',
                    $this->getSession());
        } else if (strpos($value, $text) !== false) {
            throw new ExpectationException('The attribute "' . $attribute .
                    '" contains "' . $text . '" (value: "' . $value . '")',
                    $this->getSession());
        }
    }

    
    public function row_column_of_table_should_contain($row, $column, $table, $value) {
        $tablenode = $this->get_selected_node('table', $table);
        $tablexpath = $tablenode->getXpath();

        $rowliteral = behat_context_helper::escape($row);
        $valueliteral = behat_context_helper::escape($value);
        $columnliteral = behat_context_helper::escape($column);

        if (preg_match('/^-?(\d+)-?$/', $column, $columnasnumber)) {
                        $columnpositionxpath = "/child::*[position() = {$columnasnumber[1]}]";
        } else {
                        $theadheaderxpath = "thead/tr[1]/th[(normalize-space(.)=" . $columnliteral . " or a[normalize-space(text())=" .
                    $columnliteral . "] or div[normalize-space(text())=" . $columnliteral . "])]";
            $tbodyheaderxpath = "tbody/tr[1]/td[(normalize-space(.)=" . $columnliteral . " or a[normalize-space(text())=" .
                    $columnliteral . "] or div[normalize-space(text())=" . $columnliteral . "])]";

                        $columnheaderxpath = $tablexpath . "[" . $theadheaderxpath . " | " . $tbodyheaderxpath . "]";
            $columnheader = $this->getSession()->getDriver()->find($columnheaderxpath);
            if (empty($columnheader)) {
                $columnexceptionmsg = $column . '" in table "' . $table . '"';
                throw new ElementNotFoundException($this->getSession(), "\n$columnheaderxpath\n\n".'Column', null, $columnexceptionmsg);
            }
                                                $columnpositionxpath = "/child::*[position() = count(" . $tablexpath . "/" . $theadheaderxpath .
                "/preceding-sibling::*) + 1]";
        }

                                $rowxpath = $tablexpath."/tbody/tr[descendant::th[normalize-space(.)=" . $rowliteral .
                    "] | descendant::td[normalize-space(.)=" . $rowliteral . "]]";

        $columnvaluexpath = $rowxpath . $columnpositionxpath . "[contains(normalize-space(.)," . $valueliteral . ")]";

                $coumnnode = $this->getSession()->getDriver()->find($columnvaluexpath);
        if (empty($coumnnode)) {
            $locatorexceptionmsg = $value . '" in "' . $row . '" row with column "' . $column;
            throw new ElementNotFoundException($this->getSession(), "\n$columnvaluexpath\n\n".'Column value', null, $locatorexceptionmsg);
        }
    }

    
    public function row_column_of_table_should_not_contain($row, $column, $table, $value) {
        try {
            $this->row_column_of_table_should_contain($row, $column, $table, $value);
        } catch (ElementNotFoundException $e) {
                        return;
        }
                throw new ExpectationException(
            '"' . $column . '" with value "' . $value . '" is present in "' . $row . '"  row for table "' . $table . '"',
            $this->getSession()
        );
    }

    
    public function following_should_exist_in_the_table($table, TableNode $data) {
        $datahash = $data->getHash();

        foreach ($datahash as $row) {
            $firstcell = null;
            foreach ($row as $column => $value) {
                if ($firstcell === null) {
                    $firstcell = $value;
                } else {
                    $this->row_column_of_table_should_contain($firstcell, $column, $table, $value);
                }
            }
        }
    }

    
    public function following_should_not_exist_in_the_table($table, TableNode $data) {
        $datahash = $data->getHash();

        foreach ($datahash as $value) {
            $row = array_shift($value);
            foreach ($value as $column => $value) {
                try {
                    $this->row_column_of_table_should_contain($row, $column, $table, $value);
                                    } catch (ElementNotFoundException $e) {
                                        continue;
                }
                throw new ExpectationException('"' . $column . '" with value "' . $value . '" is present in "' .
                    $row . '"  row for table "' . $table . '"', $this->getSession()
                );
            }
        }
    }

    
    public function download_file_from_link($link) {
                $linknode = $this->find_link($link);
        $this->ensure_node_is_visible($linknode);

                $url = $linknode->getAttribute('href');
        if (!$url) {
            throw new ExpectationException('Download link does not have href attribute',
                    $this->getSession());
        }
        if (!preg_match('~^https?://~', $url)) {
            throw new ExpectationException('Download link not an absolute URL: ' . $url,
                    $this->getSession());
        }

                $session = $this->getSession()->getCookie('MoodleSession');
        return download_file_content($url, array('Cookie' => 'MoodleSession=' . $session));
    }

    
    public function following_should_download_bytes($link, $expectedsize) {
        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

                $result = $this->spin(
            function($context, $args) {
                $link = $args['link'];
                return $this->download_file_from_link($link);
            },
            array('link' => $link),
            self::EXTENDED_TIMEOUT,
            $exception
        );

                $actualsize = (int)strlen($result);
        if ($actualsize !== (int)$expectedsize) {
            throw new ExpectationException('Downloaded data was ' . $actualsize .
                    ' bytes, expecting ' . $expectedsize, $this->getSession());
        }
    }

    
    public function following_should_download_between_and_bytes($link, $minexpectedsize, $maxexpectedsize) {
                if ((int)$minexpectedsize > (int)$maxexpectedsize) {
            list($minexpectedsize, $maxexpectedsize) = array($maxexpectedsize, $minexpectedsize);
        }

        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

                $result = $this->spin(
            function($context, $args) {
                $link = $args['link'];

                return $this->download_file_from_link($link);
            },
            array('link' => $link),
            self::EXTENDED_TIMEOUT,
            $exception
        );

                $actualsize = (int)strlen($result);
        if ($actualsize < $minexpectedsize || $actualsize > $maxexpectedsize) {
            throw new ExpectationException('Downloaded data was ' . $actualsize .
                    ' bytes, expecting between ' . $minexpectedsize . ' and ' .
                    $maxexpectedsize, $this->getSession());
        }
    }

    
    public function i_start_watching_to_see_if_a_new_page_loads() {
        if (!$this->running_javascript()) {
            throw new DriverException('Page load detection requires JavaScript.');
        }

        $session = $this->getSession();

        if ($this->pageloaddetectionrunning || $session->getPage()->find('xpath', $this->get_page_load_xpath())) {
                                    throw new ExpectationException(
                'Page load expectation error: page reloads are already been watched for.', $session);
        }

        $this->pageloaddetectionrunning = true;

        $session->executeScript(
                'var span = document.createElement("span");
                span.setAttribute("data-rel", "' . self::PAGE_LOAD_DETECTION_STRING . '");
                span.setAttribute("style", "display: none;");
                document.body.appendChild(span);');
    }

    
    public function a_new_page_should_have_loaded_since_i_started_watching() {
        $session = $this->getSession();

                if (!$this->pageloaddetectionrunning) {
            throw new ExpectationException(
                'Page load expectation error: page load tracking was not started.', $session);
        }

                        if ($session->getPage()->find('xpath', $this->get_page_load_xpath())) {
                        throw new ExpectationException(
                'Page load expectation error: a new page has not been loaded when it should have been.', $session);
        }

                $this->pageloaddetectionrunning = false;
    }

    
    public function a_new_page_should_not_have_loaded_since_i_started_watching() {
        $session = $this->getSession();

                if (!$this->pageloaddetectionrunning) {
            throw new ExpectationException(
                'Page load expectation error: page load tracking was not started.', $session);
        }

                $this->find(
            'xpath',
            $this->get_page_load_xpath(),
            new ExpectationException(
                'Page load expectation error: A new page has been loaded when it should not have been.',
                $this->getSession()
            )
        );
    }

    
    protected function get_page_load_xpath() {
        return "//span[@data-rel = '" . self::PAGE_LOAD_DETECTION_STRING . "']";
    }

    
    public function i_pause_scenario_executon() {
        global $CFG;

        $posixexists = function_exists('posix_isatty');

                if ($posixexists && !@posix_isatty(STDOUT)) {
            $session = $this->getSession();
            throw new ExpectationException('Break point should only be used with interative terminal.', $session);
        }

                $isansicon = getenv('ANSICON');
        if (($CFG->ostype === 'WINDOWS') && empty($isansicon)) {
            fwrite(STDOUT, "Paused. Press Enter/Return to continue.");
            fread(STDIN, 1024);
        } else {
            fwrite(STDOUT, "\033[s\n\033[0;93mPaused. Press \033[1;31mEnter/Return\033[0;93m to continue.\033[0m");
            fread(STDIN, 1024);
            fwrite(STDOUT, "\033[2A\033[u\033[2B");
        }
    }

    
    public function i_press_in_the_browser($button) {
        $session = $this->getSession();

        if ($button == 'back') {
            $session->back();
        } else if ($button == 'forward') {
            $session->forward();
        } else if ($button == 'reload') {
            $session->reload();
        } else {
            throw new ExpectationException('Unknown browser button.', $session);
        }
    }

    
    public function i_press_key_in_element($key, $element, $selectortype) {
        if (!$this->running_javascript()) {
            throw new DriverException('Key down step is not available with Javascript disabled');
        }
                $node = $this->get_selected_node($selectortype, $element);
        $modifier = null;
        $validmodifiers = array('ctrl', 'alt', 'shift', 'meta');
        $char = $key;
        if (strpos($key, '-')) {
            list($modifier, $char) = preg_split('/-/', $key, 2);
            $modifier = strtolower($modifier);
            if (!in_array($modifier, $validmodifiers)) {
                throw new ExpectationException(sprintf('Unknown key modifier: %s.', $modifier));
            }
        }
        if (is_numeric($char)) {
            $char = (int)$char;
        }

        $node->keyDown($char, $modifier);
        $node->keyPress($char, $modifier);
        $node->keyUp($char, $modifier);
    }

    
    public function i_post_tab_key_in_element($element, $selectortype) {
        if (!$this->running_javascript()) {
            throw new DriverException('Tab press step is not available with Javascript disabled');
        }
                $node = $this->get_selected_node($selectortype, $element);
        $this->getSession()->getDriver()->post_key("\xEE\x80\x84", $node->getXpath());
    }

    
    public function database_family_used_is_one_of_the_following(TableNode $databasefamilies) {
        global $DB;

        $dbfamily = $DB->get_dbfamily();

                foreach ($databasefamilies->getRows() as $dbfamilytocheck) {
            if ($dbfamilytocheck[0] == $dbfamily) {
                return;
            }
        }

        throw new \Moodle\BehatExtension\Exception\SkippedException();
    }
}
