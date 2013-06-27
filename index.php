<?php
require_once 'AppDotNet.php';
require_once 'config.php';

$redirectUri  = 'http://jonathonduerig.com/my-rss-stream/complete.php';
$scope        =  array('stream');

$app = new AppDotNet($clientId,$clientSecret);
$url = $app->getAuthUrl($redirectUri,$scope);

?>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <title>My RSS Stream</title>
  <link rel="stylesheet" style="text/css"
        href="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" style="text/css"
        href="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap-responsive.min.css">
  <script src="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap.min.css"></script>
</head>
<body>
  <div class="container">
    <div class="page-header">
      <h1><a href="http://rss-app.net/"><img src="my-rss-stream.png"></a>My RSS Stream</h1>
    </div>
    <div class="hero-unit">
      <p>There is no native way in <a href="http://app.net">app.net</a> to get an RSS feed of 'My Stream'. This app helps bridge that gap. Authorize this app by clicking on the button below and you will be given a URL which provides an RSS feed of your personal 'My Stream'.</p>
<?php
  echo '<a href="' . $url . '" class="btn btn-primary">Authorize</a>';
?>
    </div>
    <div class="page-footer">
      <p>This app is developed and maintained by <a href="http://jonathonduerig.com">Jonathon Duerig</a>. His app.net username is <a href="http://alpha.app.net/duerig">@duerig</a></p>
      <p>Our privacy policy is <a href="http://www.privacychoice.org/policy/mobile?policy=7e57611e9551c6105db81e5c27f3a415">here</a>.</p>
    </div>
  </div>
</body>
</html>
