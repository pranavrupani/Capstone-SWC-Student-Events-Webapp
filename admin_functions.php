<?php
// Admin controller for event management.
// This page is only available to logged-in admins.
// It handles the event list view, addform, edit form, image upload and delete actions.
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/site_styles.php';


// If the user is not logged in, or they are not an admin, they are sent back to the home page.
// to the login page. This prevents students from reaching the admin tool.
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}


// The admin page has two main views:
// - view=events shows the list of all events
// - anything else shows the add/edit form
// Search text is also read from the query string so admins can filter the list.
$view = $_GET['view'] ?? 'form';
$search = trim($_GET['search'] ?? '');
$message = '';
$errors = [];
$editing = false;
$editId = 0;
$imageName = '';

// These values populate the form fields when the admin is creating a new event or editing an existing one. 
$form = [
    'title' => '',
    'description' => '',
    'event_date' => '',
    'event_time' => '',
    'location' => '',
    'category_id' => ''
];


// This section decides whether we are showing all events or just the result of a
// search. The query joins the events and categories tables so category names can
// be displayed in the cards.
if (isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $deleteSql = "DELETE FROM events WHERE event_id = $deleteId";

    if ($conn->query($deleteSql)) {
        $message = 'Event deleted successfully.';
    } else {
        $message = 'Delete failed: ' . $conn->error;
    }
}

if ($view === 'events') {
    if ($search !== '') {
        $sql = "SELECT e.event_id, e.title, e.description, e.event_date, e.event_time, e.location, c.category_name
                FROM events e
                LEFT JOIN categories c ON e.category_id = c.category_id
                WHERE LOWER(e.title) LIKE '%" . strtolower($search) . "%'
                ORDER BY e.event_date ASC";
    } else {
        $sql = "SELECT e.event_id, e.title, e.description, e.event_date, e.event_time, e.location, c.category_name
                FROM events e
                LEFT JOIN categories c ON e.category_id = c.category_id
                ORDER BY e.event_date ASC";
    }

    $result = $conn->query($sql);
} else {

    // When the admin clicks Edit on a card, the page receives edit_id in the URL.
    // We look up that event and prefill the form with the saved values.
    if (isset($_GET['edit_id'])) {
        $editId = (int) $_GET['edit_id'];
        $editSql = "SELECT * FROM events WHERE event_id = $editId";
        $editResult = $conn->query($editSql);

        if ($editResult && $editResult->num_rows > 0) {
            $editing = true;
            $event = $editResult->fetch_assoc();

            $form['title'] = $event['title'];
            $form['description'] = $event['description'];
            $form['event_date'] = $event['event_date'];
            $form['event_time'] = $event['event_time'];
            $form['location'] = $event['location'];
            $form['category_id'] = $event['category_id'];
            $imageName = $event['flyer_image_name'];
        } else {
            $message = 'Event not found.';
        }
    }

    // This allows the admin to delete the current flyer image without deleting the
    // event itself. We remove the file from the images folder and clear the DB value.
    if (isset($_POST['remove_image']) && $editing) {
        $deleteImageName = $conn->real_escape_string($imageName);

        if ($deleteImageName !== '') {
            $imagePath = __DIR__ . '/images/' . $deleteImageName;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $conn->query("UPDATE events SET flyer_image_name = NULL WHERE event_id = $editId");
            $imageName = '';
            $message = 'Image removed successfully.';
        }
    }

    // The code first validates the submitted form values, then handles any file upload, then writes the record to
    // the database using either INSERT or UPDATE.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
        // Read the values out of the POST request 
        $form['title'] = trim($_POST['title'] ?? '');
        $form['description'] = trim($_POST['description'] ?? '');
        $form['event_date'] = trim($_POST['event_date'] ?? '');
        $form['event_time'] = trim($_POST['event_time'] ?? '');
        $form['location'] = trim($_POST['location'] ?? '');
        $form['category_id'] = trim($_POST['category_id'] ?? '');

        // Required field checks.
        if ($form['title'] === '') {
            $errors[] = 'Title is required.';
        }
        if ($form['description'] === '') {
            $errors[] = 'Description is required.';
        }
        if ($form['event_date'] === '') {
            $errors[] = 'Date is required.';
        }
        if ($form['event_time'] === '') {
            $errors[] = 'Time is required.';
        }
        if ($form['location'] === '') {
            $errors[] = 'Location is required.';
        }
        if ($form['category_id'] === '' || !is_numeric($form['category_id'])) {
            $errors[] = 'Please choose a category.';
        }

        // If the admin uploaded a new image, validate and save it before inserting or
        // updating the event record in the database.
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageTmp = $_FILES['image']['tmp_name'] ?? '';
            $imageNameFromFile = $_FILES['image']['name'] ?? '';

            if ($imageTmp === '' || $imageNameFromFile === '') {
                $errors[] = 'Please upload a valid image.';
            } else {
                $ext = strtolower(pathinfo($imageNameFromFile, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($ext, $allowed, true)) {
                    $errors[] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
                } else {
                    $folder = __DIR__ . '/images';
                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($imageNameFromFile));
                    $target = $folder . '/' . $safeName;

                    if (move_uploaded_file($imageTmp, $target)) {
                        $imageName = $safeName;
                    } else {
                        $errors[] = 'Could not save the image.';
                    }
                }
            }
        }

        // If validation passed, build the SQL statement and save the event.
        if (empty($errors)) {
            $title = $conn->real_escape_string($form['title']);
            $description = $conn->real_escape_string($form['description']);
            $date = $conn->real_escape_string($form['event_date']);
            $time = $conn->real_escape_string($form['event_time']);
            $location = $conn->real_escape_string($form['location']);
            $categoryId = (int) $form['category_id'];

            if ($editing) {
                // Update an existing event record.
                if ($imageName !== '') {
                    $sql = "UPDATE events SET title = '$title', description = '$description', event_date = '$date', event_time = '$time', location = '$location', category_id = $categoryId, flyer_image_name = '$imageName' WHERE event_id = $editId";
                } else {
                    $sql = "UPDATE events SET title = '$title', description = '$description', event_date = '$date', event_time = '$time', location = '$location', category_id = $categoryId WHERE event_id = $editId";
                }
                $message = 'Event updated successfully.';
            } else {
                // Insert a completely new event into the database.
                $sql = "INSERT INTO events (title, description, event_date, event_time, location, category_id, flyer_image_name)
                        VALUES ('$title', '$description', '$date', '$time', '$location', $categoryId, '$imageName')";
                $message = 'Event added successfully.';
            }

            if ($conn->query($sql)) {
                // After a successful add, clear the form so the admin can add another event.
                if (!$editing) {
                    $form = [
                        'title' => '',
                        'description' => '',
                        'event_date' => '',
                        'event_time' => '',
                        'location' => '',
                        'category_id' => ''
                    ];
                    $imageName = '';
                }
            } else {
                // MySQL errors are displayed to the admin so they know something failed.
                $message = 'Something went wrong: ' . $conn->error;
            }
        }
    }
}

// This query returns every category in alphabetical order.
$categorySql = "SELECT * FROM categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);
$categories = [];
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $view === 'events' ? 'Admin Events' : ($editing ? 'Edit Event' : 'Add Event'); ?></title>
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

        .brand a,
        .nav a {
            color: white;
            text-decoration: none;
        }

        .nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav a {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.12);
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #d1d1d1;
            margin-top: 24px;
            margin-bottom: 40px;
        }

        .top-links {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
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
        }

        .logout-button {
            background: #4b4b4b;
        }

        .message {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bdbdbd;
            background: #eeeeee;
            color: #111111;
        }

        .success {
            background: #eeeeee;
            color: #111111;
        }

        .error {
            background: #eeeeee;
            color: #111111;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input, textarea, select, button {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #bdbdbd;
            background: white;
            color: #111111;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            background: #111111;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
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

        .event-button, .delete-button {
            padding: 10px 14px;
            border-radius: 6px;
            border: none;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            display: inline-block;
        }

        .event-button {
            background: #111111;
            color: white;
        }

        .delete-button {
            background: #4b4b4b;
            color: white;
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
            border-radius: 6px;
            border: 1px solid #bdbdbd;
            min-width: 220px;
            background: white;
            color: #111111;
        }

        .empty-state {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            padding: 20px;
            color: #111111;
        }
    </style>
</head>
<body>
    <!-- Top navigation for the admin area. -->
    <header class="topbar">
        <div class="brand"><a href="events.php">SWC Student Events</a></div>

        <nav class="nav">
            <a href="admin_functions.php?view=events">Events</a>
            <a href="admin_functions.php">Add Event</a>
            <a href="logout.php" style="background: rgba(255,255,255,0.12);">Logout</a>
        </nav>
    </header>

    <div class="container">
        <?php if ($view === 'events') : ?>
            <!-- List view: admin sees all current events and can search through them. -->
            <h1>Admin Events</h1>

            <form class="search-bar" method="GET" action="admin_functions.php">
                <input type="hidden" name="view" value="events">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search event titles...">
                <button type="submit">Search</button>
                <?php if ($search !== '') : ?>
                    <a class="button-link" href="admin_functions.php?view=events">Clear</a>
                <?php endif; ?>
            </form>

            <div class="events-grid">
                <?php if (isset($result) && $result && $result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <!-- Each card displays one event and the action buttons for edit/delete/view. -->
                        <div class="event-card">
                            <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p class="event-meta"><strong>Date:</strong> <?php echo htmlspecialchars($row['event_date']); ?></p>
                            <p class="event-meta"><strong>Time:</strong> <?php echo htmlspecialchars($row['event_time']); ?></p>
                            <p class="event-meta"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="event-meta"><strong>Category:</strong> <?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></p>
                            <div class="event-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                            <div class="card-actions">
                                <a class="event-button" href="event_details.php?event_id=<?php echo (int) $row['event_id']; ?>">View Details</a>
                                <a class="event-button" href="admin_functions.php?edit_id=<?php echo (int) $row['event_id']; ?>">Edit</a>
                                <form method="POST" action="admin_functions.php?view=events" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $row['event_id']; ?>">
                                    <button type="submit" class="delete-button" onclick="return confirm('Delete this event?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="empty-state">No events found.</div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <!-- Form view: this is where the admin adds a new event or edits an existing one. -->
            <?php if ($message !== '') : ?>
                <div class="message <?php echo empty($errors) ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)) : ?>
                <div class="message error">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h1><?php echo $editing ? 'Edit Event' : 'Add Event'; ?></h1>

            <form method="POST" action="admin_functions.php<?php echo $editing ? '?edit_id=' . $editId : ''; ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($form['title']); ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($form['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="event_date" value="<?php echo htmlspecialchars($form['event_date']); ?>">
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="event_time" value="<?php echo htmlspecialchars($form['event_time']); ?>">
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($form['location']); ?>">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                <?php if ($form['category_id'] == $category['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Event Image</label>
                    <input type="file" name="image" accept="image/*">

                    <?php if ($editing && $imageName !== '') : ?>
                        <div style="margin-top: 10px;">
                            <img src="images/<?php echo htmlspecialchars($imageName); ?>" style="max-width: 180px; max-height: 180px; display: block; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            <button type="submit" name="remove_image" value="1" style="width: auto; background: #dc3545;">Remove Image</button>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit"><?php echo $editing ? 'Update Event' : 'Add Event'; ?></button>
                <div style="margin-top: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="admin_functions.php?view=events" style="background: #4b4b4b; color: white; text-decoration: none; padding: 10px 14px; border-radius: 6px; display: inline-block;">Back to Events</a>
                    <?php if ($editing) : ?>
                        <a href="admin_functions.php?view=events" style="background: #111111; color: white; text-decoration: none; padding: 10px 14px; border-radius: 6px; display: inline-block;">View Events</a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
