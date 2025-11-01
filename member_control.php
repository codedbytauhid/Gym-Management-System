<?php
session_start();
include('config.php'); // DB connection

//Member_Registration
if (isset($_POST['register'])) {
    $name = $_POST['Name'];
    $age = $_POST['Age'];
    $contact = $_POST['Contact'];
    $email = $_POST['Email'];
    $address = $_POST['Address'];
    $gender = $_POST['Gender'];
    $password = $_POST['Password'];
    $role = $_POST['Role'];

    // Optional: Hash the password for security
    //$password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM members WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['register_error'] = "Email already registered!";
        header("Location: index.php#mem_reg");
        exit();
    }

    // Insert member into database
    $insert = $conn->prepare("INSERT INTO members (name, age, contact, email, address, gender, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("siisssss", $name, $age, $contact, $email, $address, $gender, $password, $role);

    if ($insert->execute()) {
        $_SESSION['register_success'] = "Registration successful! You can now log in.";
        header("Location: member_login.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Error: " . $insert->error;
        header("Location: index.php#mem_reg");
        exit();
    }
}

// ================= MEMBER LOGIN =================
if (isset($_POST['login'])) {
    $email = $_POST['Email'];
    $password = $_POST['Password'];

    $query = "SELECT * FROM members WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $member = $result->fetch_assoc();
        $_SESSION['member_id'] = $member['id'];
        $_SESSION['member_name'] = $member['name'];

        header("Location: member_dash.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect email or password!";
        header("Location: member_login.php");
        exit();
    }
}
?>
