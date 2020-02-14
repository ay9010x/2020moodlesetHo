<?php



defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_equation', new lang_string('pluginname', 'atto_equation')));

$settings = new admin_settingpage('atto_equation_settings', new lang_string('settings', 'atto_equation'));
if ($ADMIN->fulltree) {
        $name = new lang_string('librarygroup1', 'atto_equation');
    $desc = new lang_string('librarygroup1_desc', 'atto_equation');
    $default = '
\cdot
\times
\ast
\div
\diamond
\pm
\mp
\oplus
\ominus
\otimes
\oslash
\odot
\circ
\bullet
\asymp
\equiv
\subseteq
\supseteq
\leq
\geq
\preceq
\succeq
\sim
\simeq
\approx
\subset
\supset
\ll
\gg
\prec
\succ
\infty
\in
\ni
\forall
\exists
\neq
';
    $setting = new admin_setting_configtextarea('atto_equation/librarygroup1',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

        $name = new lang_string('librarygroup2', 'atto_equation');
    $desc = new lang_string('librarygroup2_desc', 'atto_equation');
    $default = '
\leftarrow
\rightarrow
\uparrow
\downarrow
\leftrightarrow
\nearrow
\searrow
\swarrow
\nwarrow
\Leftarrow
\Rightarrow
\Uparrow
\Downarrow
\Leftrightarrow
';
    $setting = new admin_setting_configtextarea('atto_equation/librarygroup2',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

        $name = new lang_string('librarygroup3', 'atto_equation');
    $desc = new lang_string('librarygroup3_desc', 'atto_equation');
    $default = '
\alpha
\beta
\gamma
\delta
\epsilon
\zeta
\eta
\theta
\iota
\kappa
\lambda
\mu
\nu
\xi
\pi
\rho
\sigma
\tau
\upsilon
\phi
\chi
\psi
\omega
\Gamma
\Delta
\Theta
\Lambda
\Xi
\Pi
\Sigma
\Upsilon
\Phi
\Psi
\Omega
';
    $setting = new admin_setting_configtextarea('atto_equation/librarygroup3',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

        $name = new lang_string('librarygroup4', 'atto_equation');
    $desc = new lang_string('librarygroup4_desc', 'atto_equation');
    $default = '
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
    $setting = new admin_setting_configtextarea('atto_equation/librarygroup4',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

}
