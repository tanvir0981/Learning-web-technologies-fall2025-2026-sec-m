<?php
session_start();


$IMG_FS  = __DIR__ . "/picture/metro3.jpg";
$IMG_URL = "/FinalTermLabTask-1/picture/metro3.jpg";

function is_digits_only($s) {
  return $s !== "" && ctype_digit($s);
}

function has_letter($s) {
  for ($i=0; $i<strlen($s); $i++) {
    $ch = $s[$i];
    if (ctype_alpha($ch)) return true;
  }
  return false;
}

function has_digit($s) {
  for ($i=0; $i<strlen($s); $i++) {
    $ch = $s[$i];
    if (ctype_digit($ch)) return true;
  }
  return false;
}

function is_valid_name_simple($name) {
  if ($name === "") return false;
  for ($i=0; $i<strlen($name); $i++) {
    $ch = $name[$i];
    $ok = ctype_alpha($ch) || $ch === " " || $ch === "." || $ch === "-" || $ch === "'";
    if (!$ok) return false;
  }
  return true;
}

function is_valid_email_simple($email) {
  if ($email === "") return false;
  if (strpos($email, " ") !== false) return false;

  $atPos = strpos($email, "@");
  if ($atPos === false) return false;

  if (strpos($email, "@", $atPos + 1) !== false) return false;

  if ($atPos === 0 || $atPos === strlen($email)-1) return false;

  $local = substr($email, 0, $atPos);
  $domain = substr($email, $atPos + 1);

  if ($local === "" || $domain === "") return false;

  $dotPos = strpos($domain, ".");
  if ($dotPos === false) return false;
  if ($dotPos === 0 || $dotPos === strlen($domain)-1) return false;

  return true;
}

function json_response($arr){
  header("Content-Type: application/json");
  echo json_encode($arr);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
  $action = $_POST["action"];

  if ($action === "send_code") {
    $mobile = trim($_POST["mobile"] ?? "");
    $mobile = str_replace(" ", "", $mobile);

    if (!is_digits_only($mobile) || strlen($mobile) < 10 || strlen($mobile) > 15) {
      json_response(["ok"=>false, "msg"=>"Mobile must be digits only (10-15 digits)."]);
    }

    $otp = (string)random_int(100000, 999999);
    $_SESSION["otp"] = $otp;
    $_SESSION["otp_time"] = time();
    $_SESSION["otp_verified"] = false;

    json_response(["ok"=>true, "msg"=>"Code generated (demo).", "code"=>$otp]);
  }

  if ($action === "verify_code") {
    $code = trim($_POST["code"] ?? "");
    $sess = $_SESSION["otp"] ?? "";
    $ts   = $_SESSION["otp_time"] ?? 0;

    if ($sess === "") json_response(["ok"=>false, "msg"=>"Click Get Code first."]);
    if ((time() - $ts) > 300) {
      unset($_SESSION["otp"], $_SESSION["otp_time"]);
      $_SESSION["otp_verified"] = false;
      json_response(["ok"=>false, "msg"=>"Code expired. Generate again."]);
    }
    if ($code !== $sess) {
      $_SESSION["otp_verified"] = false;
      json_response(["ok"=>false, "msg"=>"Incorrect code."]);
    }

    $_SESSION["otp_verified"] = true;
    json_response(["ok"=>true, "msg"=>"Verification successful."]);
  }

  json_response(["ok"=>false, "msg"=>"Invalid action."]);
}

$errors = []; // field errors
$old = [
  "name" => "",
  "email" => "",
  "nid" => "",
  "mobile" => "",
  "gender" => "",
  "code" => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["action"])) {
  $name   = trim($_POST["name"] ?? "");
  $email  = trim($_POST["email"] ?? "");
  $nid    = trim($_POST["nid"] ?? "");
  $mobile = trim($_POST["mobile"] ?? "");
  $mobile = str_replace(" ", "", $mobile);
  $gender = $_POST["gender"] ?? "";
  $pass   = $_POST["password"] ?? "";
  $pass2  = $_POST["password2"] ?? "";
  $code   = trim($_POST["code"] ?? "");

  $old["name"] = $name;
  $old["email"] = $email;
  $old["nid"] = $nid;
  $old["mobile"] = $mobile;
  $old["gender"] = $gender;
  $old["code"] = $code;


  if ($name === "") $errors["name"] = "Name is required.";
  else if (strlen($name) < 2) $errors["name"] = "Name must be at least 2 characters.";
  else if (!is_valid_name_simple($name)) $errors["name"] = "Name can contain letters, space, . - ' only.";

  
  if ($email === "") $errors["email"] = "Email is required.";
  else if (!is_valid_email_simple($email)) $errors["email"] = "Invalid email format.";

  if ($nid === "") $errors["nid"] = "NID is required.";
  else if (!is_digits_only($nid) || strlen($nid) < 6 || strlen($nid) > 20) $errors["nid"] = "NID must be digits only (6-20 digits).";

  
  if ($mobile === "") $errors["mobile"] = "Mobile number is required.";
  else if (!is_digits_only($mobile) || strlen($mobile) < 10 || strlen($mobile) > 15) $errors["mobile"] = "Mobile must be digits only (10-15 digits).";

  if ($gender === "") $errors["gender"] = "Please select gender.";
  else if ($gender !== "male" && $gender !== "female") $errors["gender"] = "Invalid gender selection.";

  if ($pass === "") $errors["password"] = "Password is required.";
  else if (strlen($pass) < 6) $errors["password"] = "Password must be at least 6 characters.";
  else {
    if (!has_letter($pass)) $errors["password"] = "Password must contain at least 1 letter.";
    else if (!has_digit($pass)) $errors["password"] = "Password must contain at least 1 number.";
  }

  if ($pass2 === "") $errors["password2"] = "Please re-type password.";
  else if ($pass !== $pass2) $errors["password2"] = "Passwords do not match.";

  if ($code === "") $errors["code"] = "Verification code is required.";


  if (empty($_SESSION["otp_verified"])) $errors["otp"] = "Please verify your mobile (Get Code + Verify) before registering.";

  $users = $_SESSION["users"] ?? [];
  if (!isset($errors["email"]) && isset($users[$email])) {
    $errors["email"] = "This email is already registered. Please log in.";
  }

  if (empty($errors)) {
    $users[$email] = [
      "name" => $name,
      "email" => $email,
      "nid" => $nid,
      "mobile" => $mobile,
      "gender" => $gender,
      "password_hash" => password_hash($pass, PASSWORD_DEFAULT),
    ];
    $_SESSION["users"] = $users;
    $_SESSION["otp_verified"] = false;

    header("Location: login.php?registered=1");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MetroSheba | Register</title>
  <style>
    :root{
      --card-bg:#ffffff; --page-bg:#0f1115; --soft:#f6f0f1; --muted:#6b6b6b;
      --accent:#b46a6d; --accent-dark:#9d585b; --green:#1b6f2a; --radius:24px;
      --err:#b00020;
    }
    *{
      box-sizing:border-box;
    }
    body{
       margin:0; 
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--page-bg);
       min-height:100vh; 
       display:flex; 
       align-items:center; 
       justify-content:center;
        padding:16px;}
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
      flex:1.15;
       background:#ddd;
       align-items:center;
       justify: center;
        overflow:hidden; }
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
      color:var(--err);
       font-weight:700; 
       font-size:18px;
        padding:20px;
         text-align:center; 
        }

    .right{ flex:.85; padding:44px 48px; display:flex; align-items:center; justify-content:center; }
    .center{ width:min(560px, 100%); position:relative; }

    .top-link{ position:absolute; top:-6px; left:0; }
    .small-btn{ background: var(--accent); color:#fff; border:0; border-radius:999px; padding:10px 18px;
      font-weight:700; cursor:pointer; text-decoration:none; display:inline-block; }
    .small-btn:hover{ background: var(--accent-dark); }

    .title{ font-size:54px; font-weight:900; color: var(--green); text-align:center; margin: 10px 0 18px 0; }

    .register-form{ display:grid; grid-template-columns: 170px 1fr 120px; gap: 14px 16px; align-items:center; }
    .register-form label{ font-size: 14px; color:#222; }
    input[type="text"], input[type="email"], input[type="password"], input[type="tel"]{
      width:100%; padding:14px 18px; border-radius:999px; border:1px solid #e8e0e2; background: var(--soft);
      outline:none; font-size:14px; grid-column: 2 / 3;
    }
    input:focus{ border-color:#d9c2c4; }
    .btn-mini{ grid-column: 3 / 4; justify-self:end; border:0; background: var(--accent); color:#fff;
      border-radius:999px; padding:10px 14px; cursor:pointer; font-weight:700; font-size:13px; min-width:96px; }
    .btn-mini:hover{ background: var(--accent-dark); }

    .gender{ grid-column: 2 / 4; display:flex; gap:22px; align-items:center; font-size:14px; color:#222; }

    .field-error{ grid-column: 2 / 4; color: var(--err); font-size:12px; margin-top:-8px; min-height:16px; }
    .input-error{ border-color:#ff9b9b !important; }

    .status-line{ grid-column: 1 / -1; font-size:12px; color: var(--muted); text-align:center; min-height:16px; }

    .submit-row{ grid-column: 1 / -1; display:flex; justify-content:center; margin-top:10px; }
    .btn{ border:0; background: var(--accent); color:#fff; padding: 14px 56px; border-radius:999px;
      font-size:22px; font-weight:800; cursor:pointer; box-shadow:0 10px 20px rgba(180,106,109,.28); transition:.15s ease; }
    .btn:hover{ background: var(--accent-dark); transform: translateY(-1px); }

    .helper{ grid-column: 1 / -1; font-size:12px; color: var(--muted); margin-top:-6px; text-align:center; }

    @media (max-width: 980px){
      .shell{flex-direction:column; min-height:auto;}
      .left{height: 360px;}
      .right{padding: 28px 18px;}
      .register-form{ grid-template-columns: 1fr; }
      .register-form label, input, .btn-mini, .gender, .field-error{ grid-column: 1 / -1; }
      .btn-mini{ justify-self:start; width:120px; }
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
        <div class="top-link">
          <a class="small-btn" href="login.php">Log In</a>
        </div>

        <div class="title">Register</div>

        <form id="regForm" class="register-form" method="post" novalidate onsubmit="return validateAll();">
          <label for="name">Name:</label>
          <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($old["name"]); ?>" placeholder="Enter name" />
          <div></div>
          <div class="field-error" id="err_name"><?php echo htmlspecialchars($errors["name"] ?? ""); ?></div>

          <label for="email">E-mail Address:</label>
          <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($old["email"]); ?>" placeholder="Enter email" />
          <div></div>
          <div class="field-error" id="err_email"><?php echo htmlspecialchars($errors["email"] ?? ""); ?></div>

          <label for="nid">NID NO:</label>
          <input id="nid" name="nid" type="text" value="<?php echo htmlspecialchars($old["nid"]); ?>" placeholder="Digits only" />
          <div></div>
          <div class="field-error" id="err_nid"><?php echo htmlspecialchars($errors["nid"] ?? ""); ?></div>

          <label for="mobile">Mobile Number:</label>
          <input id="mobile" name="mobile" type="tel" value="<?php echo htmlspecialchars($old["mobile"]); ?>" placeholder="Digits only" />
          <button type="button" class="btn-mini" onclick="getCode()">Get Code</button>
          <div class="field-error" id="err_mobile"><?php echo htmlspecialchars($errors["mobile"] ?? ""); ?></div>

          <label>Gender:</label>
          <div class="gender" id="genderWrap">
            <label><input type="radio" name="gender" value="male" <?php echo ($old["gender"]==="male")?"checked":""; ?> /> male</label>
            <label><input type="radio" name="gender" value="female" <?php echo ($old["gender"]==="female")?"checked":""; ?> /> female</label>
          </div>
          <div></div>
          <div class="field-error" id="err_gender"><?php echo htmlspecialchars($errors["gender"] ?? ""); ?></div>

          <label for="password">Password:</label>
          <input id="password" name="password" type="password" placeholder="Min 6 chars, include letter+number" />
          <div></div>
          <div class="field-error" id="err_password"><?php echo htmlspecialchars($errors["password"] ?? ""); ?></div>

          <label for="password2">Re-Type Password:</label>
          <input id="password2" name="password2" type="password" placeholder="Repeat password" />
          <div></div>
          <div class="field-error" id="err_password2"><?php echo htmlspecialchars($errors["password2"] ?? ""); ?></div>

          <label for="code">Verification code:</label>
          <input id="code" name="code" type="text" value="<?php echo htmlspecialchars($old["code"]); ?>" placeholder="Enter code" />
          <button type="button" class="btn-mini" onclick="verifyCode()">Verify</button>
          <div class="field-error" id="err_code"><?php echo htmlspecialchars($errors["code"] ?? ""); ?></div>

          <div class="status-line" id="statusLine"><?php echo htmlspecialchars($errors["otp"] ?? ""); ?></div>

          <div class="submit-row">
            <button class="btn" type="submit">Confirm</button>
          </div>

          <div class="helper">
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
const statusLine = document.getElementById("statusLine");

function setStatus(txt){ statusLine.textContent = txt || ""; }
function setErr(id, msg){ document.getElementById(id).textContent = msg || ""; }
function markInput(el, isErr){ if(!el) return; el.classList.toggle("input-error", !!isErr); }

function isLetter(ch){
  const c = ch.charCodeAt(0);
  return (c>=65 && c<=90) || (c>=97 && c<=122);
}
function isDigit(ch){
  const c = ch.charCodeAt(0);
  return (c>=48 && c<=57);
}
function isValidNameSimple(v){
  const t = v.trim();
  if(t.length === 0) return "Name is required.";
  if(t.length < 2) return "Name must be at least 2 characters.";
  for(let i=0;i<t.length;i++){
    const ch = t[i];
    const ok = isLetter(ch) || ch===" " || ch==="." || ch==="-" || ch=="'";
    if(!ok) return "Name can contain letters, space, . - ' only.";
  }
  return "";
}
function isValidEmailSimple(v){
  const e = v.trim();
  if(e.length === 0) return "Email is required.";
  if(e.indexOf(" ") !== -1) return "Email cannot contain spaces.";

  const at1 = e.indexOf("@");
  if(at1 === -1) return "Email must contain @.";
  if(e.indexOf("@", at1+1) !== -1) return "Email cannot contain multiple @.";
  if(at1 === 0 || at1 === e.length-1) return "Invalid email format.";

  const local = e.substring(0, at1);
  const domain = e.substring(at1+1);
  if(local.length === 0 || domain.length === 0) return "Invalid email format.";

  const dot = domain.indexOf(".");
  if(dot === -1) return "Domain must contain dot.";
  if(dot === 0 || dot === domain.length-1) return "Invalid domain format.";

  return "";
}
function isDigitsOnly(str){
  if(str.length === 0) return false;
  for(let i=0;i<str.length;i++){
    if(!isDigit(str[i])) return false;
  }
  return true;
}
function passwordHasLetter(p){
  for(let i=0;i<p.length;i++) if(isLetter(p[i])) return true;
  return false;
}
function passwordHasDigit(p){
  for(let i=0;i<p.length;i++) if(isDigit(p[i])) return true;
  return false;
}

function validateAll(){
  const name = document.getElementById("name");
  const email = document.getElementById("email");
  const nid = document.getElementById("nid");
  const mobile = document.getElementById("mobile");
  const pass = document.getElementById("password");
  const pass2 = document.getElementById("password2");
  const code = document.getElementById("code");
  const genderChecked = document.querySelector('input[name="gender"]:checked');

  let ok = true;

  let m = isValidNameSimple(name.value);
  setErr("err_name", m); markInput(name, m); if(m) ok=false;

  m = isValidEmailSimple(email.value);
  setErr("err_email", m); markInput(email, m); if(m) ok=false;

  const nidVal = nid.value.trim();
  if(nidVal.length === 0) m = "NID is required.";
  else if(!isDigitsOnly(nidVal) || nidVal.length < 6 || nidVal.length > 20) m = "NID must be digits only (6-20 digits).";
  else m = "";
  setErr("err_nid", m); markInput(nid, m); if(m) ok=false;

  const mobVal = mobile.value.trim().split(" ").join("");
  if(mobVal.length === 0) m = "Mobile number is required.";
  else if(!isDigitsOnly(mobVal) || mobVal.length < 10 || mobVal.length > 15) m = "Mobile must be digits only (10-15 digits).";
  else m = "";
  setErr("err_mobile", m); markInput(mobile, m); if(m) ok=false;

  if(!genderChecked){
    setErr("err_gender", "Please select gender.");
    ok=false;
  } else setErr("err_gender", "");

  const p = pass.value;
  if(p.length === 0) m = "Password is required.";
  else if(p.length < 6) m = "Password must be at least 6 characters.";
  else if(!passwordHasLetter(p)) m = "Password must contain at least 1 letter.";
  else if(!passwordHasDigit(p)) m = "Password must contain at least 1 number.";
  else m = "";
  setErr("err_password", m); markInput(pass, m); if(m) ok=false;

  if(pass2.value.length === 0) m = "Please re-type password.";
  else if(pass2.value !== pass.value) m = "Passwords do not match.";
  else m = "";
  setErr("err_password2", m); markInput(pass2, m); if(m) ok=false;

  if(code.value.trim().length === 0) m = "Verification code is required.";
  else m = "";
  setErr("err_code", m); markInput(code, m); if(m) ok=false;

  if(!ok) setStatus("Please fix the highlighted fields.");
  else setStatus("");

  return ok;
}

["name","email","nid","mobile","password","password2","code"].forEach(id=>{
  document.getElementById(id).addEventListener("input", validateAll);
});
document.querySelectorAll('input[name="gender"]').forEach(r=>{
  r.addEventListener("change", validateAll);
});

async function postAction(action, data){
  const form = new URLSearchParams();
  form.set("action", action);
  for(const k in data) form.set(k, data[k]);

  const res = await fetch("register.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: form.toString()
  });
  return res.json();
}

async function getCode(){
  const mobileEl = document.getElementById("mobile");
  const mobVal = mobileEl.value.trim().split(" ").join("");

  let msg = "";
  if(mobVal.length === 0) msg = "Mobile number is required.";
  else if(!isDigitsOnly(mobVal) || mobVal.length < 10 || mobVal.length > 15) msg = "Mobile must be digits only (10-15 digits).";

  setErr("err_mobile", msg);
  markInput(mobileEl, msg);
  if(msg){ setStatus("Fix Mobile Number first."); return; }

  setStatus("Generating code...");
  const j = await postAction("send_code", { mobile: mobVal });
  if(!j.ok){ setStatus(j.msg); return; }
  setStatus(j.msg + " Demo Code: " + j.code);
}

async function verifyCode(){
  const codeEl = document.getElementById("code");
  const val = codeEl.value.trim();

  if(val.length === 0){
    setErr("err_code", "Verification code is required.");
    markInput(codeEl, true);
    setStatus("Enter verification code first.");
    return;
  }

  setErr("err_code", "");
  markInput(codeEl, false);

  setStatus("Verifying...");
  const j = await postAction("verify_code", { code: val });
  setStatus(j.msg);
}
</script>
</body>
</html>
