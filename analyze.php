<?php
/*
Get channel ID:
https://www.googleapis.com/youtube/v3/channels?key=[KEY]&forUsername=[USER]

Get channel videos:
https://www.googleapis.com/youtube/v3/search?key=[KEY]&channelId=[ID]&part=snippet,id&order=date&maxResults=50
*/

define('FILE', 'data.json');

function getPage(string $base, ?string $pageToken): array {
    $items = [];
    if($pageToken !== null) {
        $base .= '&pageToken=' . $pageToken;
    }
    echo "Getting data from: $base\n";
    $json = json_decode(file_get_contents($base), true);
    echo "Done\n";
    if(array_key_exists('items', $json)) {
        $items = array_merge($items, $json['items']);
        echo "Received " . count($json['items']) . " items\n";
        echo "\n\n" . json_encode($items) . "\n\n";
    }
    if(array_key_exists('nextPageToken', $json)) {
        $token = $json['nextPageToken'];
        echo "Next token: $token\n";
    } else {
        $token = null;
    }
    return [$items, $token];
}

function getAllData(): void {
    $base = 'https://www.googleapis.com/youtube/v3/search?key=[KEY]&channelId=[ID]&part=snippet,id&order=date&maxResults=50';

    $all = [];
    $end = false;
    $token = null;
    do {
        list($items, $token) = get($base, $token);
        $all = array_merge($all, $items);
        if($token === null) {
            $end = true;
            echo "End\n";
        }
    } while (!$end);

    file_put_contents(FILE, json_encode($all));
}

// getAllData();

function process(): void {
    $data = json_decode(file_get_contents(FILE), true);
    $gaps = [];
    $lastDate = date_create();
    echo "Start         End          Mo:Da:Ho:Mi (+days)\n";
    foreach($data as $video) {
        $date = date_create($video['snippet']['publishedAt']);
        $interval = date_diff($date, $lastDate);
        $kind = $video['id']['kind'];
        echo $date->format('M/d/Y') . ' - ' . $lastDate->format('M/d/Y') . ': ' . $interval->format('%M:%D:%H:%M (%R%a)') . "\n";
        $lastDate = $date;
    }
}

process();
