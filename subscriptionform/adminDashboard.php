<?php


require __DIR__ . '/vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$conn = new mysqli("localhost", "root", "", "subscription_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete record
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM subscribers WHERE id=$delete_id") or die($conn->error);
    header("Location: adminDashboard.php");
    exit;
}

// Update record
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $conn->query("UPDATE subscribers SET name='$name', email='$email' WHERE id=$id") or die($conn->error);
    header("Location: adminDashboard.php");
    exit;
}

// Send message to all subscribers
$messageSent = "";
if (isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $result = $conn->query("SELECT email FROM subscribers");
        if ($result && $result->num_rows > 0) {
            $errors = "";
            while ($row = $result->fetch_assoc()) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'kavanan1414@gmail.com';
                    $mail->Password   = 'kfowytinhgtqpxys';   
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('kavanan1414@gmail.com', 'NewsletterUpdates');
                    $mail->addAddress($row['email']); 

                    $mail->isHTML(true);
                    $mail->Subject = 'Message from Admin';
                    $mail->Body    = nl2br($message);
                    $mail->send();
                } catch (Exception $e) {
                    $errors .= "Mailer Error for {$row['email']}: {$mail->ErrorInfo}<br>";
                }
            }
            $messageSent = $errors ?: "✅ Message sent to all subscribers!";
        } else {
            $messageSent = "⚠️ No subscribers found.";
        }
    } else {
        $messageSent = "⚠️ Message cannot be empty.";
    }
}

// Fetch subscriber records
$sql = "SELECT id, name, email, subscribed_at FROM subscribers ORDER BY subscribed_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; }
        .container { width: 90%; margin: auto; }
        h1 { text-align: center; margin: 20px 0; }
        .logout { text-align: right; margin: 10px; }
        .logout a { background:#e74c3c; color:white; padding:8px 15px; border-radius:5px; text-decoration:none; }
        .logout a:hover { background:#c0392b; }
        .message-box { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0px 2px 8px rgba(0,0,0,0.1); }
        textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd; min-height: 100px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #219150; }
        .status { margin-top: 10px; font-weight: bold; color: #2c3e50; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0px 2px 8px rgba(0,0,0,0.2); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        a.btn, button.btn { padding: 6px 12px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; color: white; }
        .edit-btn { background: #3498db; } .delete-btn { background: #e74c3c; } .save-btn { background: #27ae60; }
        input[type="text"], input[type="email"] { padding: 5px; width: 90%; }
    </style>
</head>
<body>

<div class="container">
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>

    <h1>Admin Dashboard - Subscriber Records</h1>

    <div class="message-box">
        <h2>Send Message to All Subscribers</h2>
        <form method="POST">
            <textarea name="message" placeholder="Type your message here..."></textarea><br>
            <button type="submit" name="send_message">Send</button>
        </form>
        <?php if ($messageSent) echo "<p class='status'>$messageSent</p>"; ?>
    </div>

    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Subscribed At</th><th>Actions</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if (isset($_GET['edit_id']) && $_GET['edit_id'] == $row['id']) {
                    echo "<tr>
                        <form method='POST'>
                            <td>".$row["id"]."<input type='hidden' name='id' value='".$row["id"]."'></td>
                            <td><input type='text' name='name' value='".$row["name"]."'></td>
                            <td><input type='email' name='email' value='".$row["email"]."'></td>
                            <td>".$row["subscribed_at"]."</td>
                            <td>
                                <button type='submit' name='update' class='btn save-btn'>Save</button>
                                <a class='btn delete-btn' href='adminDashboard.php'>Cancel</a>
                            </td>
                        </form>
                    </tr>";
                } else {
                    echo "<tr>
                        <td>".$row["id"]."</td>
                        <td>".$row["name"]."</td>
                        <td>".$row["email"]."</td>
                        <td>".$row["subscribed_at"]."</td>
                        <td>
                            <a class='btn edit-btn' href='adminDashboard.php?edit_id=".$row["id"]."'>Edit</a>
                            <a class='btn delete-btn' href='adminDashboard.php?delete_id=".$row["id"]."' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                        </td>
                    </tr>";
                }
            }
        } else {
            echo "<tr><td colspan='5'>No records found</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
