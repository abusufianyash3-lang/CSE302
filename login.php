<?php
session_start();
include("db.php");

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role = $_POST['role'];

    if ($role == "Admin") {
        $sql = "SELECT * FROM admin WHERE Email='$email' AND Password='$password'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['email'] = $email;
            $_SESSION['role'] = "Admin";
            header("Location: admin.php");
            exit();
        } else {
            $message = "Invalid Admin Credentials!";
        }
    } elseif ($role == "Student") {
        $sql = "SELECT * FROM student WHERE Email='$email' AND Password='$password'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['email'] = $email;
            $_SESSION['role'] = "Student";
            header("Location: student.php");
            exit();
        } else {
            $message = "Invalid Student Credentials!";
        }
    } elseif ($role == "Faculty") {
        $sql = "SELECT * FROM faculty WHERE Email='$email' AND Password='$password'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['email'] = $email;
            $_SESSION['role'] = "Faculty";
            header("Location: faculty.php");
            exit();
        } else {
            $message = "Invalid Faculty Credentials!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Bus Transport System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">🚌</div>
                <h2>University Bus Portal</h2>
                <p>Sign in to access your transit dashboard</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="user@university.edu" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label>Login As</label>
                    <select name="role" class="form-control" required>
                        <option value="Admin">Admin</option>
                        <option value="Student" selected>Student</option>
                        <option value="Faculty">Faculty</option>
                    </select>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                    Sign In &rarr;
                </button>
            </form>

            <div class="auth-footer">
                <p>Register as: <a href="student_register.php">Student</a> | <a href="faculty_register.php">Faculty</a></p>
            </div>
        </div>
    </div>

</body>
</html>
