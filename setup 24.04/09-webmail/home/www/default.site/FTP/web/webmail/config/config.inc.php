<?php

$config = array();

$config['db_dsnw'] = 'sqlite:///'. $_SERVER['HEW_SITE_ROOT']. '/hors-site/roundcubemail.db?mode=0640';
$config['login_autocomplete'] = 2;
$config['max_recipients'] = 10;
$config['product_name'] = 'Home Easy Web - Webmail';
$config['http_received_header'] = true;
$config['create_default_folders'] = true;
$config['htmleditor'] = 1;
$config['smtp_port'] = 25;
$config['smtp_user'] = '';
