<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_driver = null;

// Handle Delete Driver
if (isset($_GET['delete'])) {
    $driverID = intval($_GET['delete']);
    // Check if driver is assigned to any bus
    $check_bus = mysqli_query($conn, "SELECT BusNumber FROM bus WHERE DriverID=$driverID");
    if (mysqli_num_rows($check_bus) > 0) {
        $error = "Cannot delete driver! Driver is currently assigned to one or more buses.";
    } else {
        $del_sql = "DELETE FROM driver WHERE DriverID=$driverID";
        if (mysqli_query($conn, $del_sql)) {
            $message = "Driver deleted successfully.";
        } else {
            $error = "Error deleting driver: " . mysqli_error($conn);
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM driver WHERE DriverID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_driver = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_driver'])) {
    $fullName = mysqli_real_escape_string($conn, trim($_POST['FullName']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['Phone']));
    $license = mysqli_real_escape_string($conn, trim($_POST['LicenseNumber']));
    $address = mysqli_real_escape_string($conn, trim($_POST['Address']));
    $driverID = isset($_POST['DriverID']) ? intval($_POST['DriverID']) : 0;

    if ($driverID > 0) {
        // Update
        $sql = "UPDATE driver SET FullName='$fullName', Phone='$phone', LicenseNumber='$license', Address='$address' WHERE DriverID=$driverID";
        if (mysqli_query($conn, $sql)) {
            $message = "Driver updated successfully.";
            header("Location: driver.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating driver: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO driver (FullName, Phone, LicenseNumber, Address) VALUES ('$fullName', '$phone', '$license', '$address')";
        if (mysqli_query($conn, $sql)) {
            $message = "Driver added successfully.";
            header("Location: driver.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding driver: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all drivers
$drivers_query = "SELECT * FROM driver ORDER BY DriverID DESC";
$drivers_result = mysqli_query($conn, $drivers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Management - University Bus Transport</title>
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
            <li><a href="driver.php" class="active">Drivers</a></li>
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
                <h1>Driver Management</h1>
                <p>Register, view, and update university bus driver details</p>
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
                <div class="card-title"><?php echo $edit_driver ? 'Edit Driver Details' : 'Add New Driver'; ?></div>
                <form method="POST" action="driver.php">
                    <?php if ($edit_driver): ?>
                        <input type="hidden" name="DriverID" value="<?php echo $edit_driver['DriverID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="FullName" class="form-control" required value="<?php echo $edit_driver ? htmlspecialchars($edit_driver['FullName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="Phone" class="form-control" required value="<?php echo $edit_driver ? htmlspecialchars($edit_driver['Phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>License Number *</label>
                        <input type="text" name="LicenseNumber" class="form-control" required value="<?php echo $edit_driver ? htmlspecialchars($edit_driver['LicenseNumber']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="Address" class="form-control" rows="3"><?php echo $edit_driver ? htmlspecialchars($edit_driver['Address']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="save_driver" class="btn btn-primary btn-block">
                        <?php echo $edit_driver ? 'Update Driver' : 'Add Driver'; ?>
                    </button>

                    <?php if ($edit_driver): ?>
                        <a href="driver.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Drivers Table Card -->
            <div class="card">
                <div class="card-title">Driver Records</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>License No.</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($drivers_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($drivers_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['DriverID']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['FullName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                        <td><code><?php echo htmlspecialchars($row['LicenseNumber']); ?></code></td>
                                        <td><?php echo htmlspecialchars($row['Address'] ?: 'N/A'); ?></td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="driver.php?edit=<?php echo $row['DriverID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="driver.php?delete=<?php echo $row['DriverID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this driver?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No drivers registered yet.</td>
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
