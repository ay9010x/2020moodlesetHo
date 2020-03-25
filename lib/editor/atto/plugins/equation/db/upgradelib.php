<?php



defined('MOODLE_INTERNAL') || die();


function atto_equation_update_librarygroup4_setting() {
        $settingdefault = '
\sum{a,b}
\int_{a}^{b}{c}
\iint_{a}^{b}{c}
\iiint_{a}^{b}{c}
\oint{a}
(a)
[a]
\lbrace{a}\rbrace
\left| \begin{matrix} a_1 & a_2 \\ a_3 & a_4 \end{matrix} \right|
';
        $settingdefaultcmpr = trim(str_replace(array("\r", "\n"), '', $settingdefault));

        $currentsetting = get_config('atto_equation', 'librarygroup4');
    $currentsettingcmpr = trim(str_replace(array("\r", "\n"), '', $currentsetting));

    if ($settingdefaultcmpr === $currentsettingcmpr) {
                $newconfig = '
\sum{a,b}
\sqrt[a]{b+c}
\int_{a}^{b}{c}
\iint_{a}^{b}{c}
\iiint_{a}^{b}{c}
\oint{a}
(a)
[a]
\lbrace{a}\rbrace
\left| \begin{matrix} a_1 & a_2 \\ a_3 & a_4 \end{matrix} \right|
\frac{a}{b+c}
\vec{a}
\binom {a} {b}
{a \brack b}
{a \brace b}
';
        set_config('librarygroup4', $newconfig, 'atto_equation');
    }
}