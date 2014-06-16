<?php
//get original index.html if exist, just to see if anything has changed
if (file_exists("../index.html")) {
	$index_old = strtoupper(dechex(crc32(file_get_contents("../index.html"))));
}

//check if the scale factor is set
if ($prototype == "iphone4" || $prototype == "iphone5" || $prototype == "ipadretina"  )  {
	$factor = "0.5";
}

//set pagenr
$pagenr=0;

#add extra html function
function extrahtml($page,$filename){
	$sourcefile = "../addons/".$page.".htm";
	if ($filename == $page.".lbi"){
		$extracontent = file_get_contents($sourcefile);
		$html = $html . $extracontent;
		return $html;
		}
	}
unset ($filename);

#Replace spaces function (thanks to Tobse)
function replaceSpaces ($hits) {
	  return str_replace (" ", "", $hits[0]); 
}
#Modify href function
function modifyHref ($hits) {
	if (strstr($hits[0],"http://") || strstr($hits[0],"mailto:") || strstr($hits[0],"tel:")) {
		return $hits[0];
	} else {
	#Remove .htm extensions
	$returnhref = preg_replace ("%\..?htm.*?\"%sim", '"', $hits[0]);
	
	#Remove .html extensions, just in case
	$returnhref = preg_replace ("%\..?html.*?\"%sim", '"', $returnhref);
	
	#Add # for JQtouch in the href
	$returnhref = preg_replace ('/href="/', 'href="#', $returnhref);
	
	  return $returnhref; 
	  }
}

# get all .lbi files in the directory "Library"
if (file_exists("../Library")) {
	$handle = opendir ("../Library");
	while ($filename = readdir ($handle)) if (substr ($filename, -4) == ".lbi") $filenames[] = $filename;
	closedir ($handle);
}

# get all .htm files in directory "addons"
if (file_exists("../addons")) {
	$handle = opendir ("../addons");
	while ($filename = readdir ($handle)) if (substr ($filename, -4) == ".htm") $htmFiles[] = $filename;
	closedir ($handle);
}

$unorderedContent = array();
if (is_array ($filenames)) foreach ($filenames as $filename) {
	
	#increment pagenr
	$pagenr++;
	
	# check if filenames are correct
	$barefilename = substr ($filename, 0, -4);
	$betterfilename = preg_replace('/[^0-9a-z\.\_\-]/i','',$barefilename);
	if ($betterfilename == $barefilename) {
	} else {
  		$message .="<li class=\"warning\">Check the page called \"" .$barefilename. "\" for special characters, try \"" . $betterfilename ."\"</li>";
	}
	# check for filenames called "keep"
	$barefilename = substr ($filename, 0, -4);
	if ($barefilename == "keep") {
  		$message .="<li class=\"warning\">You named a page \"keep\". This might cause errors. Please rename this page</li>";
	}
	
	#check for filenames named m1, m2 or m3
	if (preg_match("/m[0-9]/", $barefilename))
	{
	  $message .= "<li class=\"warning\">You used the filename ".$barefilename." that is used for setting memories. Do not name your pages m1, m2 or m3</li>";
	}
	
	#start div with filename
	$startdiv = "\n<div id=\"" . $barefilename . "\">";
	$html = file_get_contents ("../Library/" . $filename);
	
	#Check for pages with tables, because this will give you trouble.
	if (strpos($html,"<table ")==true) {
		$message .="<li class=\"warning\">Check the page called \"" .$barefilename. "\" for the use of tables, try using hotspots instead of slices</li>";
	} else {
		#Change image ratio and image maps (for Retina displays or other devices with a certain scale factor)
		if (($prototype=="iphone4")||($prototype=="iphone5")||($prototype=="ipadretina")||($prototype=="other"))  {	
		
		//Find width and multiply with $factor
		$search = "%(width=\")([0-9]{1,})%e";
		$replace = "'width=\"'.round(\\2*".$factor.",0)";
		$html = preg_replace($search,$replace,$html);
		
		//Find height and multiply with $factor
		$search = "%(height=\")([0-9]{1,})%e";
		$replace = "'height=\"'.round(\\2*".$factor.",0)";
		$html = preg_replace($search,$replace,$html);
			
		#resizing image maps divide by the factor (which is 2 for Retina displays
		$html = preg_replace("/([0-9.]{1,}),([0-9.]{1,})/e","round(\\1*".$factor.",0).','.round(\\2*".$factor.",0)",$html);
		}
	}
	
	#remove metatags from .lbi files
	$html = preg_replace ("%<meta.*?>%sim", "", $html);
	
	#remove titles from .lbi files
	$html = preg_replace ("%title=\".*?\"%sim", " ", $html);
	
	#find imagenames
	$start = strpos($html, "images/");
	$end =  strpos($html, "width")-2;
	$length = $end - $start;
	$imagename=substr($html,$start,$length);
	
	if (file_exists ("../Library/".$imagename)) {
    		} else {
			$message .="<li class=\"warning\">No image found for page \"".$barefilename."\". Check if you exported images to the \"images subfolder\" when exporting from Fireworks</li>";
			}
	
	#Correct the path to the image folder
	$html = str_replace("src=\"images/", "src=\"Library/images/", $html);
	
	#Transform internal links to anchor links
	
	#Remove spaces from target
	$html = preg_replace_callback ("/href=\"([^\"]*?)\"/", "modifyHref", $html);
	
	#Remove spaces from target
	$html = preg_replace_callback ("/target=\"([^\"]*?)\"/", "replaceSpaces", $html);
	
	#Remove href="javascript:;"
	$html = str_replace ('javascript:;', '#', $html);
	
	#Remove double ##
	$html = str_replace ('##', '#', $html);
	
	#Remove href="#javascript.... 
	$html = str_replace ('#javascript', 'javascript', $html);
	
	#Replace "alt" with "class"
	$html = preg_replace ('%alt="%', 'class="', $html);
	
	#Replace "target" with "title"
	$html = preg_replace ('%target="%', 'title="', $html);
	
	#add addons stuff to files with the same name
	if (is_array ($htmFiles)) foreach ($htmFiles as $htmFile) $html = $html . extrahtml(str_replace (".htm", "", $htmFile), $filename);
	
	# check if you did not use "slide" (use slideleft or slideright instead)
	$html = preg_replace ('%slide"%', 'slideleft"', $html);
	
	# check if you did not use "swap" (use swapleft or swapright instead)
	$html = preg_replace ('%swap"%', 'swapleft"', $html);
	
	# check if you did not use "cube" use cubeleft or cuberight instead)
	$html = preg_replace ('%cube"%', 'cubeleft"', $html);
	
	# check if you did not use "flip" use cubeleft or cuberight instead)
	$html = preg_replace ('%flip"%', 'flipleft"', $html);
	
	# check if you did not use "pop" (use popup or popdown instead)
	$html = preg_replace ('%pop"%', 'popup"', $html);
		
	#close div
	$enddiv = "</div>\n";
		
	#create page
	//$content = $content . $startdiv . $html . $enddiv;
	$unorderedContent[substr ($filename, 0, -4)] = $startdiv . $html . $enddiv;
	
	$stringDataLibimages .= "Library/" . $imagename . "\n";
	
	unset ($handle);
	unset ($filename);
	unset ($filenames);

}
	
# open Manifest
if ($caching=="on"){
	$manifest = "../cache.manifest";
	$fh = fopen($manifest, 'w') or die("can't open file");

	$stringData="CACHE MANIFEST\n\n# v=".$buildnr."\n\n# JS includes:\n";
	fwrite($fh, $stringData);
	
	# get all images in the directory "includes/js"
	$handle = opendir ("includes/js");
	while ($filename = readdir ($handle)) if (substr ($filename, -3) == ".js") $filenames[] = $filename;
	closedir ($handle);
	
	if (is_array ($filenames)) foreach ($filenames as $filename) {
		$stringData ="build/includes/js/" . $filename . "\n";
		fwrite($fh, $stringData);
	}
	unset ($handle);
	unset ($filename);
	unset ($filenames);
	
	# get all images in the addons directory "addons"
	if (file_exists("../addons")) {
		$imgnames = array();
		fwrite($fh, "\n# Addon images:\n");
		$handle = opendir ("../addons");
		while ($imagename = readdir ($handle)) if ((substr ($imagename, -4) == ".png") or (substr ($imagename, -4) == ".jpg") or (substr ($imagename, -4) == ".gif")) $imgnames[] = $imagename;
		closedir ($handle);
		if (is_array ($imgnames)) foreach ($imgnames as $imagename) {
			#write to manifest
			$stringData ="addons/" . $imagename . "\n";
			fwrite($fh, $stringData);
		}
	}

	fwrite($fh, "\n# Stylesheet includes:\nbuild/includes/css/jqtouch.css\nbuild/includes/css/style.css\n");
	
	fwrite($fh, "\n# Library items:\n");
	fwrite($fh, $stringDataLibimages);
	#close manifest
	fclose($fh);
}


#Put the startpage on top
$order = array($startpage);
if (is_array ($order)) foreach($order as $i=>$key) {
	unset($order[$i]);
	if(isset($unorderedContent[$key])) {
		$order[$key] = $unorderedContent[$key];
		unset($unorderedContent[$key]);
	}
}
#Check if the start page was found at all
if (empty($order)){
	$message .="<li class=\"warning\">Start page not found! (Good to know: filenames are case sensitive)</li>";
	}
$order = array_merge($order, $unorderedContent);

// Make the final content
$content = implode($order, '');

#create .htaccess file
	$htaccess = "../.htaccess";
	$handle = fopen ($htaccess, 'w') or die("can't write file $htaccess.");
	fwrite ($handle, "AddType text/cache-manifest .manifest manifest");
	if ($pwprotect=="on") {
		$auth="\nAuthName "."\"".$loginmessage."\"\n"."AuthType Basic\nAuthUserFile ". $root .".htpasswd\nrequire valid-user ";
	fwrite ($handle, $auth);
	}
	fclose ($handle);
#create .passwd file
if ($pwprotect=="on") {
	$htpasswd = "../.htpasswd";
	$handle = fopen ($htpasswd, 'w') or die("can't write file $htaccess.");
	fwrite ($handle, $username.":".$passwordencrypted);
	fclose ($handle);
}

# write index.html

# Caching manifest declaration for index.html
if ($caching=="on") {
		$htmltag = "<html manifest=\"cache.manifest\" xmlns=\"http://www.w3.org/1999/xhtml\">";
		$introductionmessage = "Please wait while the page is loading...\\nBe sure to use Wifi or 3G";
		$showintroscreen = "true";
	} else {
		$htmltag = "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
		$introductionmessage = "To use this prototype: \\n 1. Click on the 'share/arrow' button in the Safari menu \\n 2. Select 'Add to homescreen'  \\n 3. Click the icon on your homescreen";
	}
	
#Define resolution based on prototype
if ($prototype=="iphone") {
	$targetwidth = "320";
	$targetheight = "480";
	$browserheight = "356";	
	$browserheight_ios5 = "356";	
	$browserheight_l = "208";//landscape
	$browserheight_l_ios5 = "208";
	$statusbarheight="20";
}
if ($prototype=="iphone4") {
	$targetwidth = "640";
	$targetheight = "960";	
	$browserheight = "356";	
	$browserheight_ios5 = "356";	
	$browserheight_l = "208";//landscape
	$browserheight_l_iOS5 = "208";
	$statusbarheight="40";
}
if ($prototype=="iphone5") {
	$targetwidth = "640";
	$targetheight = "1136";	
	$browserheight = "444";	
	$browserheight_ios5 = "444";	
	$browserheight_l = "208";//landscape
	$browserheight_l_iOS5 = "208";
	$statusbarheight="40";
}
if ($prototype=="ipad") {
	$targetwidth = "768";
	$targetheight = "1024";
	$browserheight = "946";		
	$browserheight_iOS5 = "928";	
	$browserheight_l = "690";//landscape
	$browserheight_l_iOS5 = "672";
	$statusbarheight="20";	
}
if ($prototype=="ipadretina") {
	$targetwidth = "1536";
	$targetheight = "2048";
	$browserheight = "946";		
	$browserheight_iOS5 = "928";	
	$browserheight_l = "690";//landscape
	$browserheight_l_iOS5 = "672";
	$statusbarheight="40";	
}
if ($prototype=="mobile") {
	$targetwidth = "320";
	$targetheight = "480";
	$browserheight = "356";	
	$browserheight_iOS5 = "356";	
	$browserheight_l = "208";//landscape
	$browserheight_l_iOS5 = "208";	
	$statusbarheight="20";
}
if ($prototype=="other") {
	//$targetwidth = $devicewidth;
	$targetheight = $deviceheight;
	$browserheight = $devicebrowserheight;	
	$browserheight_iOS5 = $devicebrowserheight;	
	$browserheight_l = $devicebrowserheight_l; //landscape
	$browserheight_l_iOS5 = $devicebrowserheight;	
	$statusbarheight="20";	
}

# Adding effect to homescreen icon
if ($springboard_icon_effect!="on") {
		$effects = "-precomposed";
	} else {
		$effects = "";
	}	

# Get template (indextemplate.shtml)
$template = @file_get_contents ("includes/indextemplate.shtml") or die ("can't load index template!");
$handle = fopen ("../index.html", "w") or die ("can't write index.html");

# Replace placeholders in template
$template = str_replace ("%MESSAGE%","<!--This file is generated by build/includes/functions.php. Making changes in this file might be overwritten. Change build/includes/functions.php or build/includes/indextemplate.shmtl instead-->", $template);
$template = str_replace ("%META%",$htmltag, $template);
$template = str_replace ("%EFFECTS%",$effects, $template);
$template = str_replace ("%TITLE%",$pagetitle, $template);
$template = str_replace ("%USERSCALABLE%",$userscalable, $template);
$template = str_replace ("%SCROLLTOTOP%",$scrolltotop, $template);
$template = str_replace ("%HOMESCREENMESSAGE%",$homescreenmessage, $template);
$template = str_replace ("%STARTPAGE%",$startpage, $template);
$template = str_replace ("%HIDEADDRESS%",$hide_address, $template);
$template = str_replace ("%DEVICEFRAME%",$deviceframe, $template);
$template = str_replace ("%BGCOLOR%",$bgcolor, $template);
$template = str_replace ("%PROTOTYPE%",$prototype, $template);
$template = str_replace ("%WIDTH%",$targetwidth, $template);
$template = str_replace ("%HEIGHT%",$targetheight, $template);
$template = str_replace ("%BROWSERHEIGHT%",$browserheight, $template);
$template = str_replace ("%BROWSERHEIGHT_IOS5%",$browserheight_iOS5, $template);
$template = str_replace ("%BROWSERHEIGHT_L%",$browserheight_l, $template);
$template = str_replace ("%BROWSERHEIGHT_L_IOS5%",$browserheight_l_iOS5, $template);
$template = str_replace ("%HTMLCODE%",$content, $template);
$template = str_replace ("%REBUILD%",$rebuildscript, $template);
$template = str_replace ("%REBUILDMS%",$rebuildscriptms, $template);
$template = str_replace ("%REBUILDUIWEBVIEW%",$rebuildscriptuiwebview, $template);
$template = str_replace ("%INTRODUCTIONMESSAGE%",$introductionmessage, $template);
$template = str_replace ("%SHOWINTROSCREEN%",$showintroscreen, $template);
$template = str_replace ("%CACHING%",$caching, $template);

fwrite ($handle, $template);
fclose ($handle);

//Compare old index.html with new index.html
$index_new = strtoupper(dechex(crc32(file_get_contents("../index.html"))));
if ($index_old==$index_new) {
	//no changes found and nothing uploaded
	if ($uploadedsomething != "true") {
		$message .="<li class=\"warning\">No changes found compared to the previous build! Did you upload any new files?</li>";
	}
} else {
	//changes found
}
?>