<?php
  include_once 'lib/AppConfig.class.php';
  $ajax_url = AppConfig::$app_callback . 'update.php';
?>
<fb:dashboard></fb:dashboard>
<div id="bodyroot" style="margin: 0 20px 20px 20px">
  <div id="ajaxstatus"></div>
  <div id="ajaxcontent"></div>
</div>
<fb:js-string var="pleasewait">
<fb:success>
  <fb:message>Please wait while the feed updates.</fb:message>
</fb:success>
</fb:js-string>
<fb:js-string var="errorgeneral">
<fb:error>
  <fb:message>There was an error updating the feed.</fb:message>
</fb:error>
</fb:js-string>
<script>
  var statuelem = document.getElementById('ajaxstatus');
  var contentelem = document.getElementById('ajaxcontent');

  function loadDiggContent() {
    var ajax = new Ajax();
    ajax.responseType = Ajax.FBML;
    ajax.onerror = function() {
      contentelem.setStyle('display', 'none');
      statuelem.setInnerFBML(errorgeneral);
      statuelem.setStyle('display', '');
    }
    ajax.ondone = function(data) {
      contentelem.setInnerFBML(data);
      statuelem.setStyle('display', 'none');
      contentelem.setStyle('display', '');
    }
    ajax.requireLogin = 0;
    ajax.post('<?= $ajax_url ?>', '');
  }

  function reloadData() {
    loadDiggContent();
    setTimeout(function() { reloadData() }, 1000 * 50); // 3 minutes
  }

  statuelem.setInnerFBML(pleasewait);
  reloadData();
</script>