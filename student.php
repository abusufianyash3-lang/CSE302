<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Student") {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";
$error = "";

// Fetch Student Profile
$std_query = "SELECT student.*, busstop.StopName, busstop.Location AS StopLocation 
              FROM student 
              LEFT JOIN busstop ON student.StopID = busstop.StopID 
              WHERE student.Email = '$email'";
$std_res = mysqli_query($conn, $std_query);
$student = mysqli_fetch_assoc($std_res);

// Handle Stop Assignment Update
if (isset($_POST['update_stop'])) {
    $stopID = intval($_POST['StopID']);
    $stdID = $student['StudentID'];
    $up_sql = "UPDATE student SET StopID=$stopID WHERE StudentID='$stdID'";
    if (mysqli_query($conn, $up_sql)) {
        $message = "Preferred bus stop updated successfully!";
        // Refresh student data
        $std_res = mysqli_query($conn, $std_query);
        $student = mysqli_fetch_assoc($std_res);
    } else {
        $error = "Error updating stop: " . mysqli_error($conn);
    }
}

// Fetch Student Bus Schedules
$schedules_query = "SELECT busschedule.*, bus.BusNumber, bus.Capacity, route.RouteName, route.StartLocation, route.EndLocation, driver.FullName AS DriverName, driver.Phone AS DriverPhone 
                    FROM busschedule 
                    JOIN bus ON busschedule.BusID = bus.BusID 
                    JOIN route ON busschedule.RouteID = route.RouteID 
                    LEFT JOIN driver ON bus.DriverID = driver.DriverID 
                    WHERE bus.BusType = 'Student' 
                    ORDER BY busschedule.ScheduleID DESC";
$schedules_res = mysqli_query($conn, $schedules_query);

// Fetch Bus Stops for Student Only (Filtered by Student Routes)
$all_stops_res = mysqli_query($conn, "SELECT DISTINCT busstop.*, route.RouteName 
                                      FROM busstop 
                                      LEFT JOIN route ON busstop.RouteID = route.RouteID 
                                      LEFT JOIN busschedule ON route.RouteID = busschedule.RouteID 
                                      LEFT JOIN bus ON busschedule.BusID = bus.BusID 
                                      WHERE bus.BusType = 'Student' 
                                         OR (busstop.RouteID IS NOT NULL AND NOT EXISTS (
                                             SELECT 1 FROM busschedule s2 JOIN bus b2 ON s2.BusID = b2.BusID WHERE s2.RouteID = busstop.RouteID AND b2.BusType = 'Faculty'
                                         ))
                                      ORDER BY busstop.StopName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - University Bus Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
        <a href="student.php" class="brand">
            <div class="brand-icon">🎓</div>
            <span>Student Bus Portal</span>
        </a>
        <ul class="nav-links">
            <li><a href="student.php" class="active">My Dashboard</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Student Portal</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($student['FullName']); ?></strong> (<?php echo htmlspecialchars($student['StudentID']); ?>)</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Student Profile Card & Stop Selector -->
        <div class="grid-layout" style="margin-bottom:2.5rem;">
            <div class="card">
                <div class="card-title">My Profile Details</div>
                <p style="margin-bottom:0.5rem;"><strong>Department:</strong> <?php echo htmlspecialchars($student['Department']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Academic Semester:</strong> Semester <?php echo htmlspecialchars($student['AcademicSemester']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($student['Email']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($student['Phone']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong>Current Bus Stop:</strong> 📍 <?php echo htmlspecialchars($student['StopName'] ?: 'Not Selected'); ?></p>
            </div>

            <div class="card">
                <div class="card-title">Select Preferred Bus Stop (Student Routes)</div>
                <form method="POST" action="student.php">
                    <div class="form-group">
                        <label>Choose Pickup / Dropoff Stop</label>
                        <select name="StopID" class="form-control" required>
                            <option value="">-- Choose Bus Stop --</option>
                            <?php 
                            mysqli_data_seek($all_stops_res, 0);
                            while ($st = mysqli_fetch_assoc($all_stops_res)): 
                            ?>
                                <option value="<?php echo $st['StopID']; ?>" <?php echo ($student['StopID'] == $st['StopID']) ? 'selected' : ''; ?>>
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

        <!-- Student Bus Schedules -->
        <div class="card">
            <div class="card-title">Student Bus Schedules</div>
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
                        <?php if (mysqli_num_rows($schedules_res) > 0): ?>
                            <?php while ($sch = mysqli_fetch_assoc($schedules_res)): ?>
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
                                <td colspan="6" style="text-align:center; color:var(--text-muted);">No student bus schedules available at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
