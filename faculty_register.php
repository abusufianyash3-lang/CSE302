<?php
include("db.php");

$message = "";
$error = "";

// Fetch Bus Stops for Faculty Only (Filtered by Faculty Routes)
$stops_res = mysqli_query($conn, "SELECT DISTINCT busstop.*, route.RouteName 
                                  FROM busstop 
                                  LEFT JOIN route ON busstop.RouteID = route.RouteID 
                                  LEFT JOIN busschedule ON route.RouteID = busschedule.RouteID 
                                  LEFT JOIN bus ON busschedule.BusID = bus.BusID 
                                  WHERE bus.BusType = 'Faculty' 
                                     OR (busstop.RouteID IS NOT NULL AND NOT EXISTS (
                                         SELECT 1 FROM busschedule s2 JOIN bus b2 ON s2.BusID = b2.BusID WHERE s2.RouteID = busstop.RouteID AND b2.BusType = 'Student'
                                     ))
                                  ORDER BY busstop.StopName ASC");

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['FullName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['Password']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['Phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['Address']));
    $department = mysqli_real_escape_string($conn, trim($_POST['Department']));
    $stopID = !empty($_POST['StopID']) ? intval($_POST['StopID']) : "NULL";

    // Check duplicate email
    $check_email = mysqli_query($conn, "SELECT FacultyID FROM faculty WHERE Email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $error = "This email address is already registered as Faculty!";
    } else {
        // Generate Faculty ID
        $query = "SELECT FacultyID FROM faculty ORDER BY FacultyID DESC LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $lastID = $row['FacultyID'];
            $number = intval(substr($lastID, 3));
            $newNumber = $number + 1;
            $facultyID = "FAC" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
        } else {
            $facultyID = "FAC001";
        }

        $sql = "INSERT INTO faculty (FacultyID, FullName, Email, Password, Phone, Address, Department, StopID)
                VALUES ('$facultyID', '$name', '$email', '$password', '$phone', '$address', '$department', $stopID)";

        if (mysqli_query($conn, $sql)) {
            $message = "Faculty Registration Successful! Your Faculty ID is: <strong>" . $facultyID . "</strong>. You can now <a href='login.php' style='color:#34d399;'>Login here</a>.";
        } else {
            $error = "Registration error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Registration - University Bus Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper" style="padding: 3rem 1.5rem;">
        <div class="auth-card" style="max-width: 550px;">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">👨‍🏫</div>
                <h2>Faculty Registration</h2>
                <p>Register for university faculty bus transport services</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="faculty_register.php">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="FullName" class="form-control" required placeholder="Prof. Robert Vance">
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="Email" class="form-control" required placeholder="faculty@university.edu">
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="Password" class="form-control" required placeholder="••••••••">
                </div>

                <div class="grid-layout" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0;">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="Phone" class="form-control" required placeholder="+1987654321">
                    </div>

                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" name="Department" class="form-control" required placeholder="Computer Science">
                    </div>
                </div>

                <div class="form-group">
                    <label>Preferred Bus Stop (Faculty Routes)</label>
                    <select name="StopID" class="form-control">
                        <option value="">-- Select Bus Stop --</option>
                        <?php while ($st = mysqli_fetch_assoc($stops_res)): ?>
                            <option value="<?php echo $st['StopID']; ?>">
                                📍 <?php echo htmlspecialchars($st['StopName']); ?>
                                <?php echo $st['RouteName'] ? " (Route: " . htmlspecialchars($st['RouteName']) . ")" : ""; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="Address" class="form-control" rows="2" placeholder="Office / Residential Address"></textarea>
                </div>

                <button type="submit" name="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
                    Complete Registration &rarr;
                </button>
            </form>

            <div class="auth-footer">
                <p>Already registered? <a href="login.php">Log In</a> | <a href="student_register.php">Student Register</a></p>
            </div>
        </div>
    </div>

</body>
</html>
