<?php

if (!session_id()) session_start();

preg_match(
    '#wp-content/plugins/clickbank-niche-storefronts/product/index.php/([^/]*)/([\w\d]*)/([\w\d]*)/([\w\d]*)/([\w\d]*)/?#',
    $_SERVER['REQUEST_URI'], $matches);
$title = urldecode($matches[1]);
$memnumber = urldecode($matches[2]);
$mem = urldecode($matches[3]);
$tar = urldecode($matches[4]);
$niche = urldecode($matches[5]);

$link = 'http://cbproads.com/xmlfeed/wp/tracksf.asp'
    . '?memnumber='.$memnumber
    . '&mem='.$mem
    . '&tar='.$tar
    . (isset($_SESSION['cs_tid'])
        ? '&tid='.$_SESSION['cs_tid']
        : (isset($_COOKIE['cs_tid'])
            ? '&tid='.$_COOKIE['cs_tid']
            : ''))
    . '&niche='.$niche;
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($title) ?></title>
</head>
<body>
<script type="text/javascript">
window.location = "<?php echo $link ?>";
</script>
</body>
</html>
