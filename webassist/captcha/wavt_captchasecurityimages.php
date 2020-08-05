<?php
session_start();
class WA_Captcha {
   function randomString($numChars) {
      $useChars = '2345679bcdhkmnrstwxzADEFHJKLMNPRSTWXYZ';
      $str = '';
      for ($x = 0; $x < $numChars; $x++) { 
         $str  .= substr($useChars, rand(0, strlen($useChars)-1), 1);
      }
	  return $str;
   }

   function hexToRGB($color) {
      if ($color[0] == '#')
        $color = substr($color, 1);
      if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
      elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
      else
        return false;
      $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
      return array($r, $g, $b);
    }
	
	function addGrid($image,$freq,$width,$height,$color)  {
	  if ($freq != 0)  {
	  $frequency = (101-$freq);
	  // horizontal line
	  $numLines = ceil($height/$frequency);
	  $shift = rand(1,$frequency-1);
      for( $i=0; $i<$numLines; $i++ ) {
         imageline($image, 0, ($frequency*$i)+$shift, $width, ($frequency*$i)+$shift, $color);
      }
	  // vertical line
	  $numLines = ceil($width/$frequency);
	  $shift = rand(1,$frequency-1);
      for( $i=0; $i<$numLines; $i++ ) {
         imageline($image, ($frequency*$i)+$shift, 0, ($frequency*$i)+$shift, $height, $color);
      }
	  // backslash line
	  $numLines = ceil($width/$frequency);
	  $shift = rand(1,$frequency-1);
      for( $i=0; $i<$numLines; $i++ ) {
         imageline($image, ($frequency*$i)+$shift+$frequency, 0, ($frequency*$i)+$shift, $height, $color);
      }
	  // slash line
	  $numLines = ceil($width/$frequency);
	  $shift = rand(1,$frequency-1);
      for( $i=0; $i<$numLines; $i++ ) {
         imageline($image, ($frequency*$i)+$shift, 0, ($frequency*$i)+$shift+$frequency, $height, $color);
      }
	  }
	}
	
	function addNoise($image,$freq,$width,$height,$color)  {
	  if ($freq != 0)  {
	    $frequency = $freq;
	    // random noise
	    $noiseSpots = ($width*$height*($frequency/20))/5;
        for( $i=0; $i<$noiseSpots; $i++ ) {
          imagefilledellipse($image, rand(0,$width), rand(0,$height), 1, 1, $color);
        }
	    // random line
	    $numLines = (($height/60)*sqrt($width*$height)*($frequency/20))/5;
        for( $i=0; $i<$numLines; $i++ ) {
          imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $color);
        }
	  }
	}
 
   function WA_Captcha($width,$height,$characters,$bg_color,$text_color,$noise_color,$font,$field,$transparent,$bgimage,$gridfreq,$grid_color,$gridorder,$noisefreq,$noiseorder,$charheight) {
      $font = realpath($font);
      $font_size = $charheight;
	  if ($bgimage != "")  {
	    $image_size = getimagesize($bgimage);
		if (strpos($bgimage,".gif") > 0)  {
		  $captchaBG = imagecreatefromgif($bgimage);
		}
		else if (strpos($bgimage,".png") > 0)  {
		  $captchaBG = imagecreatefrompng($bgimage);
		}
		else {
	      $captchaBG = imagecreatefromjpeg($bgimage);
		}
	  }
      $captcha = imagecreatetruecolor($width, $height);
	  $ystart=0;
	  
	  
	  $bgColor = $this->hexToRGB($bg_color);
	  $txtColor = $this->hexToRGB($text_color);
	  $noiseColor = $this->hexToRGB($noise_color);
	  $gridColor = $this->hexToRGB($grid_color);
	  
      $background_color = imagecolorallocate($captcha, $bgColor[0], $bgColor[1], $bgColor[2]);
      $text_color = imagecolorallocate($captcha,  $txtColor[0], $txtColor[1], $txtColor[2]);
      $noise_color = imagecolorallocate($captcha,  $noiseColor[0], $noiseColor[1], $noiseColor[2]);
      $grid_color = imagecolorallocate($captcha,  $gridColor[0], $gridColor[1], $gridColor[2]);
	  
	  imagefilledrectangle($captcha,0,0,$width,$height,$background_color);
	  if ($transparent == 1)  imagecolortransparent($captcha, $background_color);
	  
	  if ($bgimage != "")  {
	    while($ystart<=$height)  {
	      $xstart=0;
	      while($xstart<=$width)  {
            imagecopy($captcha, $captchaBG, $xstart, $ystart, 0, 0, $image_size[0], $image_size[1]);
	        $xstart += $image_size[0];
	      }
		  $ystart += $image_size[1];
	    }
	  }
	  if ($noiseorder != "in front of text")  {
	    $this->addNoise($captcha,$noisefreq,$width,$height,$noise_color);
	  }
	  if ($gridorder != "in front of text")  {
	    $this->addGrid($captcha,$gridfreq,$width,$height,$grid_color);
	  }
      $captcha_string = $this->randomString($characters);
	  $y = -1;
	  while ($y < 0 || ($textbox[4] > $width))  {
        $textbox = imagettfbbox($font_size, 0, $font, $captcha_string);
        $y = ($height - $textbox[5])/2;
		if ($y < 0 || ($textbox[4] > $width))  {
		  $font_size = 0.9 * $font_size;
		}
	  }
	  $totWidth = 0;
	  $charWidths = array();
	  
	  for ($charLen=0;$charLen<$characters;$charLen++)  {
	    $tilt = rand(-15,15);
	    $charWidth = imagettfbbox($font_size, $tilt, $font, $captcha_string[$charLen]);
		$tilts[] = $tilt;
		$charWidths[] = $charWidth[4];
		$totWidth += $charWidth[4];
	  }
	  $space = ($width-$totWidth)/($characters+2);
      $x = $space*1.5; 
	  
	  for ($charLen=0;$charLen<$characters;$charLen++)  {
        imagettftext($captcha, $font_size, $tilts[$charLen], $x, $y, $text_color, $font , $captcha_string[$charLen]);
		$x += $charWidths[$charLen]+$space;
	  }
	 
	  if ($noiseorder == "in front of text")  {
	    $this->addNoise($captcha,$noisefreq,$width,$height,$noise_color);
	  }
	  if ($gridorder == "in front of text")  {
	    $this->addGrid($captcha,$gridfreq,$width,$height,$grid_color);
	  }
      header('Content-Type: image/png');
      imagepng($captcha);
      imagedestroy($captcha);
	  
      if (!session_id()) session_start();
      $_SESSION["captcha_".$field] = $captcha_string;
   }
}

$width = isset($_GET['width']) && $_GET['width'] != "" ? $_GET['width'] : '120';
$height = isset($_GET['height']) && $_GET['height'] != "" ? $_GET['height'] : '40';
$characters = isset($_GET['characters']) && $_GET['characters'] > 2 ? $_GET['characters'] : '5';
$bgcolor = isset($_GET['bgcolor']) && $_GET['bgcolor'] != "" ? $_GET['bgcolor'] : 'FFFFFF';
$textcolor = isset($_GET['textcolor']) && $_GET['textcolor']  != "" ? $_GET['textcolor'] : '000000';
$noisecolor = isset($_GET['noisecolor']) && $_GET['noisecolor']  != "" ? $_GET['noisecolor'] : '000000';
$font = isset($_GET['font']) && $_GET['font']  != "" ? $_GET['font'] : 'Fonts/MOM_T___.TTF';
$field = isset($_GET['field']) && $_GET['field']  != "" ? $_GET['field'] : '1';
$transparent = isset($_GET['transparent']) && $_GET['transparent']  != "" ? $_GET['transparent'] : '0';

$bgimage = isset($_GET['bgimage']) && $_GET['bgimage']  != "" ? $_GET['bgimage'] : '';
$gridfreq = isset($_GET['gridfreq']) && $_GET['gridfreq']  != "" ? $_GET['gridfreq'] : '50';
$gridcolor = isset($_GET['gridcolor']) && $_GET['gridcolor']  != "" ? $_GET['gridcolor'] : '000000';
$gridorder = isset($_GET['gridorder']) && $_GET['gridorder']  != "" ? $_GET['gridorder'] : '';
$noisefreq = isset($_GET['noisefreq']) && $_GET['noisefreq']  != "" ? $_GET['noisefreq'] : '50';
$noiseorder = isset($_GET['noiseorder']) && $_GET['noiseorder']  != "" ? $_GET['noiseorder'] : '';
$charheight = isset($_GET['charheight']) && $_GET['charheight']  != "" ? $_GET['charheight'] : $height*0.75;

   /*
   Fonts/MACTYPE.TTF
   Fonts/MODERNA_.TTF
   Fonts/MOM_T___.TTF
   Fonts/monofont.ttf
   */
$captcha = new WA_Captcha($width,$height,$characters,$bgcolor,$textcolor,$noisecolor,$font,$field,$transparent,$bgimage,$gridfreq,$gridcolor,$gridorder,$noisefreq,$noiseorder,$charheight);

?>