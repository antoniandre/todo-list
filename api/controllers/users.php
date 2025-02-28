<?php

define('ENDPOINTS', [
  'get:users' => 'getAllUsers',
  'get:users/{id}' => 'getUser',
  'get:user/authenticate' => 'authenticate',
  'post:user/login' => 'logIn',
  'get:user/logout' => 'logOut'
]);

function getAllUsers () {
  try {
    $users = User::getAll();
    $code = 200;
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }

  output($code, $message ?? '', ['users' => $users ?? []]) && exit;
}

function getUser ($id) {
  try {
    $user = User::get(is_numeric($id) ? (int)$id : 0);
    $code = 200;
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }

  output($code ?? 200, $message ?? '', ['user' => $user ?? null]) && exit;
}

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