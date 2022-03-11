<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
/**
 * Notes
 * @version 0.0.1
 */

require_once __DIR__ . '/vendor/autoload.php';

error_reporting(E_ERROR | E_PARSE);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$databaseDirectory = __DIR__ . "/db";
$notesStore = new \SleekDB\Store("notes", $databaseDirectory);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

/**
 * DELETE deleteNotesById
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->delete('/notes/{id}', function($request, $response, $args) {
    global $notesStore;
    $notesStore->deleteBy(["id", "=", $args["id"]]);

    $response->getBody()->write("{}");
    return $response;
});


/**
 * GET getAllNotes
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->get('/notes', function($request, $response, $args) {
    global $notesStore;
    $allNotes = $notesStore->findAll();
    $response->getBody()->write(json_encode($allNotes));
    return $response;
});


/**
 * GET getNotesById
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->get('/notes/{id}', function(Request $request, Response $response, $args) {
    $note = $notesStore->findBy(["id", "=", $args["id"]])[0];
    $response->getBody()->write(json_encode($note));
    return $response;
});


/**
 * PATCH patchNotesById
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->patch('/notes/{id}', function(Request $request, Response $response, $args) {
    global $notesStore;
    $body = $request->getParsedBody();

    $note = $notesStore->findBy(["id", "=", $args["id"]])[0];
    $note["text"] = $body["text"];
    $notesStore->update($note);
    $response->getBody()->write(json_encode($note));

    return $response;
});


/**
 * POST postNote
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->post('/notes', function($request, $response, $args) {
    global $notesStore;
    $body = $request->getParsedBody();

    $notesStore->insert([
        "id" => uniqid(),
        "text" => $body['text']
    ]);

    $allNotes = $notesStore->findAll();
    $response->getBody()->write(json_encode($allNotes));

    return $response;
});


/**
 * PUT putNotesById
 * Summary: 
 * Notes: 
 * Output-Formats: [application/json]
 */
$app->put('/notes/{id}', function($request, $response, $args) {
    global $notesStore;
    $body = $request->getParsedBody();

    $note = $notesStore->findBy(["id", "=", $args["id"]])[0];
    $note["text"] = $body["text"];
    $notesStore->update($note);
    $response->getBody()->write(json_encode($note));

    return $response;
});


$app->run();
