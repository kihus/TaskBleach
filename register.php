<?php

// Configura os cabeçalhos para a API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Inclui a configuração do banco de dados
require 'tasks_api.php';

try {
    // Inicializa a conexão PDO
    $pdo = new PDO("mysql:host=localhost;dbname=db_task", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
    http_response_code(500);
    exit;
}

// Verifica o método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Responde OK para pré-verificações CORS
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém o corpo da requisição
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Valida os dados recebidos
    if (!$data) {
        echo json_encode(["error" => "Nenhum dado recebido."]);
        http_response_code(400);
        exit;
    }

    if (!isset($data['name'], $data['email'], $data['password'])) {
        echo json_encode(["error" => "Campos obrigatórios não preenchidos."]);
        http_response_code(400);
        exit;
    }

    // Extrai os campos e prepara o hash da senha
    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    try {
        // Prepara a consulta SQL
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
        echo json_encode(["message" => "Usuário cadastrado com sucesso."]);
        http_response_code(201);
    } catch (PDOException $e) {
        // Trata erros de duplicidade e outros problemas
        if ($e->getCode() == 23000) { // Código para violação de chave única
            echo json_encode(["error" => "Este e-mail já está em uso."]);
            http_response_code(409);
        } else {
            echo json_encode(["error" => "Erro ao cadastrar usuário: " . $e->getMessage()]);
            http_response_code(500);
        }
    }
} else {
    // Retorna erro para métodos não suportados
    echo json_encode(["error" => "Método HTTP não permitido."]);
    http_response_code(405);
}

?>
