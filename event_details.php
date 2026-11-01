<?php
// Event details page.
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/site_styles.php';

// $event holds the single event record we are about to display.
// $saveMessage and $saveError are used for success/error messages after a user
// tries to save the event from this page.
$event = null;
$saveMessage = '';
$saveError = '';
$backLink = 'student_functions.php?view=events';

if (($_SESSION['user_role'] ?? '') === 'admin') {
    $backLink = 'admin_functions.php?view=events';
}

// When a student saves an event from another page, they are sent back here with a
// status value so this page can show a clear message like "saved" or "duplicate".
$status = $_GET['status'] ?? '';
if ($status === 'saved') {
    $saveMessage = 'Event saved successfully.';
} elseif ($status === 'duplicate') {
    $saveError = 'You have already saved this event.';
} elseif ($status === 'missing') {
    $saveError = 'This event no longer exists.';
} elseif ($status === 'error') {
    $saveError = 'Could not save this event. Please try again.';
}

// The URL contains event_id, so we convert it to an int and query the database.
// We join with categories so we can display the category name in the details box.
if (isset($_GET['event_id'])) {
    $eventId = (int) $_GET['event_id'];

    if ($eventId > 0) {
        $sql = "SELECT e.*, c.category_name
                FROM events e
                LEFT JOIN categories c ON e.category_id = c.category_id
                WHERE e.event_id = $eventId";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $event = $result->fetch_assoc();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            color: #111111;
        }

        .container {
            max-width: 800px;
            margin: 30px auto 0;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #d1d1d1;
        }

        h1 {
            margin-top: 0;
            color: #111111;
        }

        .image-box {
            margin: 20px 0;
            background: #efefef;
            border: 1px dashed #777777;
            border-radius: 10px;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }

        .image-box img {
            max-width: 100%;
            max-height: 300px;
            display: block;
        }

        .image-box span {
            display: block;
            padding: 20px;
            color: #333333;
            font-weight: bold;
        }

        .details-box {
            background: #fafafa;
            border: 1px solid #d1d1d1;
            border-radius: 10px;
            padding: 20px;
        }

        .details-box p {
            margin: 10px 0;
            line-height: 1.6;
            color: #222222;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            background: #111111;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .button.secondary {
            background: #4b4b4b;
            margin-left: 10px;
        }

        .message {
            margin: 16px 0;
            padding: 12px 14px;
            border-radius: 6px;
            font-weight: bold;
            border: 1px solid #bdbdbd;
            background: #eeeeee;
            color: #111111;
        }

        .message.success {
            background: #eeeeee;
            color: #111111;
        }

        .message.error {
            background: #eeeeee;
            color: #111111;
        }

        form {
            margin-top: 20px;
        }

        .empty {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            padding: 20px;
        }

        .topbar {
            background: #111111;
            color: white;
            padding: 10px 0;
            position: relative;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand img {
            max-height: 50px;
        }

        .nav {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <?php if (($_SESSION['user_role'] ?? '') === 'admin') : ?>
                <a href="admin_functions.php?view=events" aria-label="SWC Student events"><img src="images/Logo_cropped.png" alt="SWC Student events"></a>
            <?php else : ?>
                <a href="student_functions.php?view=events" aria-label="SWC Student events"><img src="images/Logo_cropped.png" alt="SWC Student events"></a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])) : ?>
            <nav class="nav">
                <?php if (($_SESSION['user_role'] ?? '') === 'admin') : ?>
                    <a href="admin_functions.php?view=events">Events</a>
                    <a href="admin_functions.php">Add Event</a>
                    <a href="logout.php" style="background: rgba(255,255,255,0.12);">Logout</a>
                <?php else : ?>
                    <a href="student_functions.php?view=events">Events</a>
                    <a href="student_functions.php?view=saved">Saved Events</a>
                    <a href="logout.php" style="background: rgba(255,255,255,0.12);">Logout</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </header>

    <div class="container">
        <?php if ($event) : ?>
            <!-- Event exists, so render the full details panel and actions. -->
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>

            <div class="image-box">
                <?php if (!empty($event['flyer_image_name'])) : ?>
                    <!-- Use the existing flyer image if one was uploaded for this event. -->
                    <img src="images/<?php echo htmlspecialchars($event['flyer_image_name']); ?>" alt="Event image">
                <?php else : ?>
                    <!-- Fallback message shown when there is no flyer uploaded. -->
                    <span>Event Flyer</span>
                <?php endif; ?>
            </div>

            <?php if ($saveMessage !== '') : ?>
                <div class="message success"><?php echo htmlspecialchars($saveMessage); ?></div>
            <?php endif; ?>

            <?php if ($saveError !== '') : ?>
                <div class="message error"><?php echo htmlspecialchars($saveError); ?></div>
            <?php endif; ?>

            <div class="details-box">
                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name'] ?? 'General'); ?></p>
                <p><strong>Description:</strong><br>
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>

            <?php if (isset($_SESSION['user_id'])) : ?>
                <!-- Logged-in users can save the event directly from this page. -->
                <form method="POST" action="student_functions.php">
                    <input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>">
                    <input type="hidden" name="return_to" value="event_details.php">
                    <button type="submit" name="save_event" class="button">Save Event</button>
                </form>
            <?php endif; ?>

            <!-- Return to the student or admin event list after viewing the details. -->
            <a class="button secondary" href="<?php echo htmlspecialchars($backLink); ?>">Back to Events</a>
        <?php else : ?>
            <!-- If no matching event is found, show an empty-state message. -->
            <div class="empty">
                <h2>Event not found.</h2>
                <a class="button" href="<?php echo htmlspecialchars($backLink); ?>">Back to Events</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>