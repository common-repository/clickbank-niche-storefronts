<?php

if (!defined('ABSPATH')) require_once '../../../wp-config.php';
include_once ABSPATH.WPINC.'/feed.php';


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
    //$cns_options['cns_show_storefront_after_posts']['cur_val']
      //  = $_SESSION['cns_show_storefront_after_posts'];
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
          <!--
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
        -->
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
        //$post->post_content = cns_show_filter();
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
            //$new_key = max(array_keys($posts)) + 1;
            //$posts[$new_key] = $post;
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
function cns_get_items($user_id, $niche_cd, $items_per_page,  $page = 1,$include_css_js = false)
{
    
        //  echo "page:" . $page;
   

    $url = 'https://cbproads.com/xmlfeed/wp/cb.asp'
        . '?start='.(($page-1) * $items_per_page)
        . '&end='.$items_per_page
        . '&niche='.$niche_cd
        . '&id='.$user_id;
    
   // echo( $url);	
//exit;

    $url=$url."&Datem=".date('Y-m-d')."&url=".rawurlencode(home_url());
    
    if ($_GET['cs_show_url']==='yes'){

		echo $section.' -> '.$url.'<br>'; 
		echo "current time is: ".date("h:i:sa").'<br><br>';

	}
	
	 $empty_answer = array(
            'posts' => array(
                array(
                    'post_title' => cns_product_title_fmt('Sorry', $include_css_js),
                    'post_content' => 'No products in this list yet.<br />'
                        . 'May be the Account ID or Niche is wrong.')),
            'totalp' => 1);
    
	
    
    $rss = fetch_feed($url);
    if (is_wp_error($rss)) return $empty_answer;
    //if (false === @$doc->load($url)) return $empty_answer;
    //$items = $doc->getElementsByTagName('item');
    //if (0 === $items->length) return $empty_answer;
    if (0 == $rss->get_item_quantity(500)) return $empty_answer;
	
	
	$tmp = $rss->get_item()->get_item_tags('', 'totalp');
    $totalp = cns_cdata($tmp[0]['data']);
    $_SESSION['cns_totalp'] = $totalp;
    
    $count = 0;
    $item_list = array();
    $first = true;
	$items = $rss->get_items(0, 500);
    foreach ($items as $item) {
        // Title
       // $paths = $item->getElementsByTagName("title");
       // $title = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
		$paths = $item->get_item_tags('', "title");
        $title = htmlspecialchars(cns_cdata($paths[0]['data']));
        
        // URL
        $paths = $item->get_item_tags('', "affiliate");
        $mem = cns_cdata($paths[0]['data']);

        
        $paths = $item->get_item_tags('', "ids");
        $tar = cns_cdata($paths[0]['data']);
		
        $paths = $item->get_item_tags('', "niche");
        $niche = cns_cdata($paths[0]['data']);

		$paths = $item->get_item_tags('', "niche");
        $niche = cns_cdata($paths[0]['data']);

		
		$paths = $item->get_item_tags('', "gravity");
        $gravity = cns_cdata($paths[0]['data']);

		$paths = $item->get_item_tags('', "rank");
        $rank = cns_cdata($paths[0]['data']);

		
		
		if (    ($gravity>200) || ($rank<5) ) {
            $score=5; 
        }elseif (    ($gravity>100) || ($rank<15) ) {
             $score=4;
        }elseif (    ($gravity>0) || ($rank<30) ) {
             $score=3;
        }elseif (    ($gravity>0) || ($rank<30) ) {
             $score=3;
        }else{
             $score=2;
        }
		
       // $paths = $item->getElementsByTagName("ids");
        //$tar = cns_cdata($paths->item(0)->nodeValue);
      //  $paths = $item->getElementsByTagName("niche");
       // $niche = cns_cdata($paths->item(0)->nodeValue);
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
       // $paths = $item->getElementsByTagName("description");
      //  $description = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
     //   $paths = $item->getElementsByTagName("mdescr");
     //   $mdescr = htmlspecialchars(cns_cdata($paths->item(0)->nodeValue));
        
		$paths = $item->get_item_tags('', "description");
        $description = htmlspecialchars(cns_cdata($paths[0]['data']));
        $paths = $item->get_item_tags('', "mdescr");
        $mdescr = htmlspecialchars(cns_cdata($paths[0]['data']));
				
        // Images
        $paths = $item->get_item_tags('', "images");
        $imageFilename = cns_cdata($paths[0]['data']);
        if ($imageFilename != '' && $imageFilename != 'no') {
            $image = 'https://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners_mycbgenie/'
                . $imageFilename.'&resize=300';
            $image = htmlspecialchars($image);
            $imageFull = 'https://cbproads.com/cbbanners/'.$imageFilename;
            $imageFull = htmlspecialchars($imageFull);
        } else {
            unset($image, $imageFull);
        }
        //$paths = $item->getElementsByTagName("altimage");
        //$altimageFilename = cns_cdata($paths->item(0)->nodeValue);
        $paths = $item->get_item_tags('', "altimage");
        $altimageFilename = cns_cdata($paths[0]['data']);
		
        if ($imageFilename == 'blank.gif' && $altimageFilename != 'no') {
            $altimage = 'https://cbproads.com/clickbankstorefront/v4/send_binary.asp'
                . '?Path=D:/hshome/cbproads/cbproads.com/cbbanners_mycbgenie/'
                . $altimageFilename.'&resize=300';
            $altimage = htmlspecialchars($altimage);
            $altimageFull = 'https://cbproads.com/cbbanners/alter/'
                . $altimageFilename;
            $altimageFull = htmlspecialchars($altimageFull);
        } else {
            unset($altimage, $altimageFull);
        }
        
        // Price
        $paths = $item->get_item_tags('', "price");
        $price = htmlspecialchars(cns_cdata($paths[0]['data']));
        
        // Add record
        $item_list[] = array(
            'target_url' => $link,
            'post_title' => $title,
            'post_content'=> " <div align=center>"
                            . cns_image_show_box($altimage,$image,$title,$count,$imageFull,$altimageFull,$link,$section)  
	                        . "</div>\n",
			'descr'		 => $mdescr,
			'link'		 => $link,
			'score'		 => $score,
			'price'		=> $price
			);
        $count++;
    }
    
	 //$item_list[] = array(
           // 'target_url' => $link,
          //  'post_title' => cns_product_title_fmt($title, $include_css_js),
           // 'post_content'
            //    => "<div>\nytytyt");
				
    return array('posts' => $item_list, 'totalp' => $totalp);
}

function cns_image_show_box($altimage,$image,$title,$count,$imageFull,$altimageFull,$link,$section){
    //return "yasar";
    //incase you wnat to enable image preview, please remove yes
    $cs_show_img_preview="yes";
    if ($cs_show_img_preview==="yes"){
            $cs_show_img_preview="cs_preview";
    }
  //  echo 'image: '.$image.'<br>';
  //  echo 'alt: '.$altimage.'<br>';
       return   ( isset($altimage) || isset($image) 
                ? "<div class=\"cs_image_holder\" align=center style=\" text-align: center; ". ($_SESSION['cs_image_descr_layout']=='horizontal' ? "float:left;width:48%;" : '' ) ."\" >"
                		. (isset($altimage)
                        ? "<a class=\"cs_image_$count $cs_show_img_preview\" "
                        . "title=\"".htmlspecialchars($title)."\" "
                        . "href=\"$link\" src=\"$altimageFull\" "
                        . "index=\"cs_image_$count\" rel=\"nofollow\" "
                        . "onclick=\"window.open('$link'); return false\">"
                         . "<img class=\"cbpro_img_render\" style=\"".($_SESSION['cs_image_border_style']==="rounded"?'border-radius:50%;border:2px dotted silver; ':'')."  widths: 100%;\" alt=\"cs_image_$count\" src=\"$image\" /></a>\n"
                        
                        : (isset($image)
                            ? "<a class=\"cs_image_$count $cs_show_img_preview\" "
                            . "title=\"".htmlspecialchars($title)."\" "
                            . "href=\"$link\" src=\"$imageFull\" "
                            . "index=\"cs_image_$count\" rel=\"nofollow\" "
                            . "onclick=\"window.open('$link'); return false\">"
                            . "<img class=\"cbpro_img_render\" style=\"".($_SESSION['cs_image_border_style']==="rounded"?'border-radius:50%;border:2px dotted silver; ':'')."  widths: 100%;\" alt=\"cs_image_$count\" src=\"$image\" /></a>\n"
                            : '')
                           
                        )
                                      
                  ."</div>"
                  
                : "<div class=\"cs_image_holder\"></div>"
                );
            
}

function cns_products_rating($rating){
	  $font_size=(substr($_SESSION['cs_display_layout'],0,1)>2 ?'13':'14');
        
        $cs_rating_color=$_SESSION['cs_primary_color'];
        $cs_rating_color="orange";
        $cs_rating_color_dim="#E8E8E8";
        ($_SESSION['cs_image_descr_layout']=='horizontal'? $alignn='right' : $alignn='center');
        
        
             $star      =   "<i class=\"fa fa-star\" style=\"font-size:".$font_size."px;color:".$cs_rating_color ."\"></i>";
             $star_dim  =   "<i class=\"fa fa-star\" style=\"font-size:".$font_size."px;color:".$cs_rating_color_dim ."\"></i>";
             
             for ($x = 0; $x <= $rating-1; $x++) {
              $star_plus.=$star;
            } 
            
            for ($x = 0; $x <=4- $rating; $x++) {
              $star_plus.=$star_dim;
            } 
            
        return "<div align=".$alignn." style=\"letter-spacing:4px; margin-bottom:20px; text-align: center;  margin-left: auto;    margin-right: auto;\">".$star_plus."</div>";
  
}

function cns_products_description($cur_view,$description,$tirm_mdescr,$section,$show_more_link,$read_review){
    //this fucntion has been discountinued
   // echo $cur_view;
    $question = substr(trim($description), -1); 
    if ($question != '.') { $description.= '.'; } 
    
    $view_similar=(   (($section==='supplement') || ($section==='bestselling') || ($section==='featured') || ($section==='popular'))  && ($_SESSION['cs_switch_view'] ==='tdli')
                                                                              ?"<br><a href='$show_more_link'  style='text-decoration:none; margin-right:4px; margin-top:3px; margin-bottom:3px; font-size:12px;color:#dcdcdc;' tagrget='_blank'><i class='fa fa-eye' aria-hidden='true' style='font-size:12px;color:#dcdcdc;'></i> Similar Products</a>  "
                                                                              :''
                                                                             );

    return  ($_SESSION['cs_image_descr_layout']=='horizontal'
                ? "<div style=\"float:right; padding-top:10px; width:47%; border:0px solid silver;\">"
                : ""
            )
            
            .(($cur_view === 'tdi')
                    
                    
                        
                        ? (get_option('cbproads_premium_store')?"":"<br><br><br>"). "\n"
                        
                          : "<p  style='padding-top:7px;  font-size:0.95em; color:#808080; margin:10px;  text-align:left; text-align:left; color:#808080;  overflow: hidden;    text-overflow: ellipsis;   display: -webkit-box;   -webkit-line-clamp: 3; /* number of lines to show */           line-clamp: 3;    -webkit-box-orient: vertical;' />".
                                            ucwords(strtolower($description)). "</p>".(get_option('cbproads_premium_store')?"":"<br>")
                                            ."\n"
            )
            
            
            
           // . cs_products_rating($section)   
            
            .($_SESSION['cs_image_descr_layout']=='horizontal'
                ? "</div><div style=\"clear: both;\"></div>"
                : ""
            );
}

function cns_price_view_more_similar_details($price){
 return "<div class=\"cs_show_price\" style=\" border:0px solid #dcdcdc; border-radius:5px; color: orange;  \" > \$".number_format($price,2)."</div>\n";
}
	 
	 
function cns_product_box ($dummy,$p_title,$p_content,$link,$section,$price,$score,$description,$show_more_link,$read_review=null,$review_url){
	//echo $_SESSION['cns_columns'].'lll'.$_SESSION['cns_items_per_page'];
	//echo $_SESSION['cns_show_price'];
	$cs_temp_col_cnt=4;
	$cs_temp_col_cnt=$_SESSION['cns_columns'];
	
	if ($cs_temp_col_cnt==1) {}
	elseif ($cs_temp_col_cnt==2) {}
	elseif ($cs_temp_col_cnt==3) {}
	elseif ($cs_temp_col_cnt==4) {}
	elseif ($cs_temp_col_cnt==5) {}
	elseif ($cs_temp_col_cnt==6) {}
	else {$cs_temp_col_cnt=4;}
		
    $item_list_box .="<div class=\"cns_column_".$cs_temp_col_cnt."\" >\n\n";
	
		$item_list_box .= "<div align='center' class=\"cs_img_center\" style=\" margin-bottom:5px;  line-height:150%;   \"> \n";
                         
                        $item_list_box .= $p_content;
						$item_list_box .=  cns_products_rating($score)
											.($_SESSION['cns_show_price']=='yes' ? cns_price_view_more_similar_details($price) :'')
											."<div style='margin:5px; font-weight:400; font-size:1.1em;'>".$p_title."</div>"
											.cns_products_description($cur_view,$description,$tirm_mdescr,$section,$show_more_link,$read_review)
											.'<br>'
											
											;
    	$item_list_box .=  "\n</div> <!-- end of align=center -->\n";                      
	$item_list_box .= "</div>\n<!-- end of cns cloumn -->\n\n";          // cs_column
	
   return $item_list_box;
                    
}	
/**
* Get all CSS and JS code
*/
function cns_get_css_js($totalp, $items_per_page)
{

    if ($totalp > $items_per_page) cns_show_paging_css();
    
}

function cns_show($user_id,  $niche_cd, $page = 1)
{
    //return '<h1>sasasa</h1>'.$page;
	
    $p_data = cns_get_items($user_id, $niche_cd, $_SESSION['cns_items_per_page'],$page );
             
    
  
    $item_list = '';
	$row_count=0;
    foreach ($p_data['posts'] as $p) {
		
		$row_count=$row_count+1;
		
		if ( $row_count==1 ) {
			
           $item_list .="\n\n<div class=\"cns_row\" >\n\n";
			
        }
			$item_list .=cns_product_box("",$p['post_title'],$p['post_content'],$p['link'],$section,$p['price'],$p['score'],$p['descr'],$p['show_more_link'],$p['read_review'],$p['review_url']);        
		
	}
    
	  $item_list .="\n\n</div>\n <!--end of cs_row-->\n\n";
	
        $cns =
        cns_show_paging($user_id, $niche_cd,$p_data['totalp'], $_SESSION['cns_items_per_page'], $page)
        . "<br />\n"
        . $item_list
        . cns_show_paging($user_id, $niche_cd,$p_data['totalp'], $_SESSION['cns_items_per_page'], $page);
    
  return $cns;
}





function cns_show_filter($attrs)
{
	
    


	$attrs = shortcode_atts(array(
        'niche' =>  $_SESSION['niche'] ), 
		$attrs, 'clickbank-niche-storefront');
		$niche_cd= sanitize_title($attrs['niche']);
		
//$_SESSION['cns_user_id']="15750";
		
		///////////////////////////////////////////////////////////////////////
		$url = 'https://cbproads.com/xmlfeed/wp/cb.asp'
        . '?start=0'
        . '&end=1'
        . '&niche='
        . (trim($niche_cd) != ''
            ? $niche_cd
            : $_SESSION['cns_niche'])
        . '&id='
        . (trim($_SESSION['cns_user_id']) != ''
            ? $_SESSION['cns_user_id']
            : $GLOBALS['cns_options']['cns_user_id']['default'])
		."&url=".rawurlencode(home_url());

//echo $url;

    	$empty_answer = array(
                'posts' => array(
                    array(
                        'post_title' => 'Sorry',
                        'post_content' => 'No product counts .<br />'
                            . 'Please contact CBProAds.com developer team.')),
                'totalp' => 1);
        $rss = fetch_feed($url);
    	if (is_wp_error($rss)) return $empty_answer;
    	if (0 == $rss->get_item_quantity(400)) return $empty_answer;
    	
    	$tmp = $rss->get_item()->get_item_tags('', 'totalp');
        $totalp = cns_cdata($tmp[0]['data']);
        $_SESSION['cns_totalp'] = $totalp;
        ///////////////////////////////////////////////////////////////////////
    
        $niche_cd=(trim($niche_cd) != ''
            ? $niche_cd
            : $_SESSION['cns_niche']);
    
 //echo $totalp;  


//exit;
		
    cns_get_css_js($totalp, $_SESSION['cns_items_per_page']);
    
    $_SESSION['cns_cur_url'] = get_permalink();
    if (false === $_SESSION['cns_cur_url']) $_SESSION['cns_cur_url'] = site_url();
    $_SESSION['cns_cur_url'] = htmlspecialchars($_SESSION['cns_cur_url'], ENT_QUOTES);
    
    $product_list = cns_show($_SESSION['cns_user_id'],$niche_cd);
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
function cns_show_paging($user_id, $niche, $totalp, $items_per_page, $page = 1)
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
        $html .= "\" onclick=\"cns_show_page('$user_id','$niche', $i); return false\">";
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
