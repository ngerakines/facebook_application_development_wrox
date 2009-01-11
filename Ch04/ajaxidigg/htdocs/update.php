<?php

function fetch_diggs() {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://services.digg.com/stories/?type=json&count=10&appkey=http%3A%2F%2Fwrox.com');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, 'ExampleFacebookApp/0.1');
  $body = curl_exec($ch);
  if (curl_errno($ch)) {
    return array();
  } else {
    curl_close($ch);
    $data = json_decode($body);
    return $data;
  }
}

$data = fetch_diggs();

?>
<p>Displaying <?= $data->count ?> articles.</p>
<ul>
<?php foreach ($data->stories as $story) { ?>
<li><a href="<?= $story->href ?>"><?= $story->title ?></a></li>
<?php } ?>
</ul>