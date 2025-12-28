<?php
session_start();

$IMG_FS = __DIR__ . "/picture/metro2.avif";
$IMG_URL = "/FinalTermLabTask-1/picture/metro2.avif";


if (!empty($_SESSION["logged_in"])) {
  header("Location: dashboard.php");
  exit;
}

function is_valid_email_simple($email) {
  if ($email === "") return false;
  if (strpos($email, " ") !== false) return false;

  $atPos = strpos($email, "@");
  if ($atPos === false) return false;
  if (strpos($email, "@", $atPos + 1) !== false) return false;
  if ($atPos === 0) return false;
  if ($atPos === strlen($email) - 1) return false;

  $local = substr($email, 0, $atPos);
  $domain = substr($email, $atPos + 1);

  if ($local === "" || $domain === "") return false;

  $dotPos = strpos($domain, ".");
  if ($dotPos === false) return false;
  if ($dotPos === 0) return false;
  if ($dotPos === strlen($domain) - 1) return false;

  return true;
}

$errors = ["email" => "", "password" => "", "general" => ""];
$old = ["email" => ""];
$success = "";

if (isset($_GET["registered"]) && $_GET["registered"] === "1") {
  $success = "Registration successful. Now log in.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";
  $old["email"] = $email;

  if ($email === "") {
    $errors["email"] = "Email is required.";
  } else if (!is_valid_email_simple($email)) {
    $errors["email"] = "Invalid email format.";
  }

  if ($password === "") {
    $errors["password"] = "Password is required.";
  }

  if ($errors["email"] === "" && $errors["password"] === "") {
    $users = $_SESSION["users"] ?? [];

    if (!isset($users[$email])) {
      $errors["general"] = "No account found for this email. Please register first.";
    } else {
      $hash = $users[$email]["password_hash"] ?? "";
      if (!$hash || !password_verify($password, $hash)) {
        $errors["general"] = "Incorrect email or password.";
      } else {
        $_SESSION["logged_in"] = true;
        $_SESSION["user"] = $users[$email];
        header("Location: dashboard.php");
        exit;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>MetroSheba | Login</title>
<style>
:root{
  --card-bg:#ffffff;
  --page-bg:#0f1115;
  --soft:#f6f0f1;
  --muted:#6b6b6b;
  --accent:#b46a6d;
  --accent-dark:#9d585b;
  --danger:#d12a2a;
  --radius:24px;
  --err:#b00020;
  --ok:#146c2e;
}
*{
  box-sizing:border-box;
}
body{
  margin:0;
  font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  background:var(--page-bg);
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:16px;
}
.shell{
  width:min(1180px, 100%);
  background:var(--card-bg);
  border-radius:var(--radius);
  overflow:hidden;
  box-shadow:0 12px 40px rgba(0,0,0,.35);
  display:flex;
  min-height:760px;
}
.left{
  flex:1.15;
  background:#ddd;
  overflow:hidden;
}
.left img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
.notfound{
  width:100%;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#eee;
  color:var(--err);
  font-weight:700;
  font-size:18px;
  padding:20px;
  text-align:center;
}
.right{
  flex:.85;
  padding:52px 56px;
  display:flex;
  align-items:center;
  justify-content:center;
}
.center{
  width:min(460px, 100%);
}
.title{
  font-size:52px;
  font-weight:900;
  color:var(--danger);
  text-align:center;
  margin-bottom:18px;
}
.notice{
  margin:0 0 14px 0;
  padding:12px 14px;
  border-radius:14px;
  font-size:14px;
  border:1px solid transparent;
}
.notice.error{
  background:#fff3f3;
  border-color:#ffd1d1;
  color:#8b1b1b;
}
.notice.ok{
  background:#f2fff5;
  border-color:#c8f3d3;
  color:var(--ok);
}
.form{
  display:grid;
  grid-template-columns:150px 1fr;
  gap:16px 18px;
  align-items:center;
  margin-top:10px;
}
label{
  font-size:24px;
  font-family:Georgia, "Times New Roman", serif;
  color:#222;
}
input[type="email"],
input[type="password"]{
  width:100%;
  padding:14px 18px;
  border-radius:999px;
  border:1px solid #e8e0e2;
  background:var(--soft);
  outline:none;
  font-size:16px;
}
input:focus{
  border-color:#d9c2c4;
}
.input-error{
  border-color:#ff9b9b !important;
}
.field-error{
  grid-column:2/3;
  color:var(--err);
  font-size:12px;
  margin-top:-10px;
  min-height:16px;
}
.row-inline{
  grid-column:2/3;
  display:flex;
  align-items:center;
  justify-content:space-between;
  font-size:12px;
  color:#666;
  margin-top:-4px;
}
.row-inline a{
  color:#6f6f6f;
  text-decoration:none;
}
.row-inline a:hover{
  text-decoration:underline;
}
.actions{
  grid-column:1/-1;
  display:flex;
  justify-content:center;
  margin-top:14px;
}
.btn{
  border:0;
  background:var(--accent);
  color:#fff;
  padding:14px 46px;
  border-radius:999px;
  font-size:20px;
  font-weight:700;
  cursor:pointer;
  box-shadow:0 10px 20px rgba(180,106,109,.28);
  transition:.15s ease;
}
.btn:hover{
  background:var(--accent-dark);
  transform:translateY(-1px);
}
.small-note{
  text-align:center;
  color:var(--muted);
  font-size:12px;
  margin-top:12px;
}
.small-note a{
  color:#333;
  text-decoration:none;
  font-weight:700;
}
.small-note a:hover{
  text-decoration:underline;
}
@media (max-width: 980px){
  .shell{
    flex-direction:column;
    min-height:auto;
  }
  .left{
    height:360px;
  }
  .right{
    padding:28px 18px;
  }
}
</style>
</head>
<body>
<div class="shell">
  <div class="left">
    <?php if (file_exists($IMG_FS)) : ?>
      <img src="<?php echo $IMG_URL; ?>" alt="MetroSheba" />
    <?php else : ?>
      <div class="notfound">Image not found</div>
    <?php endif; ?>
  </div>

  <div class="right">
    <div class="center">

      <?php if ($errors["general"] !== "") : ?>
        <div class="notice error"><?php echo htmlspecialchars($errors["general"]); ?></div>
      <?php endif; ?>

      <?php if ($success !== "") : ?>
        <div class="notice ok"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="title">Login</div>

      <form class="form" method="post" novalidate onsubmit="return validateLogin();">
        <label for="email">Email:</label>
        <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($old["email"]); ?>" placeholder="Enter email" />
        <div></div>
        <div class="field-error" id="err_email"><?php echo htmlspecialchars($errors["email"]); ?></div>

        <label for="password">Password:</label>
        <input id="password" name="password" type="password" placeholder="Enter password" />
        <div></div>
        <div class="field-error" id="err_password"><?php echo htmlspecialchars($errors["password"]); ?></div>

        <div class="row-inline">
          <label style="font-size:12px; font-family:inherit;">
            <input type="checkbox" name="remember" /> Remember me
          </label>
          <a href="#" onclick="setErr('err_general','Password reset is not added yet.'); return false;">Forget password?</a>
        </div>

        <div class="field-error" id="err_general"></div>

        <div class="actions">
          <button class="btn" type="submit">Log In</button>
        </div>

        <div class="small-note">
          Have not register yet? <a href="register.php">Register Now</a>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
function setErr(id, msg){
  document.getElementById(id).textContent = msg || "";
}

function markInput(el, isErr){
  if(!el) return;
  if(isErr){
    el.classList.add("input-error");
  }else{
    el.classList.remove("input-error");
  }
}

function isValidEmailSimple(v){
  var e = v.trim();
  if(e.length === 0){
    return "Email is required.";
  }
  if(e.indexOf(" ") !== -1){
    return "Email cannot contain spaces.";
  }
  var at1 = e.indexOf("@");
  if(at1 === -1){
    return "Email must contain @.";
  }
  if(e.indexOf("@", at1 + 1) !== -1){
    return "Email cannot contain multiple @.";
  }
  if(at1 === 0 || at1 === e.length - 1){
    return "Invalid email format.";
  }
  var local = e.substring(0, at1);
  var domain = e.substring(at1 + 1);
  if(local.length === 0 || domain.length === 0){
    return "Invalid email format.";
  }
  var dot = domain.indexOf(".");
  if(dot === -1){
    return "Domain must contain dot.";
  }
  if(dot === 0 || dot === domain.length - 1){
    return "Invalid domain format.";
  }
  return "";
}

function validateLogin(){
  var emailEl = document.getElementById("email");
  var passEl = document.getElementById("password");

  setErr("err_email", "");
  setErr("err_password", "");
  setErr("err_general", "");

  markInput(emailEl, false);
  markInput(passEl, false);

  var ok = true;

  var emsg = isValidEmailSimple(emailEl.value);
  if(emsg !== ""){
    setErr("err_email", emsg);
    markInput(emailEl, true);
    ok = false;
  }

  if(passEl.value.length === 0){
    setErr("err_password", "Password is required.");
    markInput(passEl, true);
    ok = false;
  }

  if(!ok){
    setErr("err_general", "Please fix the highlighted fields.");
  }

  return ok;
}

document.getElementById("email").addEventListener("input", validateLogin);
document.getElementById("password").addEventListener("input", validateLogin);
</script>
</body>
</html>
