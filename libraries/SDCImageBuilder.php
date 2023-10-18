<?php /*! Wedding Card Design Builder by Stephanus Dai
  * @author   : Stephanus Bagus Saputra
  *             ( 戴 Dai 偉 Wie 峯 Funk )
  * @email    : wiefunk@stephanusdai.web.id
  * @contact  : http://t.me/wiefunkdai
  * @support  : http://opencollective.com/wiefunkdai
  * @link     : http://www.stephanusdai.web.id
  * Copyright (c) ID 2023 Stephanus Bagus Saputra. All rights reserved.
  * Terms of the following https://stephanusdai.web.id/p/license.html
  */

/*! SDaiLover Library Packages PHP Version by StephanusDai Developer
 * Email   : team@sdailover.web.id
 * Website : http://www.sdailover.web.id
 * (C) ID 2023 Stephanus Dai Developer (sdailover.github.io)
 * All rights reserved.
 *
 * Licensed under the Clause BSD License, Version 3.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://sdailover.github.io/license.html
 *
 * This software is provided by the STEPHANUS DAI DEVELOPER and
 * CONTRIBUTORS "AS IS" and Any Express or IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, the implied warranties of merchantability and
 * fitness for a particular purpose are disclaimed in no event shall the
 * Stephanus Dai Developer or Contributors be liable for any direct,
 * indirect, incidental, special, exemplary, or consequential damages
 * arising in anyway out of the use of this software, even if advised
 * of the possibility of such damage.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'SDCImageAdditional.php');

/**
 * SDCImageBuilder class
 */
class SDCImageBuilder {
  public $lineHeight = 5;

  private $_assetsPath;
  private $_imageSize;
  private $_imageLocation;
  private $_imageExtention;
  private $_imageSavePath;
  private $_defaultImage;
  private $_additionalImages=array();

  public function __construct($image) {
    $this->setDefaultImage($image);
  }

  public function getImageSize() {
    if ($this->_imageSize===null) {
      throw new Exception("Don't have Default Image or can not be null. Please set first!");
    }

    $imageSize = $this->_imageSize;
    $imageWidth = isset($imageSize['width']) ? $imageSize['width'] : 0;
    $imageHeight = isset($imageSize['height']) ? $imageSize['height'] : 0;

    if ($imageWidth <= 0 || $imageHeight <= 0) {
      throw new Exception("Size of Default Image is invalid or can not be 0!");
    }

    return $this->_imageSize;
  }

  public function getAssetPath() {
    if ($this->_assetsPath===null) {
      if (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'assets')) {
        mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'assets');
      }
      $this->_assetsPath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';
    }
    return $this->_assetsPath;
  }

  public function setAssetPath($path) {
    if(($this->_assetsPath=realpath($path))===false || !is_dir($this->_assetsPath)) {
      throw new Exception("Asset path '{$path}' is not a valid directory.");
    }
  }

  public function getFontSize($text, $fontSize=5, $fontName=null) {
    if ($fontName===null) {
      if ($fontSize <= 0 || $fontSize > 5) {
        throw new Exception("Font Size must set 1-5 of font without font style!");
      }
      $textParagraphs = explode(PHP_EOL, $text);
      return array(
        'width' => abs((imagefontwidth($fontSize) * strlen($text)) / count($textParagraphs)),
        'height' => abs(imagefontheight($fontSize) * count($textParagraphs))
      );
    }
    
    $fontFile = $this->getFontFile($fontName);
    $fontBoundBox = imagettfbbox($fontSize, 0, $fontFile, $text);

    return array(
      'width' => abs($fontBoundBox[4] - $fontBoundBox[0]),
      'height' => abs($fontBoundBox[5] - $fontBoundBox[1])
    );
  }

  public function getCharSize($text, $fontSize=5, $fontName=null) {
    $textParagraphs = explode(PHP_EOL, $text);
    $fontBoundSize = $this->getFontSize($text, $fontSize, $fontName);
    $fontWidth = $fontBoundSize['width'];
    $fontHeight = $fontBoundSize['height'];

    if ($fontName===null && ($fontSize <= 0 || $fontSize > 5)) {
      throw new Exception("Font Size must set 1-5 of font without font style!");
    }

    return array(
      'width' => abs(($fontWidth / strlen($text)) * count($textParagraphs)),
      'height' => abs($fontHeight / count($textParagraphs))
    );
  }

  public function getMaxCharLine($text, $fontSize=5, $lineHeight=0, $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];

    if (($maxWidth > $imageWidth) || ($maxWidth < 0)) {
      throw new Exception("Max width text cannot be less than zero and cannot exceed the image.");
    } elseif ($maxWidth === 0) {
      $maxWidth = $imageWidth;
    }

    if (($maxHeight > $imageHeight) || ($maxHeight < 0)) {
      throw new Exception("Max width text cannot be less than zero and cannot exceed the image.");
    } elseif ($maxHeight === 0) {
      $maxHeight = $imageHeight;
    }
    
    $charBoundSize = $this->getCharSize($text, $fontSize, $fontName);
    $charWidth = $charBoundSize['width'];
    $charHeight = $charBoundSize['height'] + $lineHeight;

    $maxWidth = $maxWidth < $charWidth ? $charWidth : $maxWidth;
    $maxHeight = $maxHeight < $charHeight ? $charHeight : $maxHeight;

    return array(
      'x' => floor($maxWidth / $charWidth),
      'y' => floor($maxHeight / $charHeight)
    );
  }

  public function parseWrapText($text, $fontSize=5, $lineHeight=0, $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $maxWidth === 0 ? $imageSize['width'] : $maxWidth;
    $imageHeight = $maxHeight === 0 ? $imageSize['height'] : $maxHeight;

    $partLines = $this->getMaxCharLine($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
    $maxLineChars = $partLines['x'];
    $maxLines = $partLines['y'];
    $textLines = explode(PHP_EOL, wordwrap($text, $maxLineChars, PHP_EOL, false));
    if (count($textLines) > $maxLines) {
        return array_slice($textLines, 0, $maxLines);
    }
    return $textLines;
  }

  public function getHeightWrapText($text, $fontSize=5, $lineHeight=0, $fontName=null, $maxWidth=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $textColor = $this->createImageColor('#000');

    if (($maxWidth > $imageWidth) || ($maxWidth < 0)) {
      throw new Exception("Max width text cannot be less than zero and cannot exceed the image.");
    } elseif ($maxWidth === 0) {
      $maxWidth = $imageWidth;
    }

    $charBoundSize = $this->getCharSize($text, $fontSize, $fontName);
    $charWidth = $charBoundSize['width'];
    $charHeight = $charBoundSize['height'];
    $textLines = $this->parseWrapText($text, $fontSize, $lineHeight, $fontName, $maxWidth);

    return floor(($charHeight + $lineHeight) * count($textLines));
  }

  public function addTextCenter($text, $wordwrap=true, $fontSize=12, $lineHeight=0, $color='#000', $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $maxWidth = $maxWidth === 0 ? $imageWidth : $maxWidth;
    $maxHeight = $maxHeight === 0 ? $imageHeight : $maxHeight;
    $textColor = $this->createImageColor($color);
    $fontFile = $this->getFontFile($fontName);
    $charLineSize = $this->getCharSize($text, $fontSize, $fontName);
    $heightOffset = $fontName !== null ? $charLineSize['height'] : 0;

    if ($wordwrap===false) {
      $textNonWrap = trim(preg_replace('/\s\s+/', ' ', $text));
      $partLines = $this->getMaxCharLine($textNonWrap, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxCharLine = $partLines['x'];
      $textFontSize = $this->getFontSize(substr($textNonWrap, 0, $maxCharLine), $fontSize, $fontName); 
      $x = floor(($imageWidth - $textFontSize['width']) / 2);
      $y = floor(($imageHeight - $textFontSize['height']) / 2);
      if ($fontName !== null) {
        imagettftext($this->_defaultImage, $fontSize, 0, $x, $y, $textColor, $fontFile, substr($textNonWrap, 0, $maxCharLine));      
      } else {
        imagestring($this->_defaultImage, $fontSize, $x, $y, substr($textNonWrap, 0, $maxCharLine), $textColor);
      }
    } else {
      $textLines = $this->parseWrapText($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $partLines = $this->getMaxCharLine($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $textFontSize = $this->getFontSize($text, $fontSize, $fontName); 
      $maxCharLine = $partLines['x'];
      $maxLines = $partLines['y'];
      if (count($textLines) > $maxLines) {
        $textLines = array_slice($textLines, 0, $maxLines);
      } else {
        $heightOffset = floor(($imageHeight - $textFontSize['height']) / 2);
        if ($fontName !== null) {
          $heightOffset += floor(($charLineSize['height'] / 2) + 25);
        }
      }
      for ($i=0;$i<count($textLines);$i++) {
        $textLine = trim(preg_replace('/\s\s+/', ' ', $textLines[$i]));
        $textFontLineSize = $this->getFontSize($textLine, $fontSize, $fontName);
        $x = floor((($imageWidth - $textFontLineSize['width']) / 2) - 5);
        if ($fontName !== null) {
          imagettftext($this->_defaultImage, $fontSize, 0, $x, $heightOffset, $textColor, $fontFile, $textLine); 
        } else {
          imagestring($this->_defaultImage, $fontSize, $x, $heightOffset, $textLine, $textColor); 
        }
        $heightOffset +=  $textFontLineSize['height'] + $lineHeight;
      }
    }
    return $this;
  }

  public function addTextHorizontal($text, $wordwrap=true, $topText=0, $fontSize=12, $lineHeight=0, $color='#000', $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $maxWidth = $maxWidth === 0 ? $imageWidth : $maxWidth;
    $maxHeight = $maxHeight === 0 ? $imageHeight : $maxHeight;
    $textColor = $this->createImageColor($color);
    $fontFile = $this->getFontFile($fontName);
    $charLineSize = $this->getCharSize($text, $fontSize, $fontName);
    $heightOffset = $fontName !== null ? $charLineSize['height'] : 0;

    if ($wordwrap===false) {
      $textNonWrap = trim(preg_replace('/\s\s+/', ' ', $text));
      $partLines = $this->getMaxCharLine($textNonWrap, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxCharLine = $partLines['x'];
      $textFontSize = $this->getFontSize(substr($textNonWrap, 0, $maxCharLine), $fontSize, $fontName); 
      $x = floor(($imageWidth - $textFontSize['width']) / 2);
      if ($fontName !== null) {
        imagettftext($this->_defaultImage, $fontSize, 0, $x, $topText + $heightOffset, $textColor, $fontFile, substr($textNonWrap, 0, $maxCharLine));      
      } else {
        imagestring($this->_defaultImage, $fontSize, $x, $topText, substr($textNonWrap, 0, $maxCharLine), $textColor);
      }
    } else {
      $textLines = $this->parseWrapText($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $partLines = $this->getMaxCharLine($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxLines = $partLines['y'];
      if (count($textLines) > $maxLines) {
        $textLines = array_slice($textLines, 0, $maxLines);
      }
      
      for ($i=0;$i<count($textLines);$i++) {
        $textLine = trim(preg_replace('/\s\s+/', ' ', $textLines[$i]));
        $textFontSize = $this->getFontSize($textLine, $fontSize, $fontName);
        $x = floor((($imageWidth - $textFontSize['width']) / 2) - 5);
        $y = floor($topText + $heightOffset);          
        if ($fontName !== null) {
          imagettftext($this->_defaultImage, $fontSize, 0, $x, $y, $textColor, $fontFile, $textLine); 
        } else {
          imagestring($this->_defaultImage, $fontSize, $x, $y, $textLine, $textColor); 
        }
        $heightOffset +=  $textFontSize['height'] + $lineHeight;
      }
    }
    return $this;
  }

  public function addTextVertical($text, $wordwrap=true, $startText=0, $fontSize=12, $lineHeight=0, $color='#000', $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $maxWidth = $maxWidth === 0 ? $imageWidth : $maxWidth;
    $maxHeight = $maxHeight === 0 ? $imageHeight : $maxHeight;
    $textColor = $this->createImageColor($color);
    $fontFile = $this->getFontFile($fontName);
    $charLineSize = $this->getCharSize($text, $fontSize, $fontName);

    if ($wordwrap===false) {
      $textNonWrap = trim(preg_replace('/\s\s+/', ' ', $text));
      $partLines = $this->getMaxCharLine($textNonWrap, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxCharLine = $partLines['x'];
      $textFontSize = $this->getFontSize(substr($textNonWrap, 0, $maxCharLine), $fontSize, $fontName); 
      $heightOffset = floor(($imageHeight - $textFontSize['height']) / 2);
      if ($fontName !== null) {
        imagettftext($this->_defaultImage, $fontSize, 0, $startText, $heightOffset, $textColor, $fontFile, substr($textNonWrap, 0, $maxCharLine));      
      } else {
        imagestring($this->_defaultImage, $fontSize, $startText, $heightOffset, substr($textNonWrap, 0, $maxCharLine), $textColor);
      }
    } else {
      $textLines = $this->parseWrapText($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);  
      $partLines = $this->getMaxCharLine($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $textFontSize = $this->getFontSize($text, $fontSize, $fontName); 
      $maxCharLine = $partLines['x'];
      $maxLines = $partLines['y'];
      if (count($textLines) > $maxLines) {
        $textLines = array_slice($textLines, 0, $maxLines);
        $heightOffset = $charLineSize['height'];
      } else {
        $heightOffset = floor(($imageHeight - $textFontSize['height']) / 2);
      }
      for ($i=0;$i<count($textLines);$i++) {
        $textLine = trim(preg_replace('/\s\s+/', ' ', $textLines[$i]));
        $textFontLineSize = $this->getFontSize($textLine, $fontSize, $fontName);
        if ($fontName !== null) {
          imagettftext($this->_defaultImage, $fontSize, 0, $startText, $heightOffset, $textColor, $fontFile, $textLine); 
        } else {
          imagestring($this->_defaultImage, $fontSize, $startText, $heightOffset, $textLine, $textColor); 
        }
        $heightOffset +=  $textFontLineSize['height'] + $lineHeight;
      }
    }
    return $this;
  }

  public function addText($text, $wordwrap=true, $posX=0, $posY=0, $fontSize=12, $lineHeight=0, $color='#000', $fontName=null, $maxWidth=0, $maxHeight=0) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $maxWidth = $maxWidth === 0 ? $imageWidth : $maxWidth;
    $maxHeight = $maxHeight === 0 ? $imageHeight : $maxHeight;
    $textColor = $this->createImageColor($color);
    $fontFile = $this->getFontFile($fontName);
    $charLineSize = $this->getCharSize($text, $fontSize, $fontName);
    $heightOffset = $charLineSize['height'];

    if ($wordwrap===false) {
      $textNonWrap = trim(preg_replace('/\s\s+/', ' ', $text));
      $partLines = $this->getMaxCharLine($textNonWrap, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxCharLine = $partLines['x'];
      $textFontSize = $this->getFontSize(substr($textNonWrap, 0, $maxCharLine), $fontSize, $fontName); 
      if ($fontName !== null) {
        imagettftext($this->_defaultImage, $fontSize, 0, $posX, $posY + $textFontSize['height'], $textColor, $fontFile, substr($textNonWrap, 0, $maxCharLine));      
      } else {
        imagestring($this->_defaultImage, $fontSize, $posX, $posY, substr($textNonWrap, 0, $maxCharLine), $textColor);
      }
    } else {
      $textLines = $this->parseWrapText($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $partLines = $this->getMaxCharLine($text, $fontSize, $lineHeight, $fontName, $maxWidth, $maxHeight);
      $maxCharLine = $partLines['x'];
      $maxLines = $partLines['y'];
      if (count($textLines) > $maxLines) {
        $textLines = array_slice($textLines, 0, $maxLines);
      }
      for ($i=0;$i<count($textLines);$i++) {
        $textLine = trim(preg_replace('/\s\s+/', ' ', $textLines[$i]));
        $textFontLineSize = $this->getFontSize($textLine, $fontSize, $fontName);
        if ($fontName !== null) {
          imagettftext($this->_defaultImage, $fontSize, 0, $posX, $posY + $heightOffset, $textColor, $fontFile, $textLine); 
        } else {
          imagestring($this->_defaultImage, $fontSize, $posX, $posY + $heightOffset, $textLine, $textColor); 
        }
        $heightOffset +=  $textFontLineSize['height'] + $lineHeight;
      }
    }
    return $this;
  }

  public function setCropResize($width, $height) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    $imageResized = $this->createLayoutDocument($imageWidth, $imageHeight);
    imagecopyresampled($imageResized, $this->_defaultImage, 0, 0, 0, 0, $width, $height, $width, $height);
    imagesavealpha($imageResized, true);
    $this->_defaultImage = $imageResized;
    return $this;
}

public function setStretchResize($width, $height) {
  $imageSize = $this->getImageSize();
  $imageWidth = $imageSize['width'];
  $imageHeight = $imageSize['height'];
  $imageResized = $this->createLayoutDocument($imageWidth, $imageHeight);
  imagecopyresampled($imageResized, $this->_defaultImage, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);
  imagesavealpha($imageResized, true);
  $this->_defaultImage = $imageResized;
  return $this;
}

  public function setResize($width, $height) {
    $imageSize = $this->getImageSize();
    $imageWidth = $imageSize['width'];
    $imageHeight = $imageSize['height'];
    if ($imageWidth > $imageHeight) {
        $ratio = $height / $imageHeight;
        $resizeWidth = $imageWidth * $ratio;
        $resizeHeight = $height;
    } else {
        $ratio = $width / $imageWidth;
        $resizeHeight = $imageHeight * $ratio;
        $resizeWidth = $width;
    }
    $imageResized = $this->createLayoutDocument($imageWidth, $imageHeight);
    imagecopyresampled($imageResized, $this->_defaultImage, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
    imagesavealpha($imageResized, true);
    $this->_defaultImage = $imageResized;
    return $this;
}

  public function getFontFile($fontName=null) {
    $fontFilePath =  $fontName;
    if ($fontName!==null) {
      if (strpos($fontName, '/')===false) {
        $fontPath = $this->getAssetPath() . DIRECTORY_SEPARATOR . 'fonts';
        $fontFilePath = $fontPath . DIRECTORY_SEPARATOR . $fontName . '.ttf';
      }
      if (!file_exists($fontFilePath)) {
        throw new Exception("Font file on path '{$fontFilePath}' is not a available exists.");
      }
    }
    return $fontFilePath;
  }

  public function createImageColor($color) {
    $rgbcolor = array('r' => 0, 'g'=>0, 'b'=>0, 'a'=>false);
    if (strpos($color, '#') === 0) {
      $rgbcolor = $this->getRgbColorfromHexColor($color);
    } else {
      if (is_array($color)) {
        $rgbcolor['r'] = isset($color[0]) ? $color[0] : 0;
        $rgbcolor['g'] = isset($color[1]) ? $color[1] : 0;
        $rgbcolor['b'] = isset($color[2]) ? $color[2] : 0;
        $rgbcolor['a'] = false;
      }
    }
    return imagecolorallocate($this->_defaultImage, $rgbcolor['r'], $rgbcolor['g'], $rgbcolor['b']);
  }

  public function getRgbColorfromHexColor($hex, $alpha=false) {
    $rgba = array();
    $hex = str_replace('#', '', $hex);
    $length = strlen($hex);
    $rgba['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
    $rgba['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
    $rgba['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

    if ($alpha) {
      $rgba['a'] = $alpha;
    }
    return $rgba;
  }
  public function download($type='jpg', $fileName=null) {
    $this->checkInvalidExtOutput($type);
    $fileName = $fileName===null ? time() : $fileName . '.'.$type;
    header('Content-Description: File Transfer');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
    header('Pragma: public');
    $this->output($type);
  }

  public function getSaveFile() {
    return $this->_imageSavePath;
  }

  public function clear() {
    if ($this->_imageSavePath!==null) {
      if (file_exists($this->_imageSavePath)) {
        unlink($this->_imageSavePath);
      }
    } else {
      $cacheDirPath = $this->getAssetPath() . '/caches/';
      if (is_dir($cacheDirPath)) {
        if ($openCacheDir = opendir($cacheDirPath)) {
            while (($fileCache = readdir($openCacheDir)) !== false) {
              if ($fileCache == '.' || $fileCache == '..') {
                  continue;
              }
              unlink($cacheDirPath . $fileCache);
            }
            closedir($openCacheDir);
        }
    }
    }
  }

  public function save($type='jpg', $fileName=null, $path=null) {
    $this->checkInvalidExtOutput($type);
    $fileName = $fileName===null ? time().'.'.$type : $fileName;
    if (strpos($fileName, '/') > 0) {
      $savePath = $fileName;
    } else {
      if ($path===null) {
        $savePath = $this->getAssetPath() . '/caches/' . $fileName;
      } else {
        $savePath = $path . DIRECTORY_SEPARATOR . $fileName;
      }
    }
    $this->_imageSavePath = $savePath;
    $this->outputInternal($type='jpg', $savePath, false);
  }

  public function output($type='jpg', $return=false) {
    if ($return!==false) {
      return $this->outputInternal($type='jpg', null, $return);
    }
    $this->outputInternal($type='jpg', null, $return);
  }

  private function outputInternal($type='jpg', $path=null, $return=false) {
    $this->render();
    switch ($type) {
      case 'bmp':
        try {
          if ($return===false) {
            if ($path!==null) {
              imagebmp($this->_defaultImage, $path);              
            } else {
              header('Content-type: image/bmp');
              imagebmp($this->_defaultImage);  
            }
            imagedestroy($this->_defaultImage);
          } else {
            ob_start();
            imagebmp($this->_defaultImage);
            $imgBin = ob_get_clean();
            $imgData = base64_encode($imgBin);
            return 'data:image/bmp;base64,' . $imgData;
            imagedestroy($imgData);
          }
        } catch(Exception $e) {
          throw new Exception('output Error: ' . $e->getMessage());
        }
      break;
      case 'gif':
        try {
          if ($return===false) {
            if ($path!==null) {
              imagegif($this->_defaultImage, $path);              
            } else {
              header('Content-type: image/gif');
              imagegif($this->_defaultImage);
            }
            imagedestroy($this->_defaultImage);
          } else {
            ob_start();
            imagegif($this->_defaultImage);
            $imgBin = ob_get_clean();
            $imgData = base64_encode($imgBin);
            return 'data:image/gif;base64,' . $imgData;
            imagedestroy($imgData);
          }
        } catch(Exception $e) {
          throw new Exception('output Error: ' . $e->getMessage());
        }
      break;
      case 'png':
        try {
          if ($return===false) {
            if ($path!==null) {
              imagepng($this->_defaultImage, $path);              
            } else {
              header('Content-type: image/png');
              imagepng($this->_defaultImage);
            }
            imagedestroy($this->_defaultImage);
          } else {
            ob_start();
            imagepng($this->_defaultImage);
            $imgBin = ob_get_clean();
            $imgData = base64_encode($imgBin);
            return 'data:image/png;base64,' . $imgData;
            imagedestroy($imgData);
          }
        } catch(Exception $e) {
          throw new Exception('output Error: ' . $e->getMessage());
        }
      break;
      default:
        try {
          if ($return===false) {
            if ($path!==null) {
              imagejpeg($this->_defaultImage, $path);         
            } else {
              header('Content-type: image/jpeg');
              imagejpeg($this->_defaultImage);
            }
            imagedestroy($this->_defaultImage);
          } else {
            ob_start();
            imagejpeg($this->_defaultImage);
            $imgBin = ob_get_clean();
            $imgData = base64_encode($imgBin);
            return 'data:image/jpeg;base64,' . $imgData;
            imagedestroy($imgData);
          }
        } catch(Exception $e) {
          throw new Exception('output Error: ' . $e->getMessage());
        }
      break;
    }
  }

  public function getImageLocation() {
      return $this->_imageLocation;
  }

  public function setLocation($x, $y) {
      $this->_imageLocation = array(
          'x' => $x,
          'y' => $y
      );
      return $this;
  }

  public function addImage($image) {
    return $this->_additionalImages[count($this->_additionalImages)] = new SDCImageBuilder($image);
  }

  public function getImageStream() {
    return $this->_defaultImage;
  }

  public function getImageExt() {
    return $this->_imageExtention;
  }

  public function render() {
    if ($this->_additionalImages!==null && !empty($this->_additionalImages)) {
      foreach($this->_additionalImages as $additionalImage) {
        $imageSize = $additionalImage->getImageSize();
        $width = $imageSize['width'];
        $height = $imageSize['height'];
        $mainImageSize = $this->getImageSize();
        $mainWidth = $mainImageSize['width'];
        $mainHeight = $mainImageSize['height'];
        $imageResized = $additionalImage->getImageStream();
        if (($pos=$additionalImage->getImageLocation()) && $pos!==null) {
          imagecopyresampled($this->_defaultImage, $imageResized, $pos['x'], $pos['y'], 0, 0, $width, $height, $width, $height);
        } else {
          imagecopyresampled($this->_defaultImage, $imageResized, 0, 0, 0, 0, $width, $height, $width, $height);
        }        
        imagesavealpha($this->_defaultImage, true);
      }
    }
  }

  public function addCopyrightLogo() {
    $imageSize = $this->getImageSize();
    $copyrightImage = @imagecreatefromstring(base64_decode($this->_copyrightLogo));
    $width = imagesx($copyrightImage);
    $height = imagesy($copyrightImage);
    $x = floor(($imageSize['width'] - $width) / 2);
    $y = floor(($imageSize['height'] - $height) - 25);
    imagecopyresampled($this->_defaultImage, $copyrightImage, $x, $y, 0, 0, $width, $height, $width, $height);
  }

  public function addCopyrightText() {
    $copyrightText = 'Copyright (c) ID ' . date('Y') . ' StephanusDai Developer';
    $imageSize = $this->getImageSize();
    $textSize = $this->getFontSize($copyrightText);
    $textColor = $this->createImageColor('#000');
    $fontSize = 5;
    $x = floor(($imageSize['width'] - $textSize['width']) / 2);
    $y = floor(($imageSize['height'] - $textSize['height']) - 25);
    imagestring($this->_defaultImage, $fontSize, $x, $y, $copyrightText, $textColor); 
  }

  protected function createLayoutDocument($width, $height) {
    $documentImage = imagecreatetruecolor($width, $height);
    $transparent = imagecolorallocatealpha($documentImage, 0, 0, 0, 127);
    imagealphablending($documentImage, true);
    imagefill($documentImage, 0, 0, $transparent);
    return $documentImage;
  }

  protected function setDefaultImage($image) {
    $imageType = @exif_imagetype($image);
    if (!$imageType) {
      $image = base64_decode($image);
      $imageValid = @imagecreatefromstring($image);
      if (!$imageValid) {
        throw new Exception('Unknown type of default image!');
      } else {
        $fileInfo = finfo_open();
        $imageType = finfo_buffer($fileInfo, $image, FILEINFO_MIME_TYPE);
      }
    }

    try {
      switch ($imageType) {
        case IMAGETYPE_JPEG:
          $this->_imageExtention = 'jpg';
          $backgroundImage = @imagecreatefromjpeg($image);
        break;
        case IMAGETYPE_PNG:
          $this->_imageExtention = 'png';
          $backgroundImage = @imagecreatefrompng($image);
        break;
        case IMAGETYPE_BMP:
          $this->_imageExtention = 'bmp';
          $backgroundImage = @imagecreatefrombmp($image);
        break;
        case IMAGETYPE_GIF:
          $this->_imageExtention = 'gif';
          $backgroundImage = @imagecreatefromgif($image);
        break;
        default:
          $this->_imageExtention = 'jpg';
          $backgroundImage = @imagecreatefromstring($image);
        break;
      }

      $imageWidth = imagesx($backgroundImage);
      $imageHeight = imagesy($backgroundImage);
      $this->setImageSize($imageWidth, $imageHeight);      
      $this->_defaultImage = $this->createLayoutDocument($imageWidth, $imageHeight);
      imagecopyresampled($this->_defaultImage, $backgroundImage, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight); 
      imagesavealpha($this->_defaultImage, true);
    } catch(Exception $e) {
      throw new Exception('setDefaultImage Error: ' . $e->getMessage());
    }
  }

  private function checkInvalidExtOutput($type) {
      $types = ['jpg', 'bmp', 'gif', 'png'];
      if (!in_array($type, $types)) {
          throw new Exception('Extention file for output or export image is invalid!');
      }
  }

  private function setImageSize($width, $height) {
    $this->_imageSize = array(
        'width' => $width,
        'height' => $height
    );
  }

  private $_copyrightLogo = 'iVBORw0KGgoAAAANSUhEUgAAAVQAAABkCAYAAADZn8isAAAKN2lDQ1BzUkdCIElFQzYxOTY2LTIuMQAAeJydlndUU9kWh8+9N71Q'
                          . 'khCKlNBraFICSA29SJEuKjEJEErAkAAiNkRUcERRkaYIMijggKNDkbEiioUBUbHrBBlE1HFwFBuWSWStGd+8ee/Nm98f935rn73P'
                          . '3Wfvfda6AJD8gwXCTFgJgAyhWBTh58WIjYtnYAcBDPAAA2wA4HCzs0IW+EYCmQJ82IxsmRP4F726DiD5+yrTP4zBAP+flLlZIjEA'
                          . 'UJiM5/L42VwZF8k4PVecJbdPyZi2NE3OMErOIlmCMlaTc/IsW3z2mWUPOfMyhDwZy3PO4mXw5Nwn4405Er6MkWAZF+cI+LkyviZj'
                          . 'g3RJhkDGb+SxGXxONgAoktwu5nNTZGwtY5IoMoIt43kA4EjJX/DSL1jMzxPLD8XOzFouEiSniBkmXFOGjZMTi+HPz03ni8XMMA43'
                          . 'jSPiMdiZGVkc4XIAZs/8WRR5bRmyIjvYODk4MG0tbb4o1H9d/JuS93aWXoR/7hlEH/jD9ld+mQ0AsKZltdn6h21pFQBd6wFQu/2H'
                          . 'zWAvAIqyvnUOfXEeunxeUsTiLGcrq9zcXEsBn2spL+jv+p8Of0NffM9Svt3v5WF485M4knQxQ143bmZ6pkTEyM7icPkM5p+H+B8H'
                          . '/nUeFhH8JL6IL5RFRMumTCBMlrVbyBOIBZlChkD4n5r4D8P+pNm5lona+BHQllgCpSEaQH4eACgqESAJe2Qr0O99C8ZHA/nNi9GZ'
                          . 'mJ37z4L+fVe4TP7IFiR/jmNHRDK4ElHO7Jr8WgI0IABFQAPqQBvoAxPABLbAEbgAD+ADAkEoiARxYDHgghSQAUQgFxSAtaAYlIKt'
                          . 'YCeoBnWgETSDNnAYdIFj4DQ4By6By2AE3AFSMA6egCnwCsxAEISFyBAVUod0IEPIHLKFWJAb5AMFQxFQHJQIJUNCSAIVQOugUqgc'
                          . 'qobqoWboW+godBq6AA1Dt6BRaBL6FXoHIzAJpsFasBFsBbNgTzgIjoQXwcnwMjgfLoK3wJVwA3wQ7oRPw5fgEVgKP4GnEYAQETqi'
                          . 'izARFsJGQpF4JAkRIauQEqQCaUDakB6kH7mKSJGnyFsUBkVFMVBMlAvKHxWF4qKWoVahNqOqUQdQnag+1FXUKGoK9RFNRmuizdHO'
                          . '6AB0LDoZnYsuRlegm9Ad6LPoEfQ4+hUGg6FjjDGOGH9MHCYVswKzGbMb0445hRnGjGGmsVisOtYc64oNxXKwYmwxtgp7EHsSewU7'
                          . 'jn2DI+J0cLY4X1w8TogrxFXgWnAncFdwE7gZvBLeEO+MD8Xz8MvxZfhGfA9+CD+OnyEoE4wJroRIQiphLaGS0EY4S7hLeEEkEvWI'
                          . 'TsRwooC4hlhJPEQ8TxwlviVRSGYkNimBJCFtIe0nnSLdIr0gk8lGZA9yPFlM3kJuJp8h3ye/UaAqWCoEKPAUVivUKHQqXFF4pohX'
                          . 'NFT0VFysmK9YoXhEcUjxqRJeyUiJrcRRWqVUo3RU6YbStDJV2UY5VDlDebNyi/IF5UcULMWI4kPhUYoo+yhnKGNUhKpPZVO51HXU'
                          . 'RupZ6jgNQzOmBdBSaaW0b2iDtCkVioqdSrRKnkqNynEVKR2hG9ED6On0Mvph+nX6O1UtVU9Vvuom1TbVK6qv1eaoeajx1UrU2tVG'
                          . '1N6pM9R91NPUt6l3qd/TQGmYaYRr5Grs0Tir8XQObY7LHO6ckjmH59zWhDXNNCM0V2ju0xzQnNbS1vLTytKq0jqj9VSbru2hnaq9'
                          . 'Q/uE9qQOVcdNR6CzQ+ekzmOGCsOTkc6oZPQxpnQ1df11Jbr1uoO6M3rGelF6hXrtevf0Cfos/ST9Hfq9+lMGOgYhBgUGrQa3DfGG'
                          . 'LMMUw12G/YavjYyNYow2GHUZPTJWMw4wzjduNb5rQjZxN1lm0mByzRRjyjJNM91tetkMNrM3SzGrMRsyh80dzAXmu82HLdAWThZC'
                          . 'iwaLG0wS05OZw2xljlrSLYMtCy27LJ9ZGVjFW22z6rf6aG1vnW7daH3HhmITaFNo02Pzq62ZLde2xvbaXPJc37mr53bPfW5nbse3'
                          . '22N3055qH2K/wb7X/oODo4PIoc1h0tHAMdGx1vEGi8YKY21mnXdCO3k5rXY65vTW2cFZ7HzY+RcXpkuaS4vLo3nG8/jzGueNueq5'
                          . 'clzrXaVuDLdEt71uUnddd457g/sDD30PnkeTx4SnqWeq50HPZ17WXiKvDq/XbGf2SvYpb8Tbz7vEe9CH4hPlU+1z31fPN9m31XfK'
                          . 'z95vhd8pf7R/kP82/xsBWgHcgOaAqUDHwJWBfUGkoAVB1UEPgs2CRcE9IXBIYMj2kLvzDecL53eFgtCA0O2h98KMw5aFfR+OCQ8L'
                          . 'rwl/GGETURDRv4C6YMmClgWvIr0iyyLvRJlESaJ6oxWjE6Kbo1/HeMeUx0hjrWJXxl6K04gTxHXHY+Oj45vipxf6LNy5cDzBPqE4'
                          . '4foi40V5iy4s1licvvj4EsUlnCVHEtGJMYktie85oZwGzvTSgKW1S6e4bO4u7hOeB28Hb5Lvyi/nTyS5JpUnPUp2Td6ePJninlKR'
                          . '8lTAFlQLnqf6p9alvk4LTduf9ik9Jr09A5eRmHFUSBGmCfsytTPzMoezzLOKs6TLnJftXDYlChI1ZUPZi7K7xTTZz9SAxESyXjKa'
                          . '45ZTk/MmNzr3SJ5ynjBvYLnZ8k3LJ/J9879egVrBXdFboFuwtmB0pefK+lXQqqWrelfrry5aPb7Gb82BtYS1aWt/KLQuLC98uS5m'
                          . 'XU+RVtGaorH1futbixWKRcU3NrhsqNuI2ijYOLhp7qaqTR9LeCUXS61LK0rfb+ZuvviVzVeVX33akrRlsMyhbM9WzFbh1uvb3Lcd'
                          . 'KFcuzy8f2x6yvXMHY0fJjpc7l+y8UGFXUbeLsEuyS1oZXNldZVC1tep9dUr1SI1XTXutZu2m2te7ebuv7PHY01anVVda926vYO/N'
                          . 'er/6zgajhop9mH05+x42Rjf2f836urlJo6m06cN+4X7pgYgDfc2Ozc0tmi1lrXCrpHXyYMLBy994f9Pdxmyrb6e3lx4ChySHHn+b'
                          . '+O31w0GHe4+wjrR9Z/hdbQe1o6QT6lzeOdWV0iXtjusePhp4tLfHpafje8vv9x/TPVZzXOV42QnCiaITn07mn5w+lXXq6enk02O9'
                          . 'S3rvnIk9c60vvG/wbNDZ8+d8z53p9+w/ed71/LELzheOXmRd7LrkcKlzwH6g4wf7HzoGHQY7hxyHui87Xe4Znjd84or7ldNXva+e'
                          . 'uxZw7dLI/JHh61HXb95IuCG9ybv56Fb6ree3c27P3FlzF3235J7SvYr7mvcbfjT9sV3qID0+6j068GDBgztj3LEnP2X/9H686CH5'
                          . 'YcWEzkTzI9tHxyZ9Jy8/Xvh4/EnWk5mnxT8r/1z7zOTZd794/DIwFTs1/lz0/NOvm1+ov9j/0u5l73TY9P1XGa9mXpe8UX9z4C3r'
                          . 'bf+7mHcTM7nvse8rP5h+6PkY9PHup4xPn34D94Tz+49wZioAAAAJcEhZcwAALiMAAC4jAXilP3YAACAASURBVHic7F0HXFPX97/Z'
                          . 'kxFG2HsjQxQ3y723dXeorVq1jmq1raMWratTa2tbZ9VWW7d116rg3ltRhrJENoQwAgH+57wkkIQACULH75/v55MPZLz37rvv3u89'
                          . '59wzmNXV1cQII4wwwohXB/OfboARRhhhxP8KjIRqhBFGGNFMMBKqEUYYYUQzwUioRhhhhBHNBCOhGmGEEUY0E4yEaoQRRhjRTDAS'
                          . 'qhFGGGFEM8FIqEYYYYQRzQQjoRphhBFGNBOMhGqEEUYY0UwwEqoRRhhhRDPBSKhGGGGEEc2E/wlCTUtL49na2ral0+mtaTRaK/jI'
                          . 'FV7O8LKClxBebOVPy+ElgVcuvFLh9by6uvphVVXV7fT09JsuLi5l/0DzjTDCiP8R/GcJtby83J/JZA4BAu3j4ODQoVQmZ7/Mk5CE'
                          . '9AISn5ZHnqTkkbTsIpKRW0xKyiqoYwQ8FtPWQsB3EpvaejuJWnk5WhBPB3NiZykkzs7OZUCuV+B1Qi6XH2Kz2XH/8C0aYYQR/zH8'
                          . 'pwhVKpVa8/n814FEJzCYzIAnqfnkxLVnZM/ZOJJf1LhwWV5USf3ucXIuOXX9mcZ3lmY87uhuflG92rlGeTqIVgGx3oXX9uLi4p9N'
                          . 'TExyW+qejDDCiP8d/CcIVSaTeYDEOF8gELyRnCnhbjv+gPwOJNqcuVxzC0vJdwduUS86nUbGdPcPfrNPqy8drU2WwXW2gUT8OYfD'
                          . 'ed5sFzTCCCP+5/CvJtSSkhJbHo8XDWQ64drjDObSrRfJ85eFLX7dqqpq8sufD6mXh4M5f+mEsGltvW3eBmLdBG36FIg9q8UbYYQR'
                          . 'Rvzn8K8k1L179zKGDRs2C8h06b3EbJP3vztDXuRIGz2ORqMRJ7EJcbU1I7aWAmJlyiNCHpuwWXTq+/KKKiItLSc5klLqfMkvJSQ1'
                          . 'S9LgORPTC8jry48QR7EJ+5sZ3af5u1qOq6qqWrxs2bLvPvnkk6pmuWEjjDDifwL/OkIF1dpn+PDh2yUl5e1nf/sXufLwRb2/RQIN'
                          . '8rAmXQIcSDtfOxLkKSY8NiMFvsINpUR4ZYFUWUAUu/sINhwjgr828HKDl39xWYXj3YQscj3uJbn4IJ08SMrWea20rCIyYslBEhHs'
                          . 'ZPbFtK7rlixZMkomk73B4XCSmvP+jTDCiP8u/lWECpLfGywW6/tjV5IEH2w4V6+NFNRwMjzSh/Tr4E7EIv4j+OgYHHu2uLj4Mo1j'
                          . 'mq/+WyTdhlAtl1l29Lfr1DnAodusEW37vcwr9jl6OYnsi3mi07wQezeVdJi6g3zzXvcuPUNdbsN1p9Dp9N1Nv2sjjDDifwX/CkKN'
                          . 'iYlhRkREfCOvqp4+a+1pcu5Ois7fdWrlQN4eEAR/7VFs3VZRUbETCPMxfsdgMIipqSn1u8zMTIGVlVUv+M4bv4JXFhDf07KyskdC'
                          . 'oTBH/ZzKHfwjytf7VqacgEn9A8fDa8KF+2niTUfukWuPMzTagUQ/a91p0qudmylIq7totOrQ6Ojo+UYTgBFG/P/GP06oSH5ApnsK'
                          . 'pLK+gz/eT7ILSur8JshDTD4Y05609bZ5CGS26vbt27+1adOmgs1m1/ltZWVlD0sr698u3E+3SEjPJ5VV1cTSlEvZVb0cRUiGqKIn'
                          . 'w0sGL7nysCQg3APLly+PBVJ8AO8/TEhI+KRLgPuYsEDHBVcfZfh+vvsaefRcg4sp16s+H2ST/cuHzl2yZIlncnLyaGNwgBFG/P/F'
                          . 'P0qoeXl5pmKx+GhypiRs8Ef7Sbm8UuN7MwGHItJhEd7pQIQfgRT4C0qBQKb1nhPU75/eWnnMQluqVMHNzszd1c7cncNikEKpDCRb'
                          . 'GvFztiR9O7rPBFJ8AIQ8H6Td456enki420B63hkeHv7mnk8Hf7bnXJzNl79dJ0Ul5TXnw82tqJm7yJHVwwc7OzsfhQVikI2NTXGz'
                          . 'dJASBQUFZlwu15zWmP3iFQF9XA3XSVb/DBc8MzMzS7g0vaWuK5fLS7Q9J2Bx4toC9GkjIjc31wS0D0vtz+F5yvh8vu7BAPj000/p'
                          . 'H330kSeMGzEsqtkrV66M11fTwOcCWlE/6JtAeMuBpsUXFxfvqc9vGdrSE/4ItD8vLy+/wePx0vS5pqGA9oiZTCZf399DH8hfpS3Y'
                          . 'J3C8SJ/fwn0XNcXHOyMjgw8aqC/0O0cmkyXB2Mms77e3bt1itWoFqq0OrFq1KqW5tcp/jFBxoiKZxqflhw1ZeKCOvTQ8yIl89k54'
                          . 'lZUZ75usrKwlSFJw8w2eEyZm+2JZpfPNJ/X2L3mWUUi91HHhXhrZeOQuXjNg0RudjjmJTX4AKXgmSsGRkZEoxW4G8t8zsqvvyqjW'
                          . 'zu9++FMMTX2zrKxcTnrN/Z0cWTWim6ut+DCQQf/mkFRhAg6AiR7N5JmGZBZXEH29bh3N2CQHfl8m199PF5naWcTBFY0aE9CXXWBh'
                          . 'WWEisgrPlMpp+p7JzpRF2LBIvZBUkIpK/Y4SgKbBr67GRfPb8+fPf4l97uDg0E5eTY99WVSh3UZczTja5xCJRG8Ul1evzy2R13zG'
                          . 'YdKIrQnvBvzbTvv3SKSLFy9+DxbReYm5MsdMiYyITZgE3r8AUvli2bJlaxuabPCbdwQmpl8cflhgei1FSsoqqkg7ZyEZHmSxCp7b'
                          . 'BOi7A9rHwLP8/tSTQk+prFZwwGOczLmFcO+l2r8Hwumky/cZfov3VIckgFw6wGKTovSSmQmEM7u0mu0sLdaPM5iwZDqYcTEk21mv'
                          . 'A3QAFpjpRbKqz/JLKxv9rTmPh/eSDK8fk5KSvlIKMfVC6Ua5ysLaZvT9l6UcmbyK+NrwcexchD5fAAvHRe1jAgICvGgM5sP0wgqN'
                          . 'z53N2WTGjBloIywy8BYbxD9CqEqb6Z7nLyV1yBSFsFkj2pLJA4PToJPGw/sYINNGz4krUUhIyG8f/nCGUVnVtEXn/L1UMnTRS/L5'
                          . 'u1FTu4aEODx69GiEv78/JY5aWFigf9V0KzPuwc3z+27/7sAt2+8P3q45Fn1XB3y4l5z6cmQ3kFR/hQk74lVWP5iwc1MKKr54b/9z'
                          . '8ueTQoOCGBIXh5C3fk0k5xMbdglTBwYzlK5pr7r2yPyy6l/nHEhg7L+fR+R6EiPi7PRWpLObkHT/7hFJytV/TfGz4Tl8MdhlVY+I'
                          . 'iDAYH0O7dOlCbqRKSbfvHtX8hsWgE+nqOtxYg503s8ks6C8V2jgKyOXZAXV+B8RGg7H184WkovGzDjwnDzJqzUz+tnz7tUNdvwJi'
                          . 'bQ+/G1uFD1YL8NnsuKyyr0duiydPszV58JsYgdmhST57rAVkEJDqMe1jZ8P1EnNq+2XbWE8ypo2lmWv0bbMqtWd8fmYAsRNQ9n9d'
                          . 'EHf85oHti8JaTenirABiw6cxcKGAtu+OTSwaMeeg5r01BkdzDklc1Frv39eHDRczyZLjqXr9tpUt32VJb8cVQwI9hkml0r7aexwq'
                          . 'lJWVOXG5vEtrzrxw/OJsBiksVSycOG5hEesCz+yciFc5Cvp8v/ax8dllpPXn9zQ+y/2s/nH0KvhHCBXIdG1+UVnfIR/v1yAKPpdF'
                          . 'vng3ikSFOJ8BVWU0dK5uHyYdaN269eibTzNd69vQ0hcY9z9z7V/k6/e6DezR1u9H+GiC+vfwwP6ElbLNjGFtfvd2sghb8GMMkZUr'
                          . 'Hi7Ovf4L9pGYdWOGwqBeAx/Na0obYLJHvZRWfh65/hF5KSlv/IBmBLqtyaoYP/fc8Jjx6KX+k/FV8TizlAza9IQcmOgzoHdExGLo'
                          . 'g9MtdS2QvkfdSise3/enOFIu11zz8J77wecxM/xHY04H+EjDgwMmtnMFYa0esPEJSSuoK1DdTismr22LZ5yb4b9NIpH4gMSWX+dH'
                          . 'OoCSuPpcqKR4vH4LT7ZUTjLVpHe58vcgdc8/lyAZge2rqPz375E+hP4e9fNT8ll/59B5Xe0OwmIapdQKNQCS+vcLj6U6fnFG040S'
                          . '59yeO7lImsyLs1ptLszLO60Ufv4R/O2ECqv7BHj404aCZKpuMzUVcMhP83qjX+lWULenoLqt7znRTsTh8r5c9cuVZmkjSrjotvXr'
                          . '4gFv+TpbXABJZbP692iTS0hI6NGrnccWSzPe2KlfniLFpQriQ3IdvuggOfHFa3PptOqbcOwuQ68Px6yaezCR9neTKYLFYq1YfCSF'
                          . '+3eSqQpIIlN+TyJxH7f+gE2n3Wv8iKYBtJ6pIOnUIVMVkIhWw/e/v+k1nWgRKpvNHrv1chZbF5mqcDW5iGy9mmU9qaN4Lrxd1Jxt'
                          . 'bwiw2FvBLS2aDH34XyBTdSw8mkIC7fhdeoWHY5+tVv8OFzFZNbP/utiX9R5/J72Y/PGwwHxIgPlYePtDCze3XvythArSjx9M2PUz'
                          . '1/5JstR28zGaafP8PqSVm9VXMCjm6VKzGgIQ3IqNR+5ZP3ymU1toEpAYF/wQQ/YtG/K1vKzkKFxD42mivQfUq9dBEi3Y+EHvaW+v'
                          . 'OVGT1So9p4jM+/4s+XpGtx9lMtk1WF0T9b0uSEVtk/IrOuy/l9ds92IA6EWyyiE/XKzfBt3SyIBF5Jcb2Twgo7da4vxFRUWWbJ4g'
                          . '/NSThkOYT8YVkNKKqi7VFWVi9Q0zIONOZ+MbF4BQLYV7eOfWrVufGiIcvApg7oz/9UaOIDmvQVPkvxZoDrk3P2hhmVTyk7pkD4vY'
                          . 'wD/u5dPqWwBVOHg/jwwNFA0h/x8IVWk33fHHpUR+zJ1a+wqbxSAb5vZCMl0Hg3VulYH2T9zdNTE1H7f5qKZAYy7kkvBgRyq89F5i'
                          . '00Lv0e1q+8mHJpP6By6Ft1O1v0cbKUiTM0A9ZX47q8fkKV+cJHKlZHDy2jNy+maySY+2Lj8D8Uboa0+F8/U7fF8vLbElQDsZV0gD'
                          . 'Ivmnrk8B7bZARj1a4txAjj1iEovoxbKGN01wkyk2sYjWy8e0F7zdqfaV+IUOzYHNpGtIvGg/Pp9UJO4SHNwH3v7RPK1vFP0O3f9H'
                          . 'FuJmAfbZb3dyTca1sZwIb79UfQ680PvPRhZAhGKvgUSlp6fxHB0d62zy/R342wg1PDx8ToFU1vbDH2M0Pv/s7XD0L90Nq+tsQ8kU'
                          . 'IRKJhp29ncpFNygHKxPSJciBCDgsEnM3lYzq6ktKZXKSmV9Mvvr9BsmTGN7HSNRjevhNoFVVfKrL/Qal6b17904bPny4eOmELkMW'
                          . 'bTpf892sdX+Rqz+83mXx4sXT4O16fa4Hgyf4Zpqm1xVuxuwY70n8bXh6tdnWhEW2jvEgJeW1/bnpShZZF1vb/G5eZuSboa5q11X8'
                          . 'xY0gbawb5kaiPE31uraLhWID/tgUXyCYxhWNwjI5mbQrSWNz52Yqdf9cvS5oIKB/o04/rTs534uwI9/Gaj5e/F1vX7OuRJNQOdr3'
                          . '9X6UPQlzNyHDtjzR+Pzwg3wS7m4yiPx9hOqhPXbwefw81pNY8Buf6ixGy3jltXcRkk2jPDQ+w+cefTIdSLBA4/MtV7PI+LZWrxMl'
                          . 'oaLHAsytcO1n5gr3FWDHJ0ce1gofucUVqPpzgu3tOsHbMy1yM43gbyHU0tJSey6XuwQJRt3w/lbfQNK/k8e1xMTEtwxV81WACTLz'
                          . '+NUksnPRAPLXrWTS3s+OWJryCIfNoGy0IPkSK3MeeaN3K/LNnhsGn79AWkaOXEpgvxblg5tTK3T9ZsSIEZUZGRnjhkV4X3rwLCd4'
                          . '919U8BZ1r6j6/zC31zKpVPqbnptsDmkFmhLQ4AARqjJnKioqpuvT5upKObEVwOQQKDaJWSzWZGshc476b0y4DOIj5p6Gc76n9vE6'
                          . 'uHZP9d+1dhCQKZ3F8fC7wXjqxi8uJxWg4Dqa6Oe2ymJxx7zf1W7J1N9rUyLkl8hJC0rJkWcTNFV2MSxAawY6o6mB5Km5XZ2NpyZx'
                          . 'VGMndLPkkP7+5tQkf66mbqPZ4PNBzr2bq+GNAWYQTX3nHzEz3JZ0chV+Ac9vcz2HaaC8vLyCw6njlfZK4LOosXYF2lCzwctgsN33'
                          . 'TvDeHfrlfZN4tcX00jMpbrgFmzDljugPO3jw4OCEnDJzbZv1oAAL0sPbTINQEWfiJSTEURBF/pcJFch0+Z2ELOH1uFoJoJWrFZkz'
                          . 'MrRAJpO91pj/WX2Qy+WdkjOlbVC9dxKbnH+rT8B1IOYToDYvmjakTQRGMmHklYe9SMMZ31AcupBARnb1HUnqIVSEnZ1dCdzL8A/H'
                          . 'drh988lLk/g0xYPG2P/Hybnmfi6WS+GtPoTIK63QVEddLakBfq2pVQTQYb2er4rUzwkLgExdqqWurZA478HvHjfl2o2hsrIy1tm8'
                          . '7gTWbkdzADcv5Qyu3y0tKS7C3ZTQYf2JBCn8gJrt+n5GCckplrubMCudYAzX6weklOuKhwRaCL6JqR3jT7JKkRyczNhVbkBSz+o5'
                          . 'vNlQpmMRcrXk4vOP/RdUoCjWakMch1a14v0ou5Xv7qldTFEIufCsiAwJMA+Ht7tAc408G1/XjIHPCrUCJkjV6m59uFjO7WoX2YL3'
                          . '0SBanFBhxfMGCemN99fXLhgsJp2smBxBmHTaVDqX22Q/J+jsviiVjuzqg4NmGbo0YUx/WVnZU3h4kZ0DHDa9yJGyCotlxMpM72CR'
                          . 'OoDFACRVWTCHUWXXUNQNbj5BO95bOSVy26hPDhOVP+zc786SY2tGvA2Eq0+S6mIBW9P9UOlL2BfO/UTnEcA/sPrfgnMn6HtP9aBI'
                          . 'yNG89qOXlPTQBa79lr4nAY3kmPpGDm5GMpnMIPi3js0CFr9ejzLrehQIOPW5YBK6rraAptKhsXaBxBN19HEh0VaGIhXmjKSunmbu'
                          . 'B7Q2A2MSJWRYoAgn6E7SMM729TMfoE6oiItADoMDzDvCvy1OqDwWnfLjVtcCcewM8Dd/A/qsThQZUQSe5cN8ufJP5PiFcfHLoAAR'
                          . 'EKrm59hm0MiwNhxlojmnpVGg72k4LIICNv1ZqJPQ7crzWt987G95VXXHzIz0f8SO2uKECmS64MqjF4yXebVSwaT+QRhXfwg667dX'
                          . 'PH2ouZCD57oG5/pT9aEyNHG7KZ+VY+pisR2kU0vcNFABB52rrSl5/lKil8M8/gZJNSLIASdGnQgYdQBB/OzvYjnmjd6tem89fp/6'
                          . 'DLNWwfHs1p5i9Eud0cjlUlwsOJ0vqw2SE48LyKJjqcHeYt5WXQcI2XTS2c2E2LKr/ygpKXn7FSbHczdLTWkRbZtT9zyzhfPrvLY2'
                          . '+vqaEwseB6WLLJgwXvD8N2WWVEdcTc4nxTqkTkmpnKz4M13jM2shi3CZde156Pi+/UYOjlmdbbmW3HDOXHjuXWMS6u7QR3iY4jNe'
                          . 'HuVpukX7O/z98CALbTtqHcDxMZ1cTfpwWXSmuqSINukhgSL0IjfYfc5QwLCuchax6eq7/F+ezQCVmz7CnM8cUef38LIUMEmkh6kc'
                          . 'o9Ru37694O/ySECg1M+prk5xs+Q6P1MLAklQBD64K+2nkbFJms8s2J5PzLh0KstcV0/TeeqEWlJeCX1ezG7v9M/YUVuUUEHFsgGJ'
                          . 'btySLRdqPhOLBOSdAcFlIK3NhA591Uu0xc0owEldX2KkSlFRkY+ZUPgeSCU1catIkB4OIsLnsMjD5/q5WuGOf2Swo68+v0W75LtD'
                          . 'Qh4eupjAUm2EYbWBg58NfUsikSxuyNkb2narvbNw9O5bmu36/Ez9eWERuHG1qJfDwA+728fANTrr61CuDpBibnV0Edb5fOvVLOql'
                          . 'DzBSqoMzD8MgXZks9sU5B5Otf7iUWUcqbAgYjglAdtRoDPqpvrNbbw80XYjSJlQbExbxFXPzY2Njd0RERHxuZ8q2zFDbxY9VRJvp'
                          . 'o0JKOUzaNWh7Z/UItdvplCAR8iqNNgCPYOwEqBOqpExOPjhcJ/WBBiz4TOZPo9znDAwJcQKBYGRT9zOaiEQXEUeDUNMVdmCHIUOG'
                          . 'tH6UWWqWVaTJ8bAA4J+z0M4zsAjOW3lac0HGZwzjGJ/Z/xahgor1JkhnHEzOrMK0Ia0Jj8P8mkZjvVJIE0xYd0lppdjTkcrD0B46'
                          . 'dyGQ0bW7d++eU19llckXlopMuAN7tXNroyrOJ5dXkbAgR70JNSu/BCWcOsk6dIHNZsezWNXfTR4YPFsVbPA0NQ8TqQjsLIW4g7mu'
                          . 'vmPlcvkfINGsmf9HskEhn+jI/cnxVGLKYfhOC7PBKK139D5YCVgA/+rqZVYu4jPZ+SV1glUMAvTB9x8fTbX+/kL9ztj1YXiwBf45'
                          . 'Dq/XXqkRasA48HIax/eeViimcnLGYHQOjJ9zMEGH71JbzDCCK1sq9zBlNWxHVeJCFzcTTUJNo64X1Fz30Qj+GBFsGYCRQ4YAN+LG'
                          . 'bE+AxdB/BIy/N+GjbS3Sunoub87TNO8oXdoEQO5R5xPrhtqjiQbm+7mCgoILnVxFcg6TzpSpuayhRLugu/0/YkdtUULF6qRbjt2v'
                          . 'eW9jISBDI7xBaCz6XJW71BCgU7ZQKET3I1sGkxV87XEa5RZ16EJ8b2tzfm/M2h8SEpIIEuIIUDXvqB9bWVk5Y9mksJjkzELW09R8'
                          . 'IjLlEhdbRRs+nRBGtp96QJU7qQ/K8FK9RWqYwKtGdfOdsvHIXR4WAETsOPWQLBjb4S3SAKGi4d6BVX3qzXbWvTZfMVxzX3IilYxt'
                          . 'azWBz6hcYehGCEq1mN4wyJ7vq0s11hcwEQLTCsv7NhTZUh/cLblkdIhlIUyYHaQZCRXIMPL0o4I6Jh6cnEikStvjmUgtQkWchwk6'
                          . 'NFAUAf/+0tA1oM2XOrlqSvjoygMSl4Ulp8qxGTSyBgFkuHtwgGhKK1u+xUMDI91wQcbY/wszWy3GDGu6wj9bCFV0LeuO0sODD88k'
                          . 'MiZRt/20tLQ0xtLSsgiFqI6uws7q4/XycyllR32RlsK1s7Nr+TtQQ4sRKpBaMI3O8N0X87Tms9d7tULVdCO7CeooQiAQDLj0IH30'
                          . '1uMPiKRYRtr62FK20H4dPcizFwUEN74C3a09vp7R7Vhubq4PdrjqWCaTeVlAo43ZuWjAL6t/vcpJBakZ0wOC5Er4XCbZ+EEfsmz7'
                          . 'ZXL2lm71iMdh4R+9jdyYUgwe9tYx3f2mrd9/i/oMCXX+mA4hFRXl3kCcT+s7FibGgs/6O3e9kFTEwp1iQ1BUVkl23shmTA+zwRC8'
                          . 'zww5Fp5ZqxdFVb6xOqQCQwATYejeO3kGhz/ipsrWsR6EQSOfgtZZ/+rWtDZF6rovtJ/CYnsWFgHs97Nddfjbnk8qIsOCLBolVNzc'
                          . 'ae9c12Ry90UJ6eNjGtj01usHIPQiBqP6w81jPH7queERNRYMwfUUKfoAu4cB4O25FmlkXQi1beuc2v2OiPNa9lN04zPh0B/QuDUu'
                          . 'iPDMzDQIFSVc0Aw4bRwcOjTg4dIiaDFCZTAYgx89z62RCNhMBuY1rS4vL1/fVD83GPDXgESr84vKaBfvp5PZI9qSotIKcvxKEuUe'
                          . 'BSo2VV76xLVndn3au46DQ37AlH4Ykgf/B8OkYQi49BvRE8M6ZReW0i8/SKcSTxcWl5MKUBmWvNmZXLyfRsor6g5EZxsTKtWYISlJ'
                          . 'gaDWj+zqN23DwTvUjj+aptAW6+lgjj6dn9d3HErX5oyqCaCCbQO1mXngfl5Ndh19cOxxAZkRbov+pAYRKvRTt1NP8pqjPHfbK8n6'
                          . 'kzJOIHSBgUWEhDjwN0M7voFxEvaqjdBCZKyWtGNnyibe1tyc6OjoB5gaEiTIOCDXDFcLjp26P+mFJP3sqLiI8mGMeFhxXdQzSmHC'
                          . 'lL6+Zq+exkkPwBjfCH0YEDOj1cyPjqQQ3CGXNRKyqY7jMHbaOglw7JxrsUZqwjFDK/KMz6YI1SUuq4yvbT+NUtpPVe/RjtrVy3Th'
                          . '0hOaJ0UiDnXiR8D3+1qm2brRYoQKxNP32JVa/7KoEGdiLuT8BZ832X1EOeCpja1gTzEVtlqYLSXoFoU+rm525pQjP24EwXXeAGII'
                          . 'ySwom7zr9CMSl5JLbWoEulmTiNZOGJ1FBnXxrHS0NmFcfviCqpaKQLMBkqo2MD8rXPsMSjL6An03rVjVF8OCHLqowm1PXX9OvBxD'
                          . '+pIGCBUB1/nFnFsV9+NItyXwioKmm+riuXwg2n4/xoEUVOtFcV9hJ/TXu6FKQJ/533uhqSqid8TVOQHEx1q/KC2GonssnuuIJ/+0'
                          . 'rxOZ3ElMzLhM7WNw1tyC57Ue2rATI+ZgIaxzPG68Fa6sP+3aj5cyKbVVG1Kp1LqSyfOvYz9VSKOxqrBg3IwBnI3yNBu77VqtueV+'
                          . 'RikpLKvyYVWV4SZrvddX4nqok1CDUDHyC+6r/qzoOlACai+dzqrvYryGAh/gWrP8xJzYw2/7vA9v24KioFOCiQPtJ+Lbh0Q9P6uy'
                          . 'j1oZ0tamIi0tjSe2tfd7kqWZ5hEj/QB8XeknIz1NKPspukciMjIyLoc62csEHAZHPZwYtDvyfpQdepv89wlVmTy6LUqLKvTt4IaS'
                          . 'z65XSTr/9OlTNpAidQp/V8qt7pCrnWmYu725pbeTiJpwTCCAed+fI7mS0k6gznc6fy+N0OEAazM+6drGmfJHvfHkJdkf+5R08rdn'
                          . 'DOjsQZ6m5aNUS6JaOxMPe/M6hNol0BEJ9y5c+K6hbcZ77tPBvYZQsU+mDw3p+OjRI7Yq12p9YDKZN+EPSrPkwL69DGtr6zqdFx4e'
                          . '/v7EjtarZ+2vJVRM7QbTyhpzYxqYk9U6U0siCAfJ0d+Gdz42NrabvieJiIh4LJVVeap/NjTIgnzY3f52aWlp/4sXLmmoYSp7nT5j'
                          . 'Ax5xObSlTtZ76Id3Ya3TaZvm8XgRJ59I6ngaKN2lzqlfF+2oUZ6mGoSKEvvFZxJU2xuVmuG3N0OdBCN+u11rh72uCOdFN5567TcY'
                          . '9qke0orZk1rZWKK71n3136Ffd04psULbrDowqTcQTc2HQDhIJBSZXLwQo3OeY07i/v6iIeptVT5/cWP32Ryws7PrceGZlKktQXta'
                          . 'K2zNaGpRB4NOI13cTKpLSkpiTEwUAhD6mkKfXwpzM+mKkWkqoD8qPLbO8Gz/1gRQLXIxS0vL0JKyClaxMvsSE2ZBWJAjdsRhobCu'
                          . 'jUlfYEQV31egawAAIABJREFUdF5mWnaRTfc2LjiAUJpZBGSN0h4Odv6hiwl0JEUfJwuy8cg9sjd6MMnILSY7Tz0kP594QAJAQsWq'
                          . 'qVMGBRNpaQU5fDGBjO5GeUNhY6VcDlOjfIM3nGf1lMgqvI5qVTQEMpnsEBD1epXDNZomZBWVPG9vb3SluarveTC8Vdfn0K4UEU/z'
                          . 'MeJ1ykEsGTduHC71hkShseRapMNnUSJnviGbFHB9WqXWeTBUED7/AQMj4FwGNKkudLWlqoFEECDtR2hPTgTmJ1DZT1WoqKg4qytv'
                          . 'AUo8/fyo6J0Ggdn0QULV+AzV1sRcmYOHJafejNuWfCZRV33XxmSQka0toxlV8huwsF7CzyQSiQiIZNO3p+o6G1gKWKQgJzMPFo86'
                          . '39X37KCtaSK+5phWJnipW6ytBQD9Puvn63VNnP42CsFc234a4oj2U8Y9GlezbIpCqzDVINQC0NwevCwVBNhyQxRT++9BixAqdFTr'
                          . '9JzazkD1XMBl3aDR2M2RX++Ak9h0KkqUfdq77Tbhs/9EKQM+/620vPL7I5cSeBvm9pKb8tny5Tsuc4F8qdh+B2sT8vu5J2RwmBcV'
                          . 'qfUgKYckvSigpE8EnCMaNy6kJeU1WY44bCaWiyYiE854uKcjTWksxiNzq6sfgEQdoEoviEEOzmKTYFIPocIkHwZt0Su0C343Wunr'
                          . 'WANU00FiqWhCSK/EjKs5wS4nS3HXtSePRd9G9InlV6COhONgxsI+fiUn0ldAuPbkdDBjEyC4bFgkH6pzMYfDSbJjVz/3EfNc1TcE'
                          . 'LygIuVFClUqlN9o4mVDSlPqicuZpIfHoJObWVxbGW8zVIFQ0vQzf+tRs4yiPi7YmVMmTQr7ApP3a2JcmX53TjMZys+RiIEQKViKA'
                          . 'exnfWBtVwCxOSreuGpgonn+zlgVRwgOe/zK1936Xn0u7a3tUIMI9TEhSroxo5yVQLnTntH9P2VE9TaO1P0etItCO1+gza060CKHC'
                          . 'g2qlimVHtPGiSpicr/cAw2CLLkzDIrwJqPMM+L8PDNE+qLQ9Ts4ly9+OICY8Fqb/+shcyPErkMqIo7XJ8hGR3rODPKyFpgI2sQKC'
                          . 'zSoopaRPrLQaGexE2vnZLrOAzzEiSgU4Br0I4tPT0w+CavEqbT7fxtumhlAToG9cbEzrtVPBxFi7716eo67IIm3kFcuJdv5Sd0W0'
                          . 'k8G2ahjwCd5iTQknR1pBwtY95IU4CN7Ux1qzoLs98bTi1mFeJZGwDG1TM8AMFgQP7fh9pf00ph4n9rPw/QR1QsXjy+TVrYG4GuxX'
                          . 'MzOzAujHuEA7vu8dtYXua5A40e6Zkq9zjUuJ9DB11nZVOwUSl+fy2yTYgR+KCx2GZGqbZBDdvc3wTywWUyytqN6xV89cuveBtK9q'
                          . 'bR6i2xrguV4n0BPP8srI0hNprkQt2TbW/vrlZg7R1mRCnYVUTbQdN+oSLfoMq9tPVbh379614NYhxeY8pqBAbfP2IiyCUzvb/PcJ'
                          . 'FeCGm0AqBLhb4WS91kxFOwXo8tQ5gPZLZLBjDJxzAHwWClqufe/2buicjdLkb/B3lLuduZ+thYDyz5NXlH/bytUSf4te4ylAlB8d'
                          . 'v/qs9a4lAylpIkdSStBfVFUqGtuKcw1I2ys8yBHdZYY1tcF47wFu1u+q3iekF5DubV3cGjrmg0PJqogRg9HRhbIvGZxaC9p5BW2m'
                          . '2sCJrG9togntxUCodT8vVxDqP1Fyx/NaipSmHSShy36qAtpRQeKZ8NOl2oUKXcCup0jp0D/uelwzNszdRINQcZNKV8SSUiLcNrat'
                          . '1ZLlf6bXsfPidW+kNBxS+3YHaySaX/F/JKpXiSbr4CKkzBbNWWAXI7e0o5nqw6QOCuUGbaDqwCQoaD8tLi6O1fZhx0AeaPN5eKZ9'
                          . 'Dj+oXUwuKM7h9UqNNxAtNcCd0HleBS9HEZLafTa7WUwz8UwGHd062gJxji8vL49lsVg7KuSV9mwWgwnq8gO0iUEH/zxjWJv+Ry8n'
                          . 'YqaoO3w+/wh8dhS+PwltyeVyufSiEhkJ8nC7Dp/NYjCsZm0/+XCU6iIfjutApQLE+lJ7owcPZRC5uKkx8jDY73s51ppm49Ooh+70'
                          . 'qh1RH0a2tsT7P2zopHj27Nm5zu4e+SCliAwpsPcvB017ciJQfUS/U11jUiaTnUVpSDvRCKr9QKiNdio87z+GB1lOXn++8cCGAFse'
                          . '2m13uVlwwie0t+5qaDAHEDHaFm9FR0efXLBgwSupUVwWnQxqJULXxiMtHYSgCygdvx5qRT2sS8+KNFZ2LLgoYNPv0DimOsVv5SKo'
                          . 'QahoMnieJ2vR0uvaaClCtUzJVKgvOCgdrEyqM16kJbm4uLzyiaHj7uMqDhKHz/17t7HS6Q+Pnue283Qwx69PwCQpAIJFtXkPkNhu'
                          . 'B2uT0SBlMmUVlUM4LMYQH2cLypn/5MUEIFMxlaUKnf7hvPMfKFVytJ2O7uZHjgAZp2ZJKLWE8QpV6aVSaYKzuHZ8pCpCcXXIca+O'
                          . 'DiCddvc2Tbl9+/Z+WLkNOhZtrtAfXy/t4xj9xi+vmrjq3wPMsakOJ3MOAQLLBNXxsa69LB6Pl86trn4SaMfzUXcju/AMx7R9o9e7'
                          . 'cOHCiYiIiGed3UzcLukgcxW6gDbgasFJhDkSh/kfVg90vnE1WcrVVxvABMtrh7qWw5h/B705yspebREE9RiTpeyn0VgNB/+3ADAC'
                          . '6vvX3NDbYXNOsXy2dkBLffZTFeA5no3UtZkI/d/WsY5TSIuhpQjVLEOZXcrSlIubQFnNUacegbv8mDA6p7CEBmS6FD+6FpdB7iRk'
                          . '4oZTVwGXizH9XtDBPwCpVmHylF7tXC/j7yrkVZ3i0/JpGGKK/qju9mYHYFIdKS0t9SyTk4FnbyvSC4C0C+cvJWXlchLZ2gn9Z+/B'
                          . 'oG9yejNzc3Osuy4R8tim0tJykplPTRjDY28bAWZp2jLGHftoXlOzBmVmZn45KsR2zPXUYj/tDPb/RaAGrR1k0Ij9VIUzUZ5mGoR6'
                          . '5bmU6BP8hbvqoPXM3TTaYz/6eaIdWhuo6n8/gnpWn6DQAULAQwaj6o0TU/x2D9/6lK5t26xzDbiHXa97yYVs2jgGg3mr8VY1DLRd'
                          . 'wkKKaSA/biZNUm+gOr/hNXcCEuZJ6I+r6pnWVKjPfqrCwYMHbw8fPrxQbMLSSKaCdtT/NKEqa56zVKWVURoENFv1PDh3nJudGUl6'
                          . 'UUhsLQQfw0ep6CaFWfJvx2dx2vvavYulpD9+vVN3Byvh/WcZhRiZhBFOY/LzcgQ+TpZ+vs4iMTy4LHg4N9PT03kwgLat3XeNoapc'
                          . 'isiVlBIk2M/exmCLqtlNcZnSQjb0BUWoymJ+DY5aLFlRpkcJEQSoQtSAW9rXiTiYMheidK79GwzrRNcaFUzqyTeKibJB0un7xSDn'
                          . 'ozDAW/16M4dkFJUTfYOnlDZBIoL2S8pqr8duQnkN9M1Ub3NjJTow4kr992Y8JnmaXQbXxs9rVQzM9I6uNg2ZRPB7+N27uHGijscg'
                          . 'OeGzsTGpvQ6XVVd9gfFywMOS/unZ6f6fvLvnmSraikJbJyH5aZQ7Zrn6GtpQE86Kz82SX118bob/9q1Xsyx/vJSlEbCB7W3nLKAk'
                          . 'ybFtrF5WV1e9gTmA1a+LcfHqfdAY0F1rSJCIfNTdoQSGxGsMRv0h0foCI530aQMmRUGb7exIO0zJdyArK+t1sVj8GQamqB+P2mFn'
                          . 'V5Mq0PRw403nudCtEG3i/fxEg/9Qy+KPyW1wj0S7Pc1oItZAi28S8LnUjTSbG8bhw4efwkqUdfVRhjgtG8NBiVN4kCPB15lbKWTp'
                          . '1gtUSepJq4/TBnb2DMLO9HRoTUnHNjY2ODprNmtkMlkIqPubDp6Pb7PtxAPqswB3azJ/THtyJz6LRE8MJ1Zm3Kkw0M/qbo1BkPK5'
                          . 'end33o25gYYYsVCMugGLzTpoa4yO70vfC7fNgZf25zqfC+aTTUhIaNvPz/2N/v7mWMIDdwoMGYLul2e1qrNgwMJkyC5bRXtnYc6L'
                          . 'T+uYLeqTvEsntLfOgVedL3Scg/I3bUgSKykpOdfb10zX9cnxKb4op2rIqjCZ6+jpQIBLvaw4j/6a5vdZcn65Z2q+jNgp3LWS4fdL'
                          . '4ftt2sdgykmJROI9qaN4JrzGFpZVeuHmJHa+s4gLiyftHhz7q0RS+ANqPlptqHIw4+hscwNA9okpLy9fzWC8coJyREk9Y00XcPzd'
                          . 'gXGxFfriD5ifeA++H/dwyIGX9m8f0DhmDeZ3gGNP/DjSrcuPpO5+r64+KW2BNIXNTqjK8L0KDptJSalMhfGx2QrMK1eig5VVVZMd'
                          . 'rU3I7G//oqTVDv72lLN+7Ldjqd9lg8p+4V4a6d3erRyIZiMQJ1FFDmGlVAsLi+WFJRUzvth9if7HpQSyakokJlah1PxzIJn27ehO'
                          . 'bES8mRgb3UxNL2cxFdKbcpGpt0+wUF9TLoD3qAtwD9/An28MOZfSh3Wj8tUsMETKh3vBvId12bEewD1icug6CaLrQ2NqrbL+l97X'
                          . 'r0/ahXb9Dn9+txfSvB1N+baYrAP6Ia4hc4OpKbXxshRf9IoSCy8LLrJTdVFRfprQ0rIIrwVkWuc49Hk2pM3qaK46Uk0Za+rjAu6t'
                          . 'zytcG8tH611C2tq6SV3VIFpKQi20sxBYYaZ65bhp1uugOiYWCSajr+v0oW0oyRTznPaYE0fQzxQ3m97sE0CGhlMeE1g2ZDEcE7Bw'
                          . '4cJxpaWliUCmZ/acfeL9xW/XqFpTuJuP7lWutqZVQM50jPV3sTFFdezbZmw2s1JpgLMRUT77Tc+PZ8R/DsrsYpQ6bUh1XyW5UlvX'
                          . 'lpa6qpgY8W9CSxFqrrONKUWomK8U0PSCTjoAqvr5UB9bKmwUq5liEpbdnwwiT1LzqETQt+MzCZarNuGzMVlKoL2lMHD60JDsixcv'
                          . 'XoqIiNj77f5b3hsO3qbOJQBpcfXUKCobVOzdNDpKvf4ullujo6PnYQaiZgS/TGlXViZiaTa7shFGGPHvQEsRaqq3k8gHK35iJihA'
                          . 'sy6tSreWe3mSUioTepdAB/L2mhNkQt8AikTbeNsQMyGH5EnKCG5gDexM5QCYHRoaaikpKR+0+ei9mnNNH9YGJcZNIDUcA+LtDr+7'
                          . 'CCrI7hYoA2GJUVsIL0cqI31j2d+NMMKI/xhailCf+TorODSnsAQ3juz27dvLqC/BR1MAxPeLi61ZEKr9KK1i1qiPfoqlCNxJbErG'
                          . '9fQn80a3y6YRsrykpORXOkeYA0Q85cjlJJoq3ymGnr7eq1WeVCr9UFkq5YAiQqp5SxgnJydz7R2crFSEqvSZbfEqmEYYoS+wIF7X'
                          . 'rl356knZjTAcLUKoQHYPVZFBmLg5M7+YOWDAAIziaDaH4eLi4s39Orp/+v76M9zP3okgr0X5kBGRPpjJiXDZDNxQ2A1E+hkm/cUM'
                          . 'V2VlZU4cDmfhHrWUgnNGhqKbyWIlmbYY7OzsXDFJiyrqButgYR81Z3hfZWVlb8zeA//i7sIduVy+lcViPWi2C/zLkZ2dLbSyskIb'
                          . 'DRsWxCNw76e1tQwYD3awqB6Ef53hFYeRc3DcBqX3x/9bwNjp22/Q0N0yebUp9AmGd50sLy9fhmXRm+P8t27dYrVu3Xo4/Ivb76ml'
                          . 'paWnWrJsdWFhoTnM6d5wL2UFBQVn/s5FokUIFQbyHQer2hRm6EjfqZVdAGlGQkUSRCnV00E0KWrWLsoW6m5vTt4DFb69n90TdALm'
                          . 'crkB8DcKiKsLncl6a+UvV0wwF6q5kEttRkUEO5GC/PydFhYWzdUsnWAwGAGJ6bW+cbgBBu26a0iyagT6+AJRjob78YN7fwqS9VFV'
                          . 'dVP47uPFx1MjrqcUk06uwm4zwmxnm9Pk4arUb80BaPN89OeFf7G8yxblDva/AjBphl1Nls7DjPOvtbacCSRxEfpnCCymNbZqIIiA'
                          . '53my9oM2PSEeVlzb8aFWUcODxJhLcGBLtw89TBYvXvw69J839OOfMCbOtfQ1tYHjB8jMTSaT5atXxYU2OcVllph23/AYizzavtvF'
                          . '5s0F3e0xJKwXfq+s5XYI/sUct/eh/d9A+4/qc03UzkJCQo5dSCrqirWePKw4pLeveTlGKEJ7lrfEfcK9XfzjYb4/ukz28bXIgMUh'
                          . 'EgtntsS1tNEihJqbm3tDLBZXAMlROVEfJeeSzgH2beErvR6CPsAH5ezsHIXJI1ZOjiTr998kN4Es31p5jAyP9AmLau0UJuSxiaRE'
                          . 'Ru4nZVMbVxg1NX9sB+LrbEEmrjpOlU4BKXc7nG5Ic7VLF2DgtMVMWAhrcz62o/Tx48e3/f0NS6qPZUHSJRW/YnapYAcBGeBvXgjE'
                          . 'MRIG9ykYoKunh9mGB625RzsbX4jhlfSJHazfxKgpovAjzULviLi4uB8bS2ytC7DSm7F4Jqu6b3hEczBlka+HuvW2N6GnYtiuvufA'
                          . 'CQ33EAET2BKkiNOwkDWbpwPcfzKmLcQkHPj6YrBLl/fCbZfCVzNUv0GpFfrrVJA9vxdWBhWy6WR4kIUfHDsF2uSIi1xeXt4Ja2vr'
                          . 'hrORNAFLlixZfS1FOu9UXCF5o531x07mtLegP35u7us0BLj3PQ9elg63N+Wha+MhiUQyAbNjwTPciBrOmDZWwzZdzlRlCrupOg7I'
                          . 'yCyvpLKLx/LbpKePmU10X6cePtacN6H92xu7JszRDw/ez+86ent8jYZmLWSxz83wX+YmYp2Da19o5BQGISEhgePk6u4/YqsiPmFZ'
                          . 'Pye7+d3sJ8C/HzfndepDixAqqlCYuXxkV9+OW4/fJ3fiM2EVDOrSXOfHbPd+fn4bT11/7oEZ8BcASfbt4Jae9KLQ4RZcC9Pj/Xnj'
                          . 'OWGABGhhyiXONqbkx3m9qWz8SMDvrFEUoFnwQwwmmx7s5WA+CEjpcHO1Twe63I5XaDjQJ/jnSlNIDUjojoPIIuXuixLnz8+8ILMi'
                          . '7czWDHQeBWR3de3atSdg0h4Aghi25WoWcVOk8Htr4+UsNkb7YGTPW+2th/b180NH/QGGXhukO2FeWSXtVqqUYJxj/1Yi8lY7K1wR'
                          . '9CZUmLTrH2eWTkstKCc9vUXpZWVlnfQozawX4PkNw5yjCIxxHxZkgaRxC80qKB1+8MEHYiBzTEjDB9WWCndcNdCZpBWWe8QkSH5I'
                          . 'zJFRUTs9vK1wkZoI59vfHO1Sw7jxOxOozEuYwf/QJB8sJ/63Eiog5+jDfLLsVDptyxj3Ia8FW+CzW4OmEdQq3Sw4VEa1Lm6UFwof'
                          . 'PvsYFz8gVLEFm5SzGDT2oft5BBZqAoTaaC4KJDc3d49ZWBIdyRQj1zCBdba0gipc6GEpavaSpKC5MtUzi+UWU541f1ssbYtFSkEH'
                          . 'HgfpjyLU63EvMZlJeObLdB6WLHiV82J5FSDT/X/dTO6FrlHoipScWUjsLfgDnMUCupudV0dUq4giTR/uPqFpIAU+G/wiR9rtgw3n'
                          . 'KLcqBLpKfX/gNlk7s/uimJiYYy1ROheDCIQmZp1U1+zVzpXqG0Ptpzg4YbW3h8MODQ20eA9zZWKqPTjPRBpHOBHIFH0V73eGyYCE'
                          . 'isIAXOezSR3F0+Ff8dITaeQwTKbERSH9rbjVnkCQBkXFgDTCUS9VoSz9q/fuHT43MwvrqV3WPaSqUq4d5uowpZMYkyGvNKQduiCX'
                          . 'y9vll1VP//zsCyos9OAkX2JvyhyNJgkghXcWLV7yVU6xXJj6oow8yS6jYvuZcAP9f4ojQg6DSn6jypc6t6u92Yr+Tphq8ZUIVUeJ'
                          . 'G05hqWIztEhR++hvDZiHxcQXJHQ/rMiAQoWHJRfHzkwYI3ivttLyKm6WMudAQk4ZphuciWSUXVxBldTJK5FThSK7epmBGm3+ODY2'
                          . 'dl1jlRfc3NzCrjyXmicr64t90NWeYFgo5ikQC6mE481ekRTGNVO96gRPERb8SpxjCFqMUGGVP+TrYvkpEgfGrwOh8Np623WHrwzK'
                          . 'fI8T0crKqgucpy28OonF4k57Y55aYYipas/h0IUEtJ2+A99Ph7caiSKUA+kXUPnbLN16kWqLOlCSjb2b1i4iImI/SEzTm0tiUkEk'
                          . 'EvW5+CCdicRPp8JgMZVhxSFDElAAKcx1cXPH8EVOcp6kZvIjSU7anUQ4TBp5PdTaAoigc6CdwuVXmWi3gkaqV07uJF42to2V8E+Q'
                          . '4GyErGxJYX6WoZEx0LcahKos9at3RQCBQMAHIqGrCqm9lFQQfasSNARl1NuO6XvjGQVAWCD5ESdz1ho49+/Kdns+yCgV/vEwj6rZ'
                          . 'hEmeMXnG0cm+GNOPE7rq2OMCm6Gbn2AfkuX9qKyKmOsBVVF0VUGx9yyMje+UkUiNori4WAyL/i14bmuSkpJ+VEadlZtyGdRzoSmi'
                          . 'eJs97LEhwBw48PHRVN9vYjIoSTH6VBqBRcYBSRP7xN6URSUG/6CbPSmD91gR1t+WB5oNm1gJmFR+0j+fFBIYY0iEy/URPrDoo3ou'
                          . 'gqFBIvLwpSKptZM5GzkivRlyZGgAKxurp8DAPAu4OdWcG8ANocUIFR7gXcxcPjzS23fvuSfk5LVnpJ2v7UjSAKEqbWxt4G9XzPoP'
                          . 'H/mKLKza3HyaxUY76L3EbHI3MYtk5mluyh6IfYqEOgpUu/fUi9Khbay8kny9dNt53oHzmjkfzAQclY8sFb46sV/gwCmDW3eDNm+V'
                          . 'yWRrmotY4T5Gnrz2nPr/9V6tMCnDbWXUjCHn6HbkQT4HJSsBm1FTpfKboa4ECRSTTCgh9bbmUu4V8SCJ3UkvWfE4q4xSM/E3oAaD'
                          . 'ZFn9VVNsl9qEylMMVL1Xfpg8FVwOvU6O0VeBMhHP5k1XsnwOgiq6qJcj6e1rdv727duLLC0tubD4WsE43BNox+ME2jm8BofYY+Z8'
                          . 'L2selSAFIACS5++9k0tMuUwSaM8ni4+nYh7NoOR8GckByQyTvAwOEHWYGWE7MTs728PMzAzVVCZWtK2vXUC8w/56KnGAc68dEujx'
                          . 'AYzDj+C+f9s53nMWagkg4eHP7msfV1FR0QruyREk7idXrlxJa2aNqQSTjuBzQ9MGJiEPceSTL85m4EYU6egqJHMi7WoS0KBZBEtQ'
                          . 'P8stIn39zEl3kEzvzQ/CahB7gAR36elaaFZQWusp6WrBIZ/0cUQ7LP7/MDo6OrGpwTPQV0HQV7bw97H6XIW2McsqdUuosCg6gyCx'
                          . 'COb3suYWnFRo0eQoSE5AVKuRUI9fTSILxnUYLpUUztCe0Eo3h6kwOd5+/lLigQEB6F/6Mk+xNxDiZUPlBMAdeiwRrQ2s0fQ0Nd9y'
                          . '4cKFofD2mrKY2ebHyXlD3//uDFHlZlWBx2GSuaPbkyWbFVVZUHpMySoiE1YeE3Rv6zJjVDe/d7CkMUgaq9R3iQ2FVCq1YrC4gzAs'
                          . 'FoGECufd1thqiRtA0H7cxLOA35rD68mQQFEPeFFi7dZr2WT/vTwqy9SZ+EIY9DLyLA9fZaKMwgrKPvjDpUyqcqYVqFaYUUheRafU'
                          . 'NrGQ2WCCCW2gywuorvZATFbqVTkFitrpNe4oSg+EydBWTP6dBgP9R3XSUZYGifl5rEfk9dRiMiqESoL95FUkB7jewhupxa/NOZhM'
                          . 'pbNb3MsBdVZx69YhZXCv9GeFqK6WkJdF5ZRUmgUEue9uLgH1lkStf4RVBPg44aBPqPagtGYtYBFXEYfK3oWmAFSCzPnUNGGDthEC'
                          . 'pzonKaukO4uqb8LiPwrzVsB9jkevCyCZuPj4+KcgnY78/uJLcgTIc06UneOqAc5YudaBMjP4iSgig99f16q2+nV2SfXsx5lSSlLs'
                          . 'EhZRjknCYQxOU+YVeCVA25YenuSzC/5lwWXZSJhzDyUTLNWM7cByO1heBROx4Di5miylJFUESrRH3/HFTFdrgLA+1DfoBc7LLVNb'
                          . 'hBlwHRdzVk/XNpbuJSUlB1977TXm3r17K9E/Hd7bwkKEbn+oFWTBs92DQhkep+QHLGskhc/Pw+ffphdVdU/MKSFB9gLCqa4+DmT5'
                          . 'Dgb8wDUZulR+5Sb2PtBGQmFxGAv38C6M2R2v2q/aaFFCLS0t/dnV1izaUWzCSQPC+vP6c37fDm5vwldUjDzaBd3d3WcJhCYfH76U'
                          . 'aHboQjyxtxSSbm1cSM9QV2JjwSeS4nKyYucVcgIIWa6Mhcedcqweqo7HKbnEy9HcHyZyKXT+gZ2nHnl8vvsq5QerjXeHhBAna81S'
                          . 'H1ZmPLJqSgS18z/1i5OcPh3c5r4W5TsVBvWPMHE2GGpzRPD5/IkHLySw0UULgwjsrYTFRUVFO7RLOKgDBkwnGkd46nR8kTAfBvZL'
                          . 'GORYvC1DohjsKNyhFDEi2AImbAFxteSQUGcBGdBKRGxBbcPaQyN/jidor9v1hheSJ6q+mNnbGu7lO2USEb0Afdk6JCRkf1phuZsF'
                          . 'DaWW2r5ESRnOV7MbDu2efvdF6ber/0onvjY8Mj3MdqYVq/o0fD4XJsB99BO1tLTyxDaOBvLytuZVVFbK7xvqOqYCTIhJKQUVy4Zu'
                          . 'eQLSlpAqOQwSOau1A99n45UssuNGNkUIfjZ8iqBwkw4n34QO1qDesikzST700eOXpQTzb2I9eizcNzhQhMmnMaPTl0Rh58SsX4Uw'
                          . 'Yf8E4vR6nFlKj1r/EDcE264Z6PwjfPfi1JPC1/Fa7aEdQKarpbKqSFSPEVR9rerquyUV1SM6r1W4BfcACQ0I6h2i3JSCe5kMbZ/d'
                          . 'Y8PjGu2Dz2awYYEY8X6UnTvMk85NKLiogeXLlx9dvHjxRCCcZSA9e88+8Jxg1x+c5EPZRdE+ioQvAGn1y7MvqP5E+/KsCDvybhcb'
                          . 'Yi1k3oRnucPACEKB+phRrB80alcWyK+nl4/vFl9f9ACsjgWJ8UhKfvmHOH6cYEGb0N76YzGjajqMjw0wX7buvZs3BM81po0VwUoI'
                          . '8/9IUSZ+p6GZou+nfRxPAjkHDxgwgFmppgHhcwaUApmu//1ObujrOxMwfaLg8uyA7TCvb8IzffQq/aqNFiVUdKpHX9HoiWET0U1p'
                          . '+8mH6KY0G0jlVyCbMA8Pj8/P3Erxwo0rJNDv5/SScdkMLFW4D3KvAAAgAElEQVSC9cSdcyVlK99aeYyG1UlVgOOpstDahFomk+OK'
                          . 'OL2ymub70YZzQix9oguYCAWL8mUXaEq6KP3SabQkOP+m3u3d5x84/9R84urjgohgp/eHR3jPYbOrsbrqpjt37hzUJ3kzLhZwf7O2'
                          . 'n1RMoqUTKCeHber+f/WgCgY2HVO9YS5PNN6Hu5tShfewRAQOkByQpJ6CKv8os4SSMDYoyWBQoAV5va0V+qGS5afSQSqt5lXIyj5h'
                          . 'srmpP1/PZsJ5NvuIue/CQBrT2AIBv/GhMZhnxu1IEO29q3D5sjertfsqVf4S5W/9yqsZn4/dEU/VTkJ8dS6DNq6tVc/1w91OAZl6'
                          . 'mZube999UeLQa4NCaF3ax4n1UQ97NAHdbawv63RQVdWUF5KKDf1+fEzC3EzI9nGexGfFHXI2oZAAoX49uZNYAi/0ZEBbCONKsjQQ'
                          . '60PxYBHAHWA0/6JWyIV/0PsBq8bi7jXmcsVcnrMibAu1XYJgvKLKyHKxUNiesVQJEGoYSHaMMdvjKSKcCsSzdqjr238+LaSriASf'
                          . 'HbT3vHpi6hJF8UVq1lN7BNbiFWgLx3OEwe+X9XUiAzbFkY+OpKBJp00Pb3fcfd9laD+pgM9yyZIle0CaD1zxZzpFQrMjbcmdFyVk'
                          . '/YWXZOPlLOg3AbXorIvNgHFVSt7uKCZLejmqiBSJ+B5WwtAXmG8Y1H1r3NBSYYOimOS3I4It8bzX39v7jP3bnVzyXrhtTyDEbqkF'
                          . 'MrJJWQIGxj/tuxFurZWHBiw5nkqNLSzeh6VoVMX98O+q0+lkSKBFqyFDhoSClpsl11L5oe3j4J56TN2j0BRRGIH27WhuMkW0eD5U'
                          . 'kHJWd/S3f9PWQsC4l5hFrjx64d7Wx/bFjSeZbCRYjLWfPDC4PDzI8XuQaFfTaPyXcLPvAJmueHPFMdqzjFoynTUilFrlYu7W3Rv4'
                          . '8rfrmBwlFCuLor21Prw/sh1Jzy4iDK2aJnHJuRhl5VYilXzHZDJ/eC3K5/1hEd6z/rqZbLJ483kaj8PsBRJrrw4hIZkwQb6DReFb'
                          . 'VGPruw5I3m+fu5Nq/yQlj4CUTlp7otdO+ReNbQbBta+CkO09sYP1UBgII2ITiyLXwiD3AElHBKoXbiCgbau4vJL8DEQyOsSK4GbT'
                          . '6J+fklKYxCNbW1b19RPRP4QVHAYeJ8Jd2B7OUzX7QDK1u7t2mGvo1M42n8KlxtXXBuVisB8GIEWmKKmsHuhM9tzJqyntqzT2l2PI'
                          . '4vDhw7fM2feciwPeHNqIUuDX5zIo0hnYSmTb28cCo1aSq9QkB6UqaJAxFd2fgBhWJuWVzx+y6QkZH2pNgJRfwFdVtqZsR+XkrcI8'
                          . 'pESR/g7JxLuDs/DC9xcyrf94kEdSPqnNi4lzEmtnoXqLODjRBwjNZC8Q5yxd9e2PHDnyYtiw4XIWg85E8sNNQixxopIq0fQCsDz2'
                          . 'SLFmQpsILGD5MFbuEVbt/pv6hp61tfW4gw/yLXGzBvFJb0fS2U1Y6SvmMW6mSgk6w/f0MfM1pJ+0AUQ4FckUXZU2jfYgFnxGLFGE'
                          . 'Po+DIcHceCWT4HhBokVTDpo9UBA9HldAoO/awj1ch+eHCTAeoJkGq+Oi5wz0ber169ez67H1ytp8cU+j0CRKxQhclPr5mQeiaQE3'
                          . 'KZ8qyp1IKiqrRVrnUI2PapVxBH2ssZ17J3iTE48LagiY2uyjcTGaKFe9HiMSqqSsssfIbfHUtXDPAaTu/OLi4rkYQdncaHFCxQ0Y'
                          . '6PztX83oNmFs9B/kmz03iZDHYgPBYKw9KSwuP7vml8u7QRL86dixYzg5V6VmFy2Y/PlJDdsn+ppi4mgkw+LSui6cuHuPWfsbAkq3'
                          . 'DqDqo30WS7OoA80Jj5NzaYFulq2B1HDALS4syFvTM9RlbK92rhPTs6Xt0ef1+4O3bQZ28YweEuY1F4h18bJly75T3whD5OXlmZqb'
                          . 'i5as3avIZf3l9K74ZxOQ6XN9+gxtQfBnfUlJyd4ID5NLIO25ofoPkhNIYRJKokK7IEpdsDJTJIe20l9f9ypn0ap6+Iq5u/xt+Q4H'
                          . 'QOqK8jR9HwY1G8kUfwMEjPlh19aXOxUBi8EkjDTZelUxWOd1tUeJJa9cXm1xLkGhyirXo6phw4a9dfFZUceNlzMpL4bf3/IiISDt'
                          . 'fK2sHY/11YHgHJBQ1a+BkjZ8JjPEhgpkuhEWk4kgbZNdb3phgbtjsAhPgv7ay2bQHEsVNj+NFQvHHyzqvb8e4nI9cM09hkpKuZVa'
                          . 'TDKlFaS/vzmVBR9tqCGOgmqQxj6F873QdX1lLt4UZxHbHRcPJMETcbVrahwQA5L00UeKz5R1kM5iyQf1u1SqoRShwv0P+1VZFQBN'
                          . 'JfC8qe+gWygGLiijuKpu8lMDIJPJvgp1EpTDC/sGH3xHUK/fRP9kXPTQ+2BWpB3p7WNObVg9gPvCAISfr2WTqb8nYXUHdoAdPxTI'
                          . 'KBR3/nFzyRnUcvQACA+PQJUdVxAMWb0CY/Zj1Ezh//zofk6Oq06/IPHZpdQxy/o5UVpBsD11a9zCMsVCpMymLylXJ1TNcVGtPk6G'
                          . 'wpiHhboyNb+8ZjdWtUkKv2PKKzW9USbuSqTagOfAulV0WvUHzWGX1oW/pawvrPiLgEBfa+drJ7wel0HeG96WvDu49SOY2FP6zt86'
                          . 'XF5VegjjrIFMf79wPy1s/oYYUiCtLUE1vlcrMiLKh3yy5QJRJWlWAZ32x/bwJ6Z8Nlm46Xy9O8iWZjyy8PVOVKw/Rm+ZC+tKiki0'
                          . 'wR7WKA3EUscoYoDRTvajjYjbZs7I0Olw/Nh9MU+4E1cdM4P7WAeTfKhUKh2pvnklEomW7o15KkbpFMNb/VwsC2BFXGroighq5kvo'
                          . 'o/c/7ml/YNiWp2TjKHdc2UkcqGRoK53QQUxJR2gvjO7jiJ99B4PmPKozkzpYf7jyrxfki0Eukaoa54t7OeBu/7fwm2sNXRe+f2f9'
                          . 'eYXfLJLkW4rSvls5LPpcHb9djPYsxNwoO9zMuQoSQQfV9/X5AXIUjGxonbG2XtZcsnWMxz3gKHTJ24+qONwvRwZiiVINrKOXgoR2'
                          . '25JZve7cDP85aBpBKQzbvDYmg3TzMsNaRiQlX4bSFM3bmrsHSLp7faQKeO4i4lCEinXtTz+tTZqP3hQY+ppbrLAIdVeUWjld5941'
                          . 'Xc7aq6qyYp9guZR7GSX8u+kKiVXpu9okQsWQUeifLrCQR8Db3i8kFQHo2P87qNnoxYCL8a9veJL2zkJy6kkh6frdQ2oD6tRUP/J2'
                          . 'JzEZDqoxbvCAdEttsG2HcaZye1MBngHN1oRlAZK9xaJejv4+1rzn8PEyeD6zxre1WrvnTm4gkhn6B4P2lIQugETx3L8FQsVQVsot'
                          . 'C2+1XE20VNJnHQkVMakjNR5jQUvqqt6nSvcohroNFe2tJ5WLHpox4F7PMxiMLc2dAEmFv4VQcXDCDUSvndl9TZfpv5Bdpx9hNigf'
                          . 'Ppsuga4KTfhlJtpad2w5dj/si92acx1ImMwf04H8ePgOCQt0JKt+vUp9jpVJZ49oixs9ZPPR+0Bcjpidn9q80oWlb3Wh6lthpims'
                          . 'Oorx9NpAQoUHEqDreJiU6N86SV5e9iEQ+IIBnT1nLd9+mXknPrPrOwOD/wIp6A3clUQnc0lJxayvfrtOrYhfTOuKD3pxU1fEAwcO'
                          . '/AELTTYMOutZoDIhQfFZDEr6wcGCKtDng10whBKvU/D48WM2SJg/vNHO+oPoU+mM7y9mki/PZlAbD1M62WTAJFvR0KYY2kNfFle3'
                          . 'jklUaAfdvEyJnQnzCpy7KFur2BzcHwvmmz22yUfMA8KmqhhrrBpoo8TCitrXUdZhMmijpbCwMFLM59vChHiqtTliUgTSjlKIqbHl'
                          . 'pKWl8RwcHJbAv21LKqp74vdIXlhREz0gENP3PqMmO0pcaEOM7uvka8lj4USvl1BdlXZUlPC0C/AtPVHrjYOuWTAuNGo+UfeuINQy'
                          . 'dP738PYVqQgYfTafAPm4WWC8uxm0h6uScg0iVKXJZgdh8Yefji+ix8KzRFLEul7ovbCivzOqwZT0ez+jFMaQhLyQKLQ+3JyKXP+Q'
                          . 'Iii034tNmGRGmC1oP575oAEIoP/YIN1Stmb8Hk1RzubsSuhbzDp0UiKRoCkM3ZfOwnNPA24LVGtaPnyOyWkowadQ4SutIlRU+Wt+'
                          . 'qHyWtYSqZFQML4Y+qYDj/5DJq2oIVU1CZavv8qvIFLWzz/o7VcPzmNoCqTlr8LcQKuL8+fNfR0REjFo5OaItRjit3HmF8Xb/oD0s'
                          . 'Bk21OVKhLK9cA5Q+gYSBBOXktzOPyRIgRUmxjNrlXzM1iuz88yH561eFJonJRzC89K+bz+vs7A+N8Cbd2ypKWLOBjNBkUKmjT+MU'
                          . '8fYNFuRREuM8GFA71kyNPPLNnhuOK3ZeCZrQN/COnSVzJYZARv8cQ0cJe+3MHmjeuBgdHf19U/3thgwZ0gn+JN+eF2Td7ftHVEEz'
                          . 'nKjP8xSbVmijOg3SBRIFSASfhvj65tPp9G851dW75kTajZ9/OJlSt3aO94SFu2q8MgN8vWAymb1OPM6ukfSHBVHuTXtgoE5Sl8YQ'
                          . '6DJEqqsG/DnV72B2sZy3714euiW1QglaBZwsuiJi1NVefaGsoVSoQ7owR3uctsO8nZ1dNyCMD5FALaEd5xOLqImHxIlSWUyChJye'
                          . '5k8C7erYS2OgzTgYLqC9XL0QHnz+HMkXoSp1jBtJ6OOJBKGqkorhr/amLCwRnQASr0ZlOdW9W1tbs9VJZOfrXmREkAVIWeQ5vHWN'
                          . 'zykzpK5YDVxcXPxAkHxt371cqhLulM42ZLk5GxuG0spFeI29mVrsVgH9iNI6bniixvPb7VzKgyRpUUidgohw3x8UF+b91tVD1BbG'
                          . 'F2brYuDGE/RPSlJSYrzKC0GriB6tAeYyq6Py1/XIqXM4jidQmrJgeHKL1KRlNULl6apMi5I+SOW0QFtee3jb7JtRKvxthIqGa5B+'
                          . 'Xh/UxfPGkcuJfHSRcrASevfu4E6NSuiMv8KDHCcgcSLQjemHub1QNc/79fRji0APMbl4Px3VZ/Lt7B5kf8xTIM9asxyq8ftin5JJ'
                          . '/YPID4fu1HyO50HpVB00eCJYKlobkhJqlXbU537wwWUXlNr6u1oRLL634IdzJHpS2Ec3n2RSQQzotdAz1KUI7vlNbRurvsAoLzlh'
                          . 'xK4794KGKiZGFy08mkr5njqaccg5kCzQjQQjfMx4DOILUiJMRjeQksOrafS+ccoJjwQMAzHp5cuXV4BkGrsv98dqNdE7uwrxM8fr'
                          . 'KcX+5xPrxgNgmC/8noc73XHKCpMYZaOCstopdqzGDGUbGGlVH2JiYpiduoRbo5SntOtSYo/Sr3HSxWcS8sHhFJK/IpRS7RFoa8XN'
                          . 'KMQBWAR+val4j32cBAsVqrVARJY9fcwGL+ntNNjFnAxXi+1PVu30qzCurRVlP0UTggq9Fc77x3W1WXXvKCmpdwr2tVxeEQKazkJY'
                          . 'CHwHboyjJLVni0MMyoMBx9+hyeXt3wi1CsKFobKy8vG+ffsSVPmI4bN2cH9UJbt8WARQ88DFAVVu9EDo82McZZfPhUUKiRVt9/O7'
                          . '2U+GBWAzdrn6tTDSCci0vqbQGojhMFOF4ioXGJrSBq44UPGnjoRqofAJzoNxZyopq53DXDhHZaW8DB371YWlmPdakY+PplDlpCft'
                          . 'SiSXZgWsra6QndN3P8NQ/G2EikBHbxhEM76b03NLjzm/kS3H75M5r4UG42cwAX7rEuhQyeMwGVxQ57cvHEBcbEwWwmGTgWQtxvTw'
                          . 'pwbix693lLAY9DsMBj1C+/wYMfXzx/3Jg2c5VIE+dOD3cBARFlNzRx+zTT1NrfVewqxUo7v7krcHBMthsK1obKNEubP907YT95mU'
                          . '10BOEUXoP594SA6iL62VkHw5vRsO3CmG5pRER/rg4OAw3MgBadGBRaNVLuhuTz2nFafTSSqoW1gREm1g6DzOZvDJnru51A4zj0Uj'
                          . 'C3s5zgEVbM6YHU8IRqmsG+ZGZu5/hnXXvSa0t92bnJw8DCSYGtslTDYMB66GgXhG+VGpkF1rp8adapm8etq7e3SbUqCdH6rIdBqo'
                          . 'htBWirB8V9xRfU9V40TblvpxHIbhEqoudOrUyR36gnrATHrtOYFM962LfdkZI59Q+hq3I4GgNIgEgaSvilvHtnpacagoKZzY6HyO'
                          . 'Lk/o6M6m3lP3UFNxAsbqc1dRLaGijRl9gANAylUnVLR1w2+P6gqtVEmoyiRCeQ5mbAvUNFC67e5tdn/9+ZfMxcfTgNwqKekSYHAK'
                          . 'Rhg71+EPvijSAzJV/9ocVfwHIFXjfbLgHkxhQcZ753PpmKmLIi6MrjPj1rTfFdqK9jZcHB/DuNmiLKTYEGhVOhgVF8EuYRE1qjn6'
                          . 'A8O/fTBYpeZALQFZ9dZMERWIeryJtoRaLisvBY7RcOx3NGeTTaM8SLuv7lP9u+hYqunqgc6/QBsiWyJ3x99KqAhQF7ayaNWhBz8b'
                          . 'Oq3rrN1k2/EHpG8H97UWJrwX8N2Nbm1cOqCd09XW9A94aLFJGZLP0LbZztcWs0Vtww0uJp0zUsBl1SFUDwdzEuBmVbZuZo/KWev+'
                          . 'EliZ84A462q4Hf3tyZItFyif1CFhXmh7lQP57qmoqIiGNsTVOUALw4YNG3svMTtQbM6nJufiTRfIjGFtyCdbL1Lf71s2BKOVvoSJ'
                          . 'aLDvYEhIyN7Lz6WDMEFFDkhdKJWqJr9UVkVEMKDQVQQ3V74e4krszdCZn0kNNAwhRDNA5PpHZFCAiCzs6VAIEmLG0+xS32l7n6Fd'
                          . 'rO/YNs7Hi4uLR2GCX+jfwamF8oM2Jix5aWGeJUawAQmcHhViueDLcy8on81t17JAIpZwVLXSVfY+NVjhZgzaStGO+zCjlPxys1bD'
                          . '17KF1UC5ifDKhAqTuuPtdIWpiKu5AVaOrllISBjq2dZJCP1ER+8JVGsEm69kBaPEPS7UCr0FPoW2PEY3MPiuEiN8iMIWLIc+egBj'
                          . 'oiY/BEj/z50taiXwNo4CjLSKsxIInayELAHaVJGMOrmaFKelpZyDxQsXrSq2GkMoN+RU975h53ivhejDO2LbU7SvMlVuWGj3XjPI'
                          . 'pRiu+ZEhPqDqKC0tdeByuavhX9xsxY0D3GI3xWAI9MZAdyMJXA9t0LgeoXSHtezRfIEqOZpSqCQp0goxvBdj01s7CMLmRNq/42hW'
                          . '9Tb0zeYGLk/XJaEikUFfP4r0MPVHrxH0NNh5I5uGLmgY5YfjTst8U7PLz6LXVFE2wfmgAj771PScMqFQqCGhonXI3ZJzZNtYjwEY'
                          . '8IK5DEBj69wjPHw+fL2iSZ3aAP52QkXExsbOioiIcDu4Yljf/gv2ksmfn6Rv+7jfntzCUvqtp5lkWIQ3TrYbsLIOOnX9OZUQGsi0'
                          . 'IDo6ehKqz/CdX1GJpusUbjhteL8XZuufAqTw7Md5vf56/7uzLKwvpQ2sJmAjEpBv3sPQ/epPZDLZFhqNla5PwhJl/Pj8HaceUl4D'
                          . '50ESdrAWkq9+v06FxZ76ciTmCTgAbZ3fRLvpc3RjwcgbR3MOPHxzgjvbKEX0+ymOoG/i8Sl+6N/47M8nhW6YEu79KDsyFYjj8IN8'
                          . 'cgEG5f6J3rixcQTuawbWSPx8kMs1UOHE6D6SlFMW9VEPhwfQRwvgXha9vTuR/PamF5PL4SCJSKDPT7ey5e37ZbzX8Cm/J1H+iQh0'
                          . 'eUEJBtUnLRyB74bPO5RMun9f1zRFV0wEfGZluIOsQlNsqLoAE63jJeUuORIZuvDg5IPFcQqoqZgLthTu9ZRUKr1I4yn8huH9ezDl'
                          . '1qnOAYR1CD0BdJ1fO5LryJEj6UOHDZcDUTOReLp6UWaE0/B4LBb2cBiLi12okwDNDydUmgDmV4UxEz+vm73X7lu51OJDlMQP42TJ'
                          . '4sWLS27MDYz+6EgKA23h+LwHB1iQeV3t8umkajCTyXrY1P4BDan/szzZuPmHUyi1HlV63AVHLcSczyBWsEiiJGoCizFK5nz4HE0f'
                          . 'aKrB520jZBE7U/RL5ZGKKkVyGZT076RLiZO5CMOMGyJUmQlXd/ITeAaLNo322D9xVwKJVZqS3mxnTUX+fXoire6mlObh+NZUZefF'
                          . '5w3trUI7LvQzU53EUUKGsTBvYCsRb+1Q1+7v7XtGhW339DHraHhvNo5/hFBxhcrMzHzN1VZ8AiTVsCELD2AcPV1aWkEycqXE26mm'
                          . 'RMiSi/fTSIiCFC8imaJ9MEcie+fnE7XVPVDt+ua97sTR2gTV9e1wbJ+E9AIW2jI3zddd5tvFxpQkviigudqY/Kn0+9QL8HAiM3KL'
                          . 'KU8AdL369fRjyl8WUwgeWTUCQ2fPpKSkjG2q3RQIbTZc47C/DZfKPg33MzRTKu864dcEUl5ZRfa85Y1keh2jnWBQbMWk0rP2PydZ'
                          . 'RXKypLeDFCTTozCo0DXqIkgmqjb32THe86/JvyWJlp1KQ4dt65X9nbeg1ImDGdWl0qKyUpBaqd8nJiaOGxLokdLVq/XMvXfzGLhp'
                          . '095Z+Cg5v9wfNy4wksYcpOKqqsqyoqKiye+F24pAIuuGLkO4AYaJNs48lRD0FxXxqQlVtWLFikdLliyJOT3NPxJ3lXHTBjc1mtJH'
                          . 'WuCqVDzcHcdNI/xfmYBmsrJPtTdLaiYoJplWD6FtDGiHBDKYtm6YK25e4jNqBcefxwTX08JsquDVXpmV/6BKqkJbKSxuvT7r57QK'
                          . 'XuHwUS6M430odSrHyQoRt+qvjaPcF8H/fvBCF4QYOOZz9ism8YiLi9v2f+2dC1BU5xXHv0XkKcgirouAgKgLAj6KD1BBjYiCiqAm'
                          . 'YpvUaBPTxk58xJo0RayaJjVGY4gd2mhNMtYYoyZFippqdHBiohjGqE0JCUZFXiKyPAQX2JWe/927Zl0VRFce9fxmdmBhH/d+997/'
                          . 'Pef7ziMoKMhv19P9ny2v1XvgRkbiCaEvlR9wJ7DYl0jHz1lFYr8ixusS7dO7whjehO3DAONAOmDtgH52pf8jYuSD5iJG6DX/eG9O'
                          . 'QPSR4T3tuxlrP9ysmk/H5FMvVzHz4G+CXvumqE4DMR/p262ArGFvuy42NnLVNJM0FkwKdAvBeddk/FMXGtO//WWm/9jEUHdlrnER'
                          . 'VJpvoRvnqdH+rpe+Whzig9oEmLJqutGor6ioSFwQoTpI5+bIQZ5OxbRtHz2MClTtIqgA80e0k1PIWszMXDtrTMIrn0ihTCqyHNFk'
                          . 'j6io1TWGnv2xXCx+fBgOzlcYADoQryO0yrwMn8bHHVMCeXS3XwGrkKyKlX9NPyVFA4wceOdFGCxMQXCfTxgaR09bmgu6CX32XMyT'
                          . 'Jk0IEjnfG4tZX4Vl+uYTmDs9XFZWFm8+R9la5JCOz+mC0zUpbDI2f3lZidVz5FOTS11A4/ASjcFOvK66ujphUZQ6gwR2lLNx3jOV'
                          . 'xugPlkH7sL5Q7X/rnIB/kbvmn7zvkpiY9pM1WUOuU3dnZwRVS6aevGK71EGh3zh/hEc8fWfxnj170mfMmLEwe2nIi+QG9iFrcDtZ'
                          . '9QUQbRqTaBLtaWSZ/Yxee4kE/eRzo1Tz6IFpmUN0g8mHcGRlZUWPGTNmamRfl6G0/WdpP07e7ziZjdeuF6LU85COGBXgaiAROobY'
                          . '1BaowXw8bsRkLTZpteXFdD7e83fS/m42f07nnY0sjFJMHx0Xd61WWwd334S8CJJkem7pwiNDTli0YjHdEB8EuSbrK6tWrUpetmxZ'
                          . 'z0Zdo07h4FZl+TpUhnO2s9GgTipxvKX2JLgWmxNTQJ/xUVNDzcG4QFdUj+tSWlqaYb4oioW+3bt3pyckJIyEUNON/AsaM82L49QY'
                          . 'h0Y6Z/cgs5DOrcWvT/VxXzLOMxzuPG3rAXrvATLKfCYO8Jgco3ENpOslE2OK7MXCwkLNYE/1IEVvRw+DQX/Ozt7+HD6nsrJyUqja'
                          . 'dXZRUdG2B63LfDfaTVAB5uxoUCaTpbrryNtJsbBUoSfaGh0Oxtpvz19WoAg03HYIKl2o4wrKakbvt4g1vVot6Vc3nNT0mpjzpdXh'
                          . '+0+cF89MGWRyOW9hx+e5YnPGacllFxZxk81RUlLipFarn/jmhzKp0+ridw4LR3KVaNvh5qeTcCQ9iJiaQ/sfmXelHpYfxLSE9j+V'
                          . 'TshUOiHrTGFDCIHKz89/bJKmL6q/N+Xm5m67W1sV5C3ThR5G4pMWN9Btdsr+Qsn1QXjUhQqdCPNyRCHQW/x5uqDxfBN+lxc1UunC'
                          . '3LR8+XJPTJGYXiffBNDxYC8uNNlNXmL6v0lY5EUAxCH+01p1MOlz9vf3cHj11LJQFLLZYGPjVNLSe5C15as0hk6RdX7a6QGb9Fl6'
                          . 'IzguLYlNWyNv423xwGaU+CjtNZjCoPE5Zi3rTW6AuRu/3ynCRI48kBbd5GiBM/JDmNK05X5QEa62+t50vF3o/MrD3+Xmiqj7ccvU'
                          . 'jCyW0s3N/DyTw+7epf9bZd/uRLsKKsCgkOUSHxUVtfHQW7MXvvD2IfHr9Z+JJY8PH46rFHGjIf4e+vLy8hMqlWoXgvgt43Kv10sL'
                          . 'JZIw0sCmIGwKQpEY1f+278NUwRs7sqVU1p9HB52ura197V4zmEhMV3+dV+qkUjpJPakQ24rAfSxAyXOmVku/IFdtg0ajQWfOsqNH'
                          . 'j2ZDjO50QsrW5Bb83lKPKrkwS1Jfd7u/f/hUv1UF2oYIFKsm90qn1zcWNpeOakLex3ueImkLkK1FP1bcqwiQpZIT7qes3Zjo54yC'
                          . 'N21VfLiDkz13eM9xsUFu9Tqdbuc9WPltTjPZax2GdhdUIFsuv7W1UeSkLY3ZtPdYvhOC/0P79pQswa62NjUeHh5jrlRdj804dnuR'
                          . 'JNkKJePUEH2h1GjBjg71loqSmLN131mB6YLVv4oUs8YOyKqqqpou37WahVyP/uROrPuxuGr6n7efQM6/2LBwPKIDriE0ii7ID+83'
                          . 'cP9uyK6alFXSUquJ1iIHqh/s7WIT7B3c3Z9cqtMPq+BuRwQpxeQixgzp7Ri4Zs2a96197DojZFi8OWuwu1IK9ZDLSNQAAAOfSURB'
                          . 'VLJ3Km3v7emsdAhBNYGQKhKv4/Gj+20bO8QnbMmmw1JZvdQ9Ocpnpw7ev+2z/4iGOwTkO9hLu1GHudO09FOSBTsv9tYM0i2ZZwTS'
                          . 'QX//ZDjE9Ci5z3Fwn5vbHrpT+5Lb8XKDQTyT+slJW5Tiiwj2EsfTnhQuTnbHkW7aVu1pHwZ0k8Dq8bfWbkPRGZDba3/JYmpEzgBc'
                          . '8CieC9akQwkqQPB/VlZWeGRk5KKtL8WuPHPuiguq7n98JE+BgHxnh65SEL05Lo5SuJP6XHGlN9pFo5jzqBCvm/83iencySGomn9a'
                          . 'q9VOu5uYokRccnLyBBLnBQ0GReK2zDNd3iPL1tmxq9iREi8G+vWoQm4+ufi3VZliGObRpsMJKpCnANbX1dVtHxTQc/WhDbPnZeeW'
                          . '2P5RCpxXiGGBnqK8sk5cKDV6667OkqDaooMp5k7nx/1UjwFzrhDT8UP7iN/NGVFSX18/zbIFi9wIMJJENDYlJWXmfy9e9ULbFkwv'
                          . 'qHs4i9RF0SJsQC+44Ftom1YhKJ4tG4ZhLOmQgmoC5evoxwISwbUjgjyX73tj1i8vXq52QHZVTl6p6KNyFb1I8JCx9ENhpTiQfV74'
                          . '9uoupkQESO/Hav76ndlCQ5btuufHX1cIsYhcPX+DwYAwDV96IHtkiJuyx5Cc78tsEfN68OuLUkzpnAkDxad/SkRsKyzZ9xsaGtYh'
                          . '9MUUq8kwDGNJhxZUE3I+/HPXrl1L7qNyeWrl06Pmr5gbEYx8fIjoriPfSe2g+3m5iX7eSoEsJlSc2nf8nLSw9fIvwkWZts6xUW/4'
                          . 'GJ1OK6p1Uv49ibP47mKFVGXKhazcpMeCkD0ltXpWKMRZNNSj7/wAoR+tbbvMMMyjR6cQVBPyxPkGPG7o9aEaH+X0wD7uMYtnhY28'
                          . 'Xq+3Q/fT/KJKslYrBIo7Y/X/krGbqfR+zIMieQCJA8jGGh3iLeZOChGePbohZRXxo6j7+W+DQb8XCzYIpyExbc9dZhimE9GpBNUc'
                          . 'dNIUxt7mr6KQsFqtDvNTuw7x9+wePHGYrx/9HTUbPYQxPtWUpI95UMyfItgYYUIXkOJ648aNUwUFBTkIyjcLTGcYhmkVnVZQzZEz'
                          . 'I76QH63CJKDmaYIMwzD3w/+FoDIMw3QEWFAZhmGsBAsqwzCMlWBBZRiGsRIsqAzDMFaCBZVhGMZKsKAyDMNYCRZUhmEYK8GCyjAM'
                          . 'YyVYUBmGYawECyrDMIyVYEFlGIaxEv8DQmG/Od6mX9oAAAAASUVORK5CYII=';
}