<?php

// Tests of bin/catalogue's scan() (what a plugin's PHP may not do): php tests/catalogue/run.php

require __DIR__.'/../../bin/catalogue';

$cases = [
    // [code, what scan() should find]
    ['<?php exec("ls");', ['exec']],
    ['<?php \shell_exec("ls");', ['shell_exec']],
    ['<?php namespace Plugins\X; system("ls");', ['system']],
    ['<?php $a = `ls -la`;', ['backticks']],
    ['<?php $a = `ls`; $b = `pwd`;', ['backticks', 'backticks']],
    ['<?php eval($code);', ['eval']],
    ['<?php unserialize($data);', ['unserialize']],
    ['<?php eval(base64_decode("ZWNobyAx"));', ['eval', 'base64_decode']],
    ['<?php array_map("passthru", $commands);', ['passthru']],
    ["<?php call_user_func('\\\\proc_open', \$c);", ['proc_open']],
    ['<?php include $path;', ['include']],
    ['<?php require_once __DIR__."/$name.php";', ['require_once']],
    ['<?php file_put_contents("/tmp/x", $data);', ['file_put_contents']],
    ['<?php SYSTEM("ls");', ['system']],

    // allowed
    ['<?php require __DIR__.\'/helpers.php\';', []],
    ['<?php require_once(__DIR__ . "/a" . \'/b.php\');', []],
    ['<?php $client->exec("x"); Shell::system(); $a?->eval ?? 1;', []],
    ['<?php class A { public function exec() {} const system = 1; }', []],
    ['<?php // exec("ls"); eval($x);'."\n".'/* `ls` */', []],
    ['<?php $text = "run exec(ls) or `ls` here"; $t = <<<TXT'."\n".'Use `code` in markdown'."\n".'TXT;', []],
    ['<?php $x = "exec is fine in a sentence"; json_decode($data);', []],
    ['<?php use function Foo\exec; Foo\exec("x");', []],
];

$failed = 0;
foreach ($cases as $n => [$code, $expected]) {
    $found = array_column(scan($code), 1);
    if ($found !== $expected) {
        $failed++;
        echo "✗ case {$n}: {$code}\n  expected [".implode(', ', $expected).'], found ['.implode(', ', $found)."]\n";
    }
}

echo $failed === 0 ? '✓ '.count($cases)." scan cases\n" : "{$failed} of ".count($cases)." scan cases failed\n";
exit($failed === 0 ? 0 : 1);
