<?php
// 1. SƏHVİ ARADAN QALDIRMAQ ÜÇÜN ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';
session_start();

// 2. LOGOUT (ÇIXIŞ)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 3. QEYDİYYAT MƏNTİQİ
if (isset($_POST['register'])) {
    $reg_user = trim($_POST['reg_user']);
    $reg_pass = $_POST['reg_pass'];
    $hashed_pass = password_hash($reg_pass, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$reg_user, $hashed_pass]);
        $reg_success = "SUCCESS: IDENTITY CREATED!";
    } catch (Exception $e) {
        $reg_error = "ERROR: USERNAME EXISTS!";
    }
}

// 4. LOGİN MƏNTİQİ
// --- TƏHLÜKƏLİ LOGİN KODU (ZƏİF) ---
if (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    // DİQQƏT: Burada məlumat birbaşa sorğuya (query) qoyulur. BU BÖYÜK BOŞLUQDUR!
    $sql = "SELECT * FROM users WHERE username = '$user' AND password_hash = '$pass'";
    $stmt = $pdo->query($sql); 
    $userData = $stmt->fetch();

    if ($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "ACCESS DENIED!";
    }
}
// 5. PAROL ƏLAVƏ ETMƏK
if (isset($_POST['add_password']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO passwords (user_id, site_name, site_user, site_pass) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['site_name'], $_POST['site_user'], $_POST['site_pass']]);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Cyber-Safe | Elite Vault</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css?v=3">
    
    </head>
<body>
    <div class="cyber-overlay"></div>
    <div class="container">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="auth-header">
                <h1 class="glitch" data-text="CYBER-SAFE">CYBER-SAFE</h1>
                <p>v2.0 // ENCRYPTED STORAGE SYSTEM</p>
            </div>
            <div class="auth-wrapper">
                <div class="cyber-card">
                    <h3>SYSTEM_ACCESS</h3>
                    <form method="POST">
                        <input type="text" name="user" placeholder="_USERNAME_" required>
                        <input type="password" name="pass" placeholder="_PASSWORD_" required>
                        <button type="submit" name="login" class="btn-cyan">_AUTHENTICATE_</button>
                    </form>
                    <?php if(isset($error)) echo "<p style='color:red; font-size:12px;'>$error</p>"; ?>
                </div>
                <div class="cyber-card">
                    <h3>CREATE_ID</h3>
                    <form method="POST">
                        <input type="text" name="reg_user" placeholder="_NEW_USER_" required>
                        <input type="password" name="reg_pass" placeholder="_NEW_PASS_" required>
                        <button type="submit" name="register" class="btn-green">_REGISTER_</button>
                    </form>
                    <?php if(isset($reg_success)) echo "<p style='color:green; font-size:12px;'>$reg_success</p>"; ?>
                </div>
            </div>
        <?php else: ?>
    <div class="main-header">
        <h2>OPERATOR_:: <span style="color:#00f2fe;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></h2>
        <a href="?logout=1" class="btn-logout">_DISCONNECT_</a>
    </div>

    <div class="cyber-card" style="margin-bottom: 30px;">
        <h3>_ENCRYPT_NEW_SECRET</h3>
        <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: center;">
            <input type="text" name="site_name" placeholder="_RESOURCE (məs: Google)" required style="margin: 0;">
            <input type="text" name="site_user" placeholder="_IDENTIFIER" required style="margin: 0;">
            <input type="password" name="site_pass" placeholder="_SECRET_KEY" required style="margin: 0;">
            <button type="submit" name="add_password" class="btn-cyan" style="width: auto; padding: 12px 25px; margin: 0;">_SAVE_</button>
        </form>
    </div>

    <div class="cyber-card">
        <h3>_SECURE_DATA_VAULT_</h3>
        <table>
            <thead>
                <tr>
                    <th>_RESOURCE_</th>
                    <th>_USER_</th>
                    <th>_SECRET_</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM passwords WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $count = 0;
                while ($row = $stmt->fetch()) {
                    $count++;
                    echo "<tr>
                            <td style='color:#fff; font-weight:bold;'>".htmlspecialchars($row['site_name'])."</td>
                            <td>".htmlspecialchars($row['site_user'])."</td>
                            <td><code class='cyber-code'>".htmlspecialchars($row['site_pass'])."</code></td>
                          </tr>";
                }
                
                if ($count == 0) {
                    echo "<tr><td colspan='3' style='text-align:center; opacity:0.5;'>_NO_ENCRYPTED_DATA_FOUND_</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>