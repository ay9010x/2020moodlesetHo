<?php





require_once(__DIR__ . '/../../../lib/behat/behat_base.php');


class behat_auth extends behat_base {

    
    public function i_log_in_as($username) {
                $this->getSession()->visit($this->locate_path('login/index.php'));

                $this->execute('behat_forms::i_set_the_field_to', array('Username', $this->escape($username)));
        $this->execute('behat_forms::i_set_the_field_to', array('Password', $this->escape($username)));

                $this->execute('behat_forms::press_button', get_string('login'));
    }

    
    public function i_log_out() {
                        
                if ($this->running_javascript()) {
            $xpath = "//div[@class='usermenu']//a[contains(concat(' ', @class, ' '), ' toggle-display ')]";

            $this->execute('behat_general::i_click_on', array($xpath, "xpath_element"));
        }

                $this->execute('behat_general::click_link', get_string('logout'));
    }
}
