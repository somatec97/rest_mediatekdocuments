<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require 'AccessBDD.php';

include_once("Controle.php");
$controle = new Controle();
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);


//  Authentification basique
if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== 'admin' ||
    $_SERVER['PHP_AUTH_PW'] !== 'adminpwd'
) {
    $controle->unauthorized();
    exit;
}

//  Lecture des paramètres avec filtres modernes
$table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_SPECIAL_CHARS) ??
         filter_input(INPUT_POST, 'table', FILTER_SANITIZE_SPECIAL_CHARS);

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS) ??
      filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS);

$champs = filter_input(INPUT_GET, 'champs', FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES) ??
          filter_input(INPUT_POST, 'champs', FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES);

if (!empty($champs)) {
    $champs = json_decode($champs, true);
}

//  Vérifie que la table est précisée
if (empty($table)) {
    http_response_code(400);
    echo json_encode([
        "code" => 400,
        "message" => "Le paramètre 'table' est requis."
    ]);
    exit;
}

//  Traitement REST selon le verbe HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $controle->get($table, $champs); // $champs peut contenir un filtre (comme id)
            break;

        case 'POST':
            $controle->post($table, $champs);
            break;

        case 'PUT':
            $controle->put($table, $id, $champs);
            break;

        case 'DELETE':
            $controle->delete($table, $champs);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                "code" => 405,
                "message" => "Méthode HTTP non autorisée : $method"
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "code" => 500,
        "message" => "Erreur interne : " . $e->getMessage()
    ]);
}
