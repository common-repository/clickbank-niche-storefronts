<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Lang" content="en">
    <title>Server Info</title>
</head>

<body>
<?php
echo "Session: ";
// Set error reporting to display all errors and notices
error_reporting(E_ALL);
// Start a session
session_start();
// Check for the existence of a known session variable
if ($_SESSION['test_value']) {
    echo "found a session";
} else {
    echo "no session exists - writing to test_value";
    $_SESSION['test_value'] = true;
}
// Close and write session
session_write_close();
echo "<br />\n";
?>
</body>
</html>
