<?php

// MAIN.
// --------------------------------------------------------
defineConstants();
autoload();
loadController();
// --------------------------------------------------------


// Functions.
// --------------------------------------------------------
function defineConstants () {
  // The full route (after /api/) requested by the frontend.
  define('ROUTE', preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']));
  // The request method (E.g. get, post, put, delete).
  define('METHOD', strtolower($_SERVER['REQUEST_METHOD']));
  // Any user input provided by the frontend.
  define('INPUT', json_decode(file_get_contents('php://input')));
};

function autoload () {
  require __DIR__ . '/vendor/autoload.php';

  // Autoload the PHP classes.
  spl_autoload_register(function ($className) {
    include __DIR__ . '/classes/' . strtolower($className) . '.php';
  });
}

function loadController () {
  list($endpoint, $action) = array_pad(explode('/', ROUTE), 2, '');

  if (is_file(__DIR__ . "/controllers/$endpoint.php")) {
    include_once __DIR__ . "/controllers/$endpoint.php";
    list($code, $message, $data) = runControllerAction($endpoint, $action);
    output($code ?? 200, $message ?? '', $data ?? []) && exit;
  }

  else output(404, 'Endpoint not found.') && exit;
}

function runControllerAction ($endpoint, $action) {
  $method = ROUTES[METHOD . ":$endpoint" . ($action ? "/$action" : '')];
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
