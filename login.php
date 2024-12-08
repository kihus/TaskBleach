<?php
// Cabeçalhos para permitir requisições cross-origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        // Lê os dados da requisição
        $data = json_decode(file_get_contents("php://input"), true);

        // Verifica campos obrigatórios
        if (!isset($data['email'], $data['password']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(["error" => "Campos obrigatórios não preenchidos."]);
            http_response_code(400); // Requisição inválida
            exit;
        }

        $email = trim($data['email']);
        $password = trim($data['password']);

        // Valida formato do e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "E-mail inválido."]);
            http_response_code(400);
            exit;
        }

        // Prepara e executa a consulta no banco
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Valida o usuário e senha
        if ($user && password_verify($password, $user['password'])) {
            // Sucesso
            echo json_encode([
                "message" => "Login realizado com sucesso!",
                "user_id" => $user['id'],
                "user_name" => $user['name'],
                "email" => $user['email']
            ]);
            http_response_code(200); // Sucesso
        } else {
            // Credenciais inválidas
            echo json_encode(["error" => "E-mail ou senha inválidos."]);
            http_response_code(401); // Não autorizado
        }
    } catch (PDOException $e) {
        // Erro no banco de dados
        error_log("Erro no login: " . $e->getMessage());
        echo json_encode(["error" => "Erro interno do servidor."]);
        http_response_code(500); // Erro do servidor
    } catch (Exception $e) {
        // Outros erros
        error_log("Erro inesperado: " . $e->getMessage());
        echo json_encode(["error" => "Erro inesperado."]);
        http_response_code(500); // Erro do servidor
    }
} else {
    // Método HTTP inválido
    echo json_encode(["error" => "Método não permitido. Use POST."]);
    http_response_code(405); // Método não permitido
}
