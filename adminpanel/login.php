<?php
session_start();
require "../koneksi.php";

if (isset($_POST['loginbtn'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $query = mysqli_query($con, "SELECT * FROM users WHERE username ='$username'");
    $countdata = mysqli_num_rows($query);
    $data = mysqli_fetch_array($query);

    if ($countdata > 0 && password_verify($password, $data['password'])) {
        $_SESSION['username'] = $data['username'];
        $_SESSION['id'] = $data['id'];
        $_SESSION['login'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <div class="login-box">
        <h4 class="text-center fw-bold mb-4">LOGIN ADMIN</h4>
        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-secondary" name="loginbtn">Login</button>
            </div>
        </form>

        <!-- TAMPILKAN ALERT JIKA LOGIN GAGAL -->
        <?php if (isset($error)) : ?>
            <div id="login-alert" class="alert alert-warning mt-3 text-center" role="alert">
                <?= $error ?>
            </div>
            <script>
                setTimeout(() => {
                    const alertBox = document.getElementById('login-alert');
                    if (alertBox) {
                        alertBox.style.display = 'none';
                    }
                }, 5000); // sembunyikan setelah 5 detik
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>