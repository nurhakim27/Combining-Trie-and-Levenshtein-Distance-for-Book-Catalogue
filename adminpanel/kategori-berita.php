<?php
require "session.php";
require "../koneksi.php";


$queryKategoriBerita = mysqli_query($con, "SELECT * FROM kategori_berita ORDER BY id DESC");
$jumlahKategoriBerita = mysqli_num_rows($queryKategoriBerita);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Berita</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">

    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/kategori-berita.css">
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
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-tags-fill"></i> Kategori Berita</li>

            </ol>
        </nav>
        <!-- BREADCUMB END -->

        <div class="my-5 col-12 col-md-6">
            <h4 class="mb-4">Tambah Kategori Berita</h4>

            <form action="" method="post">
                <div>
                    <input type="text" id="kategoriBerita" name="kategoriBerita" placeholder="Input Nama Kategori Berita" class="form-control" required>

                </div>
                <div class="mt-3">
                    <button class="btn btn-danger" type="submit" name="simpanKategoriBerita">Simpan</button>
                </div>


            </form>
            <?php
            if (isset($_POST['simpanKategoriBerita'])) {
                $kategoriBerita = htmlspecialchars($_POST['kategoriBerita']);

                $kategoriBeritaExist = mysqli_query($con, "SELECT nama FROM kategori_berita WHERE nama = '$kategoriBerita'");
                $jumlahDatakategoriBeritaBaru = mysqli_num_rows($kategoriBeritaExist);

                if ($jumlahDatakategoriBeritaBaru > 0) {
            ?>
                    <div class="alert alert-warning mt-3" role="alert">
                        Kategori Sudah Ada
                    </div>

                    <?php
                } else {
                    $user_id = $_SESSION['id'];
                    $querySimpan = mysqli_query($con, "INSERT INTO kategori_berita (nama, user_id) VALUES ('$kategoriBerita', '$user_id')");

                    if ($querySimpan) {
                    ?>
                        <div class="alert alert-success mt-3" role="alert">
                            Kategori Baru Berhasil Disimpan
                        </div>
                        <meta http-equiv="refresh" content="2; url=kategori-berita.php">

            <?php
                    } else {
                        echo mysqli_error($con);
                    }
                }
            }
            ?>
        </div>

        <div class="mt-3 mb-5">
            <h3 class="mb-4">List Kategori Berita</h3>
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
                        <?php if ($jumlahKategoriBerita == 0) : ?>
                            <tr>
                                <td colspan="3" class="text-center">Data Kategori Berita Tidak Tersedia</td>
                            </tr>
                            <?php else :
                            $jumlah = 1;
                            while ($data = mysqli_fetch_array($queryKategoriBerita)) : ?>
                                <tr>
                                    <td class="text-center"><?php echo $jumlah++; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td class="text-center">
                                        <a href="kategori-berita-detail.php?p=<?php echo $data['id']; ?>" class="btn btn-sm btn-danger">
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