<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Configurando o cabeçalho para JSON
header("Content-Type: application/json");

// Adicionando cabeçalhos para CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Funções para encapsular as operações do CRUD

// Função para criar um usuário
function createUser($conn) {
    $data = json_decode(file_get_contents("php://input"));

    // Validação dos dados de entrada
    if (isset($data->name) && isset($data->email) && isset($data->password)) {
        $name = $data->name;
        $email = $data->email;
        $password = password_hash($data->password, PASSWORD_DEFAULT);

        // Validação do formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array("message" => "Email inválido."));
            return;
        }

        // Usando prepared statements para evitar SQL Injection
        $stmt = $conn->prepare("INSERT INTO users_new (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Usuário adicionado com sucesso.", "user_id" => $stmt->insert_id));
        } else {
            echo json_encode(array("message" => "Erro ao adicionar o usuário.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "Dados incompletos para criar o usuário."));
    }
}

// Função para ler usuários
function readUsers($conn) {
    $sql = "SELECT id, name, email FROM users_new";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode(array("message" => "Nenhum usuário encontrado."));
    }
}

// Função para atualizar um usuário
function updateUser($conn) {
    parse_str(file_get_contents("php://input"), $data);

    // Validação dos dados de entrada
    if (isset($data['id']) && isset($data['name']) && isset($data['email'])) {
        $id = $data['id'];
        $name = $data['name'];
        $email = $data['email'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array("message" => "Email inválido."));
            return;
        }

        // Usando prepared statements
        $stmt = $conn->prepare("UPDATE users_new SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $id);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Usuário atualizado com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao atualizar o usuário.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "Dados incompletos para atualizar o usuário."));
    }
}

// Função para deletar um usuário
function deleteUser($conn) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id'])) {
        $id = $data['id'];

        // Usando prepared statements
        $stmt = $conn->prepare("DELETE FROM users_new WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Usuário deletado com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao deletar o usuário.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "ID do usuário não fornecido."));
    }
}

// Função para criar uma tarefa
function createTask($conn) {
    $data = json_decode(file_get_contents("php://input"));

    // Validação dos dados de entrada
    if (isset($data->title) && isset($data->description) && isset($data->status) && isset($data->user_id)) {
        $title = $data->title;
        $description = $data->description;
        $status = $data->status;
        $user_id = $data->user_id;

        // Verificando se o usuário existe
        $stmt = $conn->prepare("SELECT id FROM users_new WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(array("message" => "Usuário não encontrado."));
            $stmt->close();
            return;
        }
        $stmt->close();

        // Usando prepared statements
        $stmt = $conn->prepare("INSERT INTO tasks_new (title, description, status, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $description, $status, $user_id);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Task criada com sucesso.", "task_id" => $stmt->insert_id));
        } else {
            echo json_encode(array("message" => "Erro ao criar a task.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "Dados incompletos para criar a task."));
    }
}

// Função para ler tarefas
function readTasks($conn) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Usando prepared statements
        $stmt = $conn->prepare("SELECT * FROM tasks_new WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(array("message" => "Task não encontrada."));
        }

        $stmt->close();
    } else {
        $sql = "SELECT * FROM tasks_new";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $tasks = array();
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            echo json_encode($tasks);
        } else {
            echo json_encode(array("message" => "Nenhuma task encontrada."));
        }
    }
}

// Função para atualizar uma tarefa
function updateTask($conn) {
    parse_str(file_get_contents("php://input"), $data);

    // Validação dos dados de entrada
    if (isset($data['id']) && isset($data['title']) && isset($data['description']) && isset($data['status'])) {
        $id = $data['id'];
        $title = $data['title'];
        $description = $data['description'];
        $status = $data['status'];

        // Usando prepared statements
        $stmt = $conn->prepare("UPDATE tasks_new SET title = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Task atualizada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao atualizar a task.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "Dados incompletos para atualizar a task."));
    }
}

// Função para deletar uma tarefa
function deleteTask($conn) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id'])) {
        $id = $data['id'];

        // Usando prepared statements
        $stmt = $conn->prepare("DELETE FROM tasks_new WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Task deletada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao deletar a task.", "error" => $conn->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("message" => "ID da task não fornecido."));
    }
}

// Roteamento para os métodos
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

if (isset($uri[0]) && isset($uri[1])) {
    $resource = $uri[0];
    $id = isset($uri[1]) ? $uri[1] : null;

    if ($resource == 'users') {
        if ($requestMethod == 'GET') {
            readUsers($conn);
        } elseif ($requestMethod == 'POST') {
            createUser($conn);
        } elseif ($requestMethod == 'PUT') {
            updateUser($conn);
        } elseif ($requestMethod == 'DELETE') {
            deleteUser($conn);
        }
    } elseif ($resource == 'tasks') {
        if ($requestMethod == 'GET') {
            readTasks($conn);
        } elseif ($requestMethod == 'POST') {
            createTask($conn);
        } elseif ($requestMethod == 'PUT') {
            updateTask($conn);
        } elseif ($requestMethod == 'DELETE') {
            deleteTask($conn);
        }
    }
}
?>
