<?php

/**
* Get site's domain name from Wordpress Address setting
* 
*/
function cns_get_site_domain()
{
    static $domain = null;
    
    if ($domain === null) {
        $domain = substr(site_url('', 'http'), 7);
        if (false !== ($tmp = strpos($domain, '/'))) {
            $domain = substr($domain, 0, $tmp);
        }
    }
    
    return $domain;
}

/**
* Add plugin's options into DB and reset their values (called on plugin activation)
*/
function cns_activate()
{
    global $wpdb, $cns_options, $cns_ad_options;
    
    foreach ($cns_options as $oname => $o) {
        update_option($oname, $wpdb->escape($o['default']));
    }
    // Advanced
    foreach ($cns_ad_options as $oname => $o) {
        update_option($oname, $wpdb->escape($o['default']));
    }
}

function cns_deactivate() {}

/**
* Add plugin's settings page to the WP admin panel menu
*/
function cns_add_to_menu()
{
    add_options_page(
        'Clickbank Niche Storefronts',
        'Clickbank Niche Storefronts',
        'manage_options',
        'cns_menu',
        'cns_option');
}

/**
* Echo plugin's settings page HTML code
*/
function cns_option()
{
    global $wpdb, $cns_options, $cns_ad_options;
    
    // Selects
    $cns_options['cns_show_storefront_after_posts']['cur_val']
        = $_SESSION['cns_show_storefront_after_posts'];
    $cns_ad_options['cns_title_tag']['cur_val'] = $_SESSION['cns_title_tag'];
    $cns_ad_options['cns_subtitle_tag']['cur_val'] = $_SESSION['cns_subtitle_tag'];
    
    echo "<h2>Clickbank Niche Storefronts</h2>\n";
    
    if (@$_POST['cns_submit']) {
        $opts = array('cns_options', 'cns_ad_options');
        foreach ($opts as $options_changed) {
            foreach ($$options_changed as $oname => $o) {
                if (isset($_POST[$oname])) {
                    $oval = trim($_POST[$oname]);
                    
                    switch ($o['type']) {
                        case 'checkbox':
                            if ($oval != '1') $oval = '0';
                            break;
                        case 'int':
                            if ($oval !== '') {
                                if (!ctype_digit("$oval")) {
                                    $oval = get_option($oname);
                                }
                            }
                            break;
                    }
                    
                    if ($o['req'] && $oval === '') {
                        $oval = get_option($oname);
                    }
                    
                    update_option($oname, $wpdb->escape($oval));
                }
            }
        }
    }
    ?>
    
    <form name="cns_form" method="post" action="">
        <table class="form-table">
            <?php foreach ($cns_options as $oname => $o): ?>
                <?php if ($oname !== 'cns_list_title'): ?>
                    <?php if ($o['type'] === 'checkbox'): ?>
                        <tr valign="top">
                            <th scope="row"><?php echo $o['label'] ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php echo $o['label'] ?></span>
                                    </legend>
                                    <label for="<?php echo $oname ?>">
                                        <input name="<?php echo $oname ?>" type="checkbox"
                                        id="<?php echo $oname ?>" value="1"
                                        <?php if(get_option($oname)=='1'): ?>
                                            checked="checked"
                                        <?php endif; ?> />
                                        Show
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    <?php elseif ($o['type'] === 'select'): $oval = get_option($oname); ?>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo $oname ?>"><?php echo $o['label'] ?></label>
                            </th>
                            <td>
                                <select name="<?php echo $oname ?>" id="<?php echo $oname ?>">
                                    <?php foreach ($o['vals'] as $k => $v): ?>
                                        <option
                                        <?php
                                        if (gettype($k) === 'integer'
                                            && $oval == $v || gettype($k) !== 'integer'
                                            && $oval == $k):
                                        ?>
                                            selected='selected'
                                        <?php endif ?>
                                        value='<?php
                                            echo htmlspecialchars(gettype($k) === 'integer' ? $v : $k)
                                        ?>'><?php echo $v ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?php if ($oname == 'cns_show_storefront_after_posts'): ?>
                                    <p class="description">
                                        This option is not guaranteed to work with all themes.<br />
                                        As an alternative option, you can place this plugin on the
                                        home page through the below settings:<br />
                                        1. Please create a page first by putting the
                                        <b>[clickbank-niche-storefront]</b> into the page.<br />
                                        2. Go to Settings -&gt; Reading -&gt; Front page displays
                                        -&gt; A static page (for "Front page" select the page
                                        been created in step 1)
                                        </ol>
                                    </p>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php echo $cns_options['cns_list_title']['label'] ?></span>
                                        </legend>
                                        <label for="cns_list_title">
                                            <?php echo $cns_options['cns_list_title']['label'] ?>
                                        </label>
                                        <input style="width: 20em;" type="text"
                                        value="<?php echo get_option('cns_list_title') ?>"
                                        name="cns_list_title" id="cns_list_title" class="regular-text" />
                                    </fieldset>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php else: /* int */ ?>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo $oname ?>"><?php echo $o['label'] ?></label>
                            </th>
                            <td>
                                <input style="width: 35em;" type="text"
                                value="<?php echo get_option($oname) ?>" name="<?php echo $oname ?>"
                                id="<?php echo $oname ?>" class="regular-text" />
                                <?php if ($oname == 'cns_user_id'): ?>
                                    <p class="description">
                                        If you don't have one, get one at
                                        <a href="http://www.cbproads.com/" target="_blank">CBproAds.com</a>
                                    </p>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endif ?>
                <?php endif ?>
            <?php endforeach ?>
        </table>
        
        <h2>Advanced options</h2>
        Attention! Please remember the default values of these options
        before changing them to have ability to roll back your changes.
        <table class="form-table">
            <?php foreach ($cns_ad_options as $oname => $o): ?>
                <?php if ($o['type'] === 'select'): $oval = get_option($oname); ?>
                    <tr valign="top">
                        <th scope="row">
                            <label for="<?php echo $oname ?>"><?php echo $o['label'] ?></label>
                        </th>
                        <td>
                            <select name="<?php echo $oname ?>" id="<?php echo $oname ?>">
                                <?php foreach ($o['vals'] as $k => $v): ?>
                                    <option
                                    <?php if (
                                        gettype($k) === 'integer' && $oval == $v
                                        || gettype($k) !== 'integer' && $oval == $k):
                                    ?>
                                        selected='selected'
                                    <?php endif ?>
                                    value='<?php
                                        echo htmlspecialchars(gettype($k) === 'integer' ? $v : $k)
                                    ?>'><?php echo $v ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                    </tr>
                <?php else: /* text */ ?>
                    <tr valign="top">
                        <th scope="row">
                            <label for="<?php echo $oname ?>"><?php echo $o['label'] ?></label>
                        </th>
                        <td>
                            <input style="width: 35em;" type="text"
                            value="<?php echo get_option($oname) ?>" name="<?php echo $oname ?>"
                            id="<?php echo $oname ?>" class="regular-text" />
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
        </table>
        
        <p class="submit">
            <input type="submit" name="cns_submit" id="cns_submit" class="button-primary"
            value="Update" />
        </p>
    </form>
    
    <h2>Plugin usage</h2>
    Insert shortcode <strong>[clickbank-niche-storefront]</strong> into your post / page / etc.
    <?php
}

/**
 * Display JavaScript on the WP options page
 */
function cns_option_js()
{
?>
<script type="text/javascript">
//<![CDATA[
    jQuery(document).ready(function($) {
        var cns_select = $('#cns_show_storefront_after_posts'),
            cns_show_storefront_after_posts_change = function() {
                $('#cns_list_title').prop('disabled', select.val() == 'no');
            };
        cns_show_storefront_after_posts_change();
        cns_select.change(cns_show_storefront_after_posts_change);
    });
//]]>
</script>
<?php
}

/**
* Show products on the home page of the site
* 
* @param array $posts
* @return array
*/
function cns_show_products($posts)
{
    if (!is_admin() && !is_search() && !is_single() && !is_page()) {
        $post = new stdClass;
        //$post->ID = '31';
        $post->post_author = '1';
        //$post->post_date = '2012-08-25 20:27:29';
        //$post->post_date_gmt = '2012-08-25 20:27:29';
        $post->post_content = cns_show_filter();
        $post->post_title = $_SESSION['cns_list_title'];
        $post->post_excerpt = '';
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'open';
        $post->post_password = '';
        $post->post_name = 'clickbabk-niche-storefront';
        $post->to_ping = '';
        $post->pinged = '';
        //$post->post_modified = '2012-08-25 20:34:07';
        //$post->post_modified_gmt = '2012-08-25 20:34:07';
        $post->post_content_filtered = '';
        $post->post_parent = '0';
        //$post->guid = 'http://localhost/xml_to_html/?p=31';
        $post->menu_order = '0';
        $post->post_type = 'post';
        $post->post_mime_type = '';
        $post->comment_count = '0';
        
        if ($_SESSION['cns_show_storefront_after_posts'] == 'before') {
            $new_key = min(array_keys($posts)) - 1;
            $posts = array_merge(array($new_key => $post), $posts);
        } else {
            $new_key = max(array_keys($posts)) + 1;
            $posts[$new_key] = $post;
        }
    }
    
    return $posts;
}

/**
* Remove <![CDATA[ and ]]> tags if exist
* 
* @param string $data
* @return string
*/
function  cns_cdata($data)
{
    if (substr($data, 0, 9) === '<![CDATA[' && substr($data, -3) === ']]>') {
        $data = substr($data, 9, -3);
    }
    
    return $data;
}

/**
* Return formatted title
* 
* @param string $title
* @param bool $include_css_js
* @return string
*/
function cns_product_title_fmt($title, $include_css_js = false)
{
    return (!$include_css_js
            ? "<$_SESSION[cns_title_tag]"
            . ($_SESSION['cns_title_style'] != ''
                ? " style=\""
                . /*($_SESSION['cns_title_tag'] == 'strong' ?*/ "display: inline-block; " /*: '')*/
                . "$_SESSION[cns_title_style]\""
                : '')
            . ">"
            : '')
        . $title
        . (!$include_css_js
            ? "</$_SESSION[cns_title_tag]>"
            #. ($_SESSION['cns_title_tag'] == 'strong' ? "<br />" : '')
            : '');
}

/**
* Get products from the XML feed
* 
* @return array
*/
function cns_get_items($user_id, $items_per_page, $page = 1, $include_css_js = false)
{
    $url = 'http://clickbankproads.com/xmlfeed/wp/cb.asp'
        . '?start='.(($page-1) * $items_per_page)
        . '&end='.$items_per_page
        . '&niche='.$_SESSION['cns_niche']
        . '&id='.$user_id;
    
    $doc = new DOMDocument();
    if (false === @$doc->load($url)) {
		return array(
            'posts' => array(
                array(
                    'post_title' => cns_product_title_fmt('Sorry', $include_css_js),
                    'post_content' => 'Could load the products from the XML feed. There could be three reasons:<br /><br >1) No products in this list yet.<br />2) You have entered a wrong Account ID or 					Niche  in the settings page of the plugin. <br />3) Your hosting people have not enabled the PHP setting <b>allow_url_fopen</b> to ON.<br />'
                        )),
            'totalp' => 1);
    }
    $items = $doc->getElementsByTagName("item");
    if (0 === $items->length) {
        return array(
            'posts' => array(
                array(
                    'post_title' => cns_product_title_fmt('Sorry', $include_css_js),
                    'post_content' => 'No products in this list yet.<br />'
                        . 'May be the Account ID or Niche is wrong.')),
            'totalp' => 1);
    }
    
    $totalp = cns_cdata(
        $items
        ->item(0)->getElementsByTagName("totalp")
        ->item(0)->nodeValue);
    $_SESSION['cns_totalp'] = $totalp;
    
    $count = 0;
    $item_list = array();
    $first = true;
    foreach ($items as $item) {
        // Title
        $paths = $item->getElementsByTagName("title");
        $title = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
        
        // URL
        $paths = $item->getElementsByTagName("affiliate");
        $mem = cns_cdata($paths->item(0)->nodeValue);
        $paths = $item->getElementsByTagName("ids");
        $tar = cns_cdata($paths->item(0)->nodeValue);
        $paths = $item->getElementsByTagName("niche");
        $niche = cns_cdata($paths->item(0)->nodeValue);
        /*$link = htmlspecialchars($_SESSION['cns_plugin_url'].'/product/index.php/'
            . rawurlencode(str_replace(array('?', '/'), array('.', '.'), $title)).'/'
            . $user_id.'/'
            . $mem.'/'
            . $tar.'/'
            . $niche.'/');*/
        $link = htmlspecialchars($_SESSION['cns_plugin_url'].'/product.php'
            . '?memnumber='.$user_id
            . '&mem='.$mem
            . '&tar='.$tar
            . '&niche='.$niche);
        
        // Descriptions
        $paths = $item->getElementsByTagName("description");
        $description = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
        $paths = $item->getElementsByTagName("mdescr");
        $mdescr = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
        
        // Images
        $paths = $item->getElementsByTagName("images");
        $imageFilename = cns_cdata($paths->item(0)->nodeValue);
        if ($imageFilename != '' && $imageFilename != 'no') {
            $image = 'http://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners/'
                . $imageFilename.'&resize='.$_SESSION['cns_image_size'];
            $image = htmlspecialchars($image);
            $imageFull = 'http://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners/'.$imageFilename.'&resize=default';
            $imageFull = htmlspecialchars($imageFull);
        } else {
            unset($image, $imageFull);
        }
        $paths = $item->getElementsByTagName("altimage");
        $altimageFilename = cns_cdata($paths->item(0)->nodeValue);
        if ($altimageFilename != '' && $altimageFilename != 'no') {
            $altimage = 'http://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners/alter/'
                . $altimageFilename.'&resize='.$_SESSION['cns_image_size'];
            $altimage = htmlspecialchars($altimage);
            $altimageFull = 'http://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners/alter/'
                . $altimageFilename.'&resize=default';
            $altimageFull = htmlspecialchars($altimageFull);
        } else {
            unset($altimage, $altimageFull);
        }
        
        // Price
        $paths = $item->getElementsByTagName("price");
        $price = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
        
        // Add record
        $item_list[] = array(
            'target_url' => $link,
            'post_title' => cns_product_title_fmt($title, $include_css_js),
            'post_content'
                => "<div>\n"
                . "<$_SESSION[cns_subtitle_tag]"
                . ($_SESSION['cns_subtitle_style'] != ''
                    ? " style=\""
                    . ($_SESSION['cns_subtitle_tag'] == 'strong' ? "display: inline-block; " : '')
                    . "$_SESSION[cns_subtitle_style]\""
                    : '')
                . ">$description</$_SESSION[cns_subtitle_tag]>"
                . ($_SESSION['cns_subtitle_tag'] == 'strong' ? "<br />" : '')
                . "\n"
                . (isset($altimage) || isset($image)
                    ? "<span class=\"cns_image_holder\" style=\"float: left\">"
                    . (isset($altimage)
                        ? "<a class=\"cns_image_$count cns_preview\" "
                        . "title=\"".htmlspecialchars($title)."\" "
                        . "href=\"#\" src=\"$altimageFull\" "
                        . "index=\"cns_image_$count\" rel=\"nofollow\" "
                        . "onclick=\"window.open('$link'); return false\">"
                        . "<img alt=\"\" src=\"$altimage\" "
                        . "style=\"margin-right: 10px\" /></a>\n"
                        : (isset($image)
                            ? "<a class=\"cns_image_$count cns_preview\" "
                            . "title=\"".htmlspecialchars($title)."\" "
                            . "href=\"#\" src=\"$imageFull\" "
                            . "index=\"cns_image_$count\" rel=\"nofollow\" "
                            . "onclick=\"window.open('$link'); return false\">"
                            . "<img alt=\"cns_image_$count\" src=\"$image\" "
                            . "style=\"margin-right: 10px\" /></a>"
                            : '')
                        )
                    . "</span>"
                    : '')
            . "<div align=\"justify\">"
            . $mdescr
            . "</div>"
            . "<div style=\"float: right; font-size: 150%; font-weight: bold; "
            . "border: solid 1px #808080; padding: 0.4em;\">"
            . "<a target=\"_blank\" href=\"$link\" rel=\"nofollow\">Visit Website</a>"
            . "</div>"
            . ($_SESSION['cns_show_price'] == '1' && $price != ''
                ? "<div style=\"font-size: 120%; padding-top: 0.6em\">Price: \$$price</div>"
                : '')
            
            . "<div style=\"clear: both\"></div>"
            . "</div>\n");
        $count++;
    }
    
    return array('posts' => $item_list, 'totalp' => $totalp);
}

/**
* Get all CSS and JS code
*/
function cns_get_css_js($totalp, $items_per_page)
{
    wp_register_style('cns_stylesheet', $_SESSION['cns_plugin_url'].'/style.css');
    wp_enqueue_style('cns_stylesheet');
    if ($totalp > $items_per_page) cns_show_paging_css();
    
    wp_enqueue_script(
        'cns_script',
        $_SESSION['cns_plugin_url'].'/init.js'
        . '?plugin_url='.htmlspecialchars($_SESSION['cns_plugin_url']),
        array('jquery'),
        '1.2.9');
}

/**
* Return HTML product list to insert into post / page / etc
* 
* @param array $attrs
* @return string
*/
function cns_show($user_id, $page = 1)
{
    $p_data = cns_get_items($user_id, $_SESSION['cns_items_per_page'], $page);
    $item_list = '';
    foreach ($p_data['posts'] as $p) {
        $item_list .= <<<ITEM
<div class="cns_socials" style="float: right">
  <!--span class="cns_fb">
    <div class="fb-like" data-href="$p[target_url]" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>
  </span-->
  &nbsp;
  <span class="cns_twitter">
    <a href="https://twitter.com/share" class="twitter-share-button" data-url="$p[target_url]" data-text="Share">Share</a>
  </span>
</div>
$p[post_title]<br />
$p[post_content]<br />
<hr />\n
ITEM;
    }
    
    $cns = <<<SOCIALS
<div class="cns_socials">
  <span class="cns_fb">
    <div class="fb-like" data-href="$_SESSION[cns_cur_url]" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
  </span>
  &nbsp;
  <span class="cns_twitter">
    <a href="https://twitter.com/share" class="twitter-share-button" data-url="$_SESSION[cns_cur_url]" data-text="Share">Share</a>
  </span>
</div>\n
SOCIALS
        . cns_show_paging($user_id, $p_data['totalp'], $_SESSION['cns_items_per_page'], $page)
        . "<hr />\n"
        . $item_list
        . cns_show_paging($user_id, $p_data['totalp'], $_SESSION['cns_items_per_page'], $page);
    
    return $cns;
}

/**
* Return HTML product list to insert into post / page / etc
* 
* @param array $attrs
* @return string
*/
function cns_show_filter($attrs = array())
{
    cns_get_css_js($_SESSION['cns_totalp'], $_SESSION['cns_items_per_page']);
    
    $_SESSION['cns_cur_url'] = get_permalink();
    if (false === $_SESSION['cns_cur_url']) $_SESSION['cns_cur_url'] = site_url();
    $_SESSION['cns_cur_url'] = htmlspecialchars($_SESSION['cns_cur_url'], ENT_QUOTES);
    
    $product_list = cns_show($_SESSION['cns_user_id']);
    return <<<HD
<!-- Facebook -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=137077062979348";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- Twitter -->
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

<div id="cns_loading_label" align="center" style="display: none; position: fixed">
  <h1 style="font-weight: bold">
    Loading the page...
  </h1>
</div>
<div id="cns_product_list">
  $product_list
</div>\n
HD;
}

/**
* Return CSS style code for pagination buttons
* 
* @return string
*/
function cns_show_paging_css()
{
    wp_register_style('cns_paging', $_SESSION['cns_plugin_url'].'/paging.css');
    wp_enqueue_style('cns_paging');
}

/**
* Return HTML code for pagination buttons
* 
* @param int $totalp
* @param int $items_per_page
* @param int $page
* @return string
*/
function cns_show_paging($user_id, $totalp, $items_per_page, $page = 1)
{
    if ($totalp <= $items_per_page) return '';
    
    $totalp = (int)ceil($totalp / $items_per_page);
    $pages_to_show = array();
    /*for ($i = 1; $i < 4; $i++) $pages_to_show[$i] = true;
    for ($i = $totalp; $i > $totalp-3; $i--) $pages_to_show[$i] = true;
    if ($page > 2 && $page < $totalp-1) {
        for ($i = $page-1; $i < $page+2; $i++) {
            $pages_to_show[$i] = true;
        }
    }*/
    if ($totalp < 3) $loop_end = $totalp+1;
    else $loop_end = 4;
    for ($i = 1; $i < $loop_end; $i++) $pages_to_show[$i] = true;
    $loop_end = $totalp-3;
    if ($loop_end < 0) $loop_end = 0;
    for ($i = $totalp; $i > $loop_end; $i--) $pages_to_show[$i] = true;
    if ($page > 2 && $page < $totalp-1) {
        for ($i = $page-1; $i < $page+2; $i++) {
            $pages_to_show[$i] = true;
        }
    }
    ksort($pages_to_show);
    
    $html = '';
    $prev_i = 0;
    foreach (array_keys($pages_to_show) as $i) {
        if ($i - $prev_i > 1) $html .= '... ';
        
        $html .= "<span class=\"cns_page_button";
        if ($page == $i) $html .= " cns_page_button_selected";
        $html .= "\" onclick=\"cns_show_page('$user_id', $i); return false\">";
        if ($page != $i) {
            $html .= "<a href=\"javascript:#\" >";
        }
        $html .= $i;
        if ($page != $i) $html .= "</a>";
        $html .= "</span> ";
        
        $prev_i = $i;
    }
    
    return "<div align=\"right\">$html</div>";
}

?>
