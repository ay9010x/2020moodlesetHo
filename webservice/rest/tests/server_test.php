<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/rest/locallib.php');


class webservice_rest_server_testcase extends advanced_testcase {

    
    public function xmlize_provider() {
        $data = [];
        $data[] = [null, null, ''];
        $data[] = [new external_value(PARAM_BOOL), false, "<VALUE>0</VALUE>\n"];
        $data[] = [new external_value(PARAM_BOOL), true, "<VALUE>1</VALUE>\n"];
        $data[] = [new external_value(PARAM_ALPHA), null, "<VALUE null=\"null\"/>\n"];
        $data[] = [new external_value(PARAM_ALPHA), 'a', "<VALUE>a</VALUE>\n"];
        $data[] = [new external_value(PARAM_INT), 123, "<VALUE>123</VALUE>\n"];
        $data[] = [
            new external_multiple_structure(new external_value(PARAM_INT)),
            [1, 2, 3],
            "<MULTIPLE>\n" .
            "<VALUE>1</VALUE>\n" .
            "<VALUE>2</VALUE>\n" .
            "<VALUE>3</VALUE>\n" .
            "</MULTIPLE>\n"
        ];
        $data[] = [             new external_multiple_structure(new external_value(PARAM_ALPHA)),
            ['A', null, 'C'],
            "<MULTIPLE>\n" .
            "<VALUE>A</VALUE>\n" .
            "<VALUE null=\"null\"/>\n" .
            "<VALUE>C</VALUE>\n" .
            "</MULTIPLE>\n"
        ];
        $data[] = [             new external_multiple_structure(new external_value(PARAM_ALPHA)),
            [],
            "<MULTIPLE>\n" .
            "</MULTIPLE>\n"
        ];
        $data[] = [
            new external_single_structure([
                'one' => new external_value(PARAM_INT),
                'two' => new external_value(PARAM_INT),
                'three' => new external_value(PARAM_INT),
            ]),
            ['one' => 1, 'two' => 2, 'three' => 3],
            "<SINGLE>\n" .
            "<KEY name=\"one\"><VALUE>1</VALUE>\n</KEY>\n" .
            "<KEY name=\"two\"><VALUE>2</VALUE>\n</KEY>\n" .
            "<KEY name=\"three\"><VALUE>3</VALUE>\n</KEY>\n" .
            "</SINGLE>\n"
        ];
        $data[] = [             new external_single_structure([
                'one' => new external_value(PARAM_INT),
                'two' => new external_value(PARAM_INT),
                'three' => new external_value(PARAM_INT),
            ]),
            ['one' => 1, 'two' => null, 'three' => 3],
            "<SINGLE>\n" .
            "<KEY name=\"one\"><VALUE>1</VALUE>\n</KEY>\n" .
            "<KEY name=\"two\"><VALUE null=\"null\"/>\n</KEY>\n" .
            "<KEY name=\"three\"><VALUE>3</VALUE>\n</KEY>\n" .
            "</SINGLE>\n"
        ];
        $data[] = [             new external_single_structure([
                'one' => new external_value(PARAM_INT),
                'two' => new external_value(PARAM_INT),
                'three' => new external_value(PARAM_INT),
            ]),
            ['two' => null, 'three' => 3],
            "<SINGLE>\n" .
            "<KEY name=\"one\"><VALUE null=\"null\"/>\n</KEY>\n" .
            "<KEY name=\"two\"><VALUE null=\"null\"/>\n</KEY>\n" .
            "<KEY name=\"three\"><VALUE>3</VALUE>\n</KEY>\n" .
            "</SINGLE>\n"
        ];
        $data[] = [             new external_single_structure([
                'one' => new external_multiple_structure(
                    new external_value(PARAM_INT)
                ),
                'two' => new external_multiple_structure(
                    new external_single_structure([
                        'firstname' => new external_value(PARAM_RAW),
                        'lastname' => new external_value(PARAM_RAW),
                    ])
                ),
                'three' => new external_single_structure([
                    'firstname' => new external_value(PARAM_RAW),
                    'lastname' => new external_value(PARAM_RAW),
                ]),
            ]),
            [
                'one' => [2, 3, 4],
                'two' => [
                    ['firstname' => 'Louis', 'lastname' => 'Armstrong'],
                    ['firstname' => 'Neil', 'lastname' => 'Armstrong'],
                ],
                'three' => ['firstname' => 'Neil', 'lastname' => 'Armstrong'],
            ],
            "<SINGLE>\n" .
            "<KEY name=\"one\"><MULTIPLE>\n".
                "<VALUE>2</VALUE>\n" .
                "<VALUE>3</VALUE>\n" .
                "<VALUE>4</VALUE>\n" .
            "</MULTIPLE>\n</KEY>\n" .
            "<KEY name=\"two\"><MULTIPLE>\n".
                "<SINGLE>\n" .
                    "<KEY name=\"firstname\"><VALUE>Louis</VALUE>\n</KEY>\n" .
                    "<KEY name=\"lastname\"><VALUE>Armstrong</VALUE>\n</KEY>\n" .
                "</SINGLE>\n" .
                "<SINGLE>\n" .
                    "<KEY name=\"firstname\"><VALUE>Neil</VALUE>\n</KEY>\n" .
                    "<KEY name=\"lastname\"><VALUE>Armstrong</VALUE>\n</KEY>\n" .
                "</SINGLE>\n" .
            "</MULTIPLE>\n</KEY>\n" .
            "<KEY name=\"three\"><SINGLE>\n" .
                "<KEY name=\"firstname\"><VALUE>Neil</VALUE>\n</KEY>\n" .
                "<KEY name=\"lastname\"><VALUE>Armstrong</VALUE>\n</KEY>\n" .
            "</SINGLE>\n</KEY>\n" .
            "</SINGLE>\n"
        ];
        $data[] = [             new external_single_structure([
                'one' => new external_multiple_structure(
                    new external_value(PARAM_INT)
                ),
                'two' => new external_multiple_structure(
                    new external_single_structure([
                        'firstname' => new external_value(PARAM_RAW),
                        'lastname' => new external_value(PARAM_RAW),
                    ])
                ),
                'three' => new external_single_structure([
                    'firstname' => new external_value(PARAM_RAW),
                    'lastname' => new external_value(PARAM_RAW),
                ]),
            ]),
            [
                'two' => [
                    ['firstname' => 'Louis'],
                    ['lastname' => 'Armstrong'],
                ],
                'three' => ['lastname' => 'Armstrong'],
            ],
            "<SINGLE>\n" .
            "<KEY name=\"one\"><MULTIPLE>\n</MULTIPLE>\n</KEY>\n" .
            "<KEY name=\"two\"><MULTIPLE>\n".
                "<SINGLE>\n" .
                    "<KEY name=\"firstname\"><VALUE>Louis</VALUE>\n</KEY>\n" .
                    "<KEY name=\"lastname\"><VALUE null=\"null\"/>\n</KEY>\n" .
                "</SINGLE>\n" .
                "<SINGLE>\n" .
                    "<KEY name=\"firstname\"><VALUE null=\"null\"/>\n</KEY>\n" .
                    "<KEY name=\"lastname\"><VALUE>Armstrong</VALUE>\n</KEY>\n" .
                "</SINGLE>\n" .
            "</MULTIPLE>\n</KEY>\n" .
            "<KEY name=\"three\"><SINGLE>\n" .
                "<KEY name=\"firstname\"><VALUE null=\"null\"/>\n</KEY>\n" .
                "<KEY name=\"lastname\"><VALUE>Armstrong</VALUE>\n</KEY>\n" .
            "</SINGLE>\n</KEY>\n" .
            "</SINGLE>\n"
        ];
        return $data;
    }

    
    public function test_xmlize($description, $value, $expected) {
        $method = new ReflectionMethod('webservice_rest_server', 'xmlize_result');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke(null, $value, $description));
    }

}
