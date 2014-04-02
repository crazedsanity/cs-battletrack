<?php

define('CRYPT_SALT', 'csbt');

$uri = preg_replace('/\/$/', '', $_SERVER['REQUEST_URI']);
$page->add_template_var('PAGE_URI', $uri);
#$page->add_template_var("main", "


$playerUid = $_SESSION['auth']['userInfo']['uid'];


$_version = new csbt_data(null,null,null,null);
$page->add_template_var('CSBT_project_name', $_version->version->get_project());
$page->add_template_var('CSBT_version', $_version->version->get_version());