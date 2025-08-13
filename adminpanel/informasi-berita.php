<?php
require "session.php";
require "../koneksi.php";

// SETUP PAGINATION
$limit = 5;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// SEARCH START
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$q_safe  = mysqli_real_escape_string($con, $q);
$where   = '';
if ($q !== '') {
    // cari di judul berita, kategori, atau tanggal (yyyy-mm-dd)
    $where = "WHERE (a.nama LIKE '%$q_safe%' OR b.nama LIKE '%$q_safe%' OR a.tanggal LIKE '%$q_safe%')";
}
// SEARCH END

// QUERY LIST + TOTAL (pakai WHERE kalau ada q)
$sqlList  = "SELECT a.*, b.nama AS nama_kategori_berita
             FROM produk_berita a
             JOIN kategori_berita b ON a.kategori_id=b.id
             $where
             ORDER BY a.id DESC
             LIMIT $start, $limit";
$query    = mysqli_query($con, $sqlList);

$sqlCount = "SELECT COUNT(*) AS jml
             FROM produk_berita a
             JOIN kategori_berita b ON a.kategori_id=b.id
             $where";
$resCount = mysqli_query($con, $sqlCount);
$rowCount = mysqli_fetch_assoc($resCount);
$totalData = (int)$rowCount['jml'];
$totalPage = ceil($totalData / $limit);

$jumlahProdukBerita = mysqli_num_rows($query);
// ambil kategori untuk dropdown form tambah berita
$queryKategoriBerita = mysqli_query($con, "SELECT * FROM kategori_berita");


// FUNCTION GENERATE RANDOM STRING UTK STORE NAMA FOTO
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
    <title>Informasi Berita</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">
    <script src="https://cdn.tiny.cloud/1/zc21szb6it5hjdq55znj0ar26p80vj6l569kksuv2if9p4r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> <!-- LINK WYSIWYG -->


    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/informasi-berita.css">

</head>

<body>

    <!-- REQUIRE NAVBAR START -->
    <?php
    require "navbarAdmin.php";
    ?>
    <!-- REQUIRE NAVBAR END -->



    <div class="container mt-5">
        <!-- BREADCUMB START -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page"><a href="dashboard.php" class="no-decor"><i class="bi bi-house-door-fill"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-newspaper"></i> Daftar Berita</li>

            </ol>
        </nav>
        <!-- BREADCUMB END -->


        <!-- FORM UTAMA -->
        <form action="" method="post" enctype="multipart/form-data" class="mt-5">
            <h4 class="mb-4">Tambah Berita/Informasi</h4>
            <div class="row ">

                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div>
                        <label for="nama">Judul Berita</label>
                        <input type="text" name="nama" id="nama" class="form-control" autocomplete="FALSE" required>
                    </div>
                    <div>
                        <label for="kategoriBerita">Kategori Berita</label>
                        <select name="kategoriBerita" id="kategoriBerita" class="form-control" required>
                            <option value="" selected hidden>Pilih Kategori</option>
                            <?php while ($data = mysqli_fetch_array($queryKategoriBerita)) { ?>
                                <option value="<?= $data['id']; ?>"><?= $data['nama']; ?></option>
                            <?php } ?>
                        </select>
                    </div>



                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div>
                        <label for="foto">Foto Berita</label>
                        <input type="file" class="form-control" name="foto">
                    </div>
                    <div>
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" autocomplete="FALSE" required>
                    </div>



                </div>

                <div class="">
                    <label for="detail" class="form-label">Detail</label>
                    <textarea id="detail" name="detail" class="form-control"></textarea>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-danger" name="simpan">Simpan</button>
            </div>
        </form>

        <!-- VALIDASI KETIKA BELUM ADA INPUT FORM BAGIAN BACKEND START -->
        <?php
        if (isset($_POST['simpan'])) {
            $nama = htmlspecialchars($_POST['nama']);
            $kategoriBerita = htmlspecialchars($_POST['kategoriBerita']);
            $tanggal = htmlspecialchars($_POST['tanggal']);
            $detail = $_POST['detail'];

            // UNTUK STORE COVER
            $target_dir = "../cover-berita/";
            $nama_file = basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $nama_file;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $image_size = $_FILES["foto"]["size"];
            $random_name = generateRandomString(20);
            $new_name = $random_name . "." . $imageFileType;



            if ($nama == '' || $kategoriBerita == '' || $tanggal == '') {
        ?>
                <div class="alert alert-warning mt-3" role="alert">
                    Nama, Kategori, dan Tanggal Berita Wajib Diisi!
                </div>
                <?php
            } else {
                if ($nama_file != '') {
                    if ($image_size > 20000000) {
                ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            File Foto Berita Tidak Boleh Lebih Dari 20MB!
                        </div>
                        <?php
                    } else {
                        if ($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'svg' && $imageFileType != 'jfif') {
                        ?>
                            <div class="alert alert-warning mt-3" role="alert">
                                File Wajib Berformat JPG, PNG, JPEG, SVG, atau JFIF!
                            </div>
                            <?php
                        } else {
                            // Pindahkan file dan baru lanjut insert
                            move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $new_name);

                            // QUERY INSERT
                            $queryTambah = mysqli_query($con, "INSERT INTO produk_berita (kategori_id, nama, tanggal, detail, foto) VALUES ('$kategoriBerita','$nama', '$tanggal','$detail','$new_name')");

                            if ($queryTambah) {
                            ?>
                                <div class="alert alert-success mt-3" role="alert">
                                    Berita Baru Berhasil Disimpan
                                </div>
                                <meta http-equiv="refresh" content="2; url=informasi-berita.php">
        <?php
                            } else {
                                echo mysqli_error($con);
                            }
                        }
                    }
                }
            }
        }
        ?>

        <!-- VALIDASI KETIKA BELUM ADA INPUT FORM BAGIAN BACKEND END -->



        <div class="mt-5 mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h3 class="mb-0">List Berita</h3>

                <form class="d-flex ms-auto mt-2 mt-sm-0" method="get" action="informasi-berita.php">
                    <input type="text"
                        class="form-control me-2"
                        name="q"
                        placeholder="Cari judul/kategori/tanggal"
                        value="<?= htmlspecialchars($q) ?>"
                        style="min-width:280px">
                    <button class="btn btn-outline-danger" type="submit">Cari</button>
                    <?php if ($q !== '') { ?>
                        <a href="informasi-berita.php" class="btn btn-danger ms-2">Reset</a>
                    <?php } ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 50%;">Judul</th>
                            <th style="width: 20%;">Kategori</th>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($jumlahProdukBerita == 0) {
                        ?>
                            <td class="text-center" colspan="5">Data Berita Tidak Tersedia</td>

                            <?php
                        } else {
                            $jumlah = $start + 1;
                            while ($data = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $jumlah ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['nama_kategori_berita']; ?></td>
                                    <td class="text-center"><?php echo $data['tanggal']; ?></td>
                                    <td class="text-center">
                                        <a href="informasi-berita-detail.php?p=<?php echo $data['id']; ?>" class="btn btn-sm btn-danger">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                        <?php
                                $jumlah++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <!-- PAGINATION -->

                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPage; $i++) { ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="informasi-berita.php?page=<?= $i ?><?= ($q !== '' ? '&q=' . urlencode($q) : '') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </nav>


            </div>
        </div>

    </div>


    <script>
        tinymce.init({
            selector: '#detail',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link image | preview',
            height: 300
        });
    </script>




    <!-- REQUIRE FOOTER START -->
    <?php
    require "footerAdmin.php";
    ?>
    <!-- REQUIRE FOOTER END -->



    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="../fontawesome/js/all.min.js"></script>

</body>

</html>