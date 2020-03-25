<?php




require_once(__DIR__ . '/../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../lib/behat/behat_field_manager.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;


class behat_admin extends behat_base {

    
    public function i_set_the_following_administration_settings_values(TableNode $table) {

        if (!$data = $table->getRowsHash()) {
            return;
        }

        foreach ($data as $label => $value) {

                        if (!$this->getSession()->getPage()->find('css', '.block_settings')) {
                $this->getSession()->visit($this->locate_path('/'));
                $this->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);
            }

                        $searchbox = $this->find_field(get_string('searchinsettings', 'admin'));
            $searchbox->setValue($label);
            $submitsearch = $this->find('css', 'form.adminsearchform input[type=submit]');
            $submitsearch->press();

            $this->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);

                                    $exception = new ElementNotFoundException($this->getSession(), '"' . $label . '" administration setting ');

                        $label = behat_context_helper::escape($label);

                        try {
                $fieldxpath = "//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]" .
                    "[@id=//label[contains(normalize-space(.), $label)]/@for or " .
                    "@id=//span[contains(normalize-space(.), $label)]/preceding-sibling::label[1]/@for]";
                $fieldnode = $this->find('xpath', $fieldxpath, $exception);

                $formfieldtypenode = $this->find('xpath', $fieldxpath . "/ancestor::div[@class='form-setting']" .
                    "/child::div[contains(concat(' ', @class, ' '),  ' form-')]/child::*/parent::div");

            } catch (ElementNotFoundException $e) {

                                $fieldxpath = "//*[label[.= $label]|span[.= $label]]/ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' form-item ')]" .
                    "/descendant::div[@class='form-group']/descendant::*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]";
                $fieldnode = $this->find('xpath', $fieldxpath);

                                $formfieldtypenode = $fieldnode;
            }

                        $classes = explode(' ', $formfieldtypenode->getAttribute('class'));
            foreach ($classes as $class) {
                if (substr($class, 0, 5) == 'form-') {
                    $type = substr($class, 5);
                }
            }

                        $field = behat_field_manager::get_field_instance($type, $fieldnode, $this->getSession());
            $field->set_value($value);

            $this->find_button(get_string('savechanges'))->press();
        }
    }

    
    public function the_following_config_values_are_set_as_admin(TableNode $table) {

        if (!$data = $table->getRowsHash()) {
            return;
        }

        foreach ($data as $config => $value) {
                        $plugin = null;

            if (is_array($value)) {
                $plugin = $value[1];
                $value = $value[0];
            }
            set_config($config, $value, $plugin);
        }
    }

    
    protected function wait($timeout, $javascript = false) {
        if ($this->running_javascript()) {
            $this->getSession()->wait($timeout, $javascript);
        }
    }
}
