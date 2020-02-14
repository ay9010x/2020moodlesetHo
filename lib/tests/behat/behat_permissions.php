<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Gherkin\Node\TableNode as TableNode;


class behat_permissions extends behat_base {

    
    public function i_set_the_following_system_permissions_of_role($rolename, $table) {

        $parentnodes = get_string('administrationsite') . ' > ' .
            get_string('users', 'admin') . ' > ' .
            get_string('permissions', 'role');

                $this->execute("behat_general::i_am_on_homepage");

                $this->execute("behat_navigation::i_navigate_to_node_in",
            array(get_string('defineroles', 'role'), $parentnodes)
        );

        $this->execute("behat_general::click_link", "Edit " . $this->escape($rolename) . " role");
        $this->execute("behat_permissions::i_fill_the_capabilities_form_with_the_following_permissions", $table);

        $this->execute('behat_forms::press_button', get_string('savechanges'));
    }

    
    public function i_override_the_system_permissions_of_role_with($rolename, $table) {

                $roleoption = $this->find('xpath', '//select[@name="roleid"]/option[contains(.,"' . $this->escape($rolename) . '")]');

        $this->execute('behat_forms::i_set_the_field_to',
            array(get_string('advancedoverride', 'role'), $this->escape($roleoption->getText()))
        );

        if (!$this->running_javascript()) {
            $this->execute("behat_forms::press_button", get_string('go'));
        }

        $this->execute("behat_permissions::i_fill_the_capabilities_form_with_the_following_permissions", $table);

        $this->execute('behat_forms::press_button', get_string('savechanges'));
    }

    
    public function i_fill_the_capabilities_form_with_the_following_permissions($table) {

                        try {
            $advancedtoggle = $this->find_button(get_string('showadvanced', 'form'));
            if ($advancedtoggle) {
                $advancedtoggle->click();

                                $this->getSession()->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);
            }
        } catch (Exception $e) {
                    }

                foreach ($table->getRows() as $key => $row) {

            if (count($row) !== 2) {
                throw new ExpectationException('You should specify a table with capability/permission columns', $this->getSession());
            }

            list($capability, $permission) = $row;

                        if (strtolower($capability) == 'capability' || strtolower($capability) == 'capabilities') {
                continue;
            }

                        $permissionconstant = 'CAP_'. strtoupper($permission);
            if (!defined($permissionconstant)) {
                throw new ExpectationException(
                    'The provided permission value "' . $permission . '" is not valid. Use Inherit, Allow, Prevent or Prohibited',
                    $this->getSession()
                );
            }

                        $permissionvalue = constant($permissionconstant);

                        $radio = $this->find('xpath', '//input[@name="' . $capability . '" and @value="' . $permissionvalue . '"]');
            $field = behat_field_manager::get_field_instance('radio', $radio, $this->getSession());
            $field->set_value(1);
        }
    }

    
    public function capability_has_permission($capabilityname, $permission) {

                $radioxpath = "//table[@class='rolecap']/descendant::input[@type='radio']" .
            "[@name='" . $capabilityname . "'][@checked]";

        $checkedradio = $this->find('xpath', $radioxpath);

        switch ($permission) {
            case get_string('notset', 'role'):
                $perm = CAP_INHERIT;
                break;
            case get_string('allow', 'role'):
                $perm = CAP_ALLOW;
                break;
            case get_string('prevent', 'role'):
                $perm = CAP_PREVENT;
                break;
            case get_string('prohibit', 'role'):
                $perm = CAP_PROHIBIT;
                break;
            default:
                throw new ExpectationException('"' . $permission . '" permission does not exist', $this->getSession());
                break;
        }

        if ($checkedradio->getAttribute('value') != $perm) {
            throw new ExpectationException('"' . $capabilityname . '" permission is not "' . $permission . '"', $this->getSession());
        }
    }

    
    public function i_define_the_allowed_role_assignments_for_a_role_as($rolename, $table) {
        $parentnodes = get_string('administrationsite') . ' > ' .
            get_string('users', 'admin') . ' > ' .
            get_string('permissions', 'role');

                $this->execute("behat_general::i_am_on_homepage");

                $this->execute("behat_navigation::i_navigate_to_node_in",
            array(get_string('defineroles', 'role'), $parentnodes)
        );

        $this->execute("behat_general::click_link", "Allow role assignments");
        $this->execute("behat_permissions::i_fill_in_the_allowed_role_assignments_form_for_a_role_with",
            array($rolename, $table)
        );

        $this->execute('behat_forms::press_button', get_string('savechanges'));
    }

    
    public function i_fill_in_the_allowed_role_assignments_form_for_a_role_with($sourcerole, $table) {
        foreach ($table->getRows() as $key => $row) {
            list($targetrole, $allowed) = $row;

            $node = $this->find('xpath', '//input[@title="Allow users with role ' .
                $sourcerole .
                ' to assign the role ' .
                $targetrole . '"]');

            if ($allowed == 'Assignable') {
                if (!$node->isChecked()) {
                    $node->click();
                }
            } else if ($allowed == 'Not assignable') {
                if ($node->isChecked()) {
                    $node->click();
                }
            } else {
                throw new ExpectationException(
                    'The provided permission value "' . $allowed . '" is not valid. Use Assignable, or Not assignable',
                    $this->getSession()
                );
            }
        }
    }
}
