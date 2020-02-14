<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');


class behat_mod_choice extends behat_base {

    
    public function I_choose_option_from_activity($option, $choiceactivity) {

        $this->execute("behat_general::click_link", $this->escape($choiceactivity));

        $this->execute('behat_forms::i_set_the_field_to', array( $this->escape($option), 1));

        $this->execute("behat_forms::press_button", get_string('savemychoice', 'choice'));
    }

    
    public function I_choose_options_from_activity($option, $choiceactivity) {
                $behatgeneral = behat_context_helper::get('behat_general');
        $behatforms = behat_context_helper::get('behat_forms');

                $behatgeneral->click_link($this->escape($choiceactivity));

                $this->wait_for_pending_js();

                $options = explode('","', trim($option, '"'));
        foreach ($options as $option) {
            $behatforms->i_set_the_field_to($this->escape($option), '1');
        }

                $behatforms->press_button(get_string('savemychoice', 'choice'));
    }

}
