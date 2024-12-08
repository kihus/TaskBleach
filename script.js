// Função para inicializar tarefas ao carregar a página
document.addEventListener("DOMContentLoaded", loadTasks);

const apiUrl = "http://localhost/meu_projeto_api/index.php"; // Corrigido para o index.php
const tasksContainer = document.getElementById("tasks");
const addTaskModal = document.getElementById("add-task-modal");
const addTaskForm = document.getElementById("add-task-form");
let editingTaskId = null;

// Carregar tarefas da API
async function loadTasks() {
    try {
        const response = await fetch(`${apiUrl}?fn=read`);
        const data = await response.json();
        if (data.tasks) {
            displayTasks(data.tasks);
        } else {
            console.error("Nenhuma tarefa encontrada");
        }
    } catch (error) {
        console.error("Erro ao carregar as tarefas:", error);
    }
}

// Exibir tarefas no HTML
function displayTasks(tasks) {
    tasksContainer.innerHTML = ""; // Limpa as tarefas antigas antes de adicionar novas
    tasks.forEach(task => {
        const taskElement = document.createElement("li");
        taskElement.className = "task";
        taskElement.innerHTML = `
        <hr class="class-hr-tasks">
            <h3>${task.taskName}</h3>
            <p>${task.taskDescription}</p>
            <span  >Status: ${task.taskStatus}</span><br><br>
            <button class="update-btn" onclick="editTask(${task.id})">Alterar</button>
            <button class="delete-btn" onclick="deleteTask(${task.id})">Deletar</button>
            
        `;
        tasksContainer.appendChild(taskElement);
    });
}

// Abrir modal para adicionar ou editar tarefa
function openAddTaskModal() {
    editingTaskId = null;
    addTaskForm.reset();
    document.getElementById("modal-title").innerText = "Adicionar Nova Tarefa";
    addTaskModal.classList.remove("hidden");
}

// Fechar modal
function closeAddTaskModal() {
    addTaskModal.classList.add("hidden");
}

// Salvar tarefa (adicionar ou editar)
async function saveTask() {
    const titleElement = document.getElementById("title");
    const title = titleElement.value;
    const descriptionElement = document.getElementById("description");
    const description = descriptionElement.value;
    const status = document.getElementById("status").value;

    const formData = new URLSearchParams();
    formData.append("fn", editingTaskId ? "update" : "create");
    if (editingTaskId) formData.append("id", editingTaskId);
    formData.append("taskName", title);
    formData.append("taskDescription", description);
    formData.append("taskStatus", status);

 
    if(title.trim() === "" || description.trim() === ""){
        
        titleElement.classList.add("alerta-vermelho");
        descriptionElement.classList.add("alerta0-vermelho");


        setTimeout(() => {
            titleElement.classList.remove("alerta-vermelho");
            descriptionElement.classList.remove("alerta-vermelho");
            alert("Preencha o titulo e/ou descricao esta vazia");

        }, 500);


        
    } else {
        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                body: formData,
            });

            const responseData = await response.json(); // A resposta do servidor
            console.log("Resposta da API:", responseData); // Verifique o que está sendo retornado

            if (response.ok) {
                closeAddTaskModal();
                loadTasks();
            } else {
                console.error("Erro na resposta da API:", responseData);
                alert("Erro ao salvar tarefa. Tente novamente.");
            }
        } catch (error) {
            console.error("Erro ao salvar a tarefa:", error);
            alert("Ocorreu um erro ao tentar salvar a tarefa.");
        }
    }
}

// Editar tarefa
async function editTask(id) {
    const response = await fetch(`${apiUrl}?fn=read&id=${id}`);
    const data = await response.json();
    const task = data.tasks[0];

    document.getElementById("title").value = task.taskName;
    document.getElementById("description").value = task.taskDescription;
    document.getElementById("status").value = task.taskStatus;

    editingTaskId = id;
    document.getElementById("modal-title").innerText = "Editar Tarefa";
    addTaskModal.classList.remove("hidden");
}

// Excluir tarefa
async function deleteTask(id) {
    if (confirm("Deseja realmente excluir esta tarefa?")) {
        await fetch(`${apiUrl}?fn=delete&id=${id}`);
        loadTasks();
    }
}

// Filtrar tarefas por status
function filterTasks() {
    const filter = document.getElementById("filter-status").value.toLowerCase();
    const tasks = document.querySelectorAll(".task");

    tasks.forEach(task => {
        const status = task.querySelector("span").textContent.toLowerCase();
        if (filter === "all" || status.includes(filter)) {
            task.style.display = "";
        } else {
            task.style.display = "none";
        }
    });
}

function closeElement() {
    const element = document.getElementById("add-task-modal");
    if (element) {
        // Adiciona uma classe para animação de saída
        element.classList.add("fade-out");

        // Aguarda a duração da animação antes de remover/ocultar o elemento
        setTimeout(() => {
            element.classList.add("hidden");
            element.classList.remove("fade-out")
        }, 500); // Tempo da animação em milissegundos
    } else {
        console.warn("Elemento 'add-task-modal' não encontrado.");
    }
}
function logout() {
    alert("Você foi desconectado!");
    window.location.href = 'login.html';  // Redireciona para a página de login
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();  // Impede o envio do formulário e recarregamento da página

    // Obtendo os valores de email e senha
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Verifica se os campos de email e senha não estão vazios
    if (!email || !password) {
        alert("Por favor, preencha todos os campos.");
        return;
    }

    try {
        // Realiza a requisição para a API de login
        const response = await fetch('http://localhost/meu_projeto_api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        // Verifica se a resposta foi ok (status 200)
        if (response.ok) {
            const data = await response.json();

            if (data.user_id) {
                // Se login for bem-sucedido, redireciona para o dashboard
                alert("Login realizado com sucesso!");
                window.location.href = "dashboard.html";
            } else {
                // Exibe mensagem de erro se não encontrar o usuário
                alert(data.error || "Erro ao realizar login.");
            }
        } else {
            // Se a resposta não for ok (erro do servidor)
            throw new Error("Erro ao conectar com a API.");
        }
    } catch (error) {
        console.error('Erro:', error);
        alert("Erro ao conectar com a API: " + error.message);
    }
});
