<!-- TAP version: 0.48 -->
<?php 
$tapversion = "0.48";
error_reporting(0);

//if you do not want to take the risk that anyone opens your build page, set this to true
$protectbuild=false;

#check browser
if (!strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') || strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
		$message .="<li>You are not browsing with Safari, which is probably fine, but if problems occur, first try it out with Safari</li>";
	}
#get url for prototype
$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
$host     = $_SERVER['HTTP_HOST'];
$script   = $_SERVER['SCRIPT_NAME'];
$currentUrl = $protocol . '://' .$host. str_replace("build/index.php","index.html", $script);
	
#check if config file is already written
if (file_exists ("../config.php")) { 
 
	#read existing config.php if applicant
	include ("../config.php");

} else if (!isset($_GET['proto'])) { 
	
	#check if folder is writable
	$filename = '../';
	if (is_writable($filename)) {
		
		#writable
		$message .= '<li>Start by adjusting the settings below, then "Build" the prototype. Whenever you made design changes, just come back to this screen and hit Build.</li>';
	} else {
		
		#not writable
    	$message .='<li class="warning">Before you can do anything, please check your file permissions! You need to be able to write files to your prototype folder. Then refresh this page. If you need help, <a href="http://www.google.com/search?sourceid=chrome&ie=UTF-8&q=set+chmode+with#sclient=psy&hl=en&q=How+to+change+permissions+using+&aq=f&aqi=g1g-v1g-o1&aql=&oq=&pbx=1&fp=fca90e9507624f80" target="_blank">use this search</a> and add the name of your FTP program.</li>';
		$fileproblem = true;
	}
	#set default values in form
	$checkalert="checked";
}

//Save settings or build
if ($_POST["donow"] == "checkForm") {
	# prepare config.php
  	$template = '<?php
	#General setting
	$buildnr="{BUILDNR}";
	$prototype="{PROTOTYPE}";
	$userscalable="{USERSCALABLE}";
	$scrolltotop="{SCROLLTOTOP}";
	$homescreenmessage="{HOMESCREENMESSAGE}";
	$factor="{FACTOR}";
	$deviceheight="{DEVICEHEIGHT}";
	$deviceframe="{DEVICEFRAME}";
	$devicebrowserheight="{DEVICEBROWSERHEIGHT}";
	$pagetitle="{PAGETITLE}";
	$startpage="{STARTPAGE}";
	$hide_address="{HIDEADDRESS}";
	$bgcolor="{BGCOLOR}";
	$caching="{CACHING}";
	$springboard_icon_effect="{SPRINGBOARDICON}"; #Adds effect to homescreen icon
	$pwprotect="{PWPROTECTION}";
	$root="{ROOT}"; #Needed for password protection
	$loginmessage="{LOGINMESSAGE}"; #If asked for a password, what is the message
	$username="{USERNAME}";
	$password="{PASSWORD}";
	$passwordencrypted="{PASSWORDENCRYPTED}";
	?>'; 
	$template = str_replace ("{BUILDNR}", $_POST["buildnr"], $template);
	$template = str_replace ("{PROTOTYPE}", $_POST["prototype"], $template);
	$template = str_replace ("{USERSCALABLE}", $_POST["userscalable"], $template);
	$template = str_replace ("{SCROLLTOTOP}", $_POST["scrolltotop"], $template);
	$template = str_replace ("{HOMESCREENMESSAGE}", $_POST["homescreenmessage"], $template);
	$template = str_replace ("{FACTOR}", $_POST["factor"], $template);
	$template = str_replace ("{DEVICEHEIGHT}", $_POST["deviceheight"], $template);
	$template = str_replace ("{DEVICEBROWSERHEIGHT}", $_POST["devicebrowserheight"], $template);
	$template = str_replace ("{PAGETITLE}", $_POST["pagetitle"], $template);
	$template = str_replace ("{STARTPAGE}", $_POST["startpage"], $template); 
	$template = str_replace ("{HIDEADDRESS}", $_POST["hide_address"], $template); 
	$template = str_replace ("{DEVICEFRAME}", $_POST["deviceframe"], $template); 
	$template = str_replace ("{BGCOLOR}", $_POST["bgcolor"], $template); 
	$template = str_replace ("{CACHING}", $_POST["caching"], $template);
	$template = str_replace ("{SPRINGBOARDICON}", $_POST["spi_effect"], $template);
	$template = str_replace ("{PWPROTECTION}", $_POST["pwprotect"], $template);
	$template = str_replace ("{ROOT}", $_POST["root"], $template);
	$template = str_replace ("{LOGINMESSAGE}", $_POST["loginmessage"], $template);  
	$template = str_replace ("{USERNAME}", $_POST["username"], $template);
	$template = str_replace ("{PASSWORD}", $_POST["password"], $template);
	
	#generate Password	
	if ($_POST["pwprotect"]=="on"){
		$hash = base64_encode(sha1($_POST["password"], true));
    	$passwordencrypted = '{SHA}'.$hash;
		$template = str_replace ("{PASSWORDENCRYPTED}", $passwordencrypted, $template);
	}
	
	//Save config.php
	if ($protectbuild == false) {
		$cf = @fopen ("../config.php", "w");
		fwrite ($cf, $template);
		fclose ($cf);
	}
	include ("../config.php");
	
	#start message for display after build
	if ($protectbuild!=true) {
		$message = "<li>Settings saved</li>";
	} else {
		$message = "<li class='warning'>Settings are not saved because this build page is write protected</li>";
	}
	
	//upload images
	if ($protectbuild == false) {
		if(move_uploaded_file($_FILES['icon']['tmp_name'], "../icon.png")) {
			$uploadedsomething ="true";
			$message .= "<li>Icon has been uploaded</li>";
		} else {
			if (file_exists ("../icon.png")) {
				} else {
					$message .= "<li>No icon uploaded yet</li>";
			}
		} 
		if(move_uploaded_file($_FILES['startup']['tmp_name'], "../startup_image.png")) {
			$uploadedsomething ="true";
			$message .= "<li>Portrait startup image has been uploaded</li>";
		} else {
			if (file_exists ("../startup_image.png")) {
				} else {
					$message .= "<li>No portrait start up image uploaded yet</li>";
				} 
		} 
		if(move_uploaded_file($_FILES['startup_l']['tmp_name'], "../startup_image_l.png")) {
			$uploadedsomething ="true";
			$message .= "<li>Landscape startup image has been uploaded!</li>";
		} else {
			if (file_exists ("../startup_image_l.png")) {
				} else {
					$message .= "<li>No landscape start up image uploaded yet</li>";
				} 
		} 
	}
	
	if (file_exists ("../Library")) {
		include ("includes/functions.php");
		
		#check of any pages are found
		if ($pagenr == 0){
			$message .= "<li class=\"warning\">Hey! Your Library folder is empty! Export your Fireworks .lbi files to the Library folder</li>";
			$fatalerror = "true";
		}
	} else {
		$message .= "<li class=\"warning\">Hey! Your Library folder is missing!</li>";
		$fatalerror = "true";
	}
	
	#check dimensions of portrait start up image
	if (file_exists ("../startup_image.png")) {
		list($width, $height, $type, $attr) = getimagesize("../startup_image.png");
		if ($width != $targetwidth || $height != $targetheight-$statusbarheight){
			$message .= "<li class=\"warning\">Check the size of your portrait startup image. It should be ".$targetwidth." by ". ($targetheight-$statusbarheight) ." pixels</li>";
			}
	}
	#check dimensions of landscape start up image
	if (file_exists ("../startup_image_l.png")) {
		list($width, $height, $type, $attr) = getimagesize("../startup_image_l.png");
		if ($width != ($targetwidth-$statusbarheight)|| $height != $targetheight){
			$message .= "<li class=\"warning\">Check the size of your landscape startup image. It should be ".($targetwidth-$statusbarheight)." by ". $targetheight ."pixels</li>";
			}
	}
	
	#show size of prototype
	# check file size (to see how large the prototype is in total, some devices will not load all images from large prototypes
	if (file_exists ("../Library")) {
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("../Library/")) as $file){ 
			$totalsize+=$file->getSize(); 
		}
		$message .= "<li>Size of the prototype: <b>" . getsize($totalsize)  . "</b></li>";
		if ($_GET["filesize"] > 6000000) {
			$message .= "<li class=\"warning\">Your prototype is rather large. We advice to try to keep it under 6 MB, othersize you get the blue cross!</li>";
		}
	}		
	#show prototype name and build nr.
	if ($fatalerror != "true"){
		$message .= "<li>Prototype nr <b>".$buildnr."</b> consisting of <b>" . $pagenr . "</b> pages was build !  View it <a href=\"../index.html\" target=\"_new\" >here</a></li></li>";
		
		#Add build nr for next build
		$buildnr++;
	}
}


# set buildnr if not set before
if ($buildnr =="") {
	$buildnr = 1;
}
	
#show message
if ($message!=""){
	$message = "<div class=\"message\"><ul>" . $message ."</ul></div>"; 
}

#readable filesize
function getsize($size) {
    $si = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $remainder = $i = 0;
    while ($size >= 1024 && $i < 8) {
        $remainder = (($size & 0x3ff) + $remainder) / 1024;
        $size = $size >> 10;
        $i++;
    }
    return round($size + $remainder, 2) . ' ' . $si[$i];
}
#check alertbox
if ($homescreenmessage=="on"){
	$checkalert="checked";
	}
?>
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<style type="text/css">
body {
	margin: 0px;
	font-family: "Lucida Sans Unicode", "Trebuchet MS", "Lucida Grande", Helvetica, sans-serif;
	font-size: 12px;
	font-weight: lighter;
	color: #363034;
	text-decoration: none;
	line-height: 18px;
	padding: 0px;
}
#all {
	width: 960px;
	position: relative;
	margin: 20px auto 50px auto;
	display: block;
}
p {
	padding: 0px;
	margin:0px;
}
td {
	font-size: 12px;
	vertical-align: top;
}
h2 {
	font-size: 24px;
	font-weight:normal;
	padding-bottom: 25px;
	color: #000;
	display: block;
	margin: 0px;
	line-height: 32px;
}
h1, h2, h3, h4, h5, legend {
	font-family: Arial, Helvetica, sans-serif;
	color: #2e2e2e;
	font-size: 20px;
	font-weight: lighter;
}
#logo {
	text-align: center;
}
#logo h1 {
	font-size: 100px;
	line-height: 1em;
	font-weight: bold;
	letter-spacing: -5px;
	margin: -10px 0 -15px 0;
	padding: 0px;
}
#logo h2 {
	font-size: 16px;
	letter-spacing: -1px;
	margin: 0px;
	padding: 0px;
}
#logo p {
	margin: 0px;
	line-height: 10px;
}
table {
	padding: 10px;
	background-color: #F3F3F3;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	-opera-border-radius:4px;
	-khtml-border-radius:4px;
	border-radius:4px;
	border: 1px solid #CCC;
}
#header {
	margin: 0 0 20px 0;
	width: 960px;
	display: block;
	clear: both;
	height: 110px;
}
#header div {
	display: block;
	float: left;
	margin: 0 10px;
}
#introtext {
	width: 300px;
}
table td {
	border-bottom: 1px solid #CCC;
	padding: 5px 0px;
}
table tr:last-child td {
	border: none;
}
ul {
	margin: 0px;
	padding: 0px;
}
li {
	list-style-type: square;
	margin: 0 0 0 10px;
	padding: 0px;
}
#button {
	background: #003;
	width: 160px;
	height: 80px;
	-moz-border-radius:8px;
	-webkit-border-radius:8px;
	-opera-border-radius:8px;
	-khtml-border-radius:8px;
	border-radius:8px;
	cursor: pointer;
	-moz-box-shadow: 0 2px 5px #999;
	-webkit-box-shadow: 0 2px 5px #999;
	border-bottom: 1px solid #222;
	display: table;
}
#button p {
	width: 160px;
	height:  80px;
	display: table-cell;
	vertical-align: middle;
	text-align: center;
	color: #fff;
	font-size: 18px;
	text-shadow: 0 -1px 1px #333;
	text-transform: uppercase;
}
#button:hover {
	background:#006;
}
form {
	margin: 0 0 0 0;
}
.infotext {
	font-size: 10px;
	color: #333;
}
.saved {
	font-size: 14px;
}
.message {
	border: 1px solid #FC0;
	background: #FF9;
	opacity: 0.9;
	padding: 15px;
	display: none;
	clear: both;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	-opera-border-radius:4px;
	-khtml-border-radius:4px;
	border-radius:4px;
	margin: 10px 0 0 0;
}
.warning {
	color: #F00;
	font-weight: bold;
}
#header div#buttonset {
	float: right;
}
#springboardicon {
	width: 80px;
	height: 80px;
	display: block;
	-webkit-border-radius:8px;
	-opera-border-radius:8px;
	-khtml-border-radius:8px;
	-moz-box-shadow: 0 2px 5px #999;
	-webkit-box-shadow: 0 2px 5px #999;
	cursor: pointer;
}
.iconimg {
	width:100%;
	height:100%;
	-webkit-border-radius:8px;
	-opera-border-radius:8px;
	-khtml-border-radius:8px;
}
.iconsub {
	padding: 5px 0 0 0;
	font-size: 12px;
	text-align: center;
	text-shadow:-1px 2px 7px #5E5E5E;
	width: 80px;
	display: block;
}
.iconshine {
	position:absolute;
}
#distribute {
	width: 100px;
	}
#distribute img {
	margin: 0 0 0 10px;
	width: 80px;
}
#distribute .instructions {
	padding: 5px 0 0 0;
	font-size: 12px;
	text-align: center;
	width: 100px;
	display: block;
}
#distribute a, #distribute a:link , #distribute a:active , #distribute a:visited  {
	color: #333;
	}
 <?php if ($fileproblem) {
?> #button {
 display: none;
}
.settings {
 opacity: 0.5;
}
<?php
}
?>
</style>
<script type="text/javascript" src="includes/js/jquery.js"></script>
<script type="text/javascript">

    $(document).ready(function(){
		
       if ($("#pwprotect").is(":checked")) {
            $(".protectfields").show();
        }
        else {       
            $(".protectfields").hide();
        }
	   
       $("#pwprotect").click(function(){
        if ($("#pwprotect").is(":checked"))
        {
            $(".protectfields").show();
        }
        else
        {       
            $(".protectfields").hide();
        }
      });
	  
	  if ($("#other").is(":checked"))
        {
            $(".otherfields").show();
        }
        else
        {       
            $(".otherfields").hide();
		}
	   $("#ipad, #ipadretina, #iphone4, #iphone5, #iphone, #other").click(function(){
		if ($("#other").is(":checked"))
        {
            $(".otherfields").show();
        }
        else
        {       
            $(".otherfields").hide();
        }
      });
	  
	  $('.message').show('fast');
    
	//make the build button submit settings form
	$('#button').click(function() {
		if ($("#pwprotect").is(":checked") && ($("#root").val().length<5)) {		
		 	var answer  = confirm('You did not enter a root directory with your password settings, this will probably give you trouble. Sure you want to continue?');
				if (answer){
					$('#settingsform').submit();
				}
				else {
					alert("Try the root displayed next to the root directory field")
				}
			}
			else {
				$('#settingsform').submit();
		}
		return false;
	});
	$('#springboardicon').click(function() {
		window.location = "../index.html";
		return false;
	});
});
</script>
<title><?php if ($pagetitle) echo $pagetitle; else echo "Prototype"; ?> || Build TAP Prototype</title>
</head>
<body>
<div id="all">
  <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" id="settingsform">
    <div id="header">
      <div id="logo">
        <h1>TAP</h1>
        <h2>touch application prototyping</h2>
        <p>version: <?php echo $tapversion; ?> </p>
      </div>
      <div id="introtext">
        <p>TAP is a tool for designers to quickly create interactive and realistic prototypes of iOS apps and test them on the device, without any coding. Happy prototyping.</p>
        <p>&nbsp;</p>
        <p>Matthijs Collard, Anders P. Jorgensen</p>
      </div>
      <div id="buttonset">
        <div id="distribute"><img width="80px" height="80px" src="http://chart.apis.google.com/chart?chs=200x200&cht=qr&chld=|1&chl=<?php echo $currentUrl; ?>" /><a class="instructions" href="mailto:?subject=Link%20to%20the%20<?php echo $pagetitle; ?>%20prototype&body=To%20install%20this%20prototype%20open%20the%20link%20<?php echo $currentUrl; ?>%20on your%20<?php echo $prototype; ?>%20and%20follow%20instructions" />mail&nbsp;instructions</a></div>
        <?php if (file_exists ("../icon.png")) {?>
        <div id="springboardicon">
          <?php if ($springboard_icon_effect=="on") { ?>
          <img class="iconshine" src="includes/img/shine.png" width="80px" height="80px" />
          <?php } ?>
          <img class="iconimg" src="../icon.png" /><br />
          <span class="iconsub"><?php echo $pagetitle; ?></span></div>
        <?php } ?>
        <div id="button">
          <p>Build</p>
        </div>
      </div>
    </div>
    <?php if ($message!="") { echo $message; } ?>
    <div class="settings">
      <h1>Settings </h1>
      <table cellpadding="3" cellspacing="0">
        <col width="160" />
        <col width="249" />
        <col width="" />
        <tr>
          <td>Device type</td>
          <td colspan="2"><input name="prototype" type="radio" id="iphone" value="iphone" <?php if (!(strcmp($prototype,"iphone"))||(!strcmp($prototype,""))) {echo "checked=\"checked\"";} ?>>
            iPhone 2G or 3G(S)  (320 * 480 px)<br/>
            <input type="radio" name="prototype" id="iphone4" value="iphone4" <?php if (!(strcmp($prototype,"iphone4"))) {echo "checked=\"checked\"";} ?>>
            iPhone 4 &amp; 4S (Retina display: 640 * 960 px)<br/>
            <input type="radio" name="prototype" id="iphone5" value="iphone5" <?php if (!(strcmp($prototype,"iphone5"))) {echo "checked=\"checked\"";} ?>>
            iPhone 5 (4 inch retina display: 640 * 1136 px)<br/>
            <input type="radio" name="prototype" id="ipad" value="ipad" <?php if (!(strcmp($prototype,"ipad"))) {echo "checked=\"checked\"";} ?>>
            iPad <br/>
            <input type="radio" name="prototype" id="ipadretina" value="ipadretina" <?php if (!(strcmp($prototype,"ipadretina"))) {echo "checked=\"checked\"";} ?>>
            iPad Retina <br/>
            <input type="radio" name="prototype" id="other" value="other" <?php if (!(strcmp($prototype,"other"))) {echo "checked=\"checked\"";} ?>>
            Other (experimental) </td>
        </tr>
        <tr  class="otherfields">
          <td>Device height</td>
          <td><input type="text" name="deviceheight" value="<?php if ($deviceheight) echo $deviceheight; ?>"></td>
          <td class="infotext">Height of the device in pixels</td>
        </tr>
        <tr  class="otherfields">
          <td>Device browser height</td>
          <td><input type="text" name="devicebrowserheight" value="<?php if ($devicebrowserheight) echo $devicebrowserheight; ?>"></td>
          <td class="infotext">Enter the browserheight in pixels, this will trigger the alert. Type nothing if you don't want this.</td>
        </tr>
        <tr  class="otherfields">
          <td>Scale</td>
          <td><input type="text" name="factor" value="<?php if ($factor) echo $factor; else echo "1"; ?>"></td>
          <td class="infotext">Scales your prototype including image maps. For Retina display this is 0.5 by default.</td>
        </tr>
        <tr>
          <td>Pinch behaviour</td>
          <td><select name="userscalable" id="userscalable">
            <option value="no" <?php if (!(strcmp("no", "$userscalable"))) {echo "selected=\"selected\"";} ?>>No: User is not able to zoom in or out</option>
            <option value="yes" <?php if (!(strcmp("yes", "$userscalable"))) {echo "selected=\"selected\"";} ?>>Yes: User is able to zoom in and out</option>
          </select></td>
          <td class="infotext">&nbsp;</td>
        </tr>
        <tr>
          <td>Scrolling behaviour</td>
          <td><select name="scrolltotop" id="scrolltotop">
            <option value="true" <?php if (!(strcmp("true", "$scrolltotop"))) {echo "selected=\"selected\"";} ?>>All pages will scroll to the top before page transition</option>
            <option value="false" <?php if (!(strcmp("false", "$scrolltotop"))) {echo "selected=\"selected\"";} ?>>Pages will not scroll to top before the page transition</option>
          </select></td>
          <td class="infotext">You can have pages in your prototype that are higher than the device height, you can just scroll down the page. If you have a button on the bottom part of this page that has for example a slideleft transition, you have to decide what scrolling behaviour works best for you.</td>
        </tr>
        <tr>
          <td>Message</td>
          <td><input <?php echo $checkalert; ?> name="homescreenmessage" type="checkbox" id="homescreenmessage"  value="on">Show &quot;add to homescreen&quot; message</td>
          <td class="infotext">If you uncheck this box, the user does not get the alert to add the application to the homescreen. Uncheck if you want to make a prototype forwebapps that run in the browser.</td>
        </tr>
        <tr  class="appfields">
          <td>App name</td>
          <td><input type="text" name="pagetitle" value="<?php if ($pagetitle) echo $pagetitle; else echo "Prototype"; ?>"></td>
          <td class="infotext">Is shown under the app icon on the Home Screen. Max is around 10 characters.</td>
        </tr>
        <tr>
          <td>Start page</td>
          <td title="Start page"><input type="text" name="startpage" value="<?php if ($startpage) echo $startpage; else echo "index"; ?>"></td>
          <td class="infotext">The first page displayed when loading the prototype (leave the .htm out). It should correspond exactly to the name your used in Fireworks. Do not use special characters or spaces in your page names!</td>
        </tr>
        <tr  class="mobilefields">
          <td>Addres bar</td>
          <td><input <?php if (!(strcmp($hide_address,"on"))) {echo "checked=\"checked\"";} ?> name="hide_address" type="checkbox" id="hide_address" value="on">
            Hide Safari address bar</td>
          <td class="infotext">iOS only, experimental. Hides the Safari address bar when viewing the page as a mobile website.</td>
        </tr>
        <tr  class="appfields">
          <td>Show a device frame</td>
          <td><select name="deviceframe" id="deviceframe">
              <option value="none" <?php if (!(strcmp("none", "$deviceframe"))) {echo "selected=\"selected\"";} ?>>none</option>
              <option value="iphone4" <?php if (!(strcmp("iphone4", "$deviceframe"))) {echo "selected=\"selected\"";} ?>>iPhone 4</option>
              <option value="iphone5" <?php if (!(strcmp("iphone5", "$deviceframe"))) {echo "selected=\"selected\"";} ?>>iPhone 5</option>
            </select></td>
          <td class="infotext">Will be only visible if browser is larger than the prototype</td>
        </tr>
        <tr>
          <td>Background color</td>
          <td><input type="text" name="bgcolor" value="<?php if ($bgcolor) echo $bgcolor; else echo "#0000000"; ?>"></td>
          <td class="infotext">Color of the background (body). Visible in browsers larger than the prototype and seen when using transistions like &quot;flip&quot;. Default: #0000000 (black). Use #FFFFFF for white.</td>
        </tr>
        <tr  class="appfields">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="infotext">&nbsp;</td>
        </tr>
        <tr  class="appfields">
          <td><p>Caching</p></td>
          <td><input <?php if (!(strcmp($caching,"on"))) {echo "checked=\"checked\"";} ?> name="caching" type="checkbox" id="caching" value="on">
            Turn caching on</td>
          <td class="infotext"><p>Cache your prototype for offline use and instant loading. Caching means that the prototype is stored locally on your iPhone. To clear it from cache, go to your iPhone's Settings &gt; Safari &gt; Clear Cache. </p>
            <p>You might want to wait with caching until you are ready to show your design to the world. Or simply name new versions of your design differently, to avoid clearing the cache all the time.</p></td>
        </tr>
        <tr  class="appfields">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="infotext">&nbsp;</td>
        </tr>
        <tr  class="appfields">
          <td>Home Screen icon</td>
          <td><input type="file" name="icon" id="icon"></td>
          <td class="infotext">Upload a square PNG image that makes you prototype shine on the Home Screen!<br/>
            Make it 114 x 114 px to make it look good on all devices.
            <?php if (file_exists ("../icon.png")) {?>
            <br/>
            <img src="../icon.png" height="30px" width="30px" />
            <?php } ?></td>
        </tr>
        <tr  class="appfields">
          <td>Icon shine effect</td>
          <td title="Adds effect to homescreen icon"><input <?php if (!(strcmp($springboard_icon_effect,"on"))) {echo "checked=\"checked\"";} ?> name="spi_effect" type="checkbox" id="spi_effect" value="on">
            Show effect</td>
          <td class="infotext">Do you want to add the default reflective shine to your icon?</td>
        </tr>
        <tr  class="appfields">
          <td>Portrait Startup Image</td>
          <td><input type="file" name="startup" id="startup"></td>
          <td class="infotext">Screen that appears while loading the app. For iPhone: 320 by 460 pixels. For iPad 768 by 1004 pixels. If you want this in Retina, multiply by 2
            <?php if (file_exists ("../startup_image_l.png")) {?>
            <br/>
            <img src="../startup_image.png" alt="" width="60px" height="80px" />
            <?php } ?></td>
        </tr>
        <tr  class="appfields">
          <td>Landscape Startup Image</td>
          <td><input type="file" name="startup_l" id="startup_l"></td>
          <td class="infotext">Screen that appears while loading the app. For iPhone: 300 by 480 pixels. For iPad 748 by 1024 pixels. If you want this in Retina, multiply by 2
            <?php if (file_exists ("../startup_image_l.png")) {?>
            <br/>
            <img src="../startup_image_l.png" height="80px" width="60px" />
            <?php } ?></td>
        </tr>
        <tr>
          <td>Password protection</td>
          <td><input name="pwprotect" type="checkbox" id="pwprotect" value="on" <?php if (!(strcmp($pwprotect,"on"))) {echo "checked=\"checked\"";} ?>>
            Password protect your prototype</td>
          <td class="infotext">Make sure that only people you approve can see your precious prototype. This method uses .htaccess, and requires internet connection. So no combination with the cache function possible!</td>
        </tr>
        <tr class="protectfields">
          <td>Root directory</td>
          <td><input type="text" id="root" name="root" value="<?php echo $root; ?>"></td>
          <td class="infotext">You need to enter your root to your iprototype directory to make this work. If you do not know this, don't try it out! You will not have access. Just a guess: <b><?php echo str_replace("build", "", getcwd()); ?></b></td>
        </tr>
        <tr class="protectfields">
          <td>Login message</td>
          <td title="If asked for a password, what is the message"><input type="text" name="loginmessage" value="<?php echo $loginmessage; ?>"></td>
          <td class="infotext">Just a message to explain the user what is happening</td>
        </tr>
        <tr class="protectfields">
          <td>Username</td>
          <td title="Username"><input name="username" type="text" id="username" value="<?php echo $username; ?>"></td>
          <td class="infotext">&nbsp;</td>
        </tr>
        <tr class="protectfields">
          <td>Password</td>
          <td title="Password"><input name="password" type="text" id="password" value="<?php echo $password;; ?>"></td>
          <td class="infotext"></td>
        </tr>
      </table>
      <input type="hidden" name="donow" value="checkForm">
      <input type="hidden" name="buildnr" id="buildnr" value="<?php echo $buildnr; ?>">
    </div>
  </form>
</div>
</body>
</html>