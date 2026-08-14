<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
$edit_student = null;

// Handle Delete Student
if (isset($_GET['delete'])) {
    $stdID = mysqli_real_escape_string($conn, $_GET['delete']);
    $del_sql = "DELETE FROM student WHERE StudentID='$stdID'";
    if (mysqli_query($conn, $del_sql)) {
        $message = "Student deleted successfully.";
    } else {
        $error = "Error deleting student: " . mysqli_error($conn);
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editID = mysqli_real_escape_string($conn, $_GET['edit']);
    $fetch_sql = "SELECT * FROM student WHERE StudentID='$editID'";
    $res = mysqli_query($conn, $fetch_sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_student = mysqli_fetch_assoc($res);
    }
}

// Handle Form Submission (Add / Update)
if (isset($_POST['save_student'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['FullName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['Password']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['Phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['Address']));
    $department = mysqli_real_escape_string($conn, trim($_POST['Department']));
    $semester = intval($_POST['AcademicSemester']);
    $stopID = !empty($_POST['StopID']) ? intval($_POST['StopID']) : "NULL";
    $studentID = isset($_POST['StudentID']) ? mysqli_real_escape_string($conn, $_POST['StudentID']) : "";

    if (!empty($studentID)) {
        // Update
        $sql = "UPDATE student SET FullName='$name', Email='$email', Password='$password', Phone='$phone', Address='$address', Department='$department', AcademicSemester=$semester, StopID=$stopID WHERE StudentID='$studentID'";
        if (mysqli_query($conn, $sql)) {
            $message = "Student record updated successfully.";
            header("Location: manage_students.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating student: " . mysqli_error($conn);
        }
    } else {
        // Generate Student ID
        $query = "SELECT StudentID FROM student ORDER BY StudentID DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $lastID = $row['StudentID'];
            $number = intval(substr($lastID, 2));
            $newNumber = $number + 1;
            $newStudentID = "ST" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
        } else {
            $newStudentID = "ST001";
        }

        $sql = "INSERT INTO student (StudentID, FullName, Email, Password, Phone, Address, Department, AcademicSemester, StopID)
                VALUES ('$newStudentID', '$name', '$email', '$password', '$phone', '$address', '$department', $semester, $stopID)";

        if (mysqli_query($conn, $sql)) {
            $message = "Student added successfully with ID: " . $newStudentID;
            header("Location: manage_students.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding student: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch all students with Bus Stop and Route info
$students_query = "SELECT student.*, busstop.StopName, route.RouteName 
                  FROM student 
                  LEFT JOIN busstop ON student.StopID = busstop.StopID 
                  LEFT JOIN route ON busstop.RouteID = route.RouteID 
                  ORDER BY student.StudentID DESC";
$students_result = mysqli_query($conn, $students_query);

// Fetch Bus Stops for dropdown
$stops_res = mysqli_query($conn, "SELECT busstop.*, route.RouteName FROM busstop LEFT JOIN route ON busstop.RouteID = route.RouteID ORDER BY busstop.StopName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - University Bus Transport</title>
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
            <li><a href="manage_students.php" class="active">Students</a></li>
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
                <h1>Student Management</h1>
                <p>Add, edit, and manage registered student accounts</p>
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
                <div class="card-title"><?php echo $edit_student ? 'Edit Student Details' : 'Add New Student'; ?></div>
                <form method="POST" action="manage_students.php">
                    <?php if ($edit_student): ?>
                        <input type="hidden" name="StudentID" value="<?php echo htmlspecialchars($edit_student['StudentID']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="FullName" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['FullName']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="Email" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['Email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Password *</label>
                        <input type="text" name="Password" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['Password']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="Phone" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['Phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" name="Department" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['Department']) : 'Computer Science'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Academic Semester *</label>
                        <input type="number" name="AcademicSemester" class="form-control" min="1" max="12" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['AcademicSemester']) : '1'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Preferred Bus Stop</label>
                        <select name="StopID" class="form-control">
                            <option value="">-- Select Bus Stop --</option>
                            <?php 
                            mysqli_data_seek($stops_res, 0);
                            while ($st = mysqli_fetch_assoc($stops_res)): 
                            ?>
                                <option value="<?php echo $st['StopID']; ?>" <?php echo ($edit_student && $edit_student['StopID'] == $st['StopID']) ? 'selected' : ''; ?>>
                                    📍 <?php echo htmlspecialchars($st['StopName']); ?>
                                    <?php echo $st['RouteName'] ? " (Route: " . htmlspecialchars($st['RouteName']) . ")" : ""; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="Address" class="form-control" rows="2"><?php echo $edit_student ? htmlspecialchars($edit_student['Address']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="save_student" class="btn btn-primary btn-block">
                        <?php echo $edit_student ? 'Update Student' : 'Add Student'; ?>
                    </button>

                    <?php if ($edit_student): ?>
                        <a href="manage_students.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Student List Table -->
            <div class="card">
                <div class="card-title">Student Records</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Dept</th>
                                <th>Sem</th>
                                <th>Bus Stop</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($students_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($students_result)): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($row['StudentID']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($row['FullName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Department']); ?></td>
                                        <td>Sem <?php echo htmlspecialchars($row['AcademicSemester']); ?></td>
                                        <td>
                                            <?php if ($row['StopName']): ?>
                                                📍 <?php echo htmlspecialchars($row['StopName']); ?>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted);">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="display:flex; gap:0.5rem;">
                                            <a href="manage_students.php?edit=<?php echo urlencode($row['StudentID']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <a href="manage_students.php?delete=<?php echo urlencode($row['StudentID']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student account?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted);">No student accounts registered yet.</td>
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
