<?php

require_once 'functions.inc.php';


if (!session_id()) session_start();

echo cns_show($_GET['user_id'], $_GET['niche'], $_GET['page']);

?>