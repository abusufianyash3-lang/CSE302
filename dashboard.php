<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'Admin':
        header("Location: admin.php");
        break;
    case 'Student':
        header("Location: student.php");
        break;
    case 'Faculty':
        header("Location: faculty.php");
        break;
    default:
        header("Location: login.php");
        break;
}
exit();
?>
