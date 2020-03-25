<?php



require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');


class behat_tool_lp extends behat_base {

    
    public function click_on_edit_menu_of_the_row($nodetext, $rowname) {
        $xpathtarget = "//ul//li//ul//li[@class='tool-lp-menu-item']//a[contains(.,'" . $nodetext . "')]";

        $this->execute('behat_general::i_click_on_in_the', [get_string('edit'), 'link', $this->escape($rowname), 'table_row']);
        $this->execute('behat_general::i_click_on_in_the', [$xpathtarget, 'xpath_element', $this->escape($rowname), 'table_row']);
    }

    
    public function select_of_the_competency_tree($competencyname) {
        $xpathtarget = "//li[@role='tree-item']//span[contains(.,'" . $competencyname . "')]";

        $this->execute('behat_general::i_click_on', [$xpathtarget, 'xpath_element']);
    }

    
    public function i_click_on_item_in_the_autocomplete_list($item) {
        $xpathtarget = "//ul[@class='form-autocomplete-suggestions']//li//span//span[contains(.,'" . $item . "')]";

        $this->execute('behat_general::i_click_on', [$xpathtarget, 'xpath_element']);
    }
}
