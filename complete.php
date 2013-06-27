<?php
require_once 'AppDotNet.php';
require_once 'config.php';

$redirectUri  = 'http://jonathonduerig.com/my-rss-stream/complete.php';

$app = new AppDotNet($clientId,$clientSecret);
$token = $app->getAccessToken($redirectUri);

?>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <title>My RSS Stream (Authorized)</title>
  <link rel="stylesheet" style="text/css"
        href="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" style="text/css"
        href="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap-responsive.min.css">
  <script src="https://s3.amazonaws.com/lib-storage/bootstrap/css/bootstrap.min.css"></script>
</head>
<body>
  <div class="container">
    <div class="page-header">
      <h1><a href="http://jonathonduerig.com/my-rss-stream/"><img src="my-rss-stream.png"></a>My RSS Stream <small>(Authorization Complete)</small></h1>
    </div>
    <div class="hero-unit">
      <p>My RSS Stream is now authorized and you can use the URL below to access an RSS feed of your personalized 'My Stream'. Bookmark or copy and paste it into your RSS reader of choice.</p>
<?php
  echo '<h3><a href="http://jonathonduerig.com/my-rss-stream/rss.php?token='
       . $token . '">RSS Feed Link</a><h3>';
?>
    </div>
    <p>This app is developed and maintained by <a href="http://jonathonduerig.com">Jonathon Duerig</a>. His app.net username is <a href="http://alpha.app.net/duerig">@duerig</a></p>
  </div>
</body>
</html>
