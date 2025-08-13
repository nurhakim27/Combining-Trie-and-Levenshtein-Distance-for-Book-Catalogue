<?php
require "session.php";
require "../koneksi.php";

// pastikan parameter p ada & integer
$id = isset($_GET['p']) ? (int)$_GET['p'] : 0;

// kalau id tidak valid (0), redirect atau kasih pesan error
if ($id <= 0) {
    echo '<div class="alert alert-danger mt-3" role="alert">ID berita tidak valid.</div>';
    exit;
}
$query = mysqli_query($con, "SELECT a.*, b.nama AS nama_kategori_berita  FROM produk_berita a JOIN kategori_berita b ON a.kategori_id=b.id WHERE a.id='$id'");
$data = mysqli_fetch_array($query);
$queryKategoriBerita = mysqli_query($con, "SELECT * FROM kategori_berita WHERE id!='{$data['kategori_id']}'");


// FUNCTION GENERATE RANDOM STRING UTK STORE NAMA COVER
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Berita Detail</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">
    <script src="https://cdn.tiny.cloud/1/zc21szb6it5hjdq55znj0ar26p80vj6l569kksuv2if9p4r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> <!-- LINK WYSIWYG -->


    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/informasi-berita-detail.css">

</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require "navbarAdmin.php";
    ?>
    <!-- REQUIRE NAVBAR END -->


    <div class="container">
        <h2 class="mt-5">Detail Berita</h2>

        <form action="" method="post" enctype="multipart/form-data" class="mt-4">
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div>
                        <label for="nama">Judul Berita</label>
                        <input type="text" name="nama" id="nama" class="form-control" value="<?php echo $data['nama'] ?>" autocomplete="FALSE" required>
                    </div>
                    <div>
                        <label for="kategoriBerita">Kategori Berita</label>
                        <select name="kategoriBerita" id="kategoriBerita" class="form-control" required>
                            <option value="<?php echo $data['kategori_id'] ?>" selected hidden><?php echo $data['nama_kategori_berita'] ?></option>
                            <?php while ($dataKategoriBerita = mysqli_fetch_array($queryKategoriBerita)) { ?>
                                <option value="<?= $dataKategoriBerita['id']; ?>"><?= $dataKategoriBerita['nama']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">

                    <div>
                        <label for="tanggal">Tanggal Berita</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo $data['tanggal']; ?>" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="foto">Foto Berita/Informasi</label>
                        <input type="file" class="form-control" name="foto">
                    </div>
                    <div class="mt-2">
                        <label for="currentFoto">foto Saat Ini</label><br>
                        <img src="../cover-berita/<?php echo $data['foto']; ?>" alt="" width="200px">
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <label for="detail" class="form-label">Detail</label>
                    <textarea id="detail" class="form-control" name="detail"><?php echo $data['detail']; ?></textarea>
                </div>
            </div>

            <div class="mb-5">
                <button type="submit" class="btn btn-primary" name="editBtn">Edit</button>
                <button type="submit" class="btn btn-danger" name="deleteBtn">Delete</button>
            </div>
        </form>

        <!-- UNTUK UPDATE -->
        <?php if (isset($_POST['editBtn'])) {
            // input baru
            $nama           = trim($_POST['nama']);
            $kategoriBerita = trim($_POST['kategoriBerita']);
            $tanggal        = trim($_POST['tanggal']);
            $detail         = trim($_POST['detail']);

            // file upload
            $target_dir   = "../cover-berita/";
            $nama_file    = basename($_FILES["foto"]["name"]);
            $target_file  = $target_dir . $nama_file;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $image_size   = $_FILES["foto"]["size"];
            $random_name  = generateRandomString(20);
            $new_name     = $random_name . "." . $imageFileType;

            if ($nama === '' || $kategoriBerita === '' || $tanggal === '') { ?>
                <div class="alert alert-warning mt-3" role="alert">
                    Nama, Kategori, dan Tanggal Berita Wajib Diisi!
                </div>
                <?php
            } else {
                // CEK: ada perubahan?
                $isChangedText =
                    ($nama !== $data['nama']) ||
                    ($kategoriBerita !== (string)$data['kategori_id']) ||
                    ($tanggal !== $data['tanggal']) ||
                    (trim($detail) !== trim($data['detail']));

                $hasNewPhoto = ($nama_file !== '');

                if (!$isChangedText && !$hasNewPhoto) { ?>
                    <div class="alert alert-warning mt-3" role="alert">
                        Tidak ada perubahan data.
                    </div>
                    <?php
                } else {
                    // kalau ada perubahan text -> update field text
                    if ($isChangedText) {
                        $queryUpdate = mysqli_query(
                            $con,
                            "UPDATE produk_berita 
                     SET kategori_id='$kategoriBerita',
                         nama='$nama',
                         tanggal='$tanggal',
                         detail='$detail'
                     WHERE id='$id'"
                        );
                        if (!$queryUpdate) {
                            echo '<div class="alert alert-danger mt-3" role="alert">Gagal mengupdate data: ' . mysqli_error($con) . '</div>';
                            // hentikan proses foto jika text gagal
                            exit;
                        }
                    }

                    // kalau ada foto baru -> validasi & update foto
                    if ($hasNewPhoto) {
                        if ($image_size > 20000000) { ?>
                            <div class="alert alert-warning mt-3" role="alert">
                                File Foto Berita Tidak Boleh Lebih Dari 20MB!
                            </div>
                        <?php
                        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'svg', 'jfif'])) { ?>
                            <div class="alert alert-warning mt-3" role="alert">
                                File Wajib Berformat JPG, JPEG, PNG, SVG, atau JFIF!
                            </div>
                            <?php
                        } else {
                            // 1) Upload dulu foto baru
                            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $new_name)) {

                                // 2) Update DB pakai nama foto baru
                                $queryUpdateFoto = mysqli_query($con, "UPDATE produk_berita SET foto='$new_name' WHERE id='$id'");
                                if (!$queryUpdateFoto) {
                                    // rollback upload baru kalau DB gagal (opsional)
                                    @unlink($target_dir . $new_name);
                                    echo '<div class="alert alert-danger mt-3" role="alert">Gagal mengupdate foto: ' . mysqli_error($con) . '</div>';
                                    exit;
                                }

                                // 3) Hapus foto lama setelah DB update sukses
                                $oldPhotoPath = $target_dir . $data['foto'];
                                if (!empty($data['foto']) && file_exists($oldPhotoPath)) {
                                    @unlink($oldPhotoPath);
                                }
                            } else { ?>
                                <div class="alert alert-danger mt-3" role="alert">
                                    Gagal mengunggah file foto.
                                </div>
                    <?php
                                exit;
                            }
                        }
                    }
                    ?>

                    <div class="alert alert-success mt-3" role="alert">
                        Berita Berhasil Diupdate
                    </div>
                    <meta http-equiv="refresh" content="2; url=informasi-berita.php">
        <?php
                }
            }
        } ?>
        <?php
        // UNTUK DELETE
        if (isset($_POST['deleteBtn'])) {
            // simpan path foto lama dulu
            $oldPhotoPath = "../cover-berita/" . $data['foto'];

            // hapus data dari DB
            $queryDelete = mysqli_query($con, "DELETE FROM produk_berita WHERE id='$id'");

            if ($queryDelete) {
                // kalau DB sukses, hapus file fisik kalau ada
                if (!empty($data['foto']) && file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                } ?>
                <div class="alert alert-success mt-3 mb-3" role="alert">
                    Berita Berhasil Dihapus
                </div>
                <meta http-equiv="refresh" content="1; url=informasi-berita.php">
        <?php
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert">Gagal menghapus data: ' . mysqli_error($con) . '</div>';
            }
        }
        ?>
    </div>










    <!-- REQUIRE FOOTER START -->
    <?php
    require "footerAdmin.php";
    ?>
    <!-- REQUIRE FOOTER END -->

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="../fontawesome/js/all.min.js"></script>

    <script>
        tinymce.init({
            selector: '#detail',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link image | preview',
            height: 500
        });
    </script>


</body>

</html>