<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Mink\Exception\DriverException as DriverException;


class behat_navigation extends behat_base {

    
    protected function get_node_text_node($text, $branch = false, $collapsed = null, $exception = null) {
        if ($exception === null) {
            $exception = new ExpectationException('The "' . $text . '" node could not be found', $this->getSession());
        } else if (is_string($exception)) {
            $exception = new ExpectationException($exception, $this->getSession());
        }

        $nodetextliteral = behat_context_helper::escape($text);
        $hasblocktree = "[contains(concat(' ', normalize-space(@class), ' '), ' block_tree ')]";
        $hasbranch = "[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]";
        $hascollapsed = "p[@aria-expanded='false']";
        $notcollapsed = "p[@aria-expanded='true']";
        $match = "[normalize-space(.)={$nodetextliteral}]";

                $isbranch = ($branch) ? $hasbranch : '';
        if ($collapsed === true) {
            $iscollapsed = $hascollapsed;
        } else if ($collapsed === false) {
            $iscollapsed = $notcollapsed;
        } else {
            $iscollapsed = 'p';
        }

                $xpath  = "//ul{$hasblocktree}/li/{$hascollapsed}{$isbranch}/span{$match}|";
        $xpath  .= "//ul{$hasblocktree}/li/{$hascollapsed}{$isbranch}/a{$match}|";

                $xpath .= "//ul{$hasblocktree}//ul/li/{$iscollapsed}{$isbranch}/a{$match}|";

                $xpath .= "//ul{$hasblocktree}//ul/li/{$iscollapsed}{$isbranch}/span{$match}";

        $node = $this->find('xpath', $xpath, $exception);
        $this->ensure_node_is_visible($node);
        return $node;
    }

    
    public function navigation_node_should_be_expandable($nodetext) {
        if (!$this->running_javascript()) {
                        return false;
        }

        $node = $this->get_node_text_node($nodetext, true);
        $node = $node->getParent();
        if ($node->hasClass('emptybranch')) {
            throw new ExpectationException('The "' . $nodetext . '" node is not expandable', $this->getSession());
        }

        return true;
    }

    
    public function navigation_node_should_not_be_expandable($nodetext) {
        if (!$this->running_javascript()) {
                        return false;
        }

        $node = $this->get_node_text_node($nodetext);
        $node = $node->getParent();

        if ($node->hasClass('emptybranch') || $node->hasClass('tree_item')) {
            return true;
        }
        throw new ExpectationException('The "' . $nodetext . '" node is expandable', $this->getSession());
    }

    
    public function i_follow_in_the_user_menu($nodetext) {

        if ($this->running_javascript()) {
                        $xpath = "//div[@class='usermenu']//a[contains(concat(' ', @class, ' '), ' toggle-display ')]";
            $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
        }

                        $csspath = ".usermenu [data-rel='menu-content']";

        $this->execute('behat_general::i_click_on_in_the',
            array($nodetext, "link", $csspath, "css_element")
        );
    }

    
    public function i_expand_node($nodetext) {

                                if (!$this->running_javascript()) {
            if ($nodetext === get_string('administrationsite')) {
                                $this->execute('behat_general::i_click_on_in_the',
                    array($nodetext, "link", get_string('administration'), "block")
                );
                return true;
            }
            return true;
        }

        $node = $this->get_node_text_node($nodetext, true, true, 'The "' . $nodetext . '" node can not be expanded');
                if (strtolower($node->getTagName()) === 'a') {
                        $node = $node->getParent();
        }
        $node->click();
    }

    
    public function i_collapse_node($nodetext) {

                if (!$this->running_javascript()) {
            return true;
        }

        $node = $this->get_node_text_node($nodetext, true, false, 'The "' . $nodetext . '" node can not be collapsed');
                if (strtolower($node->getTagName()) === 'a') {
                        $node = $node->getParent();
        }
        $node->click();
    }

    
    public function i_navigate_to_node_in($nodetext, $parentnodes) {
        $parentnodes = array_map('trim', explode('>', $parentnodes));
        $this->select_node_in_navigation($nodetext, $parentnodes);
    }

    
    protected function find_node_in_navigation($nodetext, $parentnodes, $nodetype = 'link') {
                $siteadminstr = get_string('administrationsite');

        $countparentnode = count($parentnodes);

                        if (!$this->running_javascript()) {
            if ($parentnodes[0] === $siteadminstr) {
                                                $siteadminlink = $this->getSession()->getPage()->find('named_exact', array('link', "'" . $siteadminstr . "'"));
                if ($siteadminlink) {
                    $siteadminlink->click();
                }
            }
        }

                $node = $this->get_top_navigation_node($parentnodes[0]);

                for ($i = 0; $i < $countparentnode; $i++) {
            if ($i > 0) {
                                $node = $this->get_navigation_node($parentnodes[$i], $node);
            }

                        $pnodexpath = "/p[contains(concat(' ', normalize-space(@class), ' '), ' tree_item ')]";
            $pnode = $node->find('xpath', $pnodexpath);

                        if ($pnode && $this->running_javascript() && $pnode->hasAttribute('aria-expanded') &&
                ($pnode->getAttribute('aria-expanded') == "false")) {

                $this->ensure_node_is_visible($pnode);

                                                                $nodetoexpandliteral = behat_context_helper::escape($parentnodes[$i]);
                $nodetoexpandxpathlink = $pnodexpath . "/a[normalize-space(.)=" . $nodetoexpandliteral . "]";

                if ($nodetoexpandlink = $node->find('xpath', $nodetoexpandxpathlink)) {
                    $behatgeneralcontext = behat_context_helper::get('behat_general');
                    $nodetoexpandlink->click();
                    $behatgeneralcontext->wait_until_the_page_is_ready();
                } else {
                    $pnode->click();
                }

                                if ($pnode->hasAttribute('data-loaded') && $pnode->getAttribute('data-loaded') == "false") {
                    $jscondition = '(document.evaluate("' . $pnode->getXpath() . '", document, null, '.
                        'XPathResult.ANY_TYPE, null).iterateNext().getAttribute(\'data-loaded\') == "true")';

                    $this->getSession()->wait(self::EXTENDED_TIMEOUT * 1000, $jscondition);
                }
            }
        }

                $nodetextliteral = behat_context_helper::escape($nodetext);
        $tagname = ($nodetype === 'link') ? 'a' : 'span';
        $xpath = "/ul/li/p[contains(concat(' ', normalize-space(@class), ' '), ' tree_item ')]" .
            "/{$tagname}[normalize-space(.)=" . $nodetextliteral . "]";
        return $node->find('xpath', $xpath);
    }

    
    protected function select_node_in_navigation($nodetext, $parentnodes) {
        $nodetoclick = $this->find_node_in_navigation($nodetext, $parentnodes);
                if (!$nodetoclick) {
            throw new ExpectationException('Navigation node "' . $nodetext . '" not found under "' .
                implode($parentnodes, ' > ') . '"', $this->getSession());
        }

        $nodetoclick->click();
    }

    
    protected function get_top_navigation_node($nodetext) {

                $nodetextliteral = behat_context_helper::escape($nodetext);
        $exception = new ExpectationException('Top navigation node "' . $nodetext . ' not found in "', $this->getSession());

                $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]" .
            "/ul[contains(concat(' ', normalize-space(@class), ' '), ' block_tree ')]" .
            "/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
            "/ul/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
            "[p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
            "/span[normalize-space(.)=" . $nodetextliteral ."]]" .
            "|" .
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]/div" .
            "/ul[contains(concat(' ', normalize-space(@class), ' '), ' block_tree ')]" .
            "/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
            "/ul/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
            "[p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
            "/span[normalize-space(.)=" . $nodetextliteral ."]]" .
            "|" .
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]/div" .
            "/ul[contains(concat(' ', normalize-space(@class), ' '), ' block_tree ')]" .
            "/li[p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
            "/span[normalize-space(.)=" . $nodetextliteral ."]]" .
            "|" .
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' content ')]/div" .
            "/ul[contains(concat(' ', normalize-space(@class), ' '), ' block_tree ')]" .
            "/li[p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
            "/a[normalize-space(.)=" . $nodetextliteral ."]]";

        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    
    protected function get_navigation_node($nodetext, $parentnode = null) {

                $nodetextliteral = behat_context_helper::escape($nodetext);

        $xpath = "/ul/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
            "[child::p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
            "/child::span[normalize-space(.)=" . $nodetextliteral ."]]";
        $node = $parentnode->find('xpath', $xpath);
        if (!$node) {
            $xpath = "/ul/li[contains(concat(' ', normalize-space(@class), ' '), ' contains_branch ')]" .
                "[child::p[contains(concat(' ', normalize-space(@class), ' '), ' branch ')]" .
                "/child::a[normalize-space(.)=" . $nodetextliteral ."]]";
            $node = $parentnode->find('xpath', $xpath);
        }

        if (!$node) {
            throw new ExpectationException('Sub-navigation node "' . $nodetext . '" not found under "' .
                $parentnode->getText() . '"', $this->getSession());
        }
        return $node;
    }

    
    public function get_expand_navbar_step() {

                
                                        $navbuttonjs = "return (
            Y.one('.btn-navbar') &&
            Y.one('.btn-navbar').getComputedStyle('display') !== 'none'
        )";

                if (!$this->getSession()->getDriver()->evaluateScript($navbuttonjs)) {
            return false;
        }

        $this->execute('behat_general::i_click_on', array(".btn-navbar", "css_element"));
    }

    
    public function i_navigate_to_in_current_page_administration($nodetext) {
        $parentnodes = array_map('trim', explode('>', $nodetext));
                $xpath = '//div[contains(@class,\'block_settings\')]//div[@id=\'settingsnav\']/ul/li[1]/p[1]/span';
        $node = $this->find('xpath', $xpath);
        array_unshift($parentnodes, $node->getText());
        $lastnode = array_pop($parentnodes);
        $this->select_node_in_navigation($lastnode, $parentnodes);
    }

    
    public function should_exist_in_current_page_administration($element, $selectortype) {
        $parentnodes = array_map('trim', explode('>', $element));
                $xpath = '//div[contains(@class,\'block_settings\')]//div[@id=\'settingsnav\']/ul/li[1]/p[1]/span';
        $node = $this->find('xpath', $xpath);
        array_unshift($parentnodes, $node->getText());
        $lastnode = array_pop($parentnodes);

        if (!$this->find_node_in_navigation($lastnode, $parentnodes, strtolower($selectortype))) {
            throw new ExpectationException(ucfirst($selectortype) . ' "' . $element .
                '" not found in current page administration"', $this->getSession());
        }
    }

    
    public function should_not_exist_in_current_page_administration($element, $selectortype) {
        $parentnodes = array_map('trim', explode('>', $element));
                $xpath = '//div[contains(@class,\'block_settings\')]//div[@id=\'settingsnav\']/ul/li[1]/p[1]/span';
        $node = $this->find('xpath', $xpath);
        array_unshift($parentnodes, $node->getText());
        $lastnode = array_pop($parentnodes);

        if ($this->find_node_in_navigation($lastnode, $parentnodes, strtolower($selectortype))) {
            throw new ExpectationException(ucfirst($selectortype) . ' "' . $element .
                '" found in current page administration"', $this->getSession());
        }
    }

    
    public function i_navigate_to_in_site_administration($nodetext) {
        $parentnodes = array_map('trim', explode('>', $nodetext));
        array_unshift($parentnodes, get_string('administrationsite'));
        $lastnode = array_pop($parentnodes);
        $this->select_node_in_navigation($lastnode, $parentnodes);
    }
}
