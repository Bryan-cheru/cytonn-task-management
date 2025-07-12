<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Browser - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .browser-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        .logo {
            color: #667eea;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .alert {
            border-radius: 10px;
        }
        .sql-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="browser-container">
            <div class="logo">
                <i class="fas fa-database"></i>
                <h2 class="mt-2">Database Browser</h2>
                <p class="text-muted">PostgreSQL Database Viewer</p>
            </div>

            <?php
            // Database connection
            try {
                if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
                    require_once __DIR__ . '/config/database-docker.php';
                } else {
                    require_once __DIR__ . '/config/database.php';
                }
                
                $db = new Database();
                $connected = true;
                
            } catch (Exception $e) {
                $connected = false;
                $error = $e->getMessage();
            }
            ?>

            <?php if (!$connected): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Database Connection Failed</strong>
                    <p class="mb-0 mt-2"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Database Connected Successfully</strong>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs" id="dbTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tables-tab" data-bs-toggle="tab" data-bs-target="#tables" type="button" role="tab">
                            <i class="fas fa-table"></i> Tables
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                            <i class="fas fa-users"></i> Users
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
                            <i class="fas fa-tasks"></i> Tasks
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="query-tab" data-bs-toggle="tab" data-bs-target="#query" type="button" role="tab">
                            <i class="fas fa-code"></i> Custom Query
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="dbTabsContent">
                    <!-- Tables Tab -->
                    <div class="tab-pane fade show active" id="tables" role="tabpanel">
                        <div class="mt-3">
                            <h5><i class="fas fa-table"></i> Database Schema</h5>
                            
                            <?php
                            try {
                                // Get table information
                                $stmt = $db->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
                                $stmt->execute();
                                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                
                                echo '<div class="row">';
                                foreach ($tables as $table) {
                                    echo '<div class="col-md-6 mb-3">';
                                    echo '<div class="card">';
                                    echo '<div class="card-header"><strong>' . htmlspecialchars($table) . '</strong></div>';
                                    echo '<div class="card-body">';
                                    
                                    // Get column information
                                    $stmt = $db->prepare("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position");
                                    $stmt->execute([$table]);
                                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    echo '<table class="table table-sm">';
                                    echo '<thead><tr><th>Column</th><th>Type</th><th>Nullable</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($columns as $column) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($column['column_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($column['data_type']) . '</td>';
                                        echo '<td>' . ($column['is_nullable'] === 'YES' ? 'Yes' : 'No') . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '</div></div></div>';
                                }
                                echo '</div>';
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Users Tab -->
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        <div class="mt-3">
                            <h5><i class="fas fa-users"></i> Users Table</h5>
                            
                            <?php
                            try {
                                $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY id");
                                $stmt->execute();
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($users)) {
                                    echo '<div class="alert alert-info">No users found in the database.</div>';
                                } else {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-striped">';
                                    echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created At</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($users as $user) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                                        echo '<td><span class="badge bg-' . ($user['role'] === 'admin' ? 'danger' : 'primary') . '">' . htmlspecialchars($user['role']) . '</span></td>';
                                        echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '</div>';
                                }
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Tasks Tab -->
                    <div class="tab-pane fade" id="tasks" role="tabpanel">
                        <div class="mt-3">
                            <h5><i class="fas fa-tasks"></i> Tasks Table</h5>
                            
                            <?php
                            try {
                                $stmt = $db->prepare("SELECT t.id, t.title, t.description, t.status, t.priority, t.due_date, u.name as assigned_user, t.created_at FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id ORDER BY t.id");
                                $stmt->execute();
                                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($tasks)) {
                                    echo '<div class="alert alert-info">No tasks found in the database.</div>';
                                } else {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-striped">';
                                    echo '<thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Priority</th><th>Assigned To</th><th>Due Date</th><th>Created</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($tasks as $task) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($task['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($task['title']) . '</td>';
                                        echo '<td><span class="badge bg-secondary">' . htmlspecialchars($task['status']) . '</span></td>';
                                        echo '<td><span class="badge bg-info">' . htmlspecialchars($task['priority']) . '</span></td>';
                                        echo '<td>' . htmlspecialchars($task['assigned_user'] ?? 'Unassigned') . '</td>';
                                        echo '<td>' . htmlspecialchars($task['due_date'] ?? 'No due date') . '</td>';
                                        echo '<td>' . htmlspecialchars($task['created_at']) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '</div>';
                                }
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Custom Query Tab -->
                    <div class="tab-pane fade" id="query" role="tabpanel">
                        <div class="mt-3">
                            <h5><i class="fas fa-code"></i> Custom SQL Query</h5>
                            
                            <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_query'])) {
                                $query = trim($_POST['sql_query']);
                                
                                if (!empty($query)) {
                                    try {
                                        $stmt = $db->prepare($query);
                                        $stmt->execute();
                                        
                                        if (stripos($query, 'SELECT') === 0) {
                                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            if (empty($results)) {
                                                echo '<div class="alert alert-info">Query executed successfully. No results returned.</div>';
                                            } else {
                                                echo '<div class="alert alert-success">Query executed successfully. ' . count($results) . ' rows returned.</div>';
                                                echo '<div class="table-responsive">';
                                                echo '<table class="table table-striped table-sm">';
                                                
                                                // Headers
                                                echo '<thead><tr>';
                                                foreach (array_keys($results[0]) as $column) {
                                                    echo '<th>' . htmlspecialchars($column) . '</th>';
                                                }
                                                echo '</tr></thead>';
                                                
                                                // Data
                                                echo '<tbody>';
                                                foreach ($results as $row) {
                                                    echo '<tr>';
                                                    foreach ($row as $value) {
                                                        echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                                                    }
                                                    echo '</tr>';
                                                }
                                                echo '</tbody></table>';
                                                echo '</div>';
                                            }
                                        } else {
                                            $rowCount = $stmt->rowCount();
                                            echo '<div class="alert alert-success">Query executed successfully. ' . $rowCount . ' rows affected.</div>';
                                        }
                                        
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                    }
                                }
                            }
                            ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="sql_query" class="form-label">SQL Query:</label>
                                    <textarea class="form-control" id="sql_query" name="sql_query" rows="5" placeholder="Enter your SQL query here..."><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Execute Query
                                </button>
                            </form>
                            
                            <div class="mt-3">
                                <h6>Quick Queries:</h6>
                                <div class="btn-group-vertical d-grid gap-2">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="setQuery('SELECT * FROM users;')">Show all users</button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="setQuery('SELECT * FROM tasks;')">Show all tasks</button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="setQuery('SELECT COUNT(*) as user_count FROM users;')">Count users</button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="setQuery('SELECT COUNT(*) as task_count FROM tasks;')">Count tasks</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setQuery(query) {
            document.getElementById('sql_query').value = query;
        }
    </script>
</body>
</html>
