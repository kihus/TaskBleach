// Referências ao modal, formulário, overlay e lista de tarefas
const modal = document.getElementById("add-task-modal");
const form = document.getElementById("add-task-form");
const overlay = document.getElementById("modal-overlay");
const taskList = document.getElementById("tasks");
const modalTitle = document.getElementById("modal-title");

let currentEditingTask = null; // Armazena a tarefa em edição
const API_URL = "http://localhost/meu_projeto_api"; // URL base da API

// Função para abrir o modal
function openAddTaskModal(task = null) {
    currentEditingTask = task;

    if (task) {
        modalTitle.textContent = "Editar Tarefa";
        form.title.value = task.title;
        form.description.value = task.description;
        form.status.value = task.status;
    } else {
        modalTitle.textContent = "Adicionar Nova Tarefa";
        form.reset();
    }

    modal.classList.remove("hidden");
    overlay.classList.add("active"); // Exibe o overlay
}

// Função para fechar o modal
function closeAddTaskModal() {
    modal.classList.add("hidden");
    overlay.classList.remove("active"); // Esconde o overlay
    form.reset();
    currentEditingTask = null;
}

// Função para carregar tarefas da API
async function loadTasks() {
    try {
        const response = await fetch(`${API_URL}/tasks_new`);
        const tasks = await response.json();

        // Renderizar as tarefas no HTML
        taskList.innerHTML = ""; // Limpa a lista antes de carregar novamente
        tasks.forEach(task => {
            const taskItem = document.createElement("li");
            taskItem.className = "task-item";
            taskItem.id = `task-${task.id}`;
            taskItem.innerHTML = `
                <span class="task-title">${task.title}</span> - 
                <span class="task-description">${task.description}</span>
                <span class="task-status ${task.status}">${task.status === "pending" ? "Pendente" : "Concluída"}</span>
                <div class="buttons">
                    <button class="edit" onclick="editTask(${task.id})">Editar</button>
                    <button class="delete" onclick="deleteTask(${task.id})">Excluir</button>
                </div>`;
            taskList.appendChild(taskItem);
        });
    } catch (error) {
        console.error("Erro ao carregar tarefas:", error);
    }
}

// Função para adicionar ou editar uma tarefa
async function saveTask(event) {
    event.preventDefault();

    const task = {
        title: form.title.value,
        description: form.description.value,
        status: form.status.value,
    };

    try {
        if (currentEditingTask) {
            // Editar tarefa
            const response = await fetch(`${API_URL}/tasks_new/${currentEditingTask.id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(task),
            });

            if (!response.ok) throw new Error("Erro ao editar a tarefa.");
        } else {
            // Adicionar tarefa
            const response = await fetch(`${API_URL}/tasks_new`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(task),
            });

            if (!response.ok) throw new Error("Erro ao adicionar a tarefa.");
        }

        loadTasks(); // Recarregar lista de tarefas
        closeAddTaskModal();
    } catch (error) {
        console.error("Erro ao salvar tarefa:", error);
    }
}

// Função para excluir uma tarefa
async function deleteTask(taskId) {
    try {
        const response = await fetch(`${API_URL}/tasks_new/${taskId}`, {
            method: "DELETE",
        });

        if (!response.ok) throw new Error("Erro ao excluir a tarefa.");

        loadTasks(); // Recarregar lista de tarefas
    } catch (error) {
        console.error("Erro ao excluir tarefa:", error);
    }
}

// Função para editar uma tarefa (abrir modal com dados)
async function editTask(taskId) {
    try {
        const response = await fetch(`${API_URL}/tasks_new/${taskId}`);
        if (!response.ok) throw new Error("Erro ao buscar tarefa.");

        const task = await response.json();
        openAddTaskModal(task);
    } catch (error) {
        console.error("Erro ao editar tarefa:", error);
    }
}
app.post('/tasks', (req, res) => {
    const { title, description, status } = req.body;
    const sql = 'INSERT INTO tasks_new (title, description, status) VALUES (?, ?, ?)';
    db.query(sql, [title, description, status], (err, result) => {
        if (err) {
            console.error(err);
            res.status(500).send('Erro ao inserir a tarefa.');
        } else {
            res.status(201).json({ id: result.insertId, ...req.body });
        }
    });
});

app.get('/tasks', (req, res) => {
    const sql = 'SELECT * FROM tasks_new';
    db.query(sql, (err, results) => {
        if (err) {
            console.error(err);
            res.status(500).send('Erro ao buscar as tarefas.');
        } else {
            res.status(200).json(results);
        }
    });
});
// Função para adicionar uma nova tarefa
function addTask(event) {
    event.preventDefault(); // Evita o reload da página

    // Captura os dados do formulário
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const status = document.getElementById('status').value;

    // Dados a serem enviados ao backend
    const taskData = { title, description, status };

    // Enviar para o backend
    fetch('http://localhost:3000/tasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(taskData),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Erro ao adicionar a tarefa!');
            }
            return response.json();
        })
        .then((newTask) => {
            console.log('Tarefa adicionada com sucesso:', newTask);
            // Atualiza a lista de tarefas na interface
            closeAddTaskModal();
            loadTasks(); // Recarrega as tarefas
        })
        .catch((error) => {
            console.error(error);
            alert('Erro ao adicionar a tarefa.');
        });
}

app.post('/tasks', (req, res) => {
    const { title, description, status } = req.body;
    const sql = 'INSERT INTO tasks_new (title, description, status) VALUES (?, ?, ?)';
    db.query(sql, [title, description, status], (err, result) => {
        if (err) {
            console.error(err);
            res.status(500).send('Erro ao inserir a tarefa.');
        } else {
            res.status(201).json({ id: result.insertId, ...req.body });
        }
    });
});

// Event listener para envio do formulário
form.addEventListener("submit", saveTask);

// Carregar tarefas ao iniciar a página
document.addEventListener("DOMContentLoaded", loadTasks);
