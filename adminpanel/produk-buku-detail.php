<?php
require "session.php";
require "../koneksi.php";

// Pastikan parameter p valid
$id = isset($_GET['p']) ? (int)$_GET['p'] : 0;
// kalau id tidak valid (0), redirect atau kasih pesan error
if ($id <= 0) {
    echo '<div class="alert alert-danger mt-3" role="alert">ID produk tidak valid.</div>';
    exit;
}
$query = mysqli_query($con, "SELECT a.*, b.nama AS nama_kategori_buku  FROM produk_buku a JOIN kategori_buku b ON a.kategori_id=b.id WHERE a.id='$id'");
$data = mysqli_fetch_array($query);
$queryKategoriBuku = mysqli_query($con, "SELECT * FROM kategori_buku WHERE id!='{$data['kategori_id']}'");


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
    <title>Produk Buku Detail</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">
    <script src="https://cdn.tiny.cloud/1/zc21szb6it5hjdq55znj0ar26p80vj6l569kksuv2if9p4r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> <!-- LINK WYSIWYG -->


    <!-- LINK CSS -->
    <link rel="stylesheet" href="../css/produk-buku-detail.css">

</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require "navbarAdmin.php";
    ?>
    <!-- REQUIRE NAVBAR END -->


    <div class="container">
        <h2 class="mt-5">Detail Buku</h2>

        <form action="" method="post" enctype="multipart/form-data" class="mt-4">
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div>
                        <label for="nama">Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" value="<?php echo $data['nama'] ?>" autocomplete="FALSE" required>
                    </div>
                    <div>
                        <label for="kategoriBuku">Kategori Buku</label>
                        <select name="kategoriBuku" id="kategoriBuku" class="form-control" required>
                            <option value="<?php echo $data['kategori_id'] ?>" selected hidden><?php echo $data['nama_kategori_buku'] ?></option>
                            <?php while ($dataKategoriBuku = mysqli_fetch_array($queryKategoriBuku)) { ?>
                                <option value="<?= $dataKategoriBuku['id']; ?>"><?= $dataKategoriBuku['nama']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="isbn">No. ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?php echo $data['isbn']; ?>" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="penulis">Penulis</label>
                        <input type="text" name="penulis" id="penulis" class="form-control" value="<?php echo $data['penulis']; ?>" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="penerbit">Penerbit</label>
                        <input type="text" name="penerbit" id="penerbit" class="form-control" value="<?php echo $data['penerbit']; ?>" autocomplete="FALSE">
                    </div>
                    <div class="col-12 mt-3">
                        <label for="sinopsis" class="form-label">Sinopsis</label>
                        <textarea id="sinopsis" class="form-control" name="sinopsis"><?php echo $data['sinopsis']; ?></textarea>
                        <small id="sinopsisCounter" class="text-muted">0/1000</small>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div>
                        <label for="tahun">Tahun Terbit</label>
                        <input type="number" class="form-control" value="<?php echo $data['tahun']; ?>" name="tahun">
                    </div>
                    <div>
                        <label for="bahasa">Bahasa</label>
                        <input type="text" name="bahasa" id="bahasa" class="form-control" value="<?php echo $data['bahasa']; ?>" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="harga">Harga</label>
                        <input type="number" class="form-control" name="harga" value="<?php echo $data['harga']; ?>" required>
                    </div>
                    <div>
                        <label for="akses">Link Akses</label>
                        <input type="text" name="akses" id="akses" class="form-control" value="<?php echo $data['akses']; ?>" autocomplete="FALSE">
                    </div>
                    <div>
                        <label for="cover">Cover Buku</label>
                        <input type="file" class="form-control" name="cover">
                    </div>
                    <div class="mt-2">
                        <label for="currentFoto">Cover Saat Ini</label><br>
                        <img src="../cover-buku/<?php echo $data['cover']; ?>" alt="" width="200px">
                    </div>
                </div>

            </div>

            <div class="mb-5">
                <button type="submit" class="btn btn-primary" name="editBtn">Edit</button>
                <button type="submit" class="btn btn-danger" name="deleteBtn">Delete</button>
            </div>
        </form>

        <?php
        // ============== UPDATE ==============
        // ============== UPDATE ==============
        if (isset($_POST['editBtn'])) {
            // Ambil input baru & trim
            $nama       = trim($_POST['nama']);
            $kategoriId = trim($_POST['kategoriBuku']);
            $isbn       = trim($_POST['isbn']);
            $penulis    = trim($_POST['penulis']);
            $penerbit   = trim($_POST['penerbit']);
            $tahun      = trim($_POST['tahun']);
            $bahasa     = trim($_POST['bahasa']);
            $harga      = trim($_POST['harga']);
            $akses      = trim($_POST['akses']);
            $sinopsis   = $_POST['sinopsis']; // HTML dari TinyMCE

            // Validasi 1000 karakter (tanpa HTML)
            $sinopsisText = trim(html_entity_decode(strip_tags($sinopsis), ENT_QUOTES, 'UTF-8'));
            $charCount    = mb_strlen($sinopsisText, 'UTF-8');

            if ($charCount > 1000) {
                echo '<div class="alert alert-warning mt-3" role="alert">
                Sinopsis maksimal 1000 karakter. Saat ini: ' . $charCount . ' karakter.
              </div>';
            } else {
                // Field wajib
                if ($nama === '' || $kategoriId === '' || $harga === '') {
                    echo '<div class="alert alert-warning mt-3" role="alert">Nama, Kategori, dan Harga wajib diisi!</div>';
                } else {
                    // Siapkan info file upload
                    $target_dir   = "../cover-buku/";
                    $nama_file    = basename($_FILES["cover"]["name"]);
                    $target_file  = $target_dir . $nama_file;
                    $imageExt     = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $image_size   = $_FILES["cover"]["size"];
                    $new_name     = generateRandomString() . "." . $imageExt;

                    // Cek apakah ada perubahan text?
                    $isChangedText =
                        ($nama       !== $data['nama']) ||
                        ($kategoriId !== (string)$data['kategori_id']) ||
                        ($isbn       !== (string)$data['isbn']) ||
                        ($penulis    !== (string)$data['penulis']) ||
                        ($penerbit   !== (string)$data['penerbit']) ||
                        ($tahun      !== (string)$data['tahun']) ||
                        ($bahasa     !== (string)$data['bahasa']) ||
                        ($harga      !== (string)$data['harga']) ||
                        ($akses      !== (string)$data['akses']) ||
                        (trim($sinopsis) !== trim($data['sinopsis']));

                    $hasNewCover = ($nama_file !== '');

                    if (!$isChangedText && !$hasNewCover) {
                        echo '<div class="alert alert-warning mt-3" role="alert">Tidak ada perubahan data.</div>';
                    } else {
                        // Update field text jika berubah
                        if ($isChangedText) {
                            $upd = mysqli_query(
                                $con,
                                "UPDATE produk_buku SET 
                            kategori_id='$kategoriId',
                            nama='$nama',
                            isbn='$isbn',
                            penulis='$penulis',
                            harga='$harga',
                            tahun='$tahun',
                            penerbit='$penerbit',
                            bahasa='$bahasa',
                            akses='$akses',
                            sinopsis='$sinopsis'
                         WHERE id='$id'"
                            );
                            if (!$upd) {
                                echo '<div class="alert alert-danger mt-3" role="alert">Gagal mengupdate data: ' . htmlspecialchars(mysqli_error($con), ENT_QUOTES) . '</div>';
                                // hentikan proses selanjutnya, jangan lanjut ke cover
                                return;
                            }
                        }

                        // Kalau ada cover baru -> validasi + upload + update + hapus lama
                        if ($hasNewCover) {
                            if ($image_size > 20000000) {
                                echo '<div class="alert alert-warning mt-3" role="alert">File cover tidak boleh lebih dari 20MB!</div>';
                                return;
                            }
                            $allowed = ['jpg', 'jpeg', 'png', 'svg', 'jfif', 'webp'];
                            if (!in_array($imageExt, $allowed)) {
                                echo '<div class="alert alert-warning mt-3" role="alert">File wajib berformat: JPG, JPEG, PNG, SVG, JFIF, atau WEBP.</div>';
                                return;
                            }

                            if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_dir . $new_name)) {
                                $updCover = mysqli_query($con, "UPDATE produk_buku SET cover='$new_name' WHERE id='$id'");
                                if (!$updCover) {
                                    @unlink($target_dir . $new_name);
                                    echo '<div class="alert alert-danger mt-3" role="alert">Gagal mengupdate cover: ' . htmlspecialchars(mysqli_error($con), ENT_QUOTES) . '</div>';
                                    return;
                                }
                                // Hapus cover lama
                                if (!empty($data['cover'])) {
                                    $old = $target_dir . $data['cover'];
                                    if (file_exists($old)) @unlink($old);
                                }
                            } else {
                                echo '<div class="alert alert-danger mt-3" role="alert">Gagal mengunggah file cover.</div>';
                                return;
                            }
                        }

                        echo '<div class="alert alert-success mt-3" role="alert">Produk berhasil diupdate.</div>';
                        echo '<meta http-equiv="refresh" content="2; url=produk-buku.php">';
                    }
                }
            }
        }


        // ============== DELETE ==============
        if (isset($_POST['deleteBtn'])) {
            // Simpan path cover lama
            $oldCoverPath = "../cover-buku/" . $data['cover'];

            $del = mysqli_query($con, "DELETE FROM produk_buku WHERE id='$id'");
            if ($del) {
                // Hapus file cover fisik bila ada
                if (!empty($data['cover']) && file_exists($oldCoverPath)) {
                    @unlink($oldCoverPath);
                }
                echo '<div class="alert alert-success mt-3 mb-3" role="alert">Produk berhasil dihapus.</div>';
                echo '<meta http-equiv="refresh" content="0; url=produk-buku.php">';
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert">Gagal menghapus produk: ' . htmlspecialchars(mysqli_error($con), ENT_QUOTES) . '</div>';
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
            selector: '#sinopsis',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link image | preview',
            height: 500,
            setup: function(editor) {
                const counterEl = document.getElementById('sinopsisCounter');

                function updateCounter() {
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



</body>

</html>