<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_stop = null;

// Handle Delete Stop
if (isset($_GET['delete'])) {
    $stopID = intval($_GET['delete']);
    // Check if stop is assigned to student or faculty
    $check_std = mysqli_query($conn, "SELECT StudentID FROM student WHERE StopID=$stopID");
    $check_fac = mysqli_query($conn, "SELECT FacultyID FROM faculty WHERE StopID=$stopID");
    if (mysqli_num_rows($check_std) > 0 || mysqli_num_rows($check_fac) > 0) {
        $error = "Cannot delete bus stop! It is currently assigned to registered students or faculty members.";
    } else {
        $del_sql = "DELETE FROM busstop WHERE StopID=$stopID";
        if (mysqli_query($conn, $del_sql)) {
            $message = "Bus stop deleted successfully.";
        } else {
            $error = "Error deleting stop: " . mysqli_error($conn);
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM busstop WHERE StopID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_stop = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_stop'])) {
    $stopName = mysqli_real_escape_string($conn, trim($_POST['StopName']));
    $location = mysqli_real_escape_string($conn, trim($_POST['Location']));
    $routeID = !empty($_POST['RouteID']) ? intval($_POST['RouteID']) : "NULL";
    $stopID = isset($_POST['StopID']) ? intval($_POST['StopID']) : 0;

    if ($stopID > 0) {
        // Update
        $sql = "UPDATE busstop SET StopName='$stopName', Location='$location', RouteID=$routeID WHERE StopID=$stopID";
        if (mysqli_query($conn, $sql)) {
            $message = "Bus stop updated successfully.";
            header("Location: busstop.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating stop: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO busstop (StopName, Location, RouteID) VALUES ('$stopName', '$location', $routeID)";
        if (mysqli_query($conn, $sql)) {
            $message = "Bus stop added successfully.";
            header("Location: busstop.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding stop: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all bus stops with Route Name
$stops_query = "SELECT busstop.*, route.RouteName 
                FROM busstop 
                LEFT JOIN route ON busstop.RouteID = route.RouteID 
                ORDER BY busstop.StopID DESC";
$stops_result = mysqli_query($conn, $stops_query);

// Fetch all routes for dropdown
$routes_res = mysqli_query($conn, "SELECT RouteID, RouteName FROM route ORDER BY RouteName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Stop Management - University Bus Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="admin.php" class="brand">
            <div class="brand-icon">🚌</div>
            <span>UniBus Admin</span>
        </a>
        <ul class="nav-links">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="bus.php">Buses</a></li>
            <li><a href="driver.php">Drivers</a></li>
            <li><a href="route.php">Routes</a></li>
            <li><a href="busstop.php" class="active">Stops</a></li>
            <li><a href="busschedule.php">Schedules</a></li>
            <li><a href="semester.php">Semesters</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Bus Stop Management</h1>
                <p>Register official pick-up and drop-off bus stops and link them to routes</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="grid-layout">
            <!-- Form Card -->
            <div class="card">
                <div class="card-title"><?php echo $edit_stop ? 'Edit Bus Stop' : 'Add New Bus Stop'; ?></div>
                <form method="POST" action="busstop.php">
                    <?php if ($edit_stop): ?>
                        <input type="hidden" name="StopID" value="<?php echo $edit_stop['StopID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Stop Name *</label>
                        <input type="text" name="StopName" class="form-control" placeholder="e.g. Science Building Stop" required value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['StopName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Assign to Route</label>
                        <select name="RouteID" class="form-control">
                            <option value="">-- Optional: Select Route --</option>
                            <?php 
                            mysqli_data_seek($routes_res, 0);
                            while ($r = mysqli_fetch_assoc($routes_res)): 
                            ?>
                                <option value="<?php echo $r['RouteID']; ?>" <?php echo ($edit_stop && $edit_stop['RouteID'] == $r['RouteID']) ? 'selected' : ''; ?>>
                                    🗺️ <?php echo htmlspecialchars($r['RouteName']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Location Details / Address</label>
                        <textarea name="Location" class="form-control" rows="3" placeholder="e.g. Corner of North Road & 5th Avenue"><?php echo $edit_stop ? htmlspecialchars($edit_stop['Location']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="save_stop" class="btn btn-primary btn-block">
                        <?php echo $edit_stop ? 'Update Stop' : 'Add Stop'; ?>
                    </button>

                    <?php if ($edit_stop): ?>
                        <a href="busstop.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bus Stop Table Card -->
            <div class="card">
                <div class="card-title">Registered Bus Stops</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Stop Name</th>
                                <th>Assigned Route</th>
                                <th>Location Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($stops_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($stops_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['StopID']; ?></td>
                                        <td><strong>📍 <?php echo htmlspecialchars($row['StopName']); ?></strong></td>
                                        <td>
                                            <?php if ($row['RouteName']): ?>
                                                <span class="badge badge-student">🗺️ <?php echo htmlspecialchars($row['RouteName']); ?></span>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted);">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['Location'] ?: 'N/A'); ?></td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="busstop.php?edit=<?php echo $row['StopID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="busstop.php?delete=<?php echo $row['StopID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this bus stop?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No bus stops registered yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
