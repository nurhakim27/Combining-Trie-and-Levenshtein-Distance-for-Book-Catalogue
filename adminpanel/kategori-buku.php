<?php
require "session.php";
require "../koneksi.php";


$queryKategoriBuku = mysqli_query($con, "SELECT * FROM kategori_buku ORDER BY id DESC");
$jumlahKategoriBuku = mysqli_num_rows($queryKategoriBuku);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Buku</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">

    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/kategori-buku.css">
</head>

<body>

    <!-- REQUIRE NAVBAR START -->
    <?php
    require "navbarAdmin.php";
    ?>
    <!-- REQUIRE NAVBAR END -->


    <div class="container mt-5 ">

        <!-- BREADCUMB START -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page"><a href="dashboard.php" class="no-decor"><i class="bi bi-house-door-fill"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-bookmark-fill"></i> Kategori Buku</li>

            </ol>
        </nav>
        <!-- BREADCUMB END -->

        <div class="my-5 col-12 col-md-6">
            <h4 class="mb-4">Tambah Kategori Buku</h4>

            <form action="" method="post">
                <div>
                    <input type="text" id="kategoriBuku" name="kategoriBuku" placeholder="Input Nama Kategori Buku" class="form-control" required>

                </div>
                <div class="mt-3">
                    <button class="btn btn-danger" type="submit" name="simpanKategoriBuku">Simpan</button>
                </div>


            </form>
            <?php
            if (isset($_POST['simpanKategoriBuku'])) {
                $kategoriBuku = htmlspecialchars($_POST['kategoriBuku']);

                $kategoriBukuExist = mysqli_query($con, "SELECT nama FROM kategori_buku WHERE nama = '$kategoriBuku'");
                $jumlahDataKategoriBukuBaru = mysqli_num_rows($kategoriBukuExist);

                if ($jumlahDataKategoriBukuBaru > 0) {
            ?>
                    <div class="alert alert-warning mt-3" role="alert">
                        Kategori Sudah Ada
                    </div>

                    <?php
                } else {
                    $user_id = $_SESSION['id'];
                    $querySimpan = mysqli_query($con, "INSERT INTO kategori_buku (nama, user_id) VALUES ('$kategoriBuku','$user_id')");
                    if ($querySimpan) {
                    ?>
                        <div class="alert alert-success mt-3" role="alert">
                            Kategori Baru Berhasil Disimpan
                        </div>
                        <meta http-equiv="refresh" content="2; url=kategori-buku.php">

            <?php
                    } else {
                        echo mysqli_error($con);
                    }
                }
            }
            ?>
        </div>

        <div class="mt-3 mb-5">
            <h3 class="mb-4">List Kategori</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 70%;">Nama Kategori</th>
                            <th style="width: 25%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jumlahKategoriBuku == 0) : ?>
                            <tr>
                                <td colspan="3" class="text-center">Data Kategori Buku Tidak Tersedia</td>
                            </tr>
                            <?php else :
                            $jumlah = 1;
                            while ($data = mysqli_fetch_array($queryKategoriBuku)) : ?>
                                <tr>
                                    <td class="text-center"><?php echo $jumlah++; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td class="text-center">
                                        <a href="kategori-buku-detail.php?p=<?php echo $data['id']; ?>" class="btn btn-sm btn-danger">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>




















    <!-- REQUIRE FOOTER START -->
    <?php
    require "footerAdmin.php";
    ?>
    <!-- REQUIRE FOOTER END -->

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="../fontawesome/js/all.min.js"></script>
</body>

</html>