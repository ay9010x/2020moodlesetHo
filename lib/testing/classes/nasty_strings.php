<?php



defined('MOODLE_INTERNAL') || die;


class nasty_strings {

    
    protected static $strings = array(
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ @@TEST@@ \\\" \\ , ; : . 日本語­% %%',
        '&amp; \' \\" \ \'$@NULL@$ < > & &lt; &gt; @@TEST@@ \\\" \\ , ; : . 日本語­% %%',
        '< > & &lt; &gt; &amp; \' \\" \ \\\" \\ , ; : . \'$@NULL@$ @@TEST@@ 日本語­% %%',
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ 日本語­% %%@@TEST@@ \. \\" \\ , ; :',
        '< > & &lt; &gt; \\\" \\ , ; : . 日本語&amp; \' \\" \ \'$@NULL@$ @@TEST@@­% %%',
        '\' \\" \ \'$@NULL@$ @@TEST@@ < > & &lt; &gt; &amp; \\\" \\ , ; : . 日本語­% %%',
        '\\\" \\ , ; : . 日本語­% < > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ @@TEST@@ %%',
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ 日本語­% %% @@TEST@@ \\\" \\ . , ; :',
        '. 日本語&amp; \' \\" < > & &lt; &gt; \\ , ; : \ \'$@NULL@$ \\\" @@TEST@@­% %%',
        '&amp; \' \\" \ < > & &lt; &gt; \\\" \\ , ; : . 日本語\'$@NULL@$ @@TEST@@­% %%',
    );

    
    protected static $usedstrings = array();

    
    public static function get($key) {

                if (isset(self::$usedstrings[$key])) {
            return self::$strings[self::$usedstrings[$key]];
        }

                do {
            $index = self::random_index();
        } while (in_array($index, self::$usedstrings));

                self::$usedstrings[$key] = $index;

        return self::$strings[$index];
    }

    
    public static function reset_used_strings() {
        self::$usedstrings = array();
    }

    
    protected static function random_index() {
        return mt_rand(0, count(self::$strings) - 1);
    }

}
