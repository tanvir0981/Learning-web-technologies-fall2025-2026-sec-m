<?php
session_start();

$IMG_FS  = __DIR__ . "/picture/metro.jpg";  
$IMG_URL = "/FinalTermLabTask-1/picture/metro.jpg";     
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MetroSheba | Home</title>
  <style>
    :root{
      --card-bg:#ffffff;
      --page-bg:#0f1115;
      --muted:#6b6b6b;
      --accent:#b46a6d;
      --accent-dark:#9d585b;
      --green:#1b6f2a;
      --danger:#d12a2a;
      --radius:24px;
    }
    *{box-sizing:border-box;}
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--page-bg);
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:16px;
    }
    .shell{
      width:min(1180px, 100%);
      background: var(--card-bg);
      border-radius: var(--radius);
      overflow:hidden;
      box-shadow: 0 12px 40px rgba(0,0,0,.35);
      display:flex;
      min-height: 760px;
    }
    .left{
      flex: 1.15;
      background:#ddd;
      overflow:hidden;
    }
    .left img{
       width:100%;
  height:100%;
  object-fit:cover;
  object-position: 40% center;
  display:block;
    }
    .notfound{
      width:100%;
      height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#eee;
      color:#b00020;
      font-weight:700;
      font-size:18px;
      padding:20px;
      text-align:center;
    }

    .right{
      flex: 0.85;
      padding: 52px 56px;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .center{ width: min(520px, 100%); }

    .tagline{ text-align:center; }
    .tagline .welcome{ font-size: 30px; font-weight: 500; }
    .tagline .name{ font-size: 54px; font-weight: 900; margin-top: 6px; }
    .tagline .online{
      font-size: 54px;
      font-weight: 900;
      color: var(--green);
      margin-top: 30px;
      font-family: Georgia, "Times New Roman", serif;
    }
    .tagline .ticketing{
      font-size: 22px;
      color: var(--danger);
      font-style: italic;
      margin-top: -6px;
      line-height: 1.2;
    }

    .btn-row{
      display:flex;
      gap: 20px;
      margin-top: 40px;
      justify-content:center;
    }
    .btn{
      border:0;
      background: var(--accent);
      color:#fff;
      padding: 16px 40px;
      border-radius: 999px;
      font-size: 20px;
      font-weight: 700;
      cursor:pointer;
      box-shadow: 0 10px 20px rgba(180,106,109,.28);
      transition: .15s ease;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      min-width: 170px;
    }
    .btn:hover{ background: var(--accent-dark); transform: translateY(-1px); }
    .btn:active{ transform: translateY(0); }

    .small-note{
      color: var(--muted);
      font-size: 12px;
      margin-top: 26px;
      text-align:center;
    }

    @media (max-width: 980px){
      .shell{flex-direction:column; min-height:auto;}
      .left{height: 360px;}
      .right{padding: 28px 18px;}
    }
  </style>
</head>
<body>
  <div class="shell">

    <div class="left">
      <?php if (file_exists($IMG_FS)) : ?>
        <img src="<?php echo $IMG_URL; ?>" alt="MetroSheba" />
      <?php else : ?>
        <div class="notfound">
          Image not found.<br>
          Expected: <b><?php echo htmlspecialchars($IMG_FS); ?></b>
        </div>
      <?php endif; ?>
    </div>

    <div class="right">
      <div class="center">
        <div class="tagline">
          <div class="welcome">Welcome To</div>
          <div class="name">MetroSheba</div>
          <div class="online">Online</div>
          <div class="ticketing">Ticketing<br/>Platform</div>
        </div>

        <div class="btn-row">
          <a class="btn" href="login.php">Log in</a>
          <a class="btn" href="register.php">Register</a>
        </div>
      </div>
    </div>

  </div>
</body>
</html>
