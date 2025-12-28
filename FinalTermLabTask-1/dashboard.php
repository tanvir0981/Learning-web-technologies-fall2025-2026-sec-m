<?php
session_start();

if (empty($_SESSION["logged_in"])) {
  header("Location: login.php");
  exit;
}

if (isset($_POST["logout"])) {
  $_SESSION = [];                

  if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
  }

  session_destroy();             
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <style>
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:#0f1115;
      color:#fff;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:20px;
    }
    .box{
      background:#151923;
      padding:28px;
      border-radius:16px;
      width:min(520px, 100%);
      text-align:center;
    }
    h1{ margin:0 0 18px 0; font-size:32px; }
    .btn{
      border:0;
      background:#b46a6d;
      color:#fff;
      padding:12px 26px;
      border-radius:999px;
      font-size:18px;
      font-weight:700;
      cursor:pointer;
    }
    .btn:hover{ opacity:.92; }
  </style>
</head>
<body>
  <div class="box">
    <h1>Welcome to Dashboard</h1>

    <form method="post">
      <button class="btn" type="submit" name="logout">Logout</button>
    </form>
  </div>
</body>
</html>
