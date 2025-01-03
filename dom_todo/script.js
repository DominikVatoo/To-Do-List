const useDatabase = false;

const input = document.getElementById('todo-input');
const addButton = document.getElementById('add-btn');
const todoList = document.getElementById('todo-list');

function escapeHTML(string) {
    return string.replace(/[&<>"\'`=\/]/g, function (s) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;'
        })[s];
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (useDatabase) {
        fetch('api.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(task => addTaskToDOM(escapeHTML(task.task), task.id));
            });
    } else {
        loadTasks();
    }
});

addButton.addEventListener('click', () => {
    const task = input.value.trim();
    if (task) {
        if (useDatabase) {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task })
            }).then(() => location.reload());
        } else {
            addTaskToDOM(escapeHTML(task));
            saveTask(task);
        }
        input.value = '';
    }
});

function addTaskToDOM(task, id = null) {
    const listItem = document.createElement('li');
    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
    listItem.innerHTML = `
        ${task}
        <button onclick="removeTask(this, ${id})" class="btn btn-danger btn-sm">Löschen</button>
    `;
    todoList.appendChild(listItem);
}

function removeTask(button, id = null) {
    const taskText = button.parentElement.textContent.trim();
    button.parentElement.remove();

    if (useDatabase && id) {
        fetch('api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
    } else {
        removeTaskFromStorage(taskText);
    }
}

function removeTaskFromStorage(task) {
    let tasks = JSON.parse(localStorage.getItem('tasks')) || [];
    tasks = tasks.filter(t => t !== task);
    localStorage.setItem('tasks', JSON.stringify(tasks));
}

function loadTasks() {
    const tasks = JSON.parse(localStorage.getItem('tasks')) || [];
    tasks.forEach(task => addTaskToDOM(escapeHTML(task)));
}


function saveTask(task) {
    let tasks = JSON.parse(localStorage.getItem('tasks')) || [];
    tasks.push(task);
    localStorage.setItem('tasks', JSON.stringify(tasks));
}
