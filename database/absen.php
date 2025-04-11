<!DOCTYPE html>
<html>
<head>
<?php
session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
    <style>
        /* ... (style yang sama seperti sebelumnya) ... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 2rem;
        }

        .attendance-container {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #333;
            font-size: 1.75rem;
            font-weight: 600;
        }

        .date-picker {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-picker input {
            padding: 0.5rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
        }

        .search-bar {
            margin-bottom: 2rem;
        }

        .search-bar input {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .attendance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .attendance-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #333;
        }

        .attendance-table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .hadir {
            background: #dcfce7;
            color: #166534;
        }

        .alfa {
            background: #fee2e2;
            color: #991b1b;
        }

        .terlambat {
            background: #fef3c7;
            color: #92400e;
        }

        .izin {
            background-color: #f4ff93;
            color: darkorange;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .page-btn {
            padding: 0.5rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
        }

        .page-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="attendance-container">
        <div class="header">
            <h1>Absen Kelas</h1>
            <div class="header-actions">
                <button class="btn btn-add-student" onclick="showAddModal()">
                    <span>+ Tambahkan Murid</span>
                </button>
                <div class="date-picker">
                    <input type="date" id="datePicker">
                    <button class="btn btn-primary" id="exportBtn">Export</button>
                </div>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Cari berdasarkan Nama atau NIS...">
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Kehadiran</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="attendanceTableBody">
                <!-- Table content will be dynamically populated -->
            </tbody>
        </table>

        <div class="pagination" id="pagination">
            <!-- Pagination buttons will be dynamically populated -->
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Kehadiran</h2>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="editForm">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Masuk</label>
                    <input type="time" id="editTimeIn">
                </div>
                <div class="form-group">
                    <label>Pulang</label>
                    <input type="time" id="editTimeOut">
                </div>
                <div class="form-group">
                    <label>Kehadiran</label>
                    <select id="editStatus">
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="alfa">Alfa</option>
                        <option value="izin">Izin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambahkan Murid</h2>
                <button class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>
            <form id="addForm">
                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" id="newId" required placeholder="Enter student ID">
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" id="newName" required placeholder="Enter student name">
                </div>
                <div class="form-group">
                    <label>Masuk</label>
                    <input type="time" id="newTimeIn">
                </div>
                <div class="form-group">
                    <label>Pulang</label>
                    <input type="time" id="newTimeOut">
                </div>
                <div class="form-group">
                    <label>Kehadiran</label>
                    <select id="newStatus">
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="alfa">Alfa</option>
                        <option value="izin">Izin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tambahkan Murid</button>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="successMessage">
        Murid Telah Berhasil Ditambahkan!
    </div>

    <script>
        // Sample data
        let attendanceData = [
            { id: '001', name: 'John Doe', timeIn: '08:00', timeOut: '17:00', status: 'hadir' },
            { id: '002', name: 'Jane Smith', timeIn: '08:30', timeOut: '17:15', status: 'terlambat' },
            { id: '003', name: 'Mike Johnson', timeIn: '', timeOut: '', status: 'alfa' }
        ];

        // Current page and items per page
        let currentPage = 1;
        const itemsPerPage = 10;

        // Initialize date picker with today's date
        document.getElementById('datePicker').valueAsDate = new Date();

        // Function to render table data
        function renderTable(data) {
            const tableBody = document.getElementById('attendanceTableBody');
            tableBody.innerHTML = '';

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = data.slice(start, end);

            paginatedData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.timeIn || '-'}</td>
                    <td>${item.timeOut || '-'}</td>
                    <td><span class="status-badge ${item.status}">${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</span></td>
                    <td class="action-buttons">
                        <button class="btn btn-primary" onclick="editAttendance('${item.id}')">Edit</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            renderPagination(data.length);
        }

        // Function to render pagination
        function renderPagination(totalItems) {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            // Previous button
            const prevButton = document.createElement('button');
            prevButton.className = 'page-btn';
            prevButton.textContent = 'Previous';
            prevButton.disabled = currentPage === 1;
            prevButton.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable(attendanceData);
                }
            };
            pagination.appendChild(prevButton);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `page-btn ${currentPage === i ? 'active' : ''}`;
                pageButton.textContent = i;
                pageButton.onclick = () => {
                    currentPage = i;
                    renderTable(attendanceData);
                };
                pagination.appendChild(pageButton);
            }

            // Next button
            const nextButton = document.createElement('button');
            nextButton.className = 'page-btn';
            nextButton.textContent = 'Next';
            nextButton.disabled = currentPage === totalPages;
            nextButton.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable(attendanceData);
                }
            };
            pagination.appendChild(nextButton);
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredData = attendanceData.filter(item =>
                item.name.toLowerCase().includes(searchTerm) ||
                item.id.includes(searchTerm)
            );
            currentPage = 1;
            renderTable(filteredData);
        });

        // Export functionality
        document.getElementById('exportBtn').addEventListener('click', () => {
            const date = document.getElementById('datePicker').value;
            const csvContent = 'data:text/csv;charset=utf-8,' +
                'ID,Name,Time In,Time Out,Status\n' +
                attendanceData.map(row => 
                    `${row.id},${row.name},${row.timeIn},${row.timeOut},${row.status}`
                ).join('\n');
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `attendance_${date}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Modal functionality
        const modal = document.getElementById('editModal');
        const closeModal = document.getElementById('closeModal');
        const editForm = document.getElementById('editForm');

        function editAttendance(id) {
            const item = attendanceData.find(x => x.id === id);
            if (item) {
                document.getElementById('editId').value = item.id;
                document.getElementById('editName').value = item.name;
                document.getElementById('editTimeIn').value = item.timeIn;
                document.getElementById('editTimeOut').value = item.timeOut;
                document.getElementById('editStatus').value = item.status;
                modal.style.display = 'flex';
            }
        }

        closeModal.onclick = () => {
            modal.style.display = 'none';
        }

        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        editForm.onsubmit = (e) => {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const index = attendanceData.findIndex(x => x.id === id);
            
            if (index !== -1) {
                attendanceData[index] = {
                    id: id,
                    name: document.getElementById('editName').value,
                    timeIn: document.getElementById('editTimeIn').value,
                    timeOut: document.getElementById('editTimeOut').value,
                    status: document.getElementById('editStatus').value
                };
                renderTable(attendanceData);
                modal.style.display = 'none';
            }
        }

        // Initial render
        renderTable(attendanceData);

        // Add Student Modal Functions
        const addModal = document.getElementById('addModal');
        const addForm = document.getElementById('addForm');
        const successMessage = document.getElementById('successMessage');

        function showAddModal() {
            addModal.style.display = 'flex';
            // Reset form
            addForm.reset();
            // Generate new ID
            const newId = generateNewId();
            document.getElementById('newId').value = newId;
        }

        function closeAddModal() {
            addModal.style.display = 'none';
        }

        function generateNewId() {
            // Get the highest current ID and increment it
            const highestId = Math.max(...attendanceData.map(item => parseInt(item.id)));
            const newId = (highestId + 1).toString().padStart(3, '0');
            return newId;
        }

        function showSuccessMessage() {
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }

        addForm.onsubmit = (e) => {
            e.preventDefault();
            
            const newStudent = {
                id: document.getElementById('newId').value,
                name: document.getElementById('newName').value,
                timeIn: document.getElementById('newTimeIn').value,
                timeOut: document.getElementById('newTimeOut').value,
                status: document.getElementById('newStatus').value
            };

            // Validate if ID already exists
            if (attendanceData.some(student => student.id === newStudent.id)) {
                alert('Student ID already exists!');
                return;
            }

            // Add new student to data
            attendanceData.push(newStudent);
            
            // Re-render table
            renderTable(attendanceData);
            
            // Close modal and show success message
            closeAddModal();
            showSuccessMessage();
        };

        // Close modal when clicking outside
        window.onclick = (event) => {
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>
