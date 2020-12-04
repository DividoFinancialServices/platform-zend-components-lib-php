<?php
class Divido_Resizer
{
	public function __construct($cache_dir = false)
	{
		$this->cache_dir = APPLICATION_PATH."/../data/cache";
		if (!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);
		}
	}
	
	
	function getImage($filename,$name = false,$width = false,$crop = 0)
	{	
		if (is_file($filename)) {
				
			$size = filesize($filename);
			$md5 = md5($filename.$name.$width.$crop);

			$cat1 = substr($md5,0,2);
			$cat2 = substr($md5,2,2);
	
			if (!is_dir($this->cache_dir."/".$cat1)) {
				mkdir($this->cache_dir."/".$cat1);
			}

			if (!is_dir($this->cache_dir."/".$cat1."/".$cat2)) {
				mkdir($this->cache_dir."/".$cat1."/".$cat2);
			}

			if (!$width) {
				$cachename = $filename;
			} else {
				$cachename = $this->cache_dir."/".$cat1."/".$cat2."/".$md5;
			}
			
			$type = "jpg";
			
			if (!is_file($cachename) || filemtime($cachename) < filemtime($filename)) {
				$this->resize($type,$filename,$cachename,$width,$crop);
			}

			session_start(); 
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Pragma: private");
			header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&  (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($img))) {
			  // send the last mod time of the file back
			  header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cachename)).' GMT', true, 304);
			  exit;
			}

			if ($type == "gif" || exif_imagetype($cachename) == IMAGETYPE_GIF) {
				header("Content-Type: image/gif");
			} else if ($type == "png" || exif_imagetype($cachename) == IMAGETYPE_PNG) {
				header("Content-Type: image/png");
			} else {
				header("Content-Type: image/jpeg");
			}
	        header('Content-transfer-encoding: binary'); 
	        header('Content-length: '.filesize($cachename)); 


			print file_get_contents($cachename);
		}
	}
	
	function resize($type,$orgFilename,$newFilename,$newWidth = 0,$crop = 0)
	{
		$newHeight = 0;

		$pathinfoOrg = pathinfo($orgFilename);
		$size = getimagesize($orgFilename);

		$orgWidth = $size[0];
		$orgHeight = $size[1];
				
		if (!$newHeight || !$newWidth) {
			if (!$newWidth) {
				$newWidth = $orgWidth * ($newHeight/$orgHeight);
			}
			if (!$newHeight) {
				$newHeight = $orgHeight * ($newWidth/$orgWidth);
			}
		}
		if (!$crop) {
			if (($newHeight/$newWidth) != ($orgHeight/$orgWidth)) {
				if (($orgHeight*($newWidth/$orgWidth)) > ($orgWidth*($newHeight/$orgHeight))) {
					$newWidth = $orgWidth * ($newHeight/$orgHeight);
					$newHeight = $orgHeight * ($newWidth/$orgWidth);
				} else {
					$newHeight = $orgHeight * ($newWidth/$orgWidth);
					$newWidth = $orgWidth * ($newHeight/$orgHeight);
				}
			}
		}
		
		$orgType = $type;

		if ($orgType == "gif" || exif_imagetype($orgFilename) == IMAGETYPE_GIF) {
			$orgImage = ImageCreateFromGif($orgFilename);
		} else if ($orgType == "png" || exif_imagetype($orgFilename) == IMAGETYPE_PNG) {
			$orgImage = ImageCreateFromPng($orgFilename);
		} else {
			$orgImage = ImageCreateFromJpeg($orgFilename);
		}
		
		if ($crop) {

			$margins = 0;
		
			if ($orgWidth < $orgHeight) {
		   		$tempNewWidth = ($newWidth > $orgWidth) ? $orgWidth:$newWidth;
		   		$tempNewWidth -= $margins;
		   		$tempNewHeight = ($tempNewWidth / $orgWidth) * $orgHeight;

		   		if ($tempNewWidth <= $tempNewHeight) {
		   			$tempNewWidth = $tempNewHeight;
		   		} else {
		   			$tempNewHeight = $tempNewWidth;
		   		}

				$placeX = ($tempNewWidth - $newWidth) / 2;
		   		$placeY = ($tempNewHeight - $newHeight) / 2;

		   	} else if ($orgHeight < $orgWidth) {
		   		$tempNewHeight = ($newHeight > $orgHeight) ? $orgHeight:$newHeight;
		   		$tempNewHeight -= $margins;
		   		$tempNewWidth = ($tempNewHeight / $orgHeight) * $orgWidth;
		   		$placeX = ($newWidth - $tempNewWidth) / 2;
		   		$placeY = ($newHeight - $tempNewHeight) / 2;


		   	} else {
		   		if ($newWidth < $newHeight) {
		   			$tempNewWidth = ($newWidth > $orgWidth) ? $orgWidth:$newWidth;
	   				$tempNewWidth -= $margins;
	   				$tempNewHeight = $tempNewWidth;
	   		 	} else if ($newHeight < $newWidth) {
	   				$tempNewHeight = ($newHeight > $orgHeight) ? $orgHeight:$newHeight;
	   				$tempNewHeight -= $margins;
	   				$tempNewWidth = $tempNewHeight;
	   			 } else {
	   			 	$tempNewHeight = ($newHeight > $orgHeight) ? $orgHeight:$newHeight;
	   			 	$tempNewHeight -= $margins;
	   				$tempNewWidth = ($newWidth > $orgHeight) ? $orgWidth:$newWidth;
	   				$tempNewWidth -= $margins;
	   			 }
	
		   		 $placeX = ($newWidth - $tempNewWidth) / 2;
		   		 $placeY = ($newHeight - $tempNewHeight) / 2;


			}

			

	   		$imgTemp = ImageCreateTruecolor($tempNewWidth,$tempNewHeight);
			   		 				
			$cutX = ImageSX($orgImage);
	   		$cutY = ImageSY($orgImage);
			   		 				
			if ($crop) {
	   			$imgDest = ImageCreateTruecolor($tempNewWidth,$tempNewHeight);
	   		} else {
	   			$imgDest = ImageCreateTruecolor($newWidth,$newHeight);
	   		}	
									
			$color = ImageColorAllocate($imgDest, "255", "255", "255" ); 
			imagefill($imgDest,0,0,$color);	   		 				

	   		imageCopy($imgTemp, $orgImage, 0, 0, 0, 0, $newWidth,$newHeight);
	
			// $newWidth = $tempNewWidth;
			// $newHeight = $tempNewHeight;
			
			ImageCopyResampled($imgDest,$orgImage,$placeX,$placeY,0,0,$newWidth,$newHeight,$cutX,$cutY);
		
		} else {
			$imgDest = ImageCreateTruecolor($newWidth,$newHeight);
			ImageCopyResampled($imgDest,$orgImage,0,0,0,0,$newWidth,$newHeight,$orgWidth,$orgHeight);
		}
		
		if (strtolower($type) == "jpg") {
			ImageJpeg($imgDest,$newFilename,80);
		} else if (strtolower($type) == "gif") {
			ImageGif($imgDest,$newFilename);
		} else if (strtolower($type) == "png") {
			ImagePng($imgDest,$newFilename);
		}
		
		return 1;
		
	}

}