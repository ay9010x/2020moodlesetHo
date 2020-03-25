<?php



defined('MOODLE_INTERNAL') || die();


class behat_selectors {

    
    protected static $allowedtextselectors = array(
        'activity' => 'activity',
        'block' => 'block',
        'css_element' => 'css_element',
        'dialogue' => 'dialogue',
        'fieldset' => 'fieldset',
        'list_item' => 'list_item',
        'question' => 'question',
        'region' => 'region',
        'section' => 'section',
        'table' => 'table',
        'table_row' => 'table_row',
        'xpath_element' => 'xpath_element',
    );

    
    protected static $allowedselectors = array(
        'activity' => 'activity',
        'block' => 'block',
        'button' => 'button',
        'checkbox' => 'checkbox',
        'css_element' => 'css_element',
        'dialogue' => 'dialogue',
        'field' => 'field',
        'fieldset' => 'fieldset',
        'file' => 'file',
        'filemanager' => 'filemanager',
        'link' => 'link',
        'link_or_button' => 'link_or_button',
        'list_item' => 'list_item',
        'optgroup' => 'optgroup',
        'option' => 'option',
        'question' => 'question',
        'radio' => 'radio',
        'region' => 'region',
        'section' => 'section',
        'select' => 'select',
        'table' => 'table',
        'table_row' => 'table_row',
        'text' => 'text',
        'xpath_element' => 'xpath_element'
    );

    
    protected static $moodleselectors = array(
        'activity' => <<<XPATH
.//li[contains(concat(' ', normalize-space(@class), ' '), ' activity ')][normalize-space(.) = %locator% ]
XPATH
        , 'block' => <<<XPATH
.//div[contains(concat(' ', normalize-space(@class), ' '), ' block ') and
    (contains(concat(' ', normalize-space(@class), ' '), concat(' ', %locator%, ' ')) or
     descendant::h2[normalize-space(.) = %locator%] or
     @aria-label = %locator%)]
XPATH
        , 'dialogue' => <<<XPATH
.//div[contains(concat(' ', normalize-space(@class), ' '), ' moodle-dialogue ') and
    normalize-space(descendant::div[
        contains(concat(' ', normalize-space(@class), ' '), ' moodle-dialogue-hd ')
        ]) = %locator%] |
.//div[contains(concat(' ', normalize-space(@class), ' '), ' yui-dialog ') and
    normalize-space(descendant::div[@class='hd']) = %locator%]
XPATH
        , 'filemanager' => <<<XPATH
.//div[contains(concat(' ', normalize-space(@class), ' '), ' ffilemanager ')]
    /descendant::input[@id = //label[contains(normalize-space(string(.)), %locator%)]/@for]
XPATH
        , 'list_item' => <<<XPATH
.//li[contains(normalize-space(.), %locator%) and not(.//li[contains(normalize-space(.), %locator%)])]
XPATH
        , 'question' => <<<XPATH
.//div[contains(concat(' ', normalize-space(@class), ' '), ' que ')]
    [contains(div[@class='content']/div[contains(concat(' ', normalize-space(@class), ' '), ' formulation ')], %locator%)]
XPATH
        , 'region' => <<<XPATH
.//*[self::div | self::section | self::aside | self::header | self::footer][./@id = %locator%]
XPATH
        , 'section' => <<<XPATH
.//li[contains(concat(' ', normalize-space(@class), ' '), ' section ')][./descendant::*[self::h3]
    [normalize-space(.) = %locator%][contains(concat(' ', normalize-space(@class), ' '), ' sectionname ') or
    contains(concat(' ', normalize-space(@class), ' '), ' section-title ')]] |
.//div[contains(concat(' ', normalize-space(@class), ' '), ' sitetopic ')]
    [./descendant::*[self::h2][normalize-space(.) = %locator%] or %locator% = 'frontpage']
XPATH
        , 'table' => <<<XPATH
.//table[(./@id = %locator% or contains(.//caption, %locator%) or contains(.//th, %locator%) or contains(concat(' ', normalize-space(@class), ' '), %locator% ))]
XPATH
        , 'table_row' => <<<XPATH
.//tr[contains(normalize-space(.), %locator%) and not(.//tr[contains(normalize-space(.), %locator%)])]
XPATH
        , 'text' => <<<XPATH
.//*[contains(., %locator%) and not(.//*[contains(., %locator%)])]
XPATH
    );

    
    public static function get_behat_selector($selectortype, $element, Behat\Mink\Session $session) {

                if ($selectortype == 'css_element' || $selectortype == 'xpath_element') {
            $selector = str_replace('_element', '', $selectortype);
            $locator = $element;
        } else {
                        $locator = array($selectortype, behat_context_helper::escape($element));
            $selector = 'named_partial';
        }

        return array($selector, $locator);
    }

    
    public static function register_moodle_selectors(Behat\Mink\Session $session) {

        foreach (self::get_moodle_selectors() as $name => $xpath) {
            $session->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath($name, $xpath);
        }
    }

    
    public static function get_allowed_selectors() {
        return self::$allowedselectors;
    }

    
    public static function get_allowed_text_selectors() {
        return self::$allowedtextselectors;
    }

    
    protected static function get_moodle_selectors() {
        return self::$moodleselectors;
    }
}
