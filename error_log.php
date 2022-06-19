<?php
ini_set('log_errors', E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('error_log', '.error_log');
$f = fopen('.error_log', 'a+');
fputs($f, "-------------------------\n");
fclose($f);
