    <?php
    // teknisi.php (Redesigned, modern, professional, elegant, fully responsive)
    session_start();
    if (!isset($_SESSION['teknisi'])) {
        $_SESSION['teknisi'] = [
            ['id' => 1, 'nama' => 'Andi Pratama', 'email' => 'andi@example.com', 'telepon' => '081234567890', 'status' => 'Aktif'],
            ['id' => 2, 'nama' => 'Budi Santoso', 'email' => 'budi@example.com', 'telepon' => '081298765432', 'status' => 'Tidak Aktif'],
            ['id' => 3, 'nama' => 'Citra Dewi', 'email' => 'citra@example.com', 'telepon' => '081212345678', 'status' => 'Aktif'],
        ];
    }
    $teknisi = $_SESSION['teknisi'];
    $successMsg = '';

    // Handle Big Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['big_update'])) {
        $ids = $_POST['ids'] ?? [];
        $new_status = $_POST['new_status'] ?? '';
        if ($ids && $new_status) {
            foreach ($teknisi as &$t) {
                if (in_array($t['id'], $ids)) {
                    $t['status'] = $new_status;
                }
            }
            unset($t);
            $_SESSION['teknisi'] = $teknisi;
            $successMsg = "Status teknisi terpilih berhasil diupdate ke <b>$new_status</b>.";
        }
    }

    // Handle Edit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
        $edit_id = (int)$_POST['edit_id'];
        $edit_nama = trim($_POST['edit_nama'] ?? '');
        $edit_email = trim($_POST['edit_email'] ?? '');
        $edit_telepon = trim($_POST['edit_telepon'] ?? '');
        $edit_status = $_POST['edit_status'] ?? '';
        foreach ($teknisi as &$t) {
            if ($t['id'] == $edit_id) {
                $t['nama'] = $edit_nama;
                $t['email'] = $edit_email;
                $t['telepon'] = $edit_telepon;
                $t['status'] = $edit_status;
                $successMsg = "Data teknisi <b>".htmlspecialchars($edit_nama)."</b> berhasil diupdate.";
                break;
            }
        }
        unset($t);
        $_SESSION['teknisi'] = $teknisi;
    }

    // Handle Delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        foreach ($teknisi as $k => $t) {
            if ($t['id'] == $delete_id) {
                $successMsg = "Teknisi <b>".htmlspecialchars($t['nama'])."</b> berhasil dihapus.";
                unset($teknisi[$k]);
                break;
            }
        }
        $_SESSION['teknisi'] = array_values($teknisi);
    }

    $teknisi = $_SESSION['teknisi'];
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Daftar Teknisi</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Google Fonts & Bootstrap 5 & Remixicon -->
        <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
        <style>
            :root {
                --primary: #2563eb;
                --primary-dark: #1e40af;
                --bg: #f8fafc;
                --card-bg: #fff;
                --border: #e5e7eb;
                --success: #22c55e;
                --danger: #ef4444;
                --danger-bg: #fee2e2;
                --success-bg: #e7fbe9;
                --shadow: 0 8px 32px rgba(37,99,235,0.09);
            }
            html, body {
                height: 100%;
            }
            body {
                font-family: 'Inter', Arial, sans-serif;
                background: linear-gradient(120deg, var(--bg) 60%, #e0e7ff 100%);
                color: #22223b;
                min-height: 100vh;
            }
            .navbar {
                border-radius: 0 0 24px 24px;
                box-shadow: var(--shadow);
                background: var(--card-bg);
            }
            .navbar-brand {
                font-size: 1.5rem;
                letter-spacing: 0.5px;
                color: var(--primary-dark) !important;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .navbar .btn {
                border-radius: 12px;
                font-weight: 500;
            }
            .main-card {
                border-radius: 24px;
                box-shadow: var(--shadow);
                border: none;
                background: var(--card-bg);
                padding: 2.5rem 2rem 2rem 2rem;
                margin-top: 2rem;
            }
            .table {
                border-radius: 18px;
                overflow: hidden;
                background: var(--card-bg);
            }
            .table thead th {
                background: #f1f5f9;
                color: #22223b;
                font-weight: 700;
                border-bottom: 2px solid var(--border);
                font-size: 1.05em;
            }
            .table tbody tr {
                transition: background 0.2s;
            }
            .table tbody tr:hover {
                background: #f3f6fa;
            }
            .status-badge {
                font-size: 0.95em;
                padding: 0.35em 1.1em;
                border-radius: 1em;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.4em;
            }
            .status-aktif {
                background: var(--success-bg);
                color: var(--success);
            }
            .status-tidak {
                background: var(--danger-bg);
                color: var(--danger);
            }
            .btn-primary {
                background: var(--primary);
                border: none;
                border-radius: 12px;
                font-weight: 600;
            }
            .btn-primary:hover, .btn-primary:focus {
                background: var(--primary-dark);
            }
            .btn-outline-primary {
                border-color: var(--primary);
                color: var(--primary);
                border-radius: 12px;
                font-weight: 600;
            }
            .btn-outline-primary:hover {
                background: var(--primary);
                color: #fff;
            }
            .btn-outline-danger {
                border-color: var(--danger);
                color: var(--danger);
                border-radius: 12px;
                font-weight: 600;
            }
            .btn-outline-danger:hover {
                background: var(--danger);
                color: #fff;
            }
            .modal-content {
                border-radius: 18px;
                box-shadow: var(--shadow);
                border: none;
            }
            .form-label {
                font-weight: 600;
                color: var(--primary-dark);
            }
            .form-control, .form-select {
                border-radius: 10px;
                border: 1px solid var(--border);
                font-size: 1em;
            }
            .alert-success {
                background: var(--success-bg);
                color: var(--success);
                border: none;
                border-radius: 12px;
                font-weight: 600;
            }
            .alert-danger {
                background: var(--danger-bg);
                color: var(--danger);
                border: none;
                border-radius: 12px;
                font-weight: 600;
            }
            .table-responsive {
                border-radius: 18px;
                overflow-x: auto;
            }
            @media (max-width: 991px) {
                .main-card {
                    padding: 1.5rem 0.5rem 1.5rem 0.5rem;
                }
                .navbar {
                    border-radius: 0 0 16px 16px;
                }
            }
            @media (max-width: 700px) {
                .main-card {
                    padding: 1rem 0.2rem;
                }
                .table th, .table td {
                    font-size: 0.97em;
                    padding: 0.6em 0.4em;
                }
                .navbar-brand {
                    font-size: 1.1rem;
                }
            }
            @media (max-width: 500px) {
                .main-card {
                    margin-top: 1rem;
                    padding: 0.5rem 0.1rem;
                }
                .table th, .table td {
                    font-size: 0.93em;
                    padding: 0.5em 0.2em;
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg shadow-sm mb-4 py-3">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">
                    <i class="ri-user-settings-line"></i> Teknisi Panel
                </a>
                <div class="ms-auto">
                    <a href="../index.php" class="btn btn-outline-primary">
                        <i class="ri-logout-box-r-line"></i> Logout
                    </a>
                </div>
            </div>
        </nav>
        <div class="container" style="max-width: 1050px;">
            <div class="main-card">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h2 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2" style="font-size:1.6rem;">
                        <i class="ri-team-line"></i> Daftar Teknisi
                    </h2>
                    <div>
                        <button class="btn btn-primary me-2" id="addBtn">
                            <i class="ri-user-add-line"></i> Tambah Teknisi
                        </button>
                        <button class="btn btn-outline-primary" id="bigUpdateBtn" disabled>
                            <i class="ri-refresh-line"></i> Big Update
                        </button>
                    </div>
                </div>
                <?php if ($successMsg): ?>
                    <div class="alert alert-success mb-3"><?= $successMsg ?></div>
                <?php endif; ?>
                <form id="teknisiForm" method="post">
                    <input type="hidden" name="big_update" value="1">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:36px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th style="width:120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teknisi as $t): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check form-check-input" name="ids[]" value="<?= $t['id'] ?>">
                                    </td>
                                    <td><?= htmlspecialchars($t['nama']) ?></td>
                                    <td><?= htmlspecialchars($t['email']) ?></td>
                                    <td><?= htmlspecialchars($t['telepon']) ?></td>
                                    <td>
                                        <?php if ($t['status'] === 'Aktif'): ?>
                                            <span class="status-badge status-aktif"><i class="ri-checkbox-circle-line"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="status-badge status-tidak"><i class="ri-close-circle-line"></i> Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary editBtn"
                                            data-id="<?= $t['id'] ?>"
                                            data-nama="<?= htmlspecialchars($t['nama']) ?>"
                                            data-email="<?= htmlspecialchars($t['email']) ?>"
                                            data-telepon="<?= htmlspecialchars($t['telepon']) ?>"
                                            data-status="<?= $t['status'] ?>"
                                            title="Edit">
                                            <i class="ri-edit-2-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger deleteBtn"
                                            data-id="<?= $t['id'] ?>"
                                            data-nama="<?= htmlspecialchars($t['nama']) ?>"
                                            title="Hapus">
                                            <i class="ri-delete-bin-6-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Big Update -->
        <div class="modal fade" id="bigUpdateModal" tabindex="-1" aria-labelledby="bigUpdateModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
              <div class="modal-header border-0">
                <h5 class="modal-title text-primary" id="bigUpdateModalLabel">
                    <i class="ri-refresh-line"></i> Big Update Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body">
                <label for="new_status" class="form-label">Status Baru</label>
                <select class="form-select" name="new_status" id="new_status" required>
                    <option value="">Pilih Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100" id="submitBigUpdate">Update</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Edit Teknisi -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
              <div class="modal-header border-0">
                <h5 class="modal-title text-primary" id="editModalLabel">
                    <i class="ri-edit-2-line"></i> Edit Teknisi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body">
                <form id="editForm" method="post" autocomplete="off">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-2">
                        <label for="edit_nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="edit_nama" id="edit_nama" required>
                    </div>
                    <div class="mb-2">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="edit_email" id="edit_email" required>
                    </div>
                    <div class="mb-2">
                        <label for="edit_telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="edit_telepon" id="edit_telepon" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" name="edit_status" id="edit_status" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Simpan Perubahan</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Hapus Teknisi -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
              <div class="modal-header border-0">
                <h5 class="modal-title text-danger" id="deleteModalLabel">
                    <i class="ri-delete-bin-6-line"></i> Hapus Teknisi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body">
                <form id="deleteForm" method="post">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div id="deleteMsg" class="mb-3"></div>
                    <button class="btn btn-danger w-100 mb-2" type="submit" id="confirmDeleteBtn">Hapus</button>
                    <button class="btn btn-outline-secondary w-100" data-bs-dismiss="modal" type="button">Batal</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Bootstrap JS & Custom Script -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Checkbox logic
            const selectAll = document.getElementById('selectAll');
            const rowChecks = document.querySelectorAll('.row-check');
            const bigUpdateBtn = document.getElementById('bigUpdateBtn');
            const teknisiForm = document.getElementById('teknisiForm');

            function updateBigUpdateBtn() {
                const checked = document.querySelectorAll('.row-check:checked').length;
                bigUpdateBtn.disabled = checked === 0;
            }
            selectAll.addEventListener('change', function() {
                rowChecks.forEach(cb => cb.checked = selectAll.checked);
                updateBigUpdateBtn();
            });
            rowChecks.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateBigUpdateBtn();
                    if (!this.checked) selectAll.checked = false;
                    else if (document.querySelectorAll('.row-check:checked').length === rowChecks.length)
                        selectAll.checked = true;
                });
            });

            // Big Update Modal
            const bigUpdateModal = new bootstrap.Modal(document.getElementById('bigUpdateModal'));
            bigUpdateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('new_status').value = '';
                bigUpdateModal.show();
            });
            document.getElementById('submitBigUpdate').onclick = function(e) {
                e.preventDefault();
                const newStatus = document.getElementById('new_status').value;
                if (!newStatus) {
                    document.getElementById('new_status').focus();
                    return;
                }
                // Append new_status to form and submit
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'new_status';
                input.value = newStatus;
                teknisiForm.appendChild(input);
                teknisiForm.submit();
            };

            // Edit Modal
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id').value = this.dataset.id;
                    document.getElementById('edit_nama').value = this.dataset.nama;
                    document.getElementById('edit_email').value = this.dataset.email;
                    document.getElementById('edit_telepon').value = this.dataset.telepon;
                    document.getElementById('edit_status').value = this.dataset.status;
                    editModal.show();
                });
            });

            // Delete Modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('delete_id').value = this.dataset.id;
                    document.getElementById('deleteMsg').innerHTML = `Yakin ingin menghapus <b>${this.dataset.nama}</b>?`;
                    deleteModal.show();
                });
            });

            // Add Teknisi (dummy)
            document.getElementById('addBtn').onclick = function() {
                alert('Fitur tambah teknisi hanya simulasi (dummy).');
            };
        </script>
    </body>
    </html>
