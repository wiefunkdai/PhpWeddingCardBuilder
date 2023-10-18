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

require_once(__DIR__ . '/libraries/SDWeedingCardBuilder.php');
if (!class_exists('SDWeedingCardBuilder')) {
    throw new Exception('SDWeedingCardBuilder class not found!');
}

$post = isset($_POST) ? $_POST : array();
$pageTitle = 'StephanusDai - Wedding Card Builder';
if (isset($_FILES['photoWedding'])) {
    $photoWeddingFile = $_FILES['photoWedding']['tmp_name']!==null && !empty($_FILES['photoWedding']['tmp_name']) ? $_FILES['photoWedding']['tmp_name'] : null;
    $post['photoWedding'] = $photoWeddingFile!==null && !empty($photoWeddingFile) ? base64_encode(file_get_contents($photoWeddingFile)) : null;
}
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest') {
    $cardBuilder = new SDWeedingCardBuilder($post);
    header('Content-type: application/json');
    echo(json_encode(array(
        'preview' => $cardBuilder->export(SDWeedingCardBuilder::OUTPUT_TYPE_DATA)
    )));
    exit();
} else if (isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'POST')) { 
    $cardBuilder = new SDWeedingCardBuilder($post);
    $cardBuilderSize = $cardBuilder->getImageSize();
    $husbandName = isset($post['husbandName']) ? $post['husbandName'] : '';
    $wifeName = isset($post['wifeName']) ? $post['wifeName'] : '';
    $weddingNameText = $husbandName . '-' . $wifeName;
    $cardLabelSize = ' (JPEG Image' . $cardBuilderSize['width'] . 'x' . $cardBuilderSize['height'] . ')';
    $pageTitle = $weddingNameText . $cardLabelSize;
        
    if (isset($_GET['download'])!==false) {
        $cardBuilder->export(SDWeedingCardBuilder::OUTPUT_TYPE_DOWNLOAD);
        exit();
    } elseif (isset($_GET['preview'])!==false) {
        header('Content-Disposition: inline; filename="'.$weddingNameText.'"');
        $cardBuilder->export(SDWeedingCardBuilder::OUTPUT_TYPE_PREVIEW);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
    <title><?= $pageTitle; ?></title>
    <meta name="viewport" content="width=device-width; height=device-height;">
    <style type="text/css">
        body {
            background: #222;
            text-align: center;
            padding: 20%;
            overflow: hidden;
        }

        img {
            display: inline-block;
            vertical-align: middle;
            height: 60px;
        }

        .loading span {
            display: inline-block;
            vertical-align: middle;
            width: .6em;
            height: .6em;
            margin: .19em;
            background: #007DB6;
            border-radius: .6em;
            -webkit-animation: loading 1s infinite alternate;
                    animation: loading 1s infinite alternate;
        }

        .loading span:nth-of-type(2) {
            background: #008FB2;
            -webkit-animation-delay: 0.2s;
                    animation-delay: 0.2s;
        }
        .loading span:nth-of-type(3) {
            background: #009B9E;
            -webkit-animation-delay: 0.4s;
                    animation-delay: 0.4s;
        }
        .loading span:nth-of-type(4) {
            background: #00A77D;
            -webkit-animation-delay: 0.6s;
                    animation-delay: 0.6s;
        }
        .loading span:nth-of-type(5) {
            background: #00B247;
            -webkit-animation-delay: 0.8s;
                    animation-delay: 0.8s;
        }
        .loading span:nth-of-type(6) {
            background: #5AB027;
            -webkit-animation-delay: 1.0s;
                    animation-delay: 1.0s;
        }
        .loading span:nth-of-type(7) {
            background: #A0B61E;
            -webkit-animation-delay: 1.2s;
                    animation-delay: 1.2s;
        }
        .loading span:nth-of-type(8) {
            background: #DCE35B;
            -webkit-animation-delay: 1.2s;
                    animation-delay: 1.2s;
        }
        .loading span:nth-of-type(9) {
            background: #CAC531;
            -webkit-animation-delay: 1.2s;
                    animation-delay: 1.2s;
        }

        @-webkit-keyframes loading {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        @keyframes loading {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>
<head>
</head>
<body>  
    <figure>
        <img src="assets/images/stephanusdai-logo.png" style="border:none" />
        <div class="loading">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </figure>
    <?php if(count($post) > 0): ?>
        <form id="WeddingForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <?php foreach($post as $name=>$value): ?>
                <input type="hidden" name="<?= $name ?>" value="<?= $value ?>">
            <?php endforeach; ?>
        </form>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {    
                const weddingForm = document.getElementById('WeddingForm');
                if (weddingForm != null) {
                    weddingForm.setAttribute('action', '<?= $_SERVER['PHP_SELF'] ?>?download=1');
                    weddingForm.submit();
                    setTimeout(() => {
                        weddingForm.setAttribute('action', '<?= $_SERVER['PHP_SELF'] ?>?preview=1');
                        weddingForm.submit();
                    }, 1500);               
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>