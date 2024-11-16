<?php
$servername = "localhost";
$username = "root";  // Usuário padrão do MySQL
$password = "";      // Sem senha por padrão no XAMPP
$dbname = "api_db";  // Nome do banco de dados 

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Conexão falhou, meu nobre: " . $conn->connect_error);
}
?>
