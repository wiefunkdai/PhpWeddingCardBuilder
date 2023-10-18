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

/**
 * SDCImageAdditional class
 */
class SDCImageAdditional {
    private $_additionalImage;
    private $_additionalImageSize;
    private $_additionalImageLocation;
    private $_mainImage;

    public function __construct($masterImage, $additionalImage) {
        $this->setMainImage($masterImage);
        $this->setAdditionalImage($additionalImage);
    }

    protected function setMainImage($image) {
        $this->_mainImage = $image;
    }

    protected function setAdditionalImage($image) {
        $imageType = @exif_imagetype($image);
        if (!$imageType) {
            throw new Exception('Unknown type of additional image!');
        }

        try {
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $additionalImage = @imagecreatefromjpeg($image);
                break;
                case IMAGETYPE_PNG:
                    $additionalImage = @imagecreatefrompng($image);
                break;
                case IMAGETYPE_BMP:
                    $additionalImage = @imagecreatefrombmp($image);
                break;
                case IMAGETYPE_GIF:
                    $additionalImage = @imagecreatefromgif($image);
                break;
                default:
                    $additionalImage = @imagecreatefromstring($image);
                break;
            }
            
            $width = imagesx($additionalImage);
            $height = imagesy($additionalImage);

            $this->_additionalImage = $this->createLayoutDocument($width, $height);
            imagecopyresampled($this->_additionalImage, $additionalImage, 0, 0, 0, 0, $width, $height, $width, $height);
            imagesavealpha($this->_additionalImage, true); 
   
        } catch(Exception $e) {
            throw new Exception('setDefaultImage Error: ' . $e->getMessage());
        }
    }

    protected function createLayoutDocument($width, $height, $return=false) {
      $documentImage = imagecreatetruecolor($width, $height);
      $transparent = imagecolorallocatealpha($documentImage, 0, 0, 0, 127);
      imagealphablending($documentImage, true);
      imagefill($documentImage, 0, 0, $transparent);
      return $documentImage;
    }

    public function setResize($width, $height) {
        $imageWidth  = imagesx($this->_additionalImage);
        $imageHeight = imagesy($this->_additionalImage);
        if ($imageWidth > $imageHeight) {
            $ratio = $height / $imageHeight;
            $resizeWidth = $imageWidth * $ratio;
            $resizeHeight = $height;
        } else {
            $ratio = $width / $imageWidth;
            $resizeHeight = $imageHeight * $ratio;
            $resizeWidth = $width;
        }
        $imageResized = $this->createLayoutDocument($width, $height);
        imagecopyresampled($imageResized, $this->_additionalImage, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);
        imagesavealpha($imageResized, true);
        $this->_additionalImage = $imageResized;
        return $this;
    }

    public function setLocation($x, $y) {
        $this->_additionalImageLocation = array(
            'x' => $x,
            'y' => $y
        );
        return $this;
    }

    public function render() {
        $width = imagesx($this->_additionalImage);
        $height = imagesy($this->_additionalImage);
        if ($this->_additionalImageLocation===null) {
            imagecopyresampled($this->_mainImage, $this->_additionalImage, 0, 0, 0, 0, $width, $height, $width, $height);
        } else {
            $pos = $this->_additionalImageLocation;
            imagecopyresampled($this->_mainImage, $this->_additionalImage, $pos['x'], $pos['y'], 0, 0, $width, $height, $width, $height);
        }        
        imagesavealpha($this->_mainImage, true);
    }
}