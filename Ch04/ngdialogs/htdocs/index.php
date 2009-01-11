<fb:js-string var="pinchconfirmstr">
<fb:success>
  <fb:message>You pinched them!</fb:message>
</fb:success>
</fb:js-string>
<fb:js-string var="pinchcancelstr">
<fb:error>
  <fb:message>Chicken ... *brrgock*</fb:message>
</fb:error>
</fb:js-string>
<script>
  var statuelem = document.getElementById('actionstatus');

  function HugThemConfirm(username) {
    diaobj = new Dialog(Dialog.DIALOG_POP);
    var msg = 'You just gave ' + username + ' a big hug!';
    diaobj.showMessage('Show the love', msg);
  }

  function PokeThemConfirm(context, username) {
    diaobj = new Dialog(Dialog.DIALOG_CONTEXTUAL);
    diaobj.setContext(context);
    var msg = 'Do you really want to poke ' + username + '?';
    diaobj.showChoice(
      'Please Confirm',
      msg,
      'Poke',
      'Nevermind'
    );
  }

  function PinchThemConfirm(context, username) {
    diaobj = new Dialog(Dialog.DIALOG_CONTEXTUAL);
    diaobj.setContext(context);
	diaobj.onconfirm = function() {
      statuelem.setInnerFBML(pinchconfirmstr);
      statuelem.setStyle('display', '');
      setTimeout(function() { statuelem.setStyle('display', 'none') }, 1000 * 10);
    };
	diaobj.oncancel = function() {
      statuelem.setInnerFBML(pinchcancelstr);
      statuelem.setStyle('display', '');
      setTimeout(function() { statuelem.setStyle('display', 'none') }, 1000 * 10);
    };
    diaobj.showChoice(
      'Please Confirm',
      'Do you really want to pinch ' + username + '?',
      'Pinch',
      'Nevermind'
    );
  }

</script>
<div id="bodyroot" style="margin: 0 20px 20px 20px">
  <h3>YAPA (Yet Another Poke App)</h3>
  <p>Your friends are listed below. Do stuff to them.</p>
  <ul>
    <li>Nick -
      <a href="#" onclick="PokeThemConfirm(this, 'Nick'); return false;">Poke</a>,
      <a href="#" onclick="HugThemConfirm('Nick'); return false;">Hug</a>,
      <a href="#" onclick="PinchThemConfirm(this, 'Nick'); return false;">Pinch</a>.
  </ul>
  <div id="actionstatus"></div>
</div>
