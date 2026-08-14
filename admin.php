<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

// Fetch Summary Statistics
$bus_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bus"))['total'];
$driver_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM driver"))['total'];
$route_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM route"))['total'];
$schedule_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM busschedule"))['total'];
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM student"))['total'];
$faculty_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM faculty"))['total'];

// Fetch Registered Students
$students_res = mysqli_query($conn, "SELECT student.*, busstop.StopName FROM student LEFT JOIN busstop ON student.StopID = busstop.StopID ORDER BY StudentID DESC LIMIT 10");

// Fetch Registered Faculty
$faculty_res = mysqli_query($conn, "SELECT faculty.*, busstop.StopName FROM faculty LEFT JOIN busstop ON faculty.StopID = busstop.StopID ORDER BY FacultyID DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University Bus Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="admin.php" class="brand">
            <div class="brand-icon">🚌</div>
            <span>UniBus Admin</span>
        </a>
        <ul class="nav-links">
            <li><a href="admin.php" class="active">Dashboard</a></li>
            <li><a href="manage_students.php">Students</a></li>
            <li><a href="manage_faculty.php">Faculty</a></li>
            <li><a href="bus.php">Buses</a></li>
            <li><a href="driver.php">Drivers</a></li>
            <li><a href="route.php">Routes</a></li>
            <li><a href="busstop.php">Stops</a></li>
            <li><a href="busschedule.php">Schedules</a></li>
            <li><a href="semester.php">Semesters</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Admin Control Center</h1>
                <p>Welcome back, Administrator (<?php echo htmlspecialchars($_SESSION['email']); ?>)</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <a href="bus.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">🚌</div>
                    <div class="stat-info">
                        <h3><?php echo $bus_count; ?></h3>
                        <p>Total Buses</p>
                    </div>
                </div>
            </a>

            <a href="driver.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-cyan">👨‍✈️</div>
                    <div class="stat-info">
                        <h3><?php echo $driver_count; ?></h3>
                        <p>Total Drivers</p>
                    </div>
                </div>
            </a>

            <a href="route.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-green">🗺️</div>
                    <div class="stat-info">
                        <h3><?php echo $route_count; ?></h3>
                        <p>Active Routes</p>
                    </div>
                </div>
            </a>

            <a href="busschedule.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-purple">⏰</div>
                    <div class="stat-info">
                        <h3><?php echo $schedule_count; ?></h3>
                        <p>Bus Schedules</p>
                    </div>
                </div>
            </a>

            <a href="manage_students.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-amber">🎓</div>
                    <div class="stat-info">
                        <h3><?php echo $student_count; ?></h3>
                        <p>Registered Students</p>
                    </div>
                </div>
            </a>

            <a href="manage_faculty.php" style="text-decoration:none; color:inherit;">
                <div class="stat-card">
                    <div class="stat-icon icon-rose">👨‍🏫</div>
                    <div class="stat-info">
                        <h3><?php echo $faculty_count; ?></h3>
                        <p>Registered Faculty</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Quick Module Navigation Cards -->
        <h2 style="margin-bottom:1rem; font-size:1.3rem;">System Management Modules</h2>
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 2.5rem;">
            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">🎓 Student Accounts</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Add, edit, or delete student registrations and update stop assignments.</p>
                <a href="manage_students.php" class="btn btn-primary btn-sm">Manage Students &rarr;</a>
            </div>

            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">👨‍🏫 Faculty Accounts</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Add, edit, or delete faculty registrations and update department details.</p>
                <a href="manage_faculty.php" class="btn btn-primary btn-sm">Manage Faculty &rarr;</a>
            </div>

            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">🚌 Fleet Management</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Add and update university buses, set seat capacity, and assign drivers.</p>
                <a href="bus.php" class="btn btn-primary btn-sm">Manage Buses &rarr;</a>
            </div>

            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">👨‍✈️ Driver Records</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Manage driver profiles, contact details, and license verification.</p>
                <a href="driver.php" class="btn btn-primary btn-sm">Manage Drivers &rarr;</a>
            </div>

            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">🗺️ Route Network</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Define route origins, destinations, distances, and duration estimates.</p>
                <a href="route.php" class="btn btn-primary btn-sm">Manage Routes &rarr;</a>
            </div>

            <div class="card">
                <h3 style="margin-bottom:0.5rem; font-size:1.1rem;">📍 Bus Stops</h3>
                <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1rem;">Configure pickup/dropoff bus stops and link them to routes.</p>
                <a href="busstop.php" class="btn btn-primary btn-sm">Manage Stops &rarr;</a>
            </div>
        </div>

        <!-- Registered Students Overview -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Registered Students</span>
                <a href="manage_students.php" class="btn btn-secondary btn-sm">+ Add / Manage Students</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Assigned Stop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($students_res) > 0): ?>
                            <?php while ($s = mysqli_fetch_assoc($students_res)): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($s['StudentID']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($s['FullName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($s['Department']); ?></td>
                                    <td>Semester <?php echo htmlspecialchars($s['AcademicSemester']); ?></td>
                                    <td><?php echo htmlspecialchars($s['StopName'] ?: 'Unassigned'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--text-muted);">No registered students yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Registered Faculty Overview -->
        <div class="card">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Registered Faculty</span>
                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">+ Add / Manage Faculty</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Faculty ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Assigned Stop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($faculty_res) > 0): ?>
                            <?php while ($f = mysqli_fetch_assoc($faculty_res)): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($f['FacultyID']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($f['FullName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($f['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($f['Department']); ?></td>
                                    <td><?php echo htmlspecialchars($f['StopName'] ?: 'Unassigned'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--text-muted);">No registered faculty members yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
