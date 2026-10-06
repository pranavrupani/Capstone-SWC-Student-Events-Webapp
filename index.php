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


// This query selects only the basic event info (title, date, time, location) and
// sorts by date so the earliest events appear first. limit to 3 cards so the
// homepage remains clean and fast-loading.
$sql = "SELECT event_id, title, event_date, event_time, location
        FROM events
        ORDER BY event_date ASC
        LIMIT 3";

$result = $conn->query($sql);
$featuredEvents = [];

// Store the query results in an array so we can loop through them in the HTML.
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $featuredEvents[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SWC Student Events</title>
    <style>
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .hero {
            background: linear-gradient(135deg, #111111, #4a4a4a);
            color: white;
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

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            border: 1px solid #d1d1d1;
        }

        .event-card h2 {
            margin: 0 0 12px;
            font-size: 22px;
        }

        .event-meta {
            margin: 8px 0;
            color: #333333;
        }

        .cta-row {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            background: #111111;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn.secondary {
            background: #e5e5e5;
            color: #111111;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <!-- Brand logo that links to the homepage. -->
        <div class="brand"><a href="index.php">SWC Student Events</a></div>

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
            <p>Discover what is coming up in the SWC.</p>
        </section>

        <!-- Featured events grid: displays the next 3 upcoming events. -->
        <div class="events-grid">
            <?php if (!empty($featuredEvents)) : ?>
                <!-- Loop through each featured event and render a card. -->
                <?php foreach ($featuredEvents as $event) : ?>
                    <div class="event-card">
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p class="event-meta"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                        <p class="event-meta"><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
                        <p class="event-meta"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Fallback message when no events are in the database. -->
                <div class="event-card">
                    <h2>No events available right now.</h2>
                </div>
            <?php endif; ?>
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
