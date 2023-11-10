<?php

define('ROUTES', [
  'get:user/authenticate' => 'authenticate',
  'post:user/login' => 'logIn',
  'get:user/logout' => 'logOut'
]);

function authenticate () {
  if (User::authenticate()) output(200, 'Access granted.') && exit;
}

function logIn () {
  list($user, $jwt) = User::logIn(INPUT->username ?? '', INPUT->password ?? '');

  if ($user) {
    $message = 'Access granted.';
    $code = 200;
    $data = ['jwt' => $jwt];
  }

  output($code ?? 403, $message ?? 'Access denied.', $data ?? []) && exit;
}

function logOut () {

}

?>
