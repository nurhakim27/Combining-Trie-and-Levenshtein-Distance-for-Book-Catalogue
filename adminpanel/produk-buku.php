<?php
require "session.php";
require "../koneksi.php";


// SETUP PAGINATION
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// SEARCH
$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$q_safe = mysqli_real_escape_string($con, $q);
$where  = '';
if ($q !== '') {
    $where = "WHERE (
        a.nama LIKE '%$q_safe%' OR
        b.nama LIKE '%$q_safe%' OR
        a.isbn LIKE '%$q_safe%' OR
        a.penulis LIKE '%$q_safe%' OR
        a.penerbit LIKE '%$q_safe%' OR
        a.tahun LIKE '%$q_safe%'
    )";
}

// LIST QUERY (pakai WHERE kalau ada q)
$sqlList = "SELECT a.*, b.nama AS nama_kategori_buku
            FROM produk_buku a
            JOIN kategori_buku b ON a.kategori_id=b.id
            $where
            ORDER BY a.id DESC
            LIMIT $start, $limit";
$query = mysqli_query($con, $sqlList);

// TOTAL (untuk pagination)
$sqlCount  = "SELECT COUNT(*) AS jml
              FROM produk_buku a
              JOIN kategori_buku b ON a.kategori_id=b.id
              $where";
$resCount  = mysqli_query($con, $sqlCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalData = (int)$rowCount['jml'];
$totalPage = ceil($totalData / $limit);

$jumlahProdukBuku = mysqli_num_rows($query);

// dropdown kategori untuk form tambah
$queryKategoriBuku = mysqli_query($con, "SELECT * FROM kategori_buku");


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
    <title>Produk Buku</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">
    <script src="https://cdn.tiny.cloud/1/zc21szb6it5hjdq55znj0ar26p80vj6l569kksuv2if9p4r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> <!-- LINK WYSIWYG -->


    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/produk-buku.css">

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
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-book-fill"></i> Daftar Buku</li>

            </ol>
        </nav>
        <!-- BREADCUMB END -->


        <!-- FORM UTAMA -->
        <form action="" method="post" enctype="multipart/form-data" class="mt-5">
            <h4 class="mb-4">Tambah Buku</h4>
            <div class="row ">

                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div>
                        <label for="nama">Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" autocomplete="FALSE" required>
                    </div>
                    <div>
                        <label for="kategoriBuku">Kategori Buku</label>
                        <select name="kategoriBuku" id="kategoriBuku" class="form-control" required>
                            <option value="" selected hidden>Pilih Kategori</option>
                            <?php while ($data = mysqli_fetch_array($queryKategoriBuku)) { ?>
                                <option value="<?= $data['id']; ?>"><?= $data['nama']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="isbn">No. ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="penulis">Penulis</label>
                        <input type="text" name="penulis" id="penulis" class="form-control" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="penerbit">Penerbit</label>
                        <input type="text" name="penerbit" id="penerbit" class="form-control" autocomplete="FALSE">
                    </div>

                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div>
                        <label for="tahun">Tahun Terbit</label>
                        <input type="number" class="form-control" name="tahun">
                    </div>
                    <div>
                        <label for="bahasa">Bahasa</label>
                        <input type="text" name="bahasa" id="bahasa" class="form-control" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="harga">Harga</label>
                        <input type="number" class="form-control" name="harga" required>
                    </div>
                    <div>
                        <label for="akses">Link Akses</label>
                        <input type="text" name="akses" id="akses" class="form-control" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="cover">Cover Buku</label>
                        <input type="file" class="form-control" name="cover">
                    </div>
                </div>

                <div class="">
                    <label for="sinopsis" class="form-label">Sinopsis</label>
                    <textarea id="sinopsis" name="sinopsis"></textarea>
                    <small id="sinopsisCounter" class="text-muted">0/1000</small>
                </div>

            </div>

            <div>
                <button type="submit" class="btn btn-danger" name="simpan">Simpan</button>
            </div>
        </form>

        <!-- VALIDASI KETIKA BELUM ADA INPUT FORM BAGIAN BACKEND START -->
        <?php
        if (isset($_POST['simpan'])) {
            $nama         = trim($_POST['nama']);
            $kategoriBuku = trim($_POST['kategoriBuku']);
            $isbn         = trim($_POST['isbn']);
            $penulis      = trim($_POST['penulis']);
            $penerbit     = trim($_POST['penerbit']);
            $tahun        = trim($_POST['tahun']);
            $bahasa       = trim($_POST['bahasa']);
            $harga        = trim($_POST['harga']);
            $akses        = trim($_POST['akses']);
            $sinopsis     = $_POST['sinopsis']; // simpan HTML apa adanya

            // Validasi sinopsis <= 1000 karakter (tanpa tag HTML)
            $sinopsisText = trim(html_entity_decode(strip_tags($sinopsis), ENT_QUOTES, 'UTF-8'));
            $charCount    = mb_strlen($sinopsisText, 'UTF-8');

            if ($charCount > 1000) {
                echo '<div class="alert alert-warning mt-3" role="alert">
                Sinopsis maksimal 1000 karakter. Saat ini: ' . $charCount . ' karakter.
              </div>';
            } else {
                // ===== lanjut proses hanya kalau lolos batas 1000 =====

                // Validasi wajib
                if ($nama === '' || $kategoriBuku === '' || $harga === '') {
                    echo '<div class="alert alert-warning mt-3" role="alert">
                    Nama, Kategori, dan Harga Wajib Diisi!
                  </div>';
                } else {
                    // Setup upload cover
                    $target_dir    = "../cover-buku/";
                    $nama_file     = basename($_FILES["cover"]["name"]);
                    $target_file   = $target_dir . $nama_file;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $image_size    = $_FILES["cover"]["size"];
                    $new_name      = generateRandomString(20) . "." . $imageFileType;

                    if ($nama_file !== '') {
                        if ($image_size > 20000000) {
                            echo '<div class="alert alert-warning mt-3">File Cover Tidak Boleh Lebih Dari 20MB!</div>';
                        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'svg', 'jfif'])) {
                            echo '<div class="alert alert-warning mt-3">File Wajib Berformat JPG, JPEG, PNG, SVG, atau JFIF!</div>';
                        } else {
                            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_dir . $new_name)) {
                                $queryTambah = mysqli_query(
                                    $con,
                                    "INSERT INTO produk_buku
                             (kategori_id, nama, isbn, penulis, harga, tahun, penerbit, bahasa, akses, sinopsis, cover)
                             VALUES
                             ('$kategoriBuku','$nama','$isbn','$penulis','$harga','$tahun','$penerbit','$bahasa','$akses','$sinopsis','$new_name')"
                                );
                                if ($queryTambah) {
                                    echo '<div class="alert alert-success mt-3">Produk Baru Berhasil Disimpan</div>';
                                    echo '<meta http-equiv="refresh" content="2; url=produk-buku.php">';
                                } else {
                                    echo mysqli_error($con);
                                }
                            } else {
                                echo '<div class="alert alert-danger mt-3">Gagal mengunggah file cover.</div>';
                            }
                        }
                    } else {
                        // Tanpa cover
                        $queryTambah = mysqli_query(
                            $con,
                            "INSERT INTO produk_buku
                     (kategori_id, nama, isbn, penulis, harga, tahun, penerbit, bahasa, akses, sinopsis, cover)
                     VALUES
                     ('$kategoriBuku','$nama','$isbn','$penulis','$harga','$tahun','$penerbit','$bahasa','$akses','$sinopsis','')"
                        );
                        if ($queryTambah) {
                            echo '<div class="alert alert-success mt-3">Produk Baru Berhasil Disimpan</div>';
                            echo '<meta http-equiv="refresh" content="2; url=produk-buku.php">';
                        } else {
                            echo mysqli_error($con);
                        }
                    }
                }
            }
        }
        ?>

        <!-- VALIDASI KETIKA BELUM ADA INPUT FORM BAGIAN BACKEND END -->



        <div class="mt-5 mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h3 class="mb-0">List Buku</h3>

                <form class="d-flex ms-auto mt-2 mt-sm-0" method="get" action="produk-buku.php">
                    <input type="text"
                        class="form-control me-2"
                        name="q"
                        placeholder="Cari judul/kategori/ISBN/penulis/penerbit/tahun"
                        value="<?= htmlspecialchars($q) ?>"
                        style="min-width:280px">
                    <button class="btn btn-outline-danger" type="submit">Cari</button>
                    <?php if ($q !== '') { ?>
                        <a href="produk-buku.php" class="btn btn-danger ms-2">Reset</a>
                    <?php } ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Judul</th>
                            <th style="width: 10%;">Kategori</th>
                            <th>ISBN</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Harga</th>
                            <th style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($jumlahProdukBuku == 0) {
                        ?>
                            <td class="text-center" colspan="9">Data Produk Buku Tidak Tersedia</td>

                            <?php
                        } else {
                            $jumlah = $start + 1;
                            while ($data = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $jumlah ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['nama_kategori_buku']; ?></td>
                                    <td class="text-center"><?php echo $data['isbn']; ?></td>
                                    <td><?php echo $data['penulis']; ?></td>
                                    <td><?php echo $data['penerbit']; ?></td>
                                    <td><?php echo $data['tahun']; ?></td>
                                    <td><?php echo $data['harga']; ?></td>
                                    <td class="text-center">
                                        <a href="produk-buku-detail.php?p=<?php echo $data['id']; ?>" class="btn btn-sm btn-danger">
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
                                    href="produk-buku.php?page=<?= $i ?><?= ($q !== '' ? '&q=' . urlencode($q) : '') ?>">
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
            selector: '#sinopsis',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link image | preview',
            height: 300,
            setup: function(editor) {
                const counterEl = document.getElementById('sinopsisCounter');

                function updateCounter() {
                    // ambil teks polos (tanpa HTML)
                    const text = editor.getContent({
                        format: 'text'
                    }).trim();
                    const len = text.length;
                    counterEl.textContent = len + '/1000';
                    counterEl.classList.toggle('text-danger', len > 1000);
                }

                editor.on('init keyup setcontent input change', updateCounter);

                // cegah submit kalau > 1000
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    const text = editor.getContent({
                        format: 'text'
                    }).trim();
                    if (text.length > 1000) {
                        e.preventDefault();
                        alert('Sinopsis maksimal 1000 karakter. Saat ini: ' + text.length + ' karakter.');
                    }
                });
            }
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