<?php
session_start();

// Predefined username and password
$correctUsername = "admin";
$correctPassword = "12345";

$error = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  if ($username === $correctUsername && $password === $correctPassword) {
    // Store login status in session
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;

    // Redirect to dashboard
    header("Location: adminDashboard.php");
    exit();
  } else {
    $error = "Invalid username or password!";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Login Page</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>


</head>

<body>
  <div class="flex h-screen bg-indigo-200">
    <div class="w-full max-w-xs m-auto bg-indigo-100 rounded p-5">
      <header>
        <img class="w-20 mx-auto mb-5" src="https://img.icons8.com/fluent/344/year-of-tiger.png" />
      </header>
      <form method="POST">
        <div>
          <label class="block mb-2 text-indigo-500" for="username">Username</label>
          <input class="w-full p-2 mb-6 text-indigo-700 border-b-2 border-indigo-500 outline-none focus:bg-gray-300"
            type="text" name="username">
        </div>
        <div>
          <label class="block mb-2 text-indigo-500" for="password">Password</label>
          <input class="w-full p-2 mb-6 text-indigo-700 border-b-2 border-indigo-500 outline-none focus:bg-gray-300"
            type="password" name="password">
        </div>
        <div>
          <input class="w-full bg-indigo-700 hover:bg-pink-700 text-white font-bold py-2 px-4 mb-6 rounded"
            type="submit">
        </div>
        <div>
          <?php if ($error)
            echo "<p class='ml-7 text-red-500'>$error</p>"; ?>
        </div>
      </form>
      <footer>

      </footer>
    </div>
  </div>
</body>

</html>