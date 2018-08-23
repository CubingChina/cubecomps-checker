<?php

include __DIR__ . '/vendor/autoload.php';

define('CUBECOMPS_BASE_URL', 'http://cubecomps.com/');

use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

$action = $_REQUEST['action'] ?? '';

$data = null;
switch ($action) {
    case 'list':
        $data = getCompList();
        break;
    case 'comp':
        $cid = $_REQUEST['cid'] ?? '';
        $data = getComp($cid);
        break;
    case 'results':
        $cid = $_REQUEST['cid'] ?? '';
        $cat = $_REQUEST['cat'] ?? '';
        $rnd = $_REQUEST['rnd'] ?? '';
        $data = getResults($cid, $cat, $rnd);
        break;
}

header('Access-Control-Allow-Origin: *');
echo json_encode($data);
exit;

function getCompList() {
    $html = getHtml();
    preg_match_all('|<table(.*?)</table>|i', $html, $matches);
    $list = $matches[1];
    return array_merge(
        getCompListFromDom($list[1] ?? ''),
        getCompListFromDom($list[3] ?? '')
    );
}

function getCompListFromDom($html) {
    $dom = new Dom();
    $dom->load($html);
    $list = [];
    foreach ($dom->find('.p0 a') as $a) {
        $name = $a->text;
        $href = $a->getAttribute('href');
        parse_str(explode('?', $href)[1], $params);
        if (!isset($params['cid'])) {
            continue;
        }
        $cid = $params['cid'];
        $list[] = [
            'cid' => $cid,
            'name' => trim($name),
        ];
    }
    return $list;
}

function getComp($cid) {
    $html = getHtml('live.php', ['cid' => $cid]);
    preg_match_all('|class=event onclick.*?>.*?</div>.*?(?:<div class=round onclick.*?>.*?</div>)+|i', $html, $matches);
    $events = [];
    foreach (array_slice($matches[0], 0, -1) as $str) {
        preg_match('|class=event onclick.*?>(.*?)</div>|i', $str, $m);
        $name = $m[1];
        preg_match_all('|<div class=round onclick.*?\?(.*?)".*?>(.*?)</div>|i', $str, $m);
        $event = [
            'name' => $name,
            'rounds' => [],
        ];
        foreach ($m[2] as $index => $roundName) {
            parse_str($m[1][$index], $params);
            $event['rounds'][] = [
                'name' => $roundName,
                'cat' => $params['cat'],
                'rnd' => $params['rnd'],
            ];
        }
        if ($event['rounds'] != []) {
          $event['cat'] = $event['rounds'][0]['cat'];
          $events[] = $event;
        }
    }
    preg_match('|<title>(.*?)</title>|i', $html, $matches);
    $name = $matches[1];
    return [
      'events' => $events,
      'name' => $name,
    ];
}

function getResults($cid, $cat, $rnd) {
    $html = getHtml('live.php', [
        'cid' => $cid,
        'cat' => $cat,
        'rnd' => $rnd,
    ]);
    preg_match_all('#<tr class=row_(?:even|odd)>.*?</tr>#i', $html, $matches);
    $results = [];
    foreach ($matches[0] as $row) {
        preg_match('#<a href=.*?\?(.*?\d+)\'.*?>(.*?)</a>#i', $row, $m);
        parse_str($m[1], $params);
        $name = $m[2];
        $compid = $params['compid'];
        preg_match('#<td class=col_cl.*?<b>(\d+)</b>#i', $row, $m);
        $pos = $m[1];
        preg_match_all('#<td class=col_tm>(.*?)</td>#i', $row, $m);
        $results[] = [
            'name' => $name,
            'compid' => intval($compid),
            'pos' => intval($pos),
            'scores' => $m[1],
        ];
    }
    return $results;
}

function getHtml($url = '', $query = []) {
    try {
        $client = new Client([
            'base_uri' => CUBECOMPS_BASE_URL,
        ]);
        $response = $client->get($url, [
            'query' => $query
        ]);
        $body = $response->getBody()->getContents();
        return (string)$body;
    } catch (\Exception $e) {
        return null;
    }
}
