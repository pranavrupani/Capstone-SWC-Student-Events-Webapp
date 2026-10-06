<?php
// This page allows guests to log in or create a new account.
// If someone is already logged in, they are sent to the events page instead.

session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/site_styles.php';


// If a user is already in a session, they are sent to events.php, which routes them by role to the admin or
// student pages.
if (isset($_SESSION['user_id'])) {
    header('Location: events.php');
    exit;
}

// These variables hold error messages (login failure, registration issues) and
// success messages (registration completed) that are shown to the user.
$loginError = '';
$registerError = '';
$successMessage = '';

// Both forms POST to this same page, so a check is made to see which one was submitted and
// handle the logic accordingly.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // When the user fills in email and password and clicks Login, this block
    // validates the credentials and creates a session if they match.
    if (isset($_POST['login_submit'])) {
        // Read the email and password from the form.
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Check that both fields are filled in.
        if ($email === '' || $password === '') {
            $loginError = 'Please enter both email and password.';
        } else {
            // Escape the inputs to prevent SQL injection.
            $email = $conn->real_escape_string($email);
            $password = $conn->real_escape_string($password);

            // Query the database for a user with matching email and password.
            // The query also pulls the user's name and role so we can populate the session.
            $sql = "SELECT user_id, name, role FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
            $result = $conn->query($sql);

            // If a row is found, the credentials match and we should log the user in.
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Store the user's details in the session so they stay logged in.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Send the user to events.php which routes them to the correct page by role.
                header('Location: events.php');
                exit;
            } else {
                // No matching user was found, so show an error.
                $loginError = 'Invalid email or password.';
            }
        }
    }

    // this block creates a new account in the database.
    if (isset($_POST['register_submit'])) {
        // Read the form fields from the registration box.
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate that all required fields are filled in.
        if ($name === '' || $email === '' || $password === '') {
            $registerError = 'Name, email and password are required.';
        } else {
            // Escape all the inputs to prevent SQL injection.
            $email = $conn->real_escape_string($email);
            $name = $conn->real_escape_string($name);
            $password = $conn->real_escape_string($password);

            // Check if the email is already registered in the database.
            $checkSql = "SELECT user_id FROM users WHERE email = '$email' LIMIT 1";
            $checkResult = $conn->query($checkSql);

            // If the email is already in use, prevent the registration.
            if ($checkResult && $checkResult->num_rows > 0) {
                $registerError = 'That email is already registered.';
            } else {
                // Email is available. Create a new student account.
                $role = 'student';
                $createdAt = date('Y-m-d H:i:s');

                // Insert the new user into the database.
                $insertSql = "INSERT INTO users (name, email, password, role, created_at)
                              VALUES ('$name', '$email', '$password', '$role', '$createdAt')";

                if ($conn->query($insertSql)) {
                    // Registration successful. Tell the user they can now log in.
                    $successMessage = 'Registration successful! You can now log in.';
                } else {
                    // If the insert failed, show an error.
                    $registerError = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login / Register</title>
    <style>
        body {
            background: #f4f4f4;
            margin: 0;
            color: #111111;
            font-family: Arial, sans-serif;
        }

        .page-wrap {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .box {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 28px;
        }

        .box h1 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #bdbdbd;
            border-radius: 6px;
            font-size: 16px;
            background: white;
            color: #111111;
        }

        button {
            width: 100%;
            background: #111111;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .error,
        .success {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #bdbdbd;
            background: #eeeeee;
            color: #111111;
        }
    </style>
</head>
<body>
    <!-- Top navigation bar for the login/register page. -->
    <header class="topbar">
        <div class="brand"><a href="index.php">SWC Student Events</a></div>

        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <div class="page-wrap">
        <!-- Login box: existing users enter their credentials here. -->
        <div class="box">
            <h1>Login</h1>

            <?php if ($loginError !== '') : ?>
                <!-- Show login errors like invalid credentials. -->
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" required>
                </div>

                <!-- Submit the login form with the login_submit flag. -->
                <button type="submit" name="login_submit" value="1">Login</button>
            </form>
        </div>

        <!-- Registration box: new users create an account here. -->
        <div class="box">
            <h1>Register</h1>

            <?php if ($successMessage !== '') : ?>
                <!-- Show success message after account is created. -->
                <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if ($registerError !== '') : ?>
                <!-- Show registration errors like duplicate email or missing fields. -->
                <div class="error"><?php echo htmlspecialchars($registerError); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="register_password">Password</label>
                    <input type="password" id="register_password" name="password" required>
                </div>

                <!-- Submit the registration form with the register_submit flag. -->
                <button type="submit" name="register_submit" value="1">Create Account</button>
            </form>
        </div>
    </div>
</body>
</html>
