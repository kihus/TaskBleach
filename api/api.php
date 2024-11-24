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
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
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
    $sql = "SELECT id, name, email FROM users";
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
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
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
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
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
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
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
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, status, user_id) VALUES (?, ?, ?, ?)");
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
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
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
        $sql = "SELECT * FROM tasks";
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
        $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
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
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
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

// Roteamento baseado no método e no recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['users'])) {
    createUser($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['users'])) {
    readUsers($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['users'])) {
    updateUser($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['users'])) {
    deleteUser($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['tasks'])) {
    createTask($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tasks'])) {
    readTasks($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['tasks'])) {
    updateTask($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['tasks'])) {
    deleteTask($conn);
} else {
    echo json_encode(array("message" => "Recurso ou método não encontrado."));
}

$conn->close();
?>
