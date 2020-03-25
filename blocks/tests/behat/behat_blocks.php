<?php



use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

require_once(__DIR__ . '/../../../lib/behat/behat_base.php');


class behat_blocks extends behat_base {

    
    public function i_add_the_block($blockname) {
        $this->execute('behat_forms::i_set_the_field_to',
            array("bui_addblock", $this->escape($blockname))
        );

                if (!$this->running_javascript()) {
            $this->execute('behat_general::i_click_on_in_the',
                array(get_string('go'), "button", "#add_block", "css_element")
            );
        }
    }

    
    public function i_add_the_block_if_not_present($blockname) {
        try {
            $this->get_text_selector_node('block', $blockname);
        } catch (ElementNotFoundException $e) {
            $this->execute('behat_blocks::i_add_the_block', [$blockname]);
        }
    }

    
    public function i_dock_block($blockname) {

                $xpath = "//input[@type='image'][@title='" . get_string('dockblock', 'block', $blockname) . "' or @alt='" . get_string('addtodock', 'block') . "']";
        $this->execute('behat_general::i_click_on_in_the',
            array($xpath, "xpath_element", $this->escape($blockname), "block")
        );
    }

    
    public function i_open_the_blocks_action_menu($blockname) {

        if (!$this->running_javascript()) {
                        return;
        }

                $blocknode = $this->get_text_selector_node('block', $blockname);
        if ($blocknode->hasClass('action-menu-shown')) {
            return;
        }

        $this->execute('behat_general::i_click_on_in_the',
            array("a[role='menuitem']", "css_element", $this->escape($blockname), "block")
        );
    }

    
    public function i_configure_the_block($blockname) {
        
        $this->execute("behat_blocks::i_open_the_blocks_action_menu", $this->escape($blockname));

        $this->execute('behat_general::i_click_on_in_the',
            array("Configure", "link", $this->escape($blockname), "block")
        );
    }
}
