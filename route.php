<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_route = null;

// Handle Delete Route
if (isset($_GET['delete'])) {
    $routeID = intval($_GET['delete']);
    // Check if route is linked to schedules
    $check_sch = mysqli_query($conn, "SELECT ScheduleID FROM busschedule WHERE RouteID=$routeID");
    if (mysqli_num_rows($check_sch) > 0) {
        $error = "Cannot delete route! It is linked to active bus schedules.";
    } else {
        $del_sql = "DELETE FROM route WHERE RouteID=$routeID";
        if (mysqli_query($conn, $del_sql)) {
            $message = "Route deleted successfully.";
        } else {
            $error = "Error deleting route: " . mysqli_error($conn);
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM route WHERE RouteID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_route = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_route'])) {
    $routeName = mysqli_real_escape_string($conn, trim($_POST['RouteName']));
    $startLoc = mysqli_real_escape_string($conn, trim($_POST['StartLocation']));
    $endLoc = mysqli_real_escape_string($conn, trim($_POST['EndLocation']));
    $distance = !empty($_POST['Distance']) ? floatval($_POST['Distance']) : "NULL";
    $duration = !empty($_POST['EstimatedDuration']) ? intval($_POST['EstimatedDuration']) : "NULL";
    $routeID = isset($_POST['RouteID']) ? intval($_POST['RouteID']) : 0;

    if ($routeID > 0) {
        // Update
        $sql = "UPDATE route SET RouteName='$routeName', StartLocation='$startLoc', EndLocation='$endLoc', Distance=$distance, EstimatedDuration=$duration WHERE RouteID=$routeID";
        if (mysqli_query($conn, $sql)) {
            $message = "Route updated successfully.";
            header("Location: route.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating route: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO route (RouteName, StartLocation, EndLocation, Distance, EstimatedDuration) VALUES ('$routeName', '$startLoc', '$endLoc', $distance, $duration)";
        if (mysqli_query($conn, $sql)) {
            $message = "Route created successfully.";
            header("Location: route.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error creating route: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all routes
$routes_query = "SELECT * FROM route ORDER BY RouteID DESC";
$routes_result = mysqli_query($conn, $routes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Management - University Bus Transport</title>
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
            <li><a href="route.php" class="active">Routes</a></li>
            <li><a href="busstop.php">Stops</a></li>
            <li><a href="busschedule.php">Schedules</a></li>
            <li><a href="semester.php">Semesters</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Route Management</h1>
                <p>Create and manage campus transit routes and travel times</p>
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
                <div class="card-title"><?php echo $edit_route ? 'Edit Route' : 'Add New Route'; ?></div>
                <form method="POST" action="route.php">
                    <?php if ($edit_route): ?>
                        <input type="hidden" name="RouteID" value="<?php echo $edit_route['RouteID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Route Name *</label>
                        <input type="text" name="RouteName" class="form-control" placeholder="e.g. Main Campus - Downtown Express" required value="<?php echo $edit_route ? htmlspecialchars($edit_route['RouteName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Start Location *</label>
                        <input type="text" name="StartLocation" class="form-control" placeholder="e.g. University Gate 1" required value="<?php echo $edit_route ? htmlspecialchars($edit_route['StartLocation']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>End Location *</label>
                        <input type="text" name="EndLocation" class="form-control" placeholder="e.g. Central Station" required value="<?php echo $edit_route ? htmlspecialchars($edit_route['EndLocation']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Distance (km)</label>
                        <input type="number" step="0.01" name="Distance" class="form-control" placeholder="e.g. 14.5" value="<?php echo $edit_route ? htmlspecialchars($edit_route['Distance']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Estimated Duration (Minutes)</label>
                        <input type="number" name="EstimatedDuration" class="form-control" placeholder="e.g. 45" value="<?php echo $edit_route ? htmlspecialchars($edit_route['EstimatedDuration']) : ''; ?>">
                    </div>

                    <button type="submit" name="save_route" class="btn btn-primary btn-block">
                        <?php echo $edit_route ? 'Update Route' : 'Create Route'; ?>
                    </button>

                    <?php if ($edit_route): ?>
                        <a href="route.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Route List Table -->
            <div class="card">
                <div class="card-title">Active Routes</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Route Name</th>
                                <th>Start Location</th>
                                <th>End Location</th>
                                <th>Distance</th>
                                <th>Est. Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($routes_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($routes_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['RouteID']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['RouteName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['StartLocation']); ?></td>
                                        <td><?php echo htmlspecialchars($row['EndLocation']); ?></td>
                                        <td><?php echo $row['Distance'] ? htmlspecialchars($row['Distance']) . ' km' : 'N/A'; ?></td>
                                        <td><?php echo $row['EstimatedDuration'] ? htmlspecialchars($row['EstimatedDuration']) . ' mins' : 'N/A'; ?></td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="route.php?edit=<?php echo $row['RouteID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="route.php?delete=<?php echo $row['RouteID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this route?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted);">No routes created yet.</td>
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
