<?php

function fetchRecentTracks($userName) {
  $url = "http://ws.audioscrobbler.com/1.0/user/" . $userName . "/recenttracks.rss";
  $ch = curl_init($url);
  $options = array(
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false
  );
  curl_setopt_array($ch, $options);

  $rss = curl_exec($ch);
  curl_close($ch);

  return $rss;
}

function parseRecentTracksXml($rss) {
  $recentTracksArray = array();
  libxml_clear_errors();

  $xml = simplexml_load_string($rss);
  $xmlErrors = libxml_get_errors();
  if (count($xmlErrors) > 0) {
    return false;
  }

  $data = $xml->channel;

  foreach($data->item as $song) {
    $title = (string) $song->title;
    $date = (string) $song->pubDate;
    $str = sprintf("[%s] %s\n", formatDate($date), $title);
    $recentTracksArray[] = $str;
  }

  return $recentTracksArray;
}

function formatDate($date) {
  $formattedDate = "";
  $phpDate = strtotime($date);
  $formattedDate = strftime("%Y-%m-%d %H:%M", $phpDate);
  return $formattedDate;
}

// Main
// Require at least one username
if ($argc < 2) {
  print 'At least one username is required.' . "\n";
  exit(1);
}

array_shift($argv);
$userNames = $argv;

libxml_use_internal_errors(true);

foreach ($userNames as $userName) {
  $rss = fetchRecentTracks($userName);
  $recentTracksArray = parseRecentTracksXml($rss);

  if (!$recentTracksArray) {
    echo("Error with $userName\n");
    continue;
  }

  echo($userName . "::recent\n");
  foreach ($recentTracksArray as $track) {
    echo($track);
  }
}
