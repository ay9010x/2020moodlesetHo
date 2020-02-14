<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

class behat_mod_forum extends behat_base {

    
    public function i_add_a_new_topic_to_forum_with($forumname, TableNode $table) {
        $this->add_new_discussion($forumname, $table, get_string('addanewtopic', 'forum'));
    }

    
    public function i_add_a_forum_discussion_to_forum_with($forumname, TableNode $table) {
        $this->add_new_discussion($forumname, $table, get_string('addanewdiscussion', 'forum'));
    }

    
    public function i_reply_post_from_forum_with($postsubject, $forumname, TableNode $table) {

                $this->execute('behat_general::click_link', $this->escape($forumname));
        $this->execute('behat_general::click_link', $this->escape($postsubject));
        $this->execute('behat_general::click_link', get_string('reply', 'forum'));

                $this->execute('behat_forms::i_set_the_following_fields_to_these_values', $table);

        $this->execute('behat_forms::press_button', get_string('posttoforum', 'forum'));
        $this->execute('behat_general::i_wait_to_be_redirected');
    }

    
    protected function add_new_discussion($forumname, TableNode $table, $buttonstr) {

                $this->execute('behat_general::click_link', $this->escape($forumname));
        $this->execute('behat_forms::press_button', $buttonstr);

                $this->execute('behat_forms::i_set_the_following_fields_to_these_values', $table);
        $this->execute('behat_forms::press_button', get_string('posttoforum', 'forum'));
        $this->execute('behat_general::i_wait_to_be_redirected');
    }

}
