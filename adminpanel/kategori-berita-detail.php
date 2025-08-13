<?php
require "session.php";
require "../koneksi.php";

$id = $_GET['p'];
$query = mysqli_query($con, "SELECT * FROM kategori_berita WHERE id='$id'");
$data = mysqli_fetch_array($query);


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Berita Detail</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">

    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/kategori-berita-detail.css">

</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require "navbarAdmin.php";
    ?>
    <!-- REQUIRE NAVBAR END -->


    <div class="container">
        <h2 class="mt-5">Detail Kategori Berita</h2>

        <div class="col-12 col-md-6 mt-4">
            <form action="" method="post">
                <div>
                    <label for="kategoriBerita">Kategori Berita</label>
                    <input type="text" name="kategoriBerita" id="kategoriBerita" class="form-control" value="<?php echo $data['nama']; ?> " required>
                </div>
                <div class="mt-4  ">
                    <button type="submit" class="btn btn-primary" name="editBtn">Edit</button>
                    <button type="submit" class="btn btn-danger" name="deleteBtn">Delete</button>

                </div>
            </form>

            <!-- EDIT FUNCTION START -->
            <?php
            if (isset($_POST['editBtn'])) {
                $kategoriBerita = htmlspecialchars($_POST['kategoriBerita']);

                if ($data['nama'] == $kategoriBerita) {

            ?>
                    <meta http-equiv="refresh" content="0; url=kategori-berita.php" />

                    <?php
                } else {
                    $query = mysqli_query($con, "SELECT * FROM kategori_berita WHERE nama = '$kategoriBerita'");
                    $jumlahData = mysqli_num_rows($query);

                    if ($jumlahData > 0) {
                    ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            Kategori Sudah Ada
                        </div>
                        <?php
                    } else {
                        $querySimpan = mysqli_query($con, "UPDATE kategori_berita SET nama='$kategoriBerita' WHERE id = $id");
                        if ($querySimpan) {
                        ?>
                            <div class="alert alert-success mt-3" role="alert">
                                Kategori Berhasil Terupdate
                            </div>
                            <meta http-equiv="refresh" content="2; url=kategori-berita.php">

                    <?php
                        } else {
                            echo mysqli_error($con);
                        }
                    }
                }
            }
            // EDIT FUNCTION END


            // DELETE FUNCTION START
            if (isset($_POST['deleteBtn'])) {
                mysqli_report(MYSQLI_REPORT_OFF); // Matikan report agar kita bisa tangani error manual

                $queryDelete = mysqli_query($con, "DELETE FROM kategori_berita WHERE id=$id");

                if ($queryDelete) {
                    ?>
                    <div class="alert alert-success mt-3" role="alert">
                        Kategori Berhasil Dihapus
                    </div>
                    <meta http-equiv="refresh" content="2; url=kategori-berita.php">
                <?php
                } else {
                ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        Kategori Tidak Bisa Dihapus Karena Sudah Digunakan pada Berita.
                    </div>
            <?php
                    // Debugging opsional:
                    // echo mysqli_error($con);
                }
            }
            ?>
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