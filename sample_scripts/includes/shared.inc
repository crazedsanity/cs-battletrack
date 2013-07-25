<?php

define('CRYPT_SALT', 'csbt');

$uri = preg_replace('/\/$/', '', $_SERVER['REQUEST_URI']);
$page->add_template_var('PAGE_URI', $uri);
#$page->add_template_var("main", "
