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
?>
<!--//
Wedding Card Design Builder by Stephanus Dai
@author   : Stephanus Bagus Saputra
            ( 戴 Dai 偉 Wie 峯 Funk )
@email    : wiefunk@stephanusdai.web.id
@contact  : http://t.me/wiefunkdai
@support  : http://opencollective.com/wiefunkdai
@link     : http://www.stephanusdai.web.id
Copyright (c) ID 2023 Stephanus Bagus Saputra. All rights reserved.
Terms of the following https://stephanusdai.web.id/p/license.html
//-->
<!DOCTYPE html>
<html>
<head>
	<title>Wedding Card Design Builder - Digital Printing Builder by Stephanus Dai</title>
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <meta name="author" content="Stephanus Bagus Saputra">
    <link rel="stylesheet" type="text/css" href="assets/css/main.css" />
</head>
<body>
<header class="navbar">
    <div class="container">
        <div class="navbar-title">
            <span class="logo sdailover"></span>
            <span class="caption">PrintDigital Builder</span>
        </div>
        <nav class="navbar-menu">
            <a href="https://opencollective.com/wiefunkdai" class="navbar-link" title="Support with Donation">
                <span class="navbar-link-icon opencollective"></span>
            </a>
            <a href="https://github.com/wiefunkdai" class="navbar-link" title="View on GitHub">
                <span class="navbar-link-icon github"></span>
            </a>
        </nav>
    </div>
</header>
<section class="design-form-builder">
    <div class="design-content container">
        <div class="design-switcher-panel">
            <div class="design-switcher">
                <ul class="nav-switcher">
                    <li class="nav-item">
                        <button class="nav-link active" data-value="0" title="Dark Wedding Card">
                            <img alt="Dark Wedding Card" src="assets/images/thumbnails/design-dark.jpg"/>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-value="1" title="Pink Wedding Card">
                            <img alt="Pink Wedding Card" src="assets/images/thumbnails/design-pink.jpg"/>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-value="2" title="Blue Wedding Card">
                            <img alt="Blue Wedding Card" src="assets/images/thumbnails/design-blue.jpg"/>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-value="3" title="Purple Wedding Card">
                            <img alt="Purple Wedding Card" src="assets/images/thumbnails/design-purple.jpg"/>
                        </button>
                    </li>
                </ul>
            </div>
            <button class="nav-toggle"></button>
        </div>
        <div class="design-window-panel">
            <div class="design-preview-panel">
                <h2 class="panel-title">Preview</h2>
                <div class="design-preview-image">
                    <img id="renderPreview" alt="Render Preview" src="assets/images/thumbnails/design-dark.jpg"/>
                </div>
                <div class="design-render-panel">
                    <button id="RenderCard" class="render-button" type="submit">Render</button>
                </div>
            </div>
            <div class="design-form-panel">
                <h2 class="panel-title">Form Builder</h2>
                <form id="WeddingForm" action="builder.php" method="post" class="form-group" enctype="multipart/form-data">
                    <input name="designModel" type="hidden" value="0" />
                    <h4 class="form-title">Photo Pre-Wedding</h4>
                    <div class="input-group">
                        <label for="photoWedding" class="form-label">Photo Wedding:</label>
                        <input name="photoWedding" type="file" class="form-control" id="photoWedding">
                    </div>
                    <h4 class="form-title">Wedding's Names</h4>
                    <div class="form-column">
                        <div class="col-start">
                            <label for="husbandName" class="form-label">Husband's Name:</label>
                            <input name="husbandName" type="text" class="form-control" id="husbandName" placeholder="Husband Name" value="Kudo Sinichi">
                        </div>
                        <div class="col-end">
                            <label for="wifeName" class="form-label">Wife's Name:</label>
                            <input name="wifeName" type="text" class="form-control" id="wifeName" placeholder="Wife Name" value="Mouri Ran">
                        </div>                        
                    </div>
                    <h4 class="form-title">Date & Time Wedding</h4>
                    <div class="form-column">
                        <div class="col-start">
                            <label for="dateWedding" class="form-label">Date Wedding:</label>
                            <input name="dateWedding" type="date" class="form-control" id="dateWedding" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-end">
                            <label for="timeWedding" class="form-label">Time Wedding:</label>
                            <input name="timeWedding" type="time" class="form-control" id="timeWedding" value="<?= date('H:i') ?>">
                        </div>                        
                    </div>
                    <h4 class="form-title">Address Wedding</h4>
                    <div class="input-group">
                        <label for="addressWedding" class="form-label">Address Wedding:</label>
                        <textarea name="addressWedding" class="form-control" id="addressWedding">STREET ROAD 123, ANY CITY&#13;ANY STATE, ANY COUNTRY</textarea>
                    </div>
                    <h4 class="form-title">Message Card</h4>
                    <div class="input-group">
                        <label for="greetingMessage" class="form-label">Greeting Message:</label>
                        <textarea name="greetingMessage" class="form-control" id="greetingMessage">WE INVITE YOU TO SHARE WITH US A&#13;CELEBRATION OF LOVE AND COMMITMENT</textarea>
                    </div>
                    <div class="input-group">
                        <label for="closingMessage" class="form-label">Closing Message:</label>
                        <textarea name="closingMessage" class="form-control" id="closingMessage" style="height:90px;">We are happy and looking&#13;forward to welcoming&#13;You and Family</textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<div class="container">
    <footer>
        <div class="supportby">
            <a href="https://opencollective.com/wiefunkdai" target="_blank">
                <img alt="StephanusDai OpenCollective" height="48" src="assets/images/supportby-button.png" title="Thanks for Your Donation">
            </a>
        </div>
        <div class="authorby">
            <a href="https://stephanusdai.web.id" target="_blank">
                <img alt="StephanusDai Blog" height="48" src="assets/images/authorwrittenby.png" title="Author by Stephanus Bagus Saputra">
            </a>
        </div>
        <div class="copyright">
            <div class="content">
                <a href="https://stephanusdai.web.id" class="logo">
                    <span class="sdailover-icon"></span>
                </a>
                <span class="author">&copy; ID 2023 StephanusDai Developer<br />All rights reserved.</span>
            </div>
        </div>
    </footer>
</div>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const switcherPanel = document.querySelector(".design-form-builder .design-switcher-panel");
        const designSwitcher = document.querySelector(".design-form-builder .design-switcher");
        const designWindow = document.querySelector(".design-form-builder .design-window-panel");
        const designContent = document.querySelector(".design-form-builder .design-content");
        const toggleButton = switcherPanel.querySelector(".nav-toggle");

        if (toggleButton != null) {
            toggleButton.addEventListener("click", function(e) { 
                if (switcherPanel != null) {
                    if (switcherPanel.classList.contains("expanded")) {
                        switcherPanel.classList.remove("expanded");
                    } else {
                        switcherPanel.classList.add("expanded");
                    }
                }
            }, true);
        }

        if ((designSwitcher != null) && (designWindow != null)) {
            designSwitcher.style.maxHeight = designWindow.offsetHeight + "px";
        }
        if ((designContent != null) && (switcherPanel != null)) {
            switcherPanel.style.maxWidth = designContent.offsetWidth + "px";
        }

        window.addEventListener('resize', function() {
            if ((designSwitcher != null) && (designWindow != null)) {
                designSwitcher.style.maxHeight = designWindow.offsetHeight + "px";
            }
            if ((designContent != null) && (switcherPanel != null)) {
                switcherPanel.style.maxWidth = designContent.offsetWidth + "px";
            }
        }, true);
        const weddingForm = document.getElementById('WeddingForm');
        const designSwitchers = document.querySelectorAll('.design-switcher .nav-switcher .nav-item .nav-link');
        if (designSwitchers.length > 0) {
            designSwitchers.forEach(function(designSwitcher) {
                designSwitcher.addEventListener('click', function() {
                    if (this.classList.contains('active')==false) {
                        removeActiveDesignSwitchers();
                        this.classList.add('active');
                    }
                    if (weddingForm != null) {                            
                        const designInput = weddingForm.querySelector('input[name=\'designModel\']');
                        let designSelectedValue = this.getAttribute('data-value');
                        designInput.setAttribute('value', designSelectedValue);
                        const designPanel = document.querySelector('.design-switcher-panel');
                        if (designPanel.classList.contains('expanded')!=false) {
                            designPanel.classList.remove('expanded');
                        }
                        sendHttpRequest();
                    }                        
                });
            });
            function removeActiveDesignSwitchers() {
                designSwitchers.forEach(function(designSwitcher) {
                    if (designSwitcher.classList.contains('active')) {
                        designSwitcher.classList.remove('active');
                    }
                });
            }
        }
        const renderButton = document.getElementById('RenderCard');
        if (weddingForm != null) {
            const weddingInputs = weddingForm.querySelectorAll('input');
            const weddingTextAreas = weddingForm.querySelectorAll('textarea');
            if (weddingInputs.length > 0) {
                weddingInputs.forEach(function(inputForm) {
                    inputForm.addEventListener('focusout', function() {
                        sendHttpRequest();
                    });
                });
            }
            if (weddingTextAreas.length > 0) {
                weddingTextAreas.forEach(function(textareaForm) {
                    textareaForm.addEventListener('focusout', function() {
                        sendHttpRequest();
                    });
                });
            }
            if (renderButton != null) {
                renderButton.addEventListener('click', function() {
                    weddingForm.submit();
                });
            }
            renderButton
        }
        function sendHttpRequest() {       
            var httpRequest;         
            if (window.XMLHttpRequest) {
                httpRequest = new XMLHttpRequest();
            } else {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            }
            if (httpRequest !== null) {
                httpRequest.onreadystatechange = function() {
                    if (httpRequest.readyState === 4 & httpRequest.status === 200) {
                        const renderPreview = document.getElementById('renderPreview');
                        let dataJson = JSON.parse(httpRequest.responseText);
                        renderPreview.setAttribute('src', dataJson.preview);
                    }
                }
                var data = new FormData(weddingForm);
                httpRequest.open('POST', 'builder.php');
                httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                httpRequest.send(data);
            }
        }
    });
</script>
</body>
</html>