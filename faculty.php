<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Faculty") {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";
$error = "";

// Fetch Faculty Profile
$fac_query = "SELECT faculty.*, busstop.StopName, busstop.Location AS StopLocation 
              FROM faculty 
              LEFT JOIN busstop ON faculty.StopID = busstop.StopID 
              WHERE faculty.Email = '$email'";
$fac_res = mysqli_query($conn, $fac_query);
$faculty = mysqli_fetch_assoc($fac_res);

// Handle Stop Assignment Update
if (isset($_POST['update_stop'])) {
    $stopID = intval($_POST['StopID']);
    $facID = $faculty['FacultyID'];
    $up_sql = "UPDATE faculty SET StopID=$stopID WHERE FacultyID='$facID'";
    if (mysqli_query($conn, $up_sql)) {
        $message = "Preferred bus stop updated successfully!";
        // Refresh faculty data
        $fac_res = mysqli_query($conn, $fac_query);
        $faculty = mysqli_fetch_assoc($fac_res);
    } else {
        $error = "Error updating stop: " . mysqli_error($conn);
    }
}

// Fetch Faculty Bus Schedules
$fac_schedules_query = "SELECT busschedule.*, bus.BusNumber, bus.Capacity, route.RouteName, route.StartLocation, route.EndLocation, driver.FullName AS DriverName, driver.Phone AS DriverPhone 
                        FROM busschedule 
                        JOIN bus ON busschedule.BusID = bus.BusID 
                        JOIN route ON busschedule.RouteID = route.RouteID 
                        LEFT JOIN driver ON bus.DriverID = driver.DriverID 
                        WHERE bus.BusType = 'Faculty' 
                        ORDER BY busschedule.ScheduleID DESC";
$fac_schedules_res = mysqli_query($conn, $fac_schedules_query);

// Fetch Student Bus Schedules (Faculty View)
$std_schedules_query = "SELECT busschedule.*, bus.BusNumber, bus.Capacity, route.RouteName, route.StartLocation, route.EndLocation, driver.FullName AS DriverName, driver.Phone AS DriverPhone 
                        FROM busschedule 
                        JOIN bus ON busschedule.BusID = bus.BusID 
                        JOIN route ON busschedule.RouteID = route.RouteID 
                        LEFT JOIN driver ON bus.DriverID = driver.DriverID 
                        WHERE bus.BusType = 'Student' 
                        ORDER BY busschedule.ScheduleID DESC";
$std_schedules_res = mysqli_query($conn, $std_schedules_query);

// Fetch Students in Faculty's Department
$dept = mysqli_real_escape_string($conn, $faculty['Department']);
$dept_students_query = "SELECT student.*, busstop.StopName FROM student LEFT JOIN busstop ON student.StopID = busstop.StopID WHERE student.Department = '$dept' ORDER BY StudentID DESC";
$dept_students_res = mysqli_query($conn, $dept_students_query);

// Fetch Bus Stops for Faculty Only (Filtered by Faculty Routes)
$all_stops_res = mysqli_query($conn, "SELECT DISTINCT busstop.*, route.RouteName 
                                      FROM busstop 
                                      LEFT JOIN route ON busstop.RouteID = route.RouteID 
                                      LEFT JOIN busschedule ON route.RouteID = busschedule.RouteID 
                                      LEFT JOIN bus ON busschedule.BusID = bus.BusID 
                                      WHERE bus.BusType = 'Faculty' 
                                         OR (busstop.RouteID IS NOT NULL AND NOT EXISTS (
                                             SELECT 1 FROM busschedule s2 JOIN bus b2 ON s2.BusID = b2.BusID WHERE s2.RouteID = busstop.RouteID AND b2.BusType = 'Student'
                                         ))
                                      ORDER BY busstop.StopName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal - University Bus Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="faculty.php" class="brand">
            <div class="brand-icon">👨‍🏫</div>
            <span>Faculty Portal</span>
        </a>
        <ul class="nav-links">
            <li><a href="faculty.php" class="active">Dashboard</a></li>
            <li><a href="#fac-schedules">Faculty Schedules</a></li>
            <li><a href="#std-schedules">Student Schedules</a></li>
            <li><a href="#std-directory">Student Directory</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Faculty Portal</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($faculty['FullName']); ?></strong> (<?php echo htmlspecialchars($faculty['FacultyID']); ?>)</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Faculty Profile Card & Stop Selector -->
        <div class="grid-layout" style="margin-bottom:2.5rem;">
            <div class="card">
                <div class="card-title">My Faculty Profile</div>
                <p style="margin-bottom:0.5rem;"><strong>Department:</strong> <?php echo htmlspecialchars($faculty['Department']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($faculty['Email']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($faculty['Phone']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Current Bus Stop:</strong> 📍 <?php echo htmlspecialchars($faculty['StopName'] ?: 'Not Selected'); ?></p>
            </div>

            <div class="card">
                <div class="card-title">Select Preferred Bus Stop (Faculty Routes)</div>
                <form method="POST" action="faculty.php">
                    <div class="form-group">
                        <label>Choose Pickup / Dropoff Stop</label>
                        <select name="StopID" class="form-control" required>
                            <option value="">-- Choose Bus Stop --</option>
                            <?php 
                            mysqli_data_seek($all_stops_res, 0);
                            while ($st = mysqli_fetch_assoc($all_stops_res)): 
                            ?>
                                <option value="<?php echo $st['StopID']; ?>" <?php echo ($faculty['StopID'] == $st['StopID']) ? 'selected' : ''; ?>>
                                    📍 <?php echo htmlspecialchars($st['StopName']); ?>
                                    <?php echo $st['RouteName'] ? " (Route: " . htmlspecialchars($st['RouteName']) . ")" : ""; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_stop" class="btn btn-primary">Save Preferred Bus Stop</button>
                </form>
            </div>
        </div>

        <!-- Faculty Bus Schedules -->
        <div class="card" id="fac-schedules" style="margin-bottom:2.5rem;">
            <div class="card-title">👨‍🏫 Faculty Bus Schedules</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bus Number</th>
                            <th>Route</th>
                            <th>University Departure</th>
                            <th>Return Departure</th>
                            <th>Capacity</th>
                            <th>Driver Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($fac_schedules_res) > 0): ?>
                            <?php while ($sch = mysqli_fetch_assoc($fac_schedules_res)): ?>
                                <tr>
                                    <td><strong>🚌 <?php echo htmlspecialchars($sch['BusNumber']); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sch['RouteName']); ?></strong><br>
                                        <small style="color:var(--text-muted);"><?php echo htmlspecialchars($sch['StartLocation']) . " ➔ " . htmlspecialchars($sch['EndLocation']); ?></small>
                                    </td>
                                    <td>⏰ <?php echo htmlspecialchars($sch['UniversityStartTime']); ?></td>
                                    <td>⏰ <?php echo htmlspecialchars($sch['LastStopStartTime']); ?></td>
                                    <td><?php echo htmlspecialchars($sch['Capacity']); ?> seats</td>
                                    <td>
                                        <?php if ($sch['DriverName']): ?>
                                            👨‍✈️ <?php echo htmlspecialchars($sch['DriverName']); ?><br>
                                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($sch['DriverPhone']); ?></small>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--text-muted);">No faculty bus schedules available at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Bus Schedules View -->
        <div class="card" id="std-schedules" style="margin-bottom:2.5rem;">
            <div class="card-title">🎓 Student Bus Schedules (Overview for Faculty)</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bus Number</th>
                            <th>Route</th>
                            <th>University Departure</th>
                            <th>Return Departure</th>
                            <th>Capacity</th>
                            <th>Driver Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($std_schedules_res) > 0): ?>
                            <?php while ($sch = mysqli_fetch_assoc($std_schedules_res)): ?>
                                <tr>
                                    <td><strong>🚌 <?php echo htmlspecialchars($sch['BusNumber']); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sch['RouteName']); ?></strong><br>
                                        <small style="color:var(--text-muted);"><?php echo htmlspecialchars($sch['StartLocation']) . " ➔ " . htmlspecialchars($sch['EndLocation']); ?></small>
                                    </td>
                                    <td>⏰ <?php echo htmlspecialchars($sch['UniversityStartTime']); ?></td>
                                    <td>⏰ <?php echo htmlspecialchars($sch['LastStopStartTime']); ?></td>
                                    <td><?php echo htmlspecialchars($sch['Capacity']); ?> seats</td>
                                    <td>
                                        <?php if ($sch['DriverName']): ?>
                                            👨‍✈️ <?php echo htmlspecialchars($sch['DriverName']); ?><br>
                                            <small style="color:var(--text-muted);"><?php echo htmlspecialchars($sch['DriverPhone']); ?></small>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--text-muted);">No student bus schedules available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Students in Faculty Department -->
        <div class="card" id="std-directory">
            <div class="card-title">📚 Students in My Department (<?php echo htmlspecialchars($faculty['Department']); ?>)</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Academic Semester</th>
                            <th>Email</th>
                            <th>Assigned Bus Stop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($dept_students_res) > 0): ?>
                            <?php while ($st = mysqli_fetch_assoc($dept_students_res)): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($st['StudentID']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($st['FullName']); ?></strong></td>
                                    <td>Semester <?php echo htmlspecialchars($st['AcademicSemester']); ?></td>
                                    <td><?php echo htmlspecialchars($st['Email']); ?></td>
                                    <td>📍 <?php echo htmlspecialchars($st['StopName'] ?: 'Unassigned'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--text-muted);">No students registered in this department yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
