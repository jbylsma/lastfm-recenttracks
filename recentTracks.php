<?php

/**
 * @file
 * Print recent tracks from Last.fm users.
 *
 * Can be used with GeekTool (http://projects.tynsoe.org/en/geektool/) to
 * display recent scrobbles on OS X desktops.
 */

/**
 * Fetch a user's recent tracks using cURL.
 *
 * @param $username
 *   The Last.fm username.
 *
 * @return
 *   The cURL response, if cURL succeeds. FALSE, if cURL fails.
 */
function fetchRecentTracks($userName) {
  $url = sprintf('http://ws.audioscrobbler.com/1.0/user/%s/recenttracks.rss', $userName);
  $ch = curl_init($url);
  $options = array(
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_HEADER          => false
  );
  curl_setopt_array($ch, $options);

  $result = curl_exec($ch);
  curl_close($ch);

  return $result;
}

/**
 * Parse Last.FM RSS feed.
 *
 * @param $rss
 *   RSS feed of a user's recent tracks.
 *
 * @return array
 *   Returns an array containing rendered date and title strings of a user's
 *   recent tracks.
 */
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
    $recentTracksArray[] = sprintf("[%s] %s\n", formatDate($date), $title);
  }

  return $recentTracksArray;
}

/**
 * Formats a date for display.
 *
 * @param $date
 *   English textual datetime capable of being parsed by strtotime().
 *
 * @return string
 *   The rendered date.
 */
function formatDate($date) {
  $phpDate = strtotime($date);
  $formattedDate = strftime("%Y-%m-%d %H:%M", $phpDate);
  return $formattedDate;
}

/**
 * Main
 */
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
    print "Error fetching $userName\n";
    continue;
  }

  print $userName . "::recent\n";
  foreach ($recentTracksArray as $track) {
    print $track;
  }
  print "\n";
}
