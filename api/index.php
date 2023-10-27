<?php

// Constants & variables.
// --------------------------------------------------------
define('ROUTE', preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']));
$params = json_decode(file_get_contents('php://input'));


// MAIN.
// --------------------------------------------------------
require __DIR__ . '/vendor/autoload.php';

// Autoload the PHP classes.
spl_autoload_register(function ($className) {
  include __DIR__ . '/classes/' . strtolower($className) . '.php';
});


if (ROUTE === 'login') {
  list($user, $jwt) = User::logIn($params->username ?? '', $params->password ?? '');
  if ($user) {
    $message = 'Access granted.';
    $code = 200;
    $data = ['jwt' => $jwt];
  }
  output($code ?? 403, $message ?? 'Access denied.', $data ?? []) && exit;
}

loadController();
// --------------------------------------------------------


// Functions.
// --------------------------------------------------------
function getPosts () {
  global $params;
  return $params;
}

function loadController () {
  list($endpoint, $action) = array_pad(explode('/', ROUTE), 2, '');
  $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

  if (is_file(__DIR__ . "/controllers/$endpoint.php")) {
    include_once __DIR__ . "/controllers/$endpoint.php";
    list($code, $message, $data) = runControllerAction($requestMethod, $endpoint, $action);
  }

  else {
    $code = 400; // Method not allowed.
    $message = 'Incorrect endpoint.';
  }

  output($code ?? 200, $message ?? '', $data ?? []);
  Database::get()->close();
}

function runControllerAction ($requestMethod, $endpoint, $action) {
  $method = ROUTES["$requestMethod:$endpoint" . ($action ? '/{id}' : '')];
  return is_callable($method) ? $method($action) : [404, 'Method not found.', null];
}

/**
 * Outputs the JSON payload to the frontend with correct response code.
 *
 * @param integer $code the response code to send back to the frontend.
 * @param string $message a potential message in case of error.
 * @param array $data some data to send back to the frontend if any.
 * @return void
 */
function output(int $code, string $message, array $data = []): true {
  $output = ['error' => $code < 200 || $code >= 300, 'message' => $message];
  $output = array_merge($output, $data);

  echo json_encode((object)$output);
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');

  return true; // So we can chain.
}
// --------------------------------------------------------

?>
