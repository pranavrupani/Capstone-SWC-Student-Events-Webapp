<?php
// This page displays events for students and handles saving/unsaving events.
// It is only accessible to logged-in students.

session_start();

// If the user is not logged in or not a student, they are sent back to
// the login page. This protects the student-only functionality.
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// The page has two main views: 'events' shows the full event list, and 'saved'
// shows only the student's saved events. Search filters the event list by title.
$userId = (int) $_SESSION['user_id'];
$view = $_GET['view'] ?? 'events';
$search = trim($_GET['search'] ?? '');
$successMessage = '';
$errorMessage = '';

// These handlers process student actions like saving an event or removing
// a saved event from their list.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // When a student clicks Save Event, this block adds a record to the
    // saved_events table linking the student to the event.
    if (isset($_POST['save_event'])) {
        // Read the event_id being saved and where to redirect after.
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? 'event_details.php';

        // Validate that the event_id is a positive number.
        if ($eventId <= 0) {
            header('Location: ' . $returnTo . '?event_id=' . $eventId . '&status=error');
            exit;
        }

        // Check that the event actually exists in the database.
        $checkEventSql = "SELECT event_id FROM events WHERE event_id = $eventId LIMIT 1";
        $checkEventResult = $conn->query($checkEventSql);

        if (!$checkEventResult || $checkEventResult->num_rows === 0) {
            header('Location: ' . $returnTo . '?event_id=' . $eventId . '&status=missing');
            exit;
        }

        // Check if this event is already saved by the current student to prevent duplicates.
        $duplicateSql = "SELECT saved_id FROM saved_events WHERE user_id = $userId AND event_id = $eventId LIMIT 1";
        $duplicateResult = $conn->query($duplicateSql);

        if ($duplicateResult && $duplicateResult->num_rows > 0) {
            header('Location: ' . $returnTo . '?event_id=' . $eventId . '&status=duplicate');
            exit;
        }

        // All validations passed. Save the event for this student with a timestamp.
        $savedAt = date('Y-m-d H:i:s');
        $insertSql = "INSERT INTO saved_events (user_id, event_id, saved_at) VALUES ($userId, $eventId, '$savedAt')";

        if ($conn->query($insertSql)) {
            header('Location: ' . $returnTo . '?event_id=' . $eventId . '&status=saved');
            exit;
        }

        // If the insert failed, send the user back with an error.
        header('Location: ' . $returnTo . '?event_id=' . $eventId . '&status=error');
        exit;
    }
    // When a student clicks Remove from their saved events, this block
    // deletes the record linking the student to that event.
    if (isset($_POST['remove_saved_event'])) {
        // Read the saved_id of the record we are trying to remove.
        $savedId = (int) ($_POST['saved_id'] ?? 0);

        // Validate the ID is a positive number.
        if ($savedId <= 0) {
            $errorMessage = 'Invalid saved event selection.';
        } else {
            // Make sure this saved record belongs to the current student (no tampering).
            $checkSql = "SELECT saved_id FROM saved_events WHERE saved_id = $savedId AND user_id = $userId LIMIT 1";
            $checkResult = $conn->query($checkSql);

            if (!$checkResult || $checkResult->num_rows === 0) {
                $errorMessage = 'That saved event does not belong to this user.';
            } else {
                // Delete the saved event record.
                $deleteSql = "DELETE FROM saved_events WHERE saved_id = $savedId AND user_id = $userId";

                if ($conn->query($deleteSql)) {
                    $successMessage = 'Saved event removed successfully.';
                } else {
                    $errorMessage = 'Could not remove the saved event. Please try again.';
                }
            }
        }
    }
}

// If the student is viewing saved events, load only the events they saved.
// Otherwise, load all events, optionally filtered by search query.
if ($view === 'saved') {
    // Retrieve the current student's saved events with all event details.
    $sql = "SELECT s.saved_id, s.user_id, s.event_id, e.title, e.description, e.event_date, e.event_time, e.location, c.category_name
            FROM saved_events s
            INNER JOIN events e ON s.event_id = e.event_id
            LEFT JOIN categories c ON e.category_id = c.category_id
            WHERE s.user_id = $userId
            ORDER BY e.event_date ASC";
} else {
    // Show all events, optionally filtered by search query on the title.
    if ($search !== '') {
        $sql = "SELECT e.event_id, e.title, e.description, e.event_date, e.event_time, e.location, c.category_name
                FROM events e
                LEFT JOIN categories c ON e.category_id = c.category_id
                WHERE LOWER(e.title) LIKE '%" . strtolower($search) . "%'
                ORDER BY e.event_date ASC";
    } else {
        // No search filter, so load all events in order.
        $sql = "SELECT e.event_id, e.title, e.description, e.event_date, e.event_time, e.location, c.category_name
                FROM events e
                LEFT JOIN categories c ON e.category_id = c.category_id
                ORDER BY e.event_date ASC";
    }
}

// Execute the query and get the results.
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($view === 'saved') ? 'My Saved Events' : 'Student Events'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #111111;
        }

        .topbar {
            background: #111111;
            color: white;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
        }

        .brand a {
            color: white;
            text-decoration: none;
        }

        .nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.12);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 20px;
            flex-wrap: wrap;
        }

        .top-links {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .button-link,
        .logout-button {
            color: white;
            text-decoration: none;
            font-weight: bold;
            background: #111111;
            padding: 10px 14px;
            border-radius: 6px;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .logout-button {
            background: #4b4b4b;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .search-bar input {
            padding: 10px 12px;
            font-size: 16px;
            border: 1px solid #bdbdbd;
            border-radius: 6px;
            min-width: 220px;
            background: white;
            color: #111111;
        }

        .search-bar button, .event-button, .delete-button, .save-button {
            padding: 10px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .search-bar button, .event-button, .save-button {
            background: #111111;
            color: white;
        }

        .delete-button {
            background: #4b4b4b;
            color: white;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #d1d1d1;
            display: flex;
            flex-direction: column;
        }

        .event-card h2 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #111111;
        }

        .event-meta {
            margin: 4px 0;
            color: #333333;
        }

        .event-description {
            margin-top: 12px;
            line-height: 1.5;
            flex: 1;
            color: #222222;
        }

        .card-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-state {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            padding: 20px;
            color: #111111;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 8px;
            font-weight: bold;
            border: 1px solid #bdbdbd;
        }

        .message.success {
            background: #eeeeee;
            color: #111111;
        }

        .message.error {
            background: #eeeeee;
            color: #111111;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand"><a href="events.php">SWC Student Events</a></div>

        <nav class="nav">
            <a href="student_functions.php?view=events">Events</a>
            <a href="student_functions.php?view=saved">Saved Events</a>
            <a href="logout.php" style="background: rgba(255,255,255,0.12);">Logout</a>
        </nav>
    </header>

    <div class="top-bar">
        <!-- Page heading that changes based on the current view. -->
        <h1 style="margin: 0;">
            <?php echo ($view === 'saved') ? 'My Saved Events' : 'Upcoming Events'; ?>
        </h1>
    </div>

    <!-- Display success or error messages from POST actions. -->
    <?php if ($successMessage !== '') : ?>
        <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage !== '') : ?>
        <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <!-- Search box: only shown when viewing all events, not saved events. -->
    <?php if ($view !== 'saved') : ?>
        <form class="search-bar" method="GET" action="student_functions.php">
            <input type="hidden" name="view" value="events">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search event titles...">
            <button type="submit">Search</button>
            <?php if ($search !== '') : ?>
                <a class="button-link" href="student_functions.php?view=events">Clear</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <!-- Event cards grid: displays the events returned from the SQL query. -->
    <div class="events-grid">
        <?php
        // Render each event card with title, metadata and action buttons.
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='event-card'>";
                echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
                echo "<p class='event-meta'><strong>Date:</strong> " . htmlspecialchars($row['event_date']) . "</p>";
                echo "<p class='event-meta'><strong>Time:</strong> " . htmlspecialchars($row['event_time']) . "</p>";
                echo "<p class='event-meta'><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
                echo "<p class='event-meta'><strong>Category:</strong> " . htmlspecialchars($row['category_name'] ?? 'General') . "</p>";
                echo "<div class='event-description'>" . nl2br(htmlspecialchars($row['description'])) . "</div>";
                echo "<div class='card-actions'>";
                
                // View Details button directs to the single-event detail page.
                echo "<a class='event-button' href='event_details.php?event_id=" . (int)$row['event_id'] . "'>View Details</a>";

                // Remove button only appears when viewing saved events.
                if ($view === 'saved') {
                    echo "<form method='POST' action='student_functions.php?view=saved' style='display:inline;'>";
                    echo "<input type='hidden' name='saved_id' value='" . (int)$row['saved_id'] . "'>";
                    echo "<button type='submit' name='remove_saved_event' class='delete-button' onclick=\"return confirm('Remove this saved event?');\">Remove</button>";
                    echo "</form>";
                }

                echo "</div>";
                echo "</div>";
            }
        } else {
            // Fallback message when no events match the current view or search.
            echo "<div class='empty-state'>" . (($view === 'saved') ? 'You have not saved any events yet.' : 'No events found.') . "</div>";
        }
        ?>
    </div>
</body>
</html>
