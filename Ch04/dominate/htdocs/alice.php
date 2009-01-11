<div id="contentroot">
  <p><a id="drinkmeobj" href="#">Drink Me</a></p>
  <p><a id="eatmeobj" href="#">Eat Me</a></p>
  <p id="alice">What a curious feeling!</p>
</div>
<script type="text/javascript">
function shrinkText() {
  document.getElementById("alice").setStyle('fontSize', '50%');
}
function GrowText() {
  document.getElementById("alice").setStyle('fontSize', '200%');
}
function load() { 
  document.getElementById("drinkmeobj").addEventListener("click", shrinkText); 
  document.getElementById("eatmeobj").addEventListener("click", GrowText); 
} 
load();
</script> 
