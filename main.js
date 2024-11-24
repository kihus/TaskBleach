// Referências ao modal, formulário e lista de tarefas
const modal = document.getElementById("add-task-modal");
const form = document.getElementById("add-task-form");
const taskList = document.getElementById("tasks");
const modalTitle = document.getElementById("modal-title");

let currentEditingTask = null; // Armazena a tarefa em edição

// URL base da API
const API_URL = "http://localhost/phpmyadmin";

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
}

// Função para fechar o modal
function closeAddTaskModal() {
    modal.classList.add("hidden");
    form.reset();
    currentEditingTask = null;
}

// Função para carregar tarefas da API
async function loadTasks() {
    try {
        const response = await fetch(`${API_URL}/tasks`);
        const tasks = await response.json();

        taskList.innerHTML = ""; // Limpa a lista de tarefas

        tasks.forEach((task) => {
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
                </div>
            `;
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
            const response = await fetch(`${API_URL}/tasks/${currentEditingTask.id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(task),
            });

            if (!response.ok) throw new Error("Erro ao editar a tarefa.");
        } else {
            // Adicionar tarefa
            const response = await fetch(`${API_URL}/tasks`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
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
        const response = await fetch(`${API_URL}/tasks/${taskId}`, {
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
        const response = await fetch(`${API_URL}/tasks/${taskId}`);
        if (!response.ok) throw new Error("Erro ao buscar tarefa.");

        const task = await response.json();
        openAddTaskModal(task);
    } catch (error) {
        console.error("Erro ao editar tarefa:", error);
    }
}

// Event listener para envio do formulário
form.addEventListener("submit", saveTask);

// Carregar tarefas ao iniciar a página
document.addEventListener("DOMContentLoaded", loadTasks);
