<?php

use \RedBeanPHP\R as R;


function getThumb($body) : string {
  $matches = array();
  $out = '';

  $div_start = "<div style='position:relative; padding-bottom:100%;'>";
  $div_close = "</div>";

  $imgur_pattern = '#^(http[s]?://i\.imgur\.com/)([[:alnum:]]{7})([[:alnum:]])?\.(\w+)$#i';
  if (preg_match($imgur_pattern, $body, $matches)) {
    $out = $body;
    // Convert to 160x160 thumbnail URL - handles both images and gifs.
    $out = preg_replace($imgur_pattern, '\1\2t.jpg', $out);
    $out = "$div_start<img class='img-thumbnail' style='position:absolute;max-width:100%;max-height:100%;' src='$out'>$div_close";
    return $out;
  }

  $redgifs_pat = '#^http[s]?://(www\.)?redgifs\.com/watch/([[:alnum:]]+)(-[[:alnum:]-]+)?$#i';
  if (preg_match($redgifs_pat, $body, $matches)) {
    $data_id = $matches[2];
    $out = "$div_start<iframe src='https://redgifs.com/ifr/$data_id?autoplay=0' frameborder='0' scrolling='no' width='100%' height='100%' style='position:absolute;top:0;left:0;max-height:256px;' allowfullscreen></iframe>$div_close";
    return $out;
  }

  return $out;
}


# Populate view menu, handle view switching
$this->vars['view_list'] = [
  [
    'id'=>'list',
    'desc' => 'List Posts Chronologically',
    'template' => 'posts-list.html'
  ],
  [
    'id'=>'body',
    'desc' => 'Group Posts By Content',
    'template' => 'posts-body.hmtl'
  ],
  [
    'id'=>'calendar',
    'desc' => 'Calendar (WIP)',
    'template' => 'posts-calendar.html'
  ]
];


# If view is queried from url, tell the session to use that view.
# If not given, just use the session's previous view
if(!is_array($_SESSION['view'])) $_SESSION['view'] = [];
$view = @$_GET['view'];
if (isset($view)) $view = (string) $view;

$_SESSION['view']['dashboard'] = $view ?? $_SESSION['view']['dashboard'] ?? "list";


$account = $this->getAccount();
$this->vars['account'] = $account;
$posts = $account->withCondition(' ( deleted IS NULL OR deleted = 0 ) ORDER BY `when` DESC ')->ownPostList;


switch ($_SESSION['view']['dashboard']) {
case 'calendar':
  $this->vars['view'] = 'posts-calendar.html';
  $this->vars['time'] = @$_GET['time'];
  $indexedPosts = [];

  foreach ($posts as $post) {
    $year = date('Y', $post->when_original);
    $month = date('n', $post->when_original);
    $day = date('j', $post->when_original);
    $indexedPosts[$year][$month][$day][] = $post;
  }

  $this->vars['posts'] = $indexedPosts;

  break;
case 'list':
default:
  $this->vars['view'] = 'posts-list.html';

  foreach ($posts as $i => $post) {
	  $posts[$i]['thumb'] = getThumb($post['body']);

  }
  $this->vars['posts'] = $posts;
  break;
}
