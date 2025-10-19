<?php
include __DIR__ . '/../db_connect.php';

$campus_id = $_SESSION['campus_id'] ?? 0;

$departments = []; // initialize

if ($campus_id) {
    $stmt = $conn->prepare("SELECT department_id, department_name FROM departments WHERE campus_id = ? ORDER BY department_name ASC");
    $stmt->bind_param("i", $campus_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }

    $stmt->close();
}

// Fetch campus name for the director
$campus_name = "Your Campus";
if (!empty($campus_id)) {
    $stmtCampus = $conn->prepare("SELECT campus_name FROM campuses WHERE campus_id = ?");
    $stmtCampus->bind_param("i", $campus_id);
    $stmtCampus->execute();
    $resultCampus = $stmtCampus->get_result();
    if ($resultCampus && $rowCampus = $resultCampus->fetch_assoc()) {
        $campus_name = htmlspecialchars($rowCampus['campus_name']);
    }
    $stmtCampus->close();
}
?>

<style>
:root {
    --primary-color: #003366;
    --secondary-color: #00509e; 
    --accent-color: #007bff; 
    --light-bg: #f9fafc;
    --dark-text: #1a1a1a;
    --border-color: #e5e7eb;
    --shadow-color: rgba(0, 0, 0, 0.1);
}

h2 {
    color: var(--primary-color);
    text-align: center;
    margin: 40px 0 25px 0;
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px var(--shadow-color);
}

thead {
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    color: white;
}

thead th {
    background: #28453D;
    padding: 14px;
    text-align: left;
    font-size: 14px;
    letter-spacing: 0.5px;
}

tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

tbody tr:hover {
    background-color: var(--light-bg);
    transform: scale(1.01);
}

tbody td {
    padding: 12px;
    font-size: 14px;
    color: var(--dark-text);
}

button.edit-btn, button.delete-btn {
    border: none;
    background: transparent;
    padding: 6px 12px;
    font-size: 13px;
    color: #000;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

#addDeptBtn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: var(--accent-color);
    color: white;
    border: none;
    font-size: 26px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 5px 18px var(--shadow-color);
    transition: transform 0.2s, background 0.3s;
}

#addDeptBtn:hover {
    background-color: #0056b3;
    transform: scale(1.1);
}

.modal {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal .modal-content {
    background-color: #fff;
    padding: 25px 30px;
    border-radius: 12px;
    width: 340px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal h3 {
    margin-top:0;
    color: var(--primary-color);
    font-size: 18px;
    margin-bottom: 10px;
}

.modal input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    margin-top: 10px;
    font-size: 14px;
    transition: border 0.2s;
}

.modal input:focus {
    border-color: var(--accent-color);
    outline: none;
}

.modal .modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}

.modal button {
    padding: 8px 14px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.modal button.cancel-btn {
    background-color: #e5e7eb;
    color: var(--dark-text);
}

.modal button.cancel-btn:hover {
    background-color: #d1d5db;
}

.modal button.submit-btn {
    background-color: var(--accent-color);
    color: #fff;
}

.modal button.submit-btn:hover {
    background-color: #0056b3;
}
</style>

<h2><?= $campus_name ?> Departments</h2>

<table>
    <thead>
        <tr>
            <th>Department Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($departments as $dept): ?>
        <tr>
            <td><?= htmlspecialchars($dept['department_name']); ?></td>
            <td>
                <button class="edit-btn" 
                        data-id="<?= $dept['department_id']; ?>" 
                        data-name="<?= htmlspecialchars($dept['department_name']); ?>">
                        <ion-icon name="create-outline"></ion-icon>
                </button>
                <button class="delete-btn"
                        data-id="<?= $dept['department_id']; ?>">
                        <ion-icon name="trash-bin-outline"></ion-icon>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Floating Add Button -->
<button id="addDeptBtn">+</button>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h3>Add Department</h3>
        <form id="addForm" method="POST">
            <label for="add_dept_name">Department Name:</label>
            <input type="text" name="department_name" id="add_dept_name" required>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" id="closeAddModal">Cancel</button>
                <button type="submit" class="submit-btn">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Department</h3>
        <form id="editForm" action="admin_includes/edit_department.php" method="POST">
            <input type="hidden" name="department_id" id="modal_dept_id">
            <label for="modal_dept_name">Department Name:</label>
            <input type="text" name="department_name" id="modal_dept_name" required>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" id="closeModal">Cancel</button>
                <button type="submit" class="submit-btn">Update</button>
            </div>
            
        </form>
    </div>
</div>

<script>
// Add Department Modal
const addBtn = document.getElementById('addDeptBtn');
const addModal = document.getElementById('addModal');
const closeAdd = document.getElementById('closeAddModal');

addBtn.addEventListener('click', () => addModal.style.display = 'flex');
closeAdd.addEventListener('click', () => addModal.style.display = 'none');

// Submit Add Department
document.getElementById('addForm').addEventListener('submit', e => {
    e.preventDefault();
    const name = document.getElementById('add_dept_name').value.trim();
    if (!name) return;

    fetch('admin_includes/add_department.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({department_name: name})
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert('Department added!');
            location.reload();
        } else {
            alert(data.message);
        }
    });
});

// Edit Department Modal
const editModal = document.getElementById('editModal');
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('modal_dept_id').value = btn.dataset.id;
        document.getElementById('modal_dept_name').value = btn.dataset.name;
        editModal.style.display = 'flex';
    });
});
document.getElementById('closeModal').addEventListener('click', () => editModal.style.display = 'none');

// Edit Department submit (success alert + reload)
document.getElementById('editForm').addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData(e.target);

    fetch('admin_includes/edit_department.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Department updated successfully!');
            location.reload();
        } else {
            alert('❌ Update failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Error: ' + err));
});

// Delete Department
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const deptId = btn.dataset.id;
        if (confirm('Are you sure you want to delete this department?')) {
            const formData = new FormData();
            formData.append('department_id', deptId);

            fetch('admin_includes/delete_department.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('🗑️ Department deleted successfully!');
                    location.reload();
                } else {
                    alert('❌ Delete failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert('Error: ' + err));
        }
    });
});


</script>
