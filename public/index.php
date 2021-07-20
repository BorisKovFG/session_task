<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);

$users = [
    ['name' => 'admin', 'passwordDigest' => hash('sha256', 'secret')],
    ['name' => 'mike', 'passwordDigest' => hash('sha256', 'superpass')],
    ['name' => 'kate', 'passwordDigest' => hash('sha256', 'strongpass')]
];

// BEGIN (write your solution here)
$app->get('/', function ($request, $response) {
    $flash = $this->get('flash')->getMessages();
    $params = [
        'currentUser' => $_SESSION['user'] ?? null,
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
});

$app->post("/session", function ($request, $response) use ($users) {
    $dataFromForm = $request->getParsedBodyParam('user');
    $hashPassFromDb = hash('sha256', $dataFromForm['password']);
    foreach ($users as $user) {
        if ($dataFromForm['name'] === $user['name'] && $hashPassFromDb === $user['passwordDigest']) {
            $_SESSION['user'] = $user;
            return $response->withRedirect("/");
        }

    }
    $this->get('flash')->addMessage('error', 'Wrong password or name');
    return $response->withRedirect("/");
});

$app->delete("/session", function ($request, $response) {
    $_SESSION['user'] = [];
    session_destroy();
    return $response->withRedirect("/");
});
// END

$app->run();