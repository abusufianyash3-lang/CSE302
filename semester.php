<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_semester = null;

// Handle Delete Semester
if (isset($_GET['delete'])) {
    $semID = intval($_GET['delete']);
    $del_sql = "DELETE FROM semester WHERE SemesterID=$semID";
    if (mysqli_query($conn, $del_sql)) {
        $message = "Semester deleted successfully.";
    } else {
        $error = "Error deleting semester: " . mysqli_error($conn);
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    $fetch_sql = "SELECT * FROM semester WHERE SemesterID=$editID";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_semester = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_semester'])) {
    $semName = mysqli_real_escape_string($conn, trim($_POST['SemesterName']));
    $duration = !empty($_POST['DurationDays']) ? intval($_POST['DurationDays']) : 90;
    $credits = !empty($_POST['TotalCredits']) ? intval($_POST['TotalCredits']) : 90;
    $semID = isset($_POST['SemesterID']) ? intval($_POST['SemesterID']) : 0;

    if ($semID > 0) {
        // Update
        $sql = "UPDATE semester SET SemesterName='$semName', DurationDays=$duration, TotalCredits=$credits WHERE SemesterID=$semID";
        if (mysqli_query($conn, $sql)) {
            $message = "Semester updated successfully.";
            header("Location: semester.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating semester: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $sql = "INSERT INTO semester (SemesterName, DurationDays, TotalCredits) VALUES ('$semName', $duration, $credits)";
        if (mysqli_query($conn, $sql)) {
            $message = "Semester added successfully.";
            header("Location: semester.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding semester: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all semesters
$semesters_query = "SELECT * FROM semester ORDER BY SemesterID DESC";
$semesters_result = mysqli_query($conn, $semesters_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Management - University Bus Transport</title>
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
            <li><a href="busschedule.php">Schedules</a></li>
            <li><a href="semester.php" class="active">Semesters</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Semester Management</h1>
                <p>Configure academic semesters and validity periods for bus passes</p>
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
                <div class="card-title"><?php echo $edit_semester ? 'Edit Semester' : 'Add New Semester'; ?></div>
                <form method="POST" action="semester.php">
                    <?php if ($edit_semester): ?>
                        <input type="hidden" name="SemesterID" value="<?php echo $edit_semester['SemesterID']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Semester Name *</label>
                        <input type="text" name="SemesterName" class="form-control" placeholder="e.g. Spring 2026" required value="<?php echo $edit_semester ? htmlspecialchars($edit_semester['SemesterName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Duration (Days)</label>
                        <input type="number" name="DurationDays" class="form-control" value="<?php echo $edit_semester ? htmlspecialchars($edit_semester['DurationDays']) : '90'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Total Credits</label>
                        <input type="number" name="TotalCredits" class="form-control" value="<?php echo $edit_semester ? htmlspecialchars($edit_semester['TotalCredits']) : '90'; ?>">
                    </div>

                    <button type="submit" name="save_semester" class="btn btn-primary btn-block">
                        <?php echo $edit_semester ? 'Update Semester' : 'Add Semester'; ?>
                    </button>

                    <?php if ($edit_semester): ?>
                        <a href="semester.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Semesters Table Card -->
            <div class="card">
                <div class="card-title">Academic Semesters</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Semester Name</th>
                                <th>Duration (Days)</th>
                                <th>Total Credits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($semesters_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($semesters_result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['SemesterID']; ?></td>
                                        <td><strong>📅 <?php echo htmlspecialchars($row['SemesterName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['DurationDays']); ?> Days</td>
                                        <td><?php echo htmlspecialchars($row['TotalCredits']); ?> Credits</td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="semester.php?edit=<?php echo $row['SemesterID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="semester.php?delete=<?php echo $row['SemesterID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this semester?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No semesters added yet.</td>
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
