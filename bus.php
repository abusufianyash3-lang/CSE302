<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_bus = null;

// Handle Delete Bus
if (isset($_GET['delete'])) {
    $busID = intval($_GET['delete']);
    // Check if bus is referenced in busschedule
    $check_schedule = mysqli_query($conn, "SELECT ScheduleID FROM busschedule WHERE BusID=$busID");
    if (mysqli_num_rows($check_schedule) > 0) {
        $error = "Cannot delete bus! It is linked to existing bus schedules.";
    } else {
        $del_sql = "DELETE FROM bus WHERE BusID=$busID";
        if (mysqli_query($conn, $del_sql)) {
            $message = "Bus deleted successfully.";
        } else {
            $error = "Error deleting bus: " . mysqli_error($conn);
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM bus WHERE BusID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_bus = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_bus'])) {
    $busNumber = mysqli_real_escape_string($conn, trim($_POST['BusNumber']));
    $busType = mysqli_real_escape_string($conn, trim($_POST['BusType']));
    $capacity = intval($_POST['Capacity']);
    $driverID = intval($_POST['DriverID']);
    $busID = isset($_POST['BusID']) ? intval($_POST['BusID']) : 0;

    if ($busID > 0) {
        // Update
        $sql = "UPDATE bus SET BusNumber='$busNumber', BusType='$busType', Capacity=$capacity, DriverID=$driverID WHERE BusID=$busID";
        if (mysqli_query($conn, $sql)) {
            $message = "Bus updated successfully.";
            header("Location: bus.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating bus: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO bus (BusNumber, BusType, Capacity, DriverID) VALUES ('$busNumber', '$busType', $capacity, $driverID)";
        if (mysqli_query($conn, $sql)) {
            $message = "Bus added successfully.";
            header("Location: bus.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding bus: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all buses with driver name
$buses_query = "SELECT bus.*, driver.FullName AS DriverName 
               FROM bus 
               LEFT JOIN driver ON bus.DriverID = driver.DriverID 
               ORDER BY bus.BusID DESC";
$buses_result = mysqli_query($conn, $buses_query);

// Fetch all drivers for form select dropdown
$drivers_option_query = "SELECT DriverID, FullName, LicenseNumber FROM driver ORDER BY FullName ASC";
$drivers_options = mysqli_query($conn, $drivers_option_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Management - University Bus Transport</title>
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
            <li><a href="manage_students.php">Students</a></li>
            <li><a href="manage_faculty.php">Faculty</a></li>
            <li><a href="bus.php" class="active">Buses</a></li>
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
                <h1>Bus Management</h1>
                <p>Add, assign drivers, and configure university bus fleet</p>
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
                <div class="card-title"><?php echo $edit_bus ? 'Edit Bus' : 'Add New Bus'; ?></div>
                <form method="POST" action="bus.php">
                    <?php if ($edit_bus): ?>
                        <input type="hidden" name="BusID" value="<?php echo $edit_bus['BusID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Bus Number / Plate *</label>
                        <input type="text" name="BusNumber" class="form-control" placeholder="e.g. BUS-101" required value="<?php echo $edit_bus ? htmlspecialchars($edit_bus['BusNumber']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Bus Type *</label>
                        <select name="BusType" class="form-control" required>
                            <option value="Student" <?php echo ($edit_bus && $edit_bus['BusType'] == 'Student') ? 'selected' : ''; ?>>Student Bus</option>
                            <option value="Faculty" <?php echo ($edit_bus && $edit_bus['BusType'] == 'Faculty') ? 'selected' : ''; ?>>Faculty Bus</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Seating Capacity *</label>
                        <input type="number" name="Capacity" class="form-control" min="1" max="150" required value="<?php echo $edit_bus ? htmlspecialchars($edit_bus['Capacity']) : '40'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Assigned Driver *</label>
                        <select name="DriverID" class="form-control" required>
                            <option value="">-- Select Driver --</option>
                            <?php 
                            mysqli_data_seek($drivers_options, 0);
                            while ($drv = mysqli_fetch_assoc($drivers_options)): 
                            ?>
                                <option value="<?php echo $drv['DriverID']; ?>" <?php echo ($edit_bus && $edit_bus['DriverID'] == $drv['DriverID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($drv['FullName']) . " (" . htmlspecialchars($drv['LicenseNumber']) . ")"; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <?php if (mysqli_num_rows($drivers_options) == 0): ?>
                            <p style="font-size:0.8rem; color:var(--warning); margin-top:0.3rem;">⚠️ No drivers registered yet. Please add a driver first in <a href="driver.php" style="color:var(--secondary);">Drivers</a>.</p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="save_bus" class="btn btn-primary btn-block">
                        <?php echo $edit_bus ? 'Update Bus' : 'Add Bus'; ?>
                    </button>

                    <?php if ($edit_bus): ?>
                        <a href="bus.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bus List Table -->
            <div class="card">
                <div class="card-title">University Fleet</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bus Number</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Driver</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($buses_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($buses_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['BusID']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['BusNumber']); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo $row['BusType'] == 'Student' ? 'badge-student' : 'badge-faculty'; ?>">
                                                <?php echo htmlspecialchars($row['BusType']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['Capacity']); ?> seats</td>
                                        <td><?php echo htmlspecialchars($row['DriverName'] ?: 'Unassigned'); ?></td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="bus.php?edit=<?php echo $row['BusID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="bus.php?delete=<?php echo $row['BusID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this bus?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No buses added yet.</td>
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
