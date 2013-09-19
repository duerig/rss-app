<?php
require_once 'AppDotNet.php';
require_once 'config.php';

if (array_key_exists('token', $_GET)
   || array_key_exists('user', $_GET)
   || array_key_exists('global', $_GET)
   || array_key_exists('channel', $_GET)
   || array_key_exists('mention', $_GET)) {
  printRss();
} else {
  printError();
}

function printRss() {
  global $clientId, $clientSecret;
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
  $expandlinks = 0;
  if (array_key_exists('expandlinks', $_GET)) {
    $expandlinks = intval($_GET['expandlinks']);
  }  
  $lang = '';
  if (array_key_exists('lang', $_GET)) {
    $lang = $_GET['lang'];
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
  if ($lang == '') {
  } else {
    $params['include_annotations'] = 1;
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
  } elseif (array_key_exists('mention', $_GET)) {
    $user = $_GET['mention'];
    $streamData = $app->getUserMentions($user, $params);
    echo '<title>Mentions for user ' . $user . ' - App.net</title>';
    echo '<link>http://jonathonduerig.com/my-rss-stream/</link>';
    echo '<description>Posts mentioning user ' . $user . '</description>';
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
    if (array_key_exists('repost_of', $post)) {
      $post = $post['repost_of'];
    }
    $postLang = getLanguage($post);
    if (($replies == 1 || ! array_key_exists('reply_to', $post)) &&
        ($lang == '' || $lang == $postLang)) {
      $username = htmlspecialchars($post['user']['username']);
      $text = htmlspecialchars($post['text']);
      if ($expandlinks == 1) {
        $html = htmlspecialchars(expandLinks($post['html']));
      } else {
        $html = htmlspecialchars($post['html']);
      }
      $date = htmlspecialchars(gmdate(DATE_RSS,
                                      strtotime($post['created_at'])));
      $link = htmlspecialchars($post['canonical_url']);
      echo '<item>';
      if (array_key_exists('user', $_GET) &&
          ($post['user']['id'] == $_GET['user'] ||
           ('@' . $post['user']['username']) == $_GET['user'])) {
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

function getLanguage($post) {
  $result = '';
  if (array_key_exists('annotations', $post)) {
    foreach ($post['annotations'] as $note) {
      if ($note['type'] == 'net.app.core.language') {
        $result = $note['value']['language'];
      }
    }
  }
  return $result;
}

function printError() {
  echo '<html><body>';
  echo '<h1>No Token argument found. Return to the <a href="http://jonathonduerig.com/my-rss-stream/">Start Page</a>.</h1>';
  echo '</body></html>';
}

function expandLinks($post_html) {
  // This regex is far from bulletproof but it only has to handle ADN API output
  $href_regex = "/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU";
  if (preg_match_all($href_regex, $post_html, $matches, PREG_PATTERN_ORDER)) {
    $hrefs = array_unique($matches[1]); // avoid duplicate curl'ing
    foreach ($hrefs as $href) {
      $post_html = str_replace($href, unshorten($href), $post_html);
    }
  }
  return $post_html;
}

function unshorten($url) {
  $ch = curl_init();
  // see http://www.php.net/manual/en/function.curl-setopt.php
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
  curl_setopt($ch, CURLOPT_MAXREDIRS, 4); // completely arbitrary
  curl_setopt($ch, CURLOPT_URL, $url);
  $curl_response = curl_exec($ch);
  curl_close($ch);

  // find the last Location header in the curl output
  // or, if no Location header is found, return the original url
  $headers = explode("\n", $curl_response);
  foreach ($headers as $header) {
    $bits = explode(": ", $header);
    if ($bits[0] == "Location") $url = $bits[1];
  }
  return $url;
}
?>
