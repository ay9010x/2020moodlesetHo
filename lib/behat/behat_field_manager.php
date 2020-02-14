<?php




use Behat\Mink\Session as Session,
    Behat\Mink\Element\NodeElement as NodeElement,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\MinkExtension\Context\RawMinkContext as RawMinkContext;


class behat_field_manager {

    
    public static function get_form_field_from_label($label, RawMinkContext $context) {

                        try {
                        $fieldnode = $context->find_field($label);
        } catch (ElementNotFoundException $fieldexception) {

                        try {
                $fieldnode = $context->find_filemanager($label);
            } catch (ElementNotFoundException $filemanagerexception) {
                                throw $fieldexception;
            }
        }

                return self::get_form_field($fieldnode, $context->getSession());
    }

    
    public static function get_form_field(NodeElement $fieldnode, Session $session) {

                if (self::is_moodleform_field($fieldnode)) {
                        try {
                $type = self::get_field_node_type($fieldnode, $session);
            } catch (WebDriver\Exception\InvalidSelector $e) {
                $type = 'field';
            }
        }

                if (empty($type)) {
            $type = 'field';
        }

        return self::get_field_instance($type, $fieldnode, $session);
    }

    
    public static function get_field_instance($type, NodeElement $fieldnode, Session $session) {

        global $CFG;

                        if ($type == 'field' &&
                $guessedtype = self::guess_field_type($fieldnode, $session)) {
            $type = $guessedtype;
        }

        $classname = 'behat_form_' . $type;

                $classpath = $CFG->libdir . '/behat/form_field/' . $classname . '.php';
        if (!file_exists($classpath)) {
            $classname = 'behat_form_field';
            $classpath = $CFG->libdir . '/behat/form_field/' . $classname . '.php';
        }

                require_once($classpath);
        return new $classname($session, $fieldnode);
    }

    
    public static function guess_field_type(NodeElement $fieldnode, Session $session) {

                $tagname = strtolower($fieldnode->getTagName());
        if ($tagname == 'textarea') {

                        $xpath = '//div[@id="' . $fieldnode->getAttribute('id') . 'editable"]';
            if ($session->getPage()->find('xpath', $xpath)) {
                return 'editor';
            }
            return 'textarea';

        } else if ($tagname == 'input') {
            $type = $fieldnode->getAttribute('type');
            switch ($type) {
                case 'text':
                case 'password':
                case 'email':
                case 'file':
                    return 'text';
                case 'checkbox':
                    return 'checkbox';
                    break;
                case 'radio':
                    return 'radio';
                    break;
                default:
                                                            return false;
            }

        } else if ($tagname == 'select') {
                        return 'select';
        }

                return false;
    }

    
    protected static function is_moodleform_field(NodeElement $fieldnode) {

                $parentformfound = $fieldnode->find('xpath',
            "/ancestor::fieldset" .
            "/ancestor::form[contains(concat(' ', normalize-space(@class), ' '), ' mform ')]"
        );

        return ($parentformfound != false);
    }

    
    protected static function get_field_node_type(NodeElement $fieldnode, Session $session) {

                if ($fieldnode->getAttribute('name') === 'availabilityconditionsjson') {
            return 'availability';
        }

                if ($class = $fieldnode->getParent()->getAttribute('class')) {

            if (strstr($class, 'felement') != false) {
                                return substr($class, 10);
            }

                        if (strstr($class, 'fcontainer') != false) {
                return false;
            }
        }

        return self::get_field_node_type($fieldnode->getParent(), $session);
    }

    
    public static function get_field(NodeElement $fieldnode, $locator, Session $session) {
        debugging('Function behat_field_manager::get_field() is deprecated, ' .
            'please use function behat_field_manager::get_form_field() instead', DEBUG_DEVELOPER);

        return self::get_form_field($fieldnode, $session);
    }

    
    protected static function get_node_type(NodeElement $fieldnode, $locator, Session $session) {
        debugging('Function behat_field_manager::get_node_type() is deprecated, ' .
            'please use function behat_field_manager::get_field_node_type() instead', DEBUG_DEVELOPER);

        return self::get_field_node_type($fieldnode, $session);
    }

}
