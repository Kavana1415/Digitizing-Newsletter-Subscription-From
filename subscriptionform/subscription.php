<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$message = "";

// Database config
$servername = "localhost";
$username   = "root";      // default for XAMPP
$password   = "";          // default for XAMPP
$dbname     = "subscription_db";

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// ✅ Fix: Use correct table name "subscribers"
$deleteDuplicates = "
    DELETE s1 FROM subscribers s1
    INNER JOIN subscribers s2 
    ON s1.name = s2.name AND s1.email = s2.email AND s1.id > s2.id
";
$conn->query($deleteDuplicates);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO subscribers (name, email) VALUES (?, ?)");
    if ($stmt === false) {
        die("❌ SQL error in prepare(): " . $conn->error);
    }

    $stmt->bind_param("ss", $name, $email);

    if ($stmt->execute()) {
        // Emails setup
        $mailUser  = new PHPMailer(true);
        $mailAdmin = new PHPMailer(true);

        try {
            foreach ([$mailUser, $mailAdmin] as $mail) {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'kavanan1414@gmail.com'; // your Gmail
                $mail->Password   = 'kfowytinhgtqpxys';      // your Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                $mail->isHTML(true);
                $mail->setFrom('kavanan1414@gmail.com', 'BY KAVANA N');
            }

            // Send email to user
            $mailUser->addAddress($email);
            $mailUser->Subject = 'Thank You for Subscribing!';
            $mailUser->Body = "
                <h2 style='color: red;'>Hello $name!</h2>
                <h3 style='color: green;'>Thanks for Subscribing! 🎉</h3>
                <p>We appreciate your interest. Stay tuned for exciting updates!</p>
                <p style='color:blue;'>- Kavana N</p>";

            $mailUser->send();

            // Send email to admin
            $mailAdmin->addAddress('kavanan1414@gmail.com');
            $mailAdmin->Subject = 'New Subscriber Alert!';
            $mailAdmin->Body = "
                <h3>New Subscriber Registered</h3>
                <p>Name: <strong>$name</strong></p>
                <p>Email: <strong>$email</strong></p>";

            $mailAdmin->send();

            $message = "<div class='alert1'>✅ Thank you for subscribing! Check your inbox.</div>";
        } catch (Exception $e) {
            $message = "<div>❌ Email error: {$e->getMessage()}</div>";
        }
    } else {
        $message = "<div>❌ Could not save to database. Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subscription</title>
  <style>
    body {
      background-color: lightblue;
      font-family: Arial, sans-serif;
    }

    .card {
      background-color: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      transition: 0.3s;
      border-radius: 10px;
    }

    .card:hover {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    form {
      padding: 15px 32px;
      text-align: center;
      font-size: 16px;
    }

    input {
      padding: 10px;
      width: 280px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      padding: 10px;
      font-size: 16px;
      cursor: pointer;
      width: 280px;
      border: none;
      border-radius: 5px;
      margin-top: 5px;
    }

    button:hover {
      background-color: #04AA6D;
      color: white;
    }

    .alert1 {
      color: green;
      background-color: #ddffdd;
      border: 1px solid green;
      padding: 5px;
      margin: 10px 0;
      border-radius: 5px;
    }

    .alert2 {
      color: red;
      background-color: #ffe0e0;
      border: 1px solid red;
      padding: 5px;
      margin: 10px 0;
      border-radius: 5px;
    }
    
    a.admin-btn {
      display: inline-block;
      background-color: green;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      margin-top: 10px;
    }

    a.admin-btn:hover {
      background-color: darkgreen;
    }
  </style>
</head>
<body>
  <center>
    <div class="card" style="width: 350px; margin-top: 5%; padding: 30px;">
      <img src="maillll.jpg" alt="logo" width="90px" height="60px" style="margin-top: 30px;" />
      <h1>Become a Subscriber</h1>
      <p>Subscribe to our page and get latest updates straight to your inbox.</p>

      <form action="subscription.php" method="post">
         <?php 
  if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($message)) {
      echo $message; 
  }
  ?>
        <input type="text" name="name" placeholder="Enter username" required /><br><br>
        <input type="email" name="email" placeholder="Enter Email" required /><br><br>
        
        <button type="submit">Subscribe</button>
        
        <h4>We do not share your information</h4>
        <a href="/subscriptionform/aLogin.php" class="admin-btn">Admin Login</a>
        <h5>Only For Admin!</h5>
      </form>
    </div>
  </center>
</body>
</html>
