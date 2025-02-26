<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Horikos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Daftar Akun</h3>
        <div id="alert-message"></div>
        <form id="register-form">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>
        <p class="text-center mt-3">
            Sudah punya akun? <a href="<?= site_url('auth/login'); ?>">Masuk</a>
        </p>
    </div>
</div>

<script>
document.getElementById("register-form").addEventListener("submit", async function(event) {
    event.preventDefault();

    let jsonData = {
        nama: document.getElementById("nama").value,
        email: document.getElementById("email").value,
        password: document.getElementById("password").value,
        confirm_password: document.getElementById("confirm_password").value
    };

    let alertMessage = document.getElementById("alert-message");

    try {
        let response = await fetch("<?= site_url('api/register'); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(jsonData)
        });

        let result = await response.json();
        alertMessage.innerHTML = `<div class="alert alert-${result.status === "success" ? "success" : "danger"}">${result.message}</div>`;

        if (result.status === "success") {
            document.getElementById("register-form").reset();
            setTimeout(() => window.location.href = "<?= site_url('auth/login'); ?>", 2000);
        }
    } catch (error) {
        alertMessage.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>`;
    }
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
