<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;


class behat_transformations extends behat_base {

    
    public function arg_replace_slashes($string) {
        if (!is_scalar($string)) {
            return $string;
        }
        return str_replace('\"', '"', $string);
    }

    
    public function arg_replace_nasty_strings($argument) {
        if (!is_scalar($argument)) {
            return $argument;
        }
        return $this->replace_nasty_strings($argument);
    }

    
    public function prefixed_tablenode_transformations(TableNode $tablenode) {
        return $this->tablenode_transformations($tablenode);
    }

    
    public function tablenode_transformations(TableNode $tablenode) {
                $rows = $tablenode->getRows();
        foreach ($rows as $rowkey => $row) {
            foreach ($row as $colkey => $value) {

                                if (preg_match('/\$NASTYSTRING(\d)/', $rows[$rowkey][$colkey])) {
                    $rows[$rowkey][$colkey] = $this->replace_nasty_strings($rows[$rowkey][$colkey]);
                }
            }
        }

                unset($tablenode);
        $tablenode = new TableNode($rows);

        return $tablenode;
    }

    
    public function replace_nasty_strings($string) {
        return preg_replace_callback(
            '/\$NASTYSTRING(\d)/',
            function ($matches) {
                return nasty_strings::get($matches[0]);
            },
            $string
        );
    }

}
