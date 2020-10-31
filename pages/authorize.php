<?php

use \RedBeanPHP\R as R;

if (isset($_SESSION['login'])) {
  $this->redirect("dashboard");
}

if (!isset($_GET['code']) && !isset($_GET['error'])) {
  $_SESSION['reddit_oauth_state'] = substr(mt_rand().mt_rand().mt_rand(), 0, 10);

  $url = $this->getReddit()->getAuthorizationUrl([
// According to the readme, the duration should be added here. However, this causes duration to be added twice:
// - Once in https://github.com/rtheunissen/oauth2-reddit/blob/master/src/Provider/Reddit.php#L165
// - Once in https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php#L386
//   (passed through all the way)
//
//    'duration' => 'permanent',
    'state' => $_SESSION['reddit_oauth_state'],
    'scope' => 'identity submit'
  ]);
// Instead, add it afterwards so it only appears once.
  $url .= "&duration=permanent";

  header("Location: $url");
  exit(0);
}

if ($_SESSION['reddit_oauth_state'] !== $_GET['state']) {
  trigger_error("Reddit OAuth state mismatch");
  $this->redirect("/");
}

$accessToken = (object)$this->getReddit()->getAccessToken('authorization_code', [
  'code' => $_GET['code'],
  'state' => $_GET['state'],
  'redirect_uri' => "{$this->getBaseUrl()}/authorize"
]);

$response = $this->api($accessToken, 'GET', 'api/v1/me');

if (strlen($response->name) <= 0) {
  trigger_error("Unable to get account name");
  $this->redirect("/");
}

$account = R::findOrCreate('account', ['name' => $response->name]);
$account->token = json_encode($accessToken->jsonSerialize());
R::store($account);
$_SESSION['login'] = $account->name;
$this->redirect("dashboard");
