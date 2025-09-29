<?php
/**
 * Template for Admin Panel Page
 */

// Check if user is logged in and is an operator
if (!isLoggedIn() || getCurrentUser()['role'] !== 'operator') {
    header('Location: index.php');
    exit();
}

$user = getCurrentUser();
$message = '';
$messageType = '';

// Handle user approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve' && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $result = approveUser($userId);
        if ($result) {
            $message = 'User approved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error approving user.';
            $messageType = 'danger';
        }
    } elseif ($_POST['action'] === 'reject' && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $result = rejectUser($userId);
        if ($result) {
            $message = 'User registration rejected.';
            $messageType = 'info';
        } else {
            $message = 'Error rejecting user.';
            $messageType = 'danger';
        }
    }
}

$pendingUsers = loadPendingUsers();
$users = loadUsers();
$content = getContent();

get_header(); ?>

<div class="container mt-4">
    <h1>Admin Panel</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
            <?php echo esc_html($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count($users); ?></h4>
                            <p class="mb-0">Active Users</p>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count($pendingUsers); ?></h4>
                            <p class="mb-0">Pending Approvals</p>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count($content); ?></h4>
                            <p class="mb-0">Total Posts</p>
                        </div>
                        <div>
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count(array_filter($users, function($u) { return $u['role'] === 'operator'; })); ?></h4>
                            <p class="mb-0">Operators</p>
                        </div>
                        <div>
                            <i class="fas fa-user-cog fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Pending User Approvals -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>
                        Pending User Approvals
                        <?php if (count($pendingUsers) > 0): ?>
                            <span class="badge bg-warning ms-2"><?php echo count($pendingUsers); ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingUsers)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>No pending user approvals</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Organization</th>
                                        <th>Scenario</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingUsers as $pendingUser): ?>
                                        <tr>
                                            <td><?php echo esc_html($pendingUser['name']); ?></td>
                                            <td><?php echo esc_html($pendingUser['email']); ?></td>
                                            <td><?php echo esc_html($pendingUser['organization']); ?></td>
                                            <td><?php echo esc_html($pendingUser['scenario_code'] ?: '-'); ?></td>
                                            <td><?php echo date('M j, Y', $pendingUser['submitted_at']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="user_id" value="<?php echo $pendingUser['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success me-1">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="user_id" value="<?php echo $pendingUser['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="create-content.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Content
                        </a>
                        <button class="btn btn-outline-secondary" onclick="refreshPage()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Data
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        System Information
                    </h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Platform:</strong> Newsroom Training<br>
                        <strong>Version:</strong> 2.0<br>
                        <strong>Last Updated:</strong> <?php echo date('M j, Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Users Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Active Users
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Organization</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $activeUser): ?>
                                    <tr>
                                        <td><?php echo esc_html($activeUser['name']); ?></td>
                                        <td><?php echo esc_html($activeUser['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $activeUser['role'] === 'operator' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($activeUser['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($activeUser['organization'] ?? '-'); ?></td>
                                        <td><?php echo isset($activeUser['created_at']) ? date('M j, Y', $activeUser['created_at']) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function refreshPage() {
        location.reload();
    }
</script>

<?php get_footer(); ?>