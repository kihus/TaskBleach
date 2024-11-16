<?php
// Incluindo a conexão com o banco de dados
include 'db.php';

// Configurando o cabeçalho para JSON
header("Content-Type: application/json");

// ------------------- CRUD PARA USUÁRIOS -------------------

// Criar Usuário (POST) C
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['users'])) {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->name) && isset($data->email) && isset($data->password)) {
        $name = $data->name;
        $email = $data->email;
        $password = password_hash($data->password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Usuário adicionado com sucesso, meu nobre."));
        } else {
            echo json_encode(array("message" => "Erro ao adicionar o usuário, meu consagrado."));
        }
    } else {
        echo json_encode(array("message" => "Dados incompletos, brother."));
    }
}

// Ler Usuários (GET) R
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['users'])) {
    $sql = "SELECT id, name, email FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode(array("message" => "Nenhum usuário encontrado, meu mano."));
    }
}

// Atualizar Usuário (PUT) U
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['users'])) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id']) && isset($data['name']) && isset($data['email'])) {
        $id = $data['id'];
        $name = $data['name'];
        $email = $data['email'];

        // Atualiza o usuário
        $sql = "UPDATE users SET name='$name', email='$email' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Usuário atualizado com sucesso, meu nobre."));
        } else {
            echo json_encode(array("message" => "Erro ao atualizar o usuário, irmão."));
        }
    } else {
        echo json_encode(array("message" => "Dados incompletos, meu casa."));
    }
}

// Deletar Usuário (DELETE) D
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['users'])) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id'])) {
        $id = $data['id'];

        // Deleta o usuário
        $sql = "DELETE FROM users WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Usuário deletado com sucesso, meu mano."));
        } else {
            echo json_encode(array("message" => "Erro ao deletar o usuário, meu cumpadre."));
        }
    } else {
        echo json_encode(array("message" => "ID do usuário não fornecido, zé."));
    }
}

// ------------------- CRUD PARA TASKS -------------------

// Criar Task (POST) C
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['tasks'])) {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->title) && isset($data->description) && isset($data->status) && isset($data->user_id)) {
        $title = $data->title;
        $description = $data->description;
        $status = $data->status;
        $user_id = $data->user_id;

        $sql = "INSERT INTO tasks (title, description, status, user_id) VALUES ('$title', '$description', '$status', $user_id)";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Task criada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao criar a task."));
        }
    } else {
        echo json_encode(array("message" => "Dados incompletos para criar a task."));
    }
}

// Ler Tasks (GET) R
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tasks'])) {
    if (isset($_GET['id'])) {
        // Obter uma única task pelo ID
        $id = $_GET['id'];
        $sql = "SELECT * FROM tasks WHERE id=$id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $task = $result->fetch_assoc();
            echo json_encode($task);
        } else {
            echo json_encode(array("message" => "Task não encontrada."));
        }
    } else {
        // Obter todas as tasks
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

// Atualizar Task (PUT) U
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['tasks'])) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id']) && isset($data['title']) && isset($data['description']) && isset($data['status'])) {
        $id = $data['id'];
        $title = $data['title'];
        $description = $data['description'];
        $status = $data['status'];

        // Atualiza a task
        $sql = "UPDATE tasks SET title='$title', description='$description', status='$status' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Task atualizada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao atualizar a task."));
        }
    } else {
        echo json_encode(array("message" => "Dados incompletos para atualizar a task."));
    }
}

// Deletar Task (DELETE) D
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['tasks'])) {
    parse_str(file_get_contents("php://input"), $data);

    if (isset($data['id'])) {
        $id = $data['id'];

        // Deleta a task
        $sql = "DELETE FROM tasks WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Task deletada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao deletar a task."));
        }
    } else {
        echo json_encode(array("message" => "ID da task não fornecido."));
    }
}

// Fechar a conexão
$conn->close();
?>
