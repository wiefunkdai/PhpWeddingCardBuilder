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

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'SDCImageBuilder.php');

/**
 * SDWeedingCardBuilder class
 */
class SDWeedingCardBuilder {
    public const OUTPUT_TYPE_PREVIEW = 0;
    public const OUTPUT_TYPE_DATA = 1;
    public const OUTPUT_TYPE_DOWNLOAD = 2;
    public $typeExport = 'jpg';

    private $_fields = array();
    private $_imageBuilder;
    private $_basePath;
    private $_baseImageSavePath;
    private $_usePhotoWedding = false;
    private $_frameBackgroundPhotoWedding;
    private $_frameBackgroundNameWedding;
    private $_frameBgPhotoHeightSize = 0;
    private $_messageDateTimeTopLocation = 0;
    private $_messageDateTimeHeightSize = 0;
    private $_messageBodyTopLocation = 0;
    private $_messageBodyHeightSize = 0;
    
    public function __construct($data)
    {        
        $data = $this->convertObjectToArray($data);
        if (class_exists('SDCImageBuilder') && $this->_imageBuilder===null) {
            $background = $this->getBasePath() . '/backgrounds/background_dark.jpg';
            $designModel = isset($data['designModel']) ? $data['designModel'] : 0;
            switch ($designModel) {
                case 1:
                    $background = $this->getBasePath() . '/backgrounds/background_pink.jpg';
                    $this->_frameBackgroundNameWedding = $this->getBasePath() . '/frames/name-frame_pink.png';
                    $this->_frameBackgroundPhotoWedding = $this->getBasePath() . '/frames/photo-frame_pink.png';
                break;
                case 2:
                    $background = $this->getBasePath() . '/backgrounds/background_blue.jpg';
                    $this->_frameBackgroundNameWedding = $this->getBasePath() . '/frames/name-frame_blue.png';
                    $this->_frameBackgroundPhotoWedding = $this->getBasePath() . '/frames/photo-frame_blue.png';
                break;
                case 3:
                    $background = $this->getBasePath() . '/backgrounds/background_purple.jpg';
                    $this->_frameBackgroundNameWedding = $this->getBasePath() . '/frames/name-frame_purple.png';
                    $this->_frameBackgroundPhotoWedding = $this->getBasePath() . '/frames/photo-frame_purple.png';
                break;
                default:
                    $background = $this->getBasePath() . '/backgrounds/background_dark.jpg';
                    $this->_frameBackgroundNameWedding = $this->getBasePath() . '/frames/name-frame_dark.png';
                    $this->_frameBackgroundPhotoWedding = $this->getBasePath() . '/frames/photo-frame_dark.png';
                break;
            }
            $this->_imageBuilder = new SDCImageBuilder($background);
            $this->_usePhotoWedding = false;
            $this->setFields($data);
        }
    }

    public function getBasePath() {
        if ($this->_basePath===null) {
            $this->setBasePath(__DIR__ . DIRECTORY_SEPARATOR . 'assets');
        }
        return $this->_basePath;
    }

    public function setBasePath($path) {
        if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath)) {
          throw new Exception("Asset path '{$path}' is not a valid directory.");
        }
    }

    public function convertPointToPixel(int $point) {
        return intval($point + ($point / 0.75));
    }

    public function addPhotoWedding($photoWedding=null) {
        if ($photoWedding !== null) {
            $this->_usePhotoWedding = true;
            $frameBackground = $this->_frameBackgroundPhotoWedding;
            $imageFrameBackground = new SDCImageBuilder($frameBackground);
            $frameBackgroundSize = $imageFrameBackground->getImageSize();
            $imageBuilderSize = $this->_imageBuilder->getImageSize();
            $frameLeftLocation = floor(($imageBuilderSize['width'] - $frameBackgroundSize['width']) / 2);
            $photoWeddingWidthSize = floor($frameBackgroundSize['width'] - 160);
            $photoWeddingHeightSize = floor($frameBackgroundSize['height'] - 240);
            $photoLeftLocation = floor(($frameLeftLocation + (($frameBackgroundSize['width'] - $photoWeddingWidthSize) / 2)));  
            $photoTopLocation = floor((($frameBackgroundSize['height'] - $photoWeddingHeightSize) / 2) + 10);          
            $imgPhotoBuilder = $this->_imageBuilder->addImage($photoWedding);
            $imgPhotoBuilder->setCropResize($photoWeddingWidthSize, $photoWeddingHeightSize);
            $imgPhotoBuilder->setLocation($photoLeftLocation, $photoTopLocation);   
            $imgFrameBgBuilder = $this->_imageBuilder->addImage($frameBackground)->setLocation($frameLeftLocation, 10);
            $this->_frameBgPhotoHeightSize = $frameBackgroundSize['height'];
        }
    }

    public function addWeddingName($husbandName, $wifeName) {
        $weddingNameText = $husbandName . PHP_EOL . '&' . PHP_EOL . $wifeName;
        $fontSize = $this->convertPointToPixel(48);
        $lineHeight = $this->convertPointToPixel(32);
        $fontColor = '#6A6A6A';
        $fontFamily = $this->getBasePath() . '/fonts/dancing.ttf';

        if ($this->_usePhotoWedding===false) {
            $frameBackground = $this->_frameBackgroundNameWedding;
            $imageFrameBackground = new SDCImageBuilder($frameBackground);
            $frameBackgroundSize = $imageFrameBackground->getImageSize();
            $imageBuilderSize = $this->_imageBuilder->getImageSize();
            $frameLeftLocation = floor(($imageBuilderSize['width'] - $frameBackgroundSize['width']) / 2);
            $imgFrameBgBuilder = $this->_imageBuilder->addImage($frameBackground)->setLocation($frameLeftLocation, 10);
            $imgFrameBgBuilder->addTextCenter($weddingNameText, true, $fontSize, $lineHeight, $fontColor, $fontFamily);
        } else {
            $fontSize = $this->convertPointToPixel(48);
            $topLocationFramePhotoWedding = $this->_frameBgPhotoHeightSize;
            $this->_imageBuilder->addTextHorizontal($weddingNameText, true,  $topLocationFramePhotoWedding, $fontSize, $lineHeight, $fontColor, $fontFamily);
        }
    }

    public function addMessageCard($greetingMessage, $closingMessage) {
        $fontColor = '#858586';
        $fontSizeGreetingText = $this->convertPointToPixel(18);
        $lineHeightGreetingText = $this->convertPointToPixel(10);
        $fontSizeClosingText = $this->convertPointToPixel(18);
        $lineHeightClosingText = $this->convertPointToPixel(10);
        $fontFamilyGreetingText = $this->getBasePath() . '/fonts/arialbold.ttf';
        $fontFamilyClosingText = $this->getBasePath() . '/fonts/dancing.ttf';
        $frameBackground = $this->_frameBackgroundNameWedding;
        $imageFrameBackground = new SDCImageBuilder($frameBackground);
        $frameBackgroundSize = $imageFrameBackground->getImageSize();
        $imageBuilderSize = $this->_imageBuilder->getImageSize();
        $heightSizeGreetingText = $this->_messageBodyHeightSize = $this->_imageBuilder->getHeightWrapText($greetingMessage, $fontSizeGreetingText, $lineHeightGreetingText, $fontFamilyGreetingText);
        $heightSizeClosingText = $this->_imageBuilder->getHeightWrapText($closingMessage, $fontSizeClosingText, $lineHeightClosingText, $fontFamilyClosingText);
        $topLocationGreetingText = $this->_messageBodyTopLocation = floor($frameBackgroundSize['height'] + 25);
        $topLocationClosingText = floor($imageBuilderSize['height'] - ($heightSizeClosingText + 80));
        $this->_imageBuilder->addTextHorizontal($greetingMessage, true, $topLocationGreetingText, $fontSizeGreetingText, $lineHeightGreetingText, $fontColor, $fontFamilyGreetingText);
        $this->_imageBuilder->addTextHorizontal($closingMessage, true, $topLocationClosingText, $fontSizeClosingText, $lineHeightClosingText, $fontColor, $fontFamilyClosingText);
    }

    public function addTimeWedding($dateWedding, $timeWedding) {
        $fontSize = $this->convertPointToPixel(32);
        $lineHeight = $this->convertPointToPixel(20);
        $fontColor = '#6A6A6A';
        $fontFamily = $this->getBasePath() . '/fonts/twcenbold.ttf';
        $imageBuilderSize = $this->_imageBuilder->getImageSize();
        $dateWeddingText = date_format(date_create($dateWedding), 'd M Y');
        $partTime = explode(':', $timeWedding);
        $timeWeddingText = date('h:i A', mktime($partTime[0], $partTime[1]));
        $dateTimeWeddingText = strtoupper($dateWeddingText . PHP_EOL . 'at ' . $timeWeddingText);
        $topLocationWeddingText = $this->_messageDateTimeTopLocation = $this->_messageBodyTopLocation!==null ? floor($this->_messageBodyTopLocation + $this->_messageBodyHeightSize + 25) : $lineHeight;
        $heightSizeWeddingText = $this->_messageDateTimeHeightSize =  $this->_imageBuilder->getHeightWrapText($dateTimeWeddingText, $fontSize, $lineHeight, $fontFamily);
        $this->_imageBuilder->addTextHorizontal($dateTimeWeddingText, true, $topLocationWeddingText, $fontSize, $lineHeight, $fontColor, $fontFamily);
    }

    public function addAddressWedding($addressWedding) {
        $fontSize = $this->convertPointToPixel(22);
        $lineHeight = $this->convertPointToPixel(10);
        $fontColor = '#6A6A6A';
        $fontFamily = $this->getBasePath() . '/fonts/twcen.ttf';
        $imageBuilderSize = $this->_imageBuilder->getImageSize();
        $topLocationAddressText = $this->_messageDateTimeTopLocation!==null ? floor($this->_messageDateTimeTopLocation + $this->_messageDateTimeHeightSize) : $lineHeight;
        $this->_imageBuilder->addTextHorizontal($addressWedding, true, $topLocationAddressText, $fontSize, $lineHeight, $fontColor, $fontFamily);
    }

    public function clearCache() {
        if ($this->_baseImageSavePath!==null) {
            unlink($this->_baseImageSavePath);
        }
        $this->_imageBuilder->clear();
    }

    public function getFileSave() {
        return $this->_baseImageSavePath;
    }

    public function save($fileName=null, $filePath=null) {
        $data = $this->getFields();
        $this->checkInvalidExtOutput($this->typeExport);
        $this->createInternal();
        if ($fileName===null) {
            $fileName = time().'.' .$this->typeExport;
        }
        if ($filePath===null) {
            $this->_baseImageSavePath = $imagePathFile = $this->getBasePath() . '/cache/' . $fileName;
        } else {
            $this->_baseImageSavePath = $imagePathFile = $filePath . '/cache/' . $fileName;
        }
        $this->_imageBuilder->save($this->typeExport, $imagePathFile); 
    }

    public function export(int $type) {
        $data = $this->getFields();
        $this->checkInvalidExtOutput($this->typeExport);
        $this->createInternal();
        switch($type) {
            case 1:
                return $this->_imageBuilder->output($this->typeExport, true);
            case 2:
                $fileName = $data['husbandName'] . '-' . $data['wifeName'];
                $this->_imageBuilder->download($this->typeExport, $fileName);
            default:
                $fileName = $data['husbandName'] . '-' . $data['wifeName'];
                header('Content-Disposition: inline; filename="'.$fileName.'"');
                $this->_imageBuilder->output($this->typeExport);
            break;
        }
    }

    protected function createInternal() {
        if ($this->_imageBuilder === null) {
            throw new Exception('Please ImageBuilder class to first.');
        }
        $data = $this->getFields();
        $this->addPhotoWedding($data['photoWedding']);
        $this->addWeddingName($data['husbandName'], $data['wifeName']);
        $this->addMessageCard($data['greetingMessage'], $data['closingMessage']);
        $this->addTimeWedding($data['dateWedding'], $data['timeWedding']);
        $this->addAddressWedding($data['addressWedding']);
    }

    public function getImageSize() {
       return $this->_imageBuilder->getImageSize();
    }

    public function getFields() {
        return $this->_fields;
    }

    protected function setFields($data) {
        $data = $this->convertObjectToArray($data);
        $this->_fields = array(
            'designModel' => isset($data['designModel']) ? $data['designModel'] : 0,
            'husbandName' => isset($data['husbandName']) ? $data['husbandName'] : '',
            'wifeName' => isset($data['wifeName']) ? $data['wifeName'] : '',
            'dateWedding' => isset($data['dateWedding']) ? $data['dateWedding'] : '',
            'timeWedding' => isset($data['timeWedding']) ? $data['timeWedding'] : '',
            'addressWedding' => isset($data['addressWedding']) ? $data['addressWedding'] : '',
            'greetingMessage' => isset($data['greetingMessage']) ? $data['greetingMessage'] : '',
            'closingMessage' => isset($data['closingMessage']) ? $data['closingMessage'] : '',
            'photoWedding' => isset($data['photoWedding']) && !empty($data['photoWedding']) ? $data['photoWedding'] : null
        );
        if (isset($data['photoWedding'])) {
            if (isset($empty['photoWedding']['tmp_name']) && (!empty($data['photoWedding']['tmp_name']) || $data['photoWedding']['tmp_name']!==null)) {
                $this->_fields['photoWedding'] = $data['photoWedding']['tmp_name'];
            }
        }
    }

    private function checkInvalidExtOutput($type) {
        $types = ['jpg', 'bmp', 'gif', 'png'];
        if (!in_array($type, $types)) {
            throw new Exception('Extention file for output or export image is invalid!');
        }
    }

    private function convertObjectToArray($data) {
        $result = array();
        if ($data!== null && (is_array($data) || is_object($data))) {
            foreach ($data as $key => $value) {
                if ((is_array($value) || is_object($value))) {
                    $result[$key] = $this->convertObjectToArray($value);
                } else {                    
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}