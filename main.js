// Referências ao modal e ao formulário
const modal = document.getElementById("add-task-modal");
const form = document.getElementById("add-task-form");

// Função para abrir o modal
function openAddTaskModal() {
    modal.classList.remove("hidden");
}

// Função para fechar o modal
function closeAddTaskModal() {
    modal.classList.add("hidden");
}

// Função para lidar com o envio do formulário
form.addEventListener("submit", (event) => {
    event.preventDefault();
    const task = {
        title: form.title.value,
        description: form.description.value,
        status: form.status.value,
    };

    console.log("Nova Tarefa:", task);

    // Aqui você pode adicionar a lógica para enviar a tarefa para a API
    closeAddTaskModal();
    form.reset();
});
