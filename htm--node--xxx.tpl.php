<?php

//show errors
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

global $attachment_id;
$attachment_id=2000;
global $post_id;
$post_id=1000;


$debug=false;
$lang="und"; //---> define which languages to export  
global $export_type;
$export_type="page";
/**

primer exportar els attachments
després de les pagines decidir quines seran projectes i quines pàgines
i exportar en els dos idiomes

**/


$xml='<?xml version="1.0" encoding="UTF-8" ?>
<!-- generator="WordPress/5.0.3" created="2019-01-21 17:09" -->
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
	<title>Vaic mobility</title>
	<link>https://pimpampum.ws/vaicmobility/wordpress</link>
	<description>Altre lloc gestionat amb el WordPress</description>
	<pubDate>Mon, 21 Jan 2019 17:09:08 +0000</pubDate>
	<language>ca</language>
	<wp:wxr_version>1.2</wp:wxr_version>
	<wp:base_site_url>https://pimpampum.ws/vaicmobility/wordpress</wp:base_site_url>
	<wp:base_blog_url>https://pimpampum.ws/vaicmobility/wordpress</wp:base_blog_url>

	<wp:author><wp:author_id>1</wp:author_id><wp:author_login><![CDATA[pimpampum]]></wp:author_login><wp:author_email><![CDATA[info@pimpampum.net]]></wp:author_email><wp:author_display_name><![CDATA[pimpampum]]></wp:author_display_name><wp:author_first_name><![CDATA[]]></wp:author_first_name><wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>';

$xml_end='</channel>
</rss>';




$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
  ->entityCondition('bundle', 'page')
  ->propertyCondition('status', NODE_PUBLISHED)
	   ->propertyCondition('language', $lang, '=')
//  ->fieldCondition('field_news_types', 'value', 'spotlight', '=')
  // See the comment about != NULL above.
//  ->fieldCondition('field_photo', 'fid', 'NULL', '!=')
//  ->fieldCondition('field_faculty_tag', 'tid', $value)
//  ->fieldCondition('field_news_publishdate', 'value', db_like($year) . '%', 'like') // Equal to "starts with"
//  ->fieldCondition('field_news_subtitle', 'value', '%' . db_like($year) . '%', 'like') // Equal to "contains"
//  ->fieldOrderBy('field_photo', 'fid', 'DESC')
  ->range(0, 50000)
  // Run the query as user 1.
  ->addMetaData('account', user_load(1));

$result = $query->execute();
if (isset($result['node'])) {
  $news_items_nids = array_keys($result['node']);

	if($debug){
		echo count($news_items_nids);

	}
  //print_r($news_items_nids);
  $node=node_load($news_items_nids[5]);

  $news_items = entity_load('node', $news_items_nids);


  //primer crear imatges
  foreach($news_items as $new){



			$sub_title="";
			if(isset($new->field_sub_title['und'][0]['value'])){
				$sub_title=$new->field_sub_title['und'][0]['value'];
			}

      if(isset($new->field_imatge['und'])){
          if(isset($new->field_imatge['und'][0])){

						if(isset($new->field_imatge['und'][0]['uri'])){
        		$path=$new->field_imatge['und'][0]['uri'];



            $my_path = file_create_url($path);

            if($new->field_imatge['und'][0]['title']!=""){
              $title=$new->field_imatge['und'][0]['title'];
            }else{
              $title=$my_path;
            }

						if($debug){
								echo "<li>im dest ".$title."</li>";
						}
						$title=slugify($title);
            $xml.=createAttachment($my_path,$title);


						$lang=$new->language;
            $xml.=createPage($new->title,$sub_title,
						$new->body['und'][0]['value'],
						$attachment_id,$lang);

						if($debug){
								echo "<li>post ".$new->title."</li>";
						}

						$attachment_id++;

        }
      }
    }else{//no te al menys una imatge

			$lang=$new->language;
			$xml.=createPage($new->title,$sub_title,
			$new->body['und'][0]['value'],
			0,$lang);

			if($debug){
					echo "<li>post sense imatge ".$new->title."</li>";
			}


		}

		//la resta d'imatges que no son la primera
		if(isset($new->field_imatge['und'])){
			$c=0;
			foreach( $new->field_imatge['und'] as $img ){
				//print_r($img);
				if($c>0){
					if(isset($img['uri'])){
						$path=$img['uri'];

						$my_path = file_create_url($path);

						if($img['title']!=""){
							$title=$img['title'];
						}else{
							$title=$my_path;
						}

						$title=slugify($title);
						$xml.=createAttachment($my_path,$title);
					}
				}
				$c++;
			}
		}
		//final resta imatges

  }

//  print_r($news_items);
}

$xml.=$xml_end;

if(!$debug){
	print $xml;
}



  function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}

function dd($var){
	print "<pre>";
	print_r($var);
	print "</pre>";
	exit();
}
function createAttachment($path,$title=""){
  global $attachment_id;
  $attachment_id++;



  return '<item>
		<title>'.$title.'</title>
		<link></link>
		<pubDate>Mon, 21 Jan 2019 10:13:57 +0000</pubDate>
		<dc:creator><![CDATA[pimpampum]]></dc:creator>
		<guid isPermaLink="false"></guid>
		<description></description>
		<content:encoded><![CDATA[]]></content:encoded>
		<excerpt:encoded><![CDATA[]]></excerpt:encoded>
		<wp:post_id>'.$attachment_id.'</wp:post_id>
		<wp:post_date><![CDATA[2019-01-21 12:13:57]]></wp:post_date>
		<wp:post_date_gmt><![CDATA[2019-01-21 10:13:57]]></wp:post_date_gmt>
		<wp:comment_status><![CDATA[open]]></wp:comment_status>
		<wp:ping_status><![CDATA[closed]]></wp:ping_status>
		<wp:post_name><![CDATA['.$title.']]></wp:post_name>
		<wp:status><![CDATA[inherit]]></wp:status>
		<wp:menu_order>0</wp:menu_order>
		<wp:post_type><![CDATA[attachment]]></wp:post_type>
		<wp:post_password><![CDATA[]]></wp:post_password>
		<wp:is_sticky>0</wp:is_sticky>
		<wp:attachment_url><![CDATA['.$path.']]></wp:attachment_url>


	</item>';
}


function createPage($title,$subtitle,$content,$thumbnail_id=0,$lang="ca"){
    global $post_id;
		global $export_type;
  $post_id++;
  $xml='
  <item>
		<title>**'.$title.'</title>
		<link></link>
		<pubDate>Thu, 17 Jan 2019 16:08:20 +0000</pubDate>
		<dc:creator><![CDATA[pimpampum]]></dc:creator>
		<guid isPermaLink="false">https://pimpampum.ws/vaicmobility/wordpress/?page_id='.$post_id.'</guid>
		<description></description>
		<content:encoded><![CDATA['.$content.']]></content:encoded>
		<excerpt:encoded><![CDATA[]]></excerpt:encoded>
		<wp:post_id>'.$post_id.'</wp:post_id>
		<wp:post_date><![CDATA[2019-01-17 18:08:20]]></wp:post_date>
		<wp:post_date_gmt><![CDATA[2019-01-17 16:08:20]]></wp:post_date_gmt>
		<wp:comment_status><![CDATA[closed]]></wp:comment_status>
		<wp:ping_status><![CDATA[open]]></wp:ping_status>
		<wp:post_name><![CDATA['.slugify($title).']]></wp:post_name>
		<wp:status><![CDATA[publish]]></wp:status>
		<wp:post_parent>0</wp:post_parent>
		<wp:menu_order>0</wp:menu_order>
		<wp:post_type><![CDATA['.$export_type.']]></wp:post_type>
		<wp:post_password><![CDATA[]]></wp:post_password>
		<wp:is_sticky>0</wp:is_sticky>
		<wp:postmeta>
			<wp:meta_key><![CDATA[_wp_page_template]]></wp:meta_key>
			<wp:meta_value><![CDATA[default]]></wp:meta_value>
		</wp:postmeta>';

		$xml.='
		<wp:postmeta>
			<wp:meta_key><![CDATA[ppp_subtitol]]></wp:meta_key>
			<wp:meta_value><![CDATA['.$subtitle.']]></wp:meta_value>
		</wp:postmeta>';

		if($lang=="und") $lang="ca";

		if($lang=="ca"){

			$xml.='<category domain="language" nicename="ca"><![CDATA[Català]]></category>';
		}

		if($lang=="es"){

			$xml.='<category domain="language" nicename="es"><![CDATA[Español]]></category>';
		}

		if($lang=="en"){

			$xml.='<category domain="language" nicename="en"><![CDATA[English]]></category>';
		}

  if($thumbnail_id!=0){
    $xml.='	<wp:postmeta>
  			<wp:meta_key><![CDATA[_thumbnail_id]]></wp:meta_key>
  			<wp:meta_value><![CDATA['.$thumbnail_id.']]></wp:meta_value>
  		</wp:postmeta>';
  }
  $xml.='</item>';
  return $xml;

}
