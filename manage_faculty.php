<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_faculty = null;

// Handle Delete Faculty
if (isset($_GET['delete'])) {
    $facID = mysqli_real_escape_string($conn, $_GET['delete']);
    $del_sql = "DELETE FROM faculty WHERE FacultyID='$facID'";
    if (mysqli_query($conn, $del_sql)) {
        $message = "Faculty deleted successfully.";
    } else {
        $error = "Error deleting faculty: " . mysqli_error($conn);
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = mysqli_real_escape_string($conn, $_GET['edit']);
    $fetch_sql = "SELECT * FROM faculty WHERE FacultyID='$editID'";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_faculty = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_faculty'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['FullName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['Password']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['Phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['Address']));
    $department = mysqli_real_escape_string($conn, trim($_POST['Department']));
    $stopID = !empty($_POST['StopID']) ? intval($_POST['StopID']) : "NULL";
    $facultyID = isset($_POST['FacultyID']) ? mysqli_real_escape_string($conn, $_POST['FacultyID']) : "";

    if (!empty($facultyID)) {
        // Update
        $sql = "UPDATE faculty SET FullName='$name', Email='$email', Password='$password', Phone='$phone', Address='$address', Department='$department', StopID=$stopID WHERE FacultyID='$facultyID'";
        if (mysqli_query($conn, $sql)) {
            $message = "Faculty record updated successfully.";
            header("Location: manage_faculty.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating faculty: " . mysqli_error($conn);
        }
    } else {
        // Generate Faculty ID
        $query = "SELECT FacultyID FROM faculty ORDER BY FacultyID DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $lastID = $row['FacultyID'];
            $number = intval(substr($lastID, 3));
            $newNumber = $number + 1;
            $newFacultyID = "FAC" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
        } else {
            $newFacultyID = "FAC001";
        }

        $sql = "INSERT INTO faculty (FacultyID, FullName, Email, Password, Phone, Address, Department, StopID)
                VALUES ('$newFacultyID', '$name', '$email', '$password', '$phone', '$address', '$department', $stopID)";

        if (mysqli_query($conn, $sql)) {
            $message = "Faculty added successfully with ID: " . $newFacultyID;
            header("Location: manage_faculty.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding faculty: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all faculty with Bus Stop info
$faculty_query = "SELECT faculty.*, busstop.StopName, route.RouteName 
                 FROM faculty 
                 LEFT JOIN busstop ON faculty.StopID = busstop.StopID 
                 LEFT JOIN route ON busstop.RouteID = route.RouteID 
                 ORDER BY faculty.FacultyID DESC";
$faculty_result = mysqli_query($conn, $faculty_query);

// Fetch Bus Stops for dropdown
$stops_res = mysqli_query($conn, "SELECT busstop.*, route.RouteName FROM busstop LEFT JOIN route ON busstop.RouteID = route.RouteID ORDER BY busstop.StopName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Management - University Bus Transport</title>
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
            <li><a href="manage_faculty.php" class="active">Faculty</a></li>
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
                <h1>Faculty Management</h1>
                <p>Add, edit, and manage registered faculty accounts</p>
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
                <div class="card-title"><?php echo $edit_faculty ? 'Edit Faculty Details' : 'Add New Faculty'; ?></div>
                <form method="POST" action="manage_faculty.php">
                    <?php if ($edit_faculty): ?>
                        <input type="hidden" name="FacultyID" value="<?php echo htmlspecialchars($edit_faculty['FacultyID']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="FullName" class="form-control" required value="<?php echo $edit_faculty ? htmlspecialchars($edit_faculty['FullName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="Email" class="form-control" required value="<?php echo $edit_faculty ? htmlspecialchars($edit_faculty['Email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Password *</label>
                        <input type="text" name="Password" class="form-control" required value="<?php echo $edit_faculty ? htmlspecialchars($edit_faculty['Password']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="Phone" class="form-control" required value="<?php echo $edit_faculty ? htmlspecialchars($edit_faculty['Phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" name="Department" class="form-control" required value="<?php echo $edit_faculty ? htmlspecialchars($edit_faculty['Department']) : 'Computer Science'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Preferred Bus Stop</label>
                        <select name="StopID" class="form-control">
                            <option value="">-- Select Bus Stop --</option>
                            <?php 
                            mysqli_data_seek($stops_res, 0);
                            while ($st = mysqli_fetch_assoc($stops_res)): 
                            ?>
                                <option value="<?php echo $st['StopID']; ?>" <?php echo ($edit_faculty && $edit_faculty['StopID'] == $st['StopID']) ? 'selected' : ''; ?>>
                                    📍 <?php echo htmlspecialchars($st['StopName']); ?>
                                    <?php echo $st['RouteName'] ? " (Route: " . htmlspecialchars($st['RouteName']) . ")" : ""; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="Address" class="form-control" rows="2"><?php echo $edit_faculty ? htmlspecialchars($edit_faculty['Address']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="save_faculty" class="btn btn-primary btn-block">
                        <?php echo $edit_faculty ? 'Update Faculty' : 'Add Faculty'; ?>
                    </button>

                    <?php if ($edit_faculty): ?>
                        <a href="manage_faculty.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Faculty List Table -->
            <div class="card">
                <div class="card-title">Faculty Records</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Faculty ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Dept</th>
                                <th>Bus Stop</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($faculty_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($faculty_result)): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($row['FacultyID']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($row['FullName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Department']); ?></td>
                                        <td>
                                            <?php if ($row['StopName']): ?>
                                                📍 <?php echo htmlspecialchars($row['StopName']); ?>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted);">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="manage_faculty.php?edit=<?php echo urlencode($row['FacultyID']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="manage_faculty.php?delete=<?php echo urlencode($row['FacultyID']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this faculty account?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No faculty accounts registered yet.</td>
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
