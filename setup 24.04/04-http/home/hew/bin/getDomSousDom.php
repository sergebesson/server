#!/usr/bin/php
<?php

$tab = explode('.', $argv[1]);

$Dom = array_pop($tab);
$Dom = array_pop($tab). '.'. $Dom;

$SDom = implode('.', $tab);

print $Dom. $argv[2]. $SDom;

?>
