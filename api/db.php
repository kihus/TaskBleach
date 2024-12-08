<?php
// Configurações do banco de dados
$servername = "localhost";
$username = "root";  // Usuário padrão do MySQL
$password = "";      // Senha padrão do XAMPP
$dbname = "api_db";  // Nome do banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die(json_encode(array(
        "success" => false,
        "message" => "Conexão com o banco de dados falhou: " . $conn->connect_error
    )));
}

// Configurando o charset para evitar problemas com acentos e caracteres especiais
$conn->set_charset("utf8");
?>
