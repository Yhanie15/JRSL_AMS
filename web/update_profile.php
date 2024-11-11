<?php
include 'database_connection.php';

$firstName = $_POST['firstName'];
$middleName = $_POST['middleName'];
$lastName = $_POST['lastName'];
$extName = $_POST['extName'];
$birthDate = $_POST['birthDate'];
$gender = $_POST['gender'];
$age = $_POST['age'];
$address = $_POST['address'];
$email = $_POST['email'];
$phoneNumber = $_POST['phoneNumber'];
$emergencyContactName = $_POST['emergencyContactName'];
$relationship = $_POST['relationship'];
$emergencyContactNumber = $_POST['emergencyContactNumber'];

// Handle file uploads (Profile Picture and Valid ID)
$profilePicture = $_FILES['profilePicture']['name'];
$validID = $_FILES['validID']['name'];
move_uploaded_file($_FILES['profilePicture']['tmp_name'], "uploads/$profilePicture");
move_uploaded_file($_FILES['validID']['tmp_name'], "uploads/$validID");

// Update query
$sql = "UPDATE users SET firstName='$firstName', middleName='$middleName', lastName='$lastName', extName='$extName', birthDate='$birthDate', gender='$gender', age='$age', address='$address', email='$email', phoneNumber='$phoneNumber', emergencyContactName='$emergencyContactName', relationship='$relationship', emergencyContactNumber='$emergencyContactNumber', profilePicture='$profilePicture', validID='$validID' WHERE id='$user_id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Profile updated successfully!'); window.location.href = 'user_dashboard.php';</script>";
} else {
    echo "<script>alert('Error updating profile.'); window.location.href = 'user_dashboard.php';</script>";
}
?>
