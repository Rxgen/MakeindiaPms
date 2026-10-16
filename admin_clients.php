<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $edit = pms_client_by_id($mysqli, (int) $_GET['id']);
}

if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $existing = pms_client_by_id($mysqli, $id);
    if ($existing) {
        $next = (int) $existing['is_active'] === 1 ? 0 : 1;
        $stmt = $mysqli->prepare('UPDATE pms_clients SET is_active = ? WHERE id = ?');
        $stmt->bind_param('ii', $next, $id);
        $stmt->execute();
        $stmt->close();
        pms_audit($mysqli, pms_admin_actor(), $next ? 'Activated client' : 'Deactivated client', 'client', $id, $existing['is_active'], $next);
        header('Location: admin_clients.php');
        exit();
    }
}

if (isset($_GET['reset_password'])) {
    $id = (int) $_GET['reset_password'];
    $existing = pms_client_by_id($mysqli, $id);
    if ($existing) {
        pms_set_client_password($mysqli, $id, pms_default_password(), 1);
        pms_audit($mysqli, pms_admin_actor(), 'Reset client password to default', 'client', $id, $existing['login_password'], pms_default_password());
        header('Location: admin_clients.php?ok=1');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = trim($_POST['name']);
    $email = pms_normalize_email($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $status = trim($_POST['status']);
    $login_password = trim(isset($_POST['login_password']) ? $_POST['login_password'] : '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $must_change = isset($_POST['must_change_password']) ? 1 : 0;

    if ($name === '' || $email === '' || $mobile === '') {
        $error = 'Name, email and mobile are required.';
    } elseif ($login_password === '') {
        $error = 'Login password is required.';
    } else {
        if ($id > 0) {
            $old = pms_client_by_id($mysqli, $id);
            $stmt = $mysqli->prepare('UPDATE pms_clients SET name = ?, email = ?, mobile = ?, login_password = ?, must_change_password = ?, status = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('ssssisii', $name, $email, $mobile, $login_password, $must_change, $status, $is_active, $id);
            if ($stmt->execute()) {
                pms_audit($mysqli, pms_admin_actor(), 'Updated client', 'client', $id, json_encode($old), json_encode(array('name' => $name, 'email' => $email, 'mobile' => $mobile, 'login_password' => $login_password, 'status' => $status, 'is_active' => $is_active)));
                $message = 'Client updated.';
            } else {
                $error = 'Could not update client. Email or mobile may already exist.';
            }
            $stmt->close();
        } else {
            $stmt = $mysqli->prepare('INSERT INTO pms_clients (name, email, mobile, login_password, must_change_password, status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssisi', $name, $email, $mobile, $login_password, $must_change, $status, $is_active);
            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                pms_audit($mysqli, pms_admin_actor(), 'Created client', 'client', $newId, '', $name . ' / ' . $email);
                $message = 'Client created.';
            } else {
                $error = 'Could not create client. Email or mobile may already exist.';
            }
            $stmt->close();
        }
        if (!$error) {
            header('Location: admin_clients.php?ok=1');
            exit();
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$clients = pms_client_rows($mysqli);
$pms_admin_title = 'Manage Clients';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Client Management</h3>
<p class="text-muted">Login is email + password. Default password for new clients is <strong><?= pms_h(pms_default_password()) ?></strong>. When a client changes their password, the new value appears in the Password column below.</p>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Client' : 'Create Client' ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input class="form-control" name="name" required value="<?= $edit ? pms_h($edit['name']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Registered Email</label>
                        <input class="form-control" type="email" name="email" required value="<?= $edit ? pms_h($edit['email']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Registered Mobile</label>
                        <input class="form-control" name="mobile" required value="<?= $edit ? pms_h($edit['mobile']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Login Password</label>
                        <input class="form-control" name="login_password" required value="<?= $edit ? pms_h($edit['login_password']) : pms_h(pms_default_password()) ?>">
                        <div class="form-text">Visible to admin. Client can change this after login.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Status</label>
                        <input class="form-control" name="status" value="<?= $edit ? pms_h($edit['status']) : 'Active & Verified' ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !$edit || (int) $edit['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active (can login)</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="must_change_password" id="must_change_password" <?= !$edit || !empty($edit['must_change_password']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="must_change_password">Ask client to change password on next login</label>
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Client' : 'Create Client' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_clients.php">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Mobile</th>
                            <th>Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clients as $row): ?>
                        <tr>
                            <td><?= pms_h($row['name']) ?></td>
                            <td><?= pms_h($row['email']) ?></td>
                            <td>
                                <code><?= pms_h($row['login_password']) ?></code>
                                <?php if (pms_client_uses_default_password($row)): ?>
                                    <div class="small text-muted">Default / pending change</div>
                                <?php else: ?>
                                    <div class="small text-success">Changed by client</div>
                                <?php endif; ?>
                            </td>
                            <td><?= pms_h($row['mobile']) ?></td>
                            <td><?= (int) $row['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <a class="btn btn-sm btn-dark" href="admin_client_view.php?id=<?= (int) $row['id'] ?>">View</a>
                                <a class="btn btn-sm btn-primary" href="admin_clients.php?id=<?= (int) $row['id'] ?>">Edit</a>
                                <a class="btn btn-sm btn-warning" href="admin_clients.php?reset_password=<?= (int) $row['id'] ?>">Reset Pwd</a>
                                <a class="btn btn-sm btn-outline-dark" href="admin_clients.php?toggle=<?= (int) $row['id'] ?>"><?= (int) $row['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
