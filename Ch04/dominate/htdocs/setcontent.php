<div id="contentroot" style="margin: 5px;">
  <p><span id="greeting"></span> <span id="myname"></span>.</p>
</div>
<fb:js-string var="loggedinname">
  <fb:name uid="loggedinuser" useyou="false" />
</fb:js-string>
<script>
<!--
function update_content() {
  document.getElementById('greeting').setTextValue('Hello');
  document.getElementById('myname').setInnerFBML(loggedinname);
}
update_content();
//-->
</script>