#!/usr/bin/php
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
    $xml = simplexml_load_string($rss);
    $data = $xml->channel;
    foreach($data->item as $song) {
      $title = (string) $song->title;
      $date = (string) $song->pubDate;
      $str = "[" . formatDate($date) . "] " . $title . "\n";
      //$str = $title . " [" . formatDate($date) . "]" . "\n";

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

  function getUserName($argv) {
    if (sizeOf($argv) == 0) {
      $userName = "RnRG";
    }
    else {
      $userName = $argv[1];  
    }

    return $userName;
  }

  // Main
  $userName = getUserName($argv);
  $rss = fetchRecentTracks($userName);
  $recentTracksArray = parseRecentTracksXml($rss);

  echo($userName . "::recent\n");
  foreach ($recentTracksArray as $track) {
    echo($track);
  }
?>
