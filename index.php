<?php
// This page displays a public homepage that shows upcoming events.
// If a user is already logged in, they are redirected to the events page.

session_start();

// Logged-in users should not see this guest page. They are sent to events.php,
// which routes them to the appropriate admin or student page based on their role.
if (isset($_SESSION['user_id'])) {
    header('Location: events.php');
    exit;
}

require_once 'db.php';
require_once __DIR__ . '/includes/site_styles.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SWC Student Events</title>
    <style>
        :root {
            --primary: #7f6db1;
            --primary-dark: #685a95;
            --primary-soft: rgba(127, 109, 177, 0.12);
            --text: #111111;
            --bg: #f4f4f4;
            --white: #ffffff;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            padding: 32px 28px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 36px;
        }

        .hero p {
            margin: 0;
            font-size: 18px;
        }

        .calendar-panel {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid #d1d1d1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            padding: 20px;
        }

        .calendar-panel img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .cta-row {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn.secondary {
            background: #e5e5e5;
            color: var(--text);
        }

        .brand a {
            color: var(--white);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .brand img {
            display: block;
            height: 90px;
            width: auto;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <!-- Brand logo that links to the homepage. -->
        <div class="brand"><a href="index.php" aria-label="SWC Student events"><img src="images/Logo_cropped.png" alt="SWC Student events"></a></div>

        <!-- Navigation bar for guests. Only shows login link. -->
        <nav class="nav">
            <?php if (!isset($_SESSION['user_id'])) : ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <!-- Hero section with the main headline and tagline. -->
        <section class="hero">
            <h1>Upcoming SWC Events</h1>
        </section>

        <div class="calendar-panel">
            <img src="images/November Calendar 2026.png" alt="November 2026 calendar">
        </div>

        <!-- Call-to-action buttons for guests to log in or register. -->
        <?php if (!isset($_SESSION['user_id'])) : ?>
            <div class="cta-row">
                <a class="btn secondary" href="login.php">Login / Register</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
