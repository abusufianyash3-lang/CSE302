<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_schedule = null;

// Handle Delete Schedule
if (isset($_GET['delete'])) {
    $scheduleID = intval($_GET['delete']);
    $del_sql = "DELETE FROM busschedule WHERE ScheduleID=$scheduleID";
    if (mysqli_query($conn, $del_sql)) {
        $message = "Schedule deleted successfully.";
    } else {
        $error = "Error deleting schedule: " . mysqli_error($conn);
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM busschedule WHERE ScheduleID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_schedule = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_schedule'])) {
    $busID = intval($_POST['BusID']);
    $routeID = intval($_POST['RouteID']);
    $univTime = mysqli_real_escape_string($conn, trim($_POST['UniversityStartTime']));
    $lastTime = mysqli_real_escape_string($conn, trim($_POST['LastStopStartTime']));
    $scheduleID = isset($_POST['ScheduleID']) ? intval($_POST['ScheduleID']) : 0;

    if ($scheduleID > 0) {
        // Update
        $sql = "UPDATE busschedule SET BusID=$busID, RouteID=$routeID, UniversityStartTime='$univTime', LastStopStartTime='$lastTime' WHERE ScheduleID=$scheduleID";
        if (mysqli_query($conn, $sql)) {
            $message = "Schedule updated successfully.";
            header("Location: busschedule.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating schedule: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO busschedule (BusID, RouteID, UniversityStartTime, LastStopStartTime) VALUES ($busID, $routeID, '$univTime', '$lastTime')";
        if (mysqli_query($conn, $sql)) {
            $message = "Schedule created successfully.";
            header("Location: busschedule.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error creating schedule: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all schedules with Bus and Route details
$schedules_query = "SELECT busschedule.*, bus.BusNumber, bus.BusType, route.RouteName, route.StartLocation, route.EndLocation 
                    FROM busschedule 
                    JOIN bus ON busschedule.BusID = bus.BusID 
                    JOIN route ON busschedule.RouteID = route.RouteID 
                    ORDER BY busschedule.ScheduleID DESC";
$schedules_result = mysqli_query($conn, $schedules_query);

// Fetch Buses dropdown options
$buses_option_res = mysqli_query($conn, "SELECT BusID, BusNumber, BusType FROM bus ORDER BY BusNumber ASC");

// Fetch Routes dropdown options
$routes_option_res = mysqli_query($conn, "SELECT RouteID, RouteName FROM route ORDER BY RouteName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - University Bus Transport</title>
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
            <li><a href="busstop.php">Stops</a></li>
            <li><a href="busschedule.php" class="active">Schedules</a></li>
            <li><a href="semester.php">Semesters</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Schedule Management</h1>
                <p>Assign buses to routes and specify departure timings (VARCHAR 12-Hour format)</p>
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
                <div class="card-title"><?php echo $edit_schedule ? 'Edit Schedule' : 'Create New Schedule'; ?></div>
                <form method="POST" action="busschedule.php">
                    <?php if ($edit_schedule): ?>
                        <input type="hidden" name="ScheduleID" value="<?php echo $edit_schedule['ScheduleID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Select Bus *</label>
                        <select name="BusID" class="form-control" required>
                            <option value="">-- Choose Bus --</option>
                            <?php 
                            mysqli_data_seek($buses_option_res, 0);
                            while ($b = mysqli_fetch_assoc($buses_option_res)): 
                            ?>
                                <option value="<?php echo $b['BusID']; ?>" <?php echo ($edit_schedule && $edit_schedule['BusID'] == $b['BusID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($b['BusNumber']) . " (" . htmlspecialchars($b['BusType']) . ")"; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Route *</label>
                        <select name="RouteID" class="form-control" required>
                            <option value="">-- Choose Route --</option>
                            <?php 
                            mysqli_data_seek($routes_option_res, 0);
                            while ($r = mysqli_fetch_assoc($routes_option_res)): 
                            ?>
                                <option value="<?php echo $r['RouteID']; ?>" <?php echo ($edit_schedule && $edit_schedule['RouteID'] == $r['RouteID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r['RouteName']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>University Start Time *</label>
                        <input type="text" name="UniversityStartTime" class="form-control" placeholder="e.g. 08:00 AM" required value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['UniversityStartTime']) : '08:00 AM'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Last Stop Start Time *</label>
                        <input type="text" name="LastStopStartTime" class="form-control" placeholder="e.g. 05:30 PM" required value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['LastStopStartTime']) : '05:30 PM'; ?>">
                    </div>

                    <button type="submit" name="save_schedule" class="btn btn-primary btn-block">
                        <?php echo $edit_schedule ? 'Update Schedule' : 'Create Schedule'; ?>
                    </button>

                    <?php if ($edit_schedule): ?>
                        <a href="busschedule.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Schedules Table Card -->
            <div class="card">
                <div class="card-title">Master Bus Schedules</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bus</th>
                                <th>Route</th>
                                <th>Univ Departure</th>
                                <th>Return Departure</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($schedules_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($schedules_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['ScheduleID']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['BusNumber']); ?></strong><br>
                                            <span class="badge <?php echo $row['BusType'] == 'Student' ? 'badge-student' : 'badge-faculty'; ?>">
                                                <?php echo htmlspecialchars($row['BusType']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['RouteName']); ?></strong><br>
                                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['StartLocation']) . " ➔ " . htmlspecialchars($row['EndLocation']); ?></small>
                                        </td>
                                        <td>⏰ <?php echo htmlspecialchars($row['UniversityStartTime']); ?></td>
                                        <td>⏰ <?php echo htmlspecialchars($row['LastStopStartTime']); ?></td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="busschedule.php?edit=<?php echo $row['ScheduleID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="busschedule.php?delete=<?php echo $row['ScheduleID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No schedules created yet.</td>
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
