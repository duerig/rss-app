<?php
require_once 'AppDotNet.php';
require_once 'config.php';

if (array_key_exists('token', $_GET)
   || array_key_exists('user', $_GET)
   || array_key_exists('global', $_GET)
   || array_key_exists('channel', $_GET)) {
  printRss();
} else {
  printError();
}

function printRss() {
  $app = new AppDotNet($clientId,$clientSecret);

  if (array_key_exists('token', $_GET)) {
    $token = $_GET['token'];
    $app->setAccessToken($token);
  } else {
    $token = $app->getAppAccessToken();
  }
  $replies = 1;
  if (array_key_exists('replies', $_GET)) {
    $replies = intval($_GET['replies']);
  }
  $directed = 1;
  if (array_key_exists('directed', $_GET)) {
    $directed = intval($_GET['directed']);
  }  
  header('Content-Type: application/rss+xml');
  echo '<?xml version="1.0" encoding="utf-8"?>';
  echo '<rss xmlns:atom="http://www.w3.org/2005/Atom" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:georss="http://www.georss.org/georss" version="2.0">';
  echo '<channel>';

  $params = array();
  $params['include_deleted'] = 0;
  if ($directed == 1) {
    $params['include_directed_posts'] = 1;
  } else {
    $params['include_directed_posts'] = 0;
  }
  if (array_key_exists('user', $_GET)) {
    $user = $_GET['user'];
    $streamData = $app->getUserPosts($user, $params);
    echo '<title>Stream for user ' . $user . ' - App.net</title>';
    echo '<link>http://jonathonduerig.com/my-rss-stream/</link>';
    echo '<description>Posts by user ' . $user . '</description>';
  } elseif (array_key_exists('global', $_GET)) {
    $streamData = $app->getPublicPosts($params);
    echo '<title>Global Stream - App.net</title>';
    echo '<link>http://jonathonduerig.com/my-rss-stream/</link>';
    echo '<description>Posts on app.net</description>';
  } elseif (array_key_exists('channel', $_GET)) {
    $streamData = $app->getMessages($_GET['channel']);
    echo '<title>Stream for channel ' . $_GET['channel'] . '</title>';
    echo '<link>http://blog-app.net/#' . $_GET['channel'] . '</link>';
    echo '<description>Posts on app.net</description>';    
  } else {
    $streamData = $app->getUserStream($params);
    echo '<title>My Stream - App.net</title>';
    echo '<link>http://jonathonduerig.com/my-rss-stream/</link>';
    echo '<description>Posts in your stream</description>';
  }

  $link = str_replace(array("&", "<", ">", "\"", "'"),
        array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"), $_SERVER['REQUEST_URI']);
  echo '<atom:link href="http://jonathonduerig.com' . $link . '" type="application/rss+xml" rel="self"/>';

  $stream = $streamData;
  foreach($stream as $post) {
    if ($replies == 1 || ! array_key_exists('reply_to', $post)) {
      $username = htmlspecialchars($post['user']['username']);
      $text = htmlspecialchars($post['text']);
      $html = htmlspecialchars($post['html']);
      $date = htmlspecialchars(gmdate(DATE_RSS,
                                      strtotime($post['created_at'])));
      $link = htmlspecialchars($post['canonical_url']);
      echo '<item>';
      if (array_key_exists('user', $_GET)) {
        echo '<title>' . $text . '</title>';
      } else {
        echo '<title>' . $username . ': ' . $text . '</title>';
      }
      echo '<description>' . $html . '</description>';
      echo '<pubDate>' . $date . '</pubDate>';
      echo '<guid>' . $link . '</guid>';
      echo '<link>' . $link . '</link>';
      foreach ($post['entities']['hashtags'] as $hashtag) {
        $category = htmlspecialchars($hashtag['name']);
        echo '<category>' . $category . '</category>';
      }
      echo '</item>';
    }
  }
  echo '</channel>';
  echo '</rss>';
}

function printError() {
  echo '<html><body>';
  echo '<h1>No Token argument found. Return to the <a href="http://jonathonduerig.com/my-rss-stream/">Start Page</a>.</h1>';
  echo '</body></html>';
}

?>
