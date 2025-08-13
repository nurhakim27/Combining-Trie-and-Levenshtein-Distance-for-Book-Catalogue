<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universitas Bakrie Press</title>
    <link rel="icon" href="image/favicon.png" type="image/png">
    <!-- FONT START -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- FONT END -->

    <!-- BOOTSTRAP & CSS START -->
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- UNTUK ICON -->
    <link href="css/aos.css" rel="stylesheet"> <!-- UNTUK ANIMATION -->

    <link rel="stylesheet" href="css/index.css">
    <!-- BOOTSTRAP & CSS END -->

</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require 'navbar.php';
    ?>
    <!-- REQUIRE NAVBAR END -->

    <!-- Jumbotron / Hero Section -->
    <section class="jumbotron-section">
        <div class="jumbotron-overlay"></div>
        <div class="jumbotron-content">
            <h6 class="mb-5 line-pertama fs-6 fs-md-5 fs-lg-4">Penerbit Perguruan Tinggi Indonesia</h6>
            <h1 class="mb-5 display-5 line-kedua fs-3 fs-md-2 fs-lg-1">UNIVERSITAS BAKRIE PRESS</h1>
            <p class="mb-5 line-ketiga fs-6 fs-md-5 fs-lg-4">
                Penerbit unggul, terpercaya dan berkontribusi dalam upaya mencerdaskan <br>kehidupan bangsa
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="produk.php" class="btn btn-orange px-4"><b>Koleksi Buku</b></a>
            </div>
        </div>
    </section>

    <!-- JUMBOTRON HERO SECTION END -->


    <!-- ABOUT SECTION START -->
    <section class="about-section py-5">
        <div class="custom-container">
            <div class="row align-items-center">
                <!-- Kolom Kiri (Teks) -->
                <div class="col-md-6 text-md-start text-center about-kolom-kiri">
                    <h2 class="fw-bold mb-3 selamat-datang fs-3 fs-md-2 fs-lg-1">Selamat Datang</h2>
                    <p>
                        <b>Universitas Bakrie Press</b> merupakan salah satu penerbit perguruan tinggi anggota IKAPI yang berkomitmen untuk mendukung kemajuan literasi, edukasi, dan pengembangan ilmu pengetahuan di berbagai bidang. Kami senantiasa berusaha menyediakan layanan penerbitan berkualitas tinggi dengan mengedepankan nilai-nilai profesionalitas, proses penerbitan transparan dan penyebarluasan karya berbasis digital.
                    </p>
                    <p>
                        Kami siap membantu menerbitkan berbagai karya Anda dalam bentuk buku, artikel, hingga poster. Universitas Bakrie Press adalah mitra terpercaya bagi akademisi, peneliti, praktisi dan masyarakat umum lainnya tanpa terkecuali yang ingin menyebarluaskan gagasan serta kontribusi ilmiahnya kepada Indonesia hingga dunia. </p>
                    <a href="https://abrasive-nose-ae2.notion.site/Jasa-Layanan-Universitas-Bakrie-Press-203706a1afdf8042a60bd6e46015055e" class="btn btn-warning fw-bold px-4">JASA PENERBITAN</a>
                </div>
                <!-- Kolom Kanan (Gambar) -->
                <div class="col-md-6 text-center mt-4 mt-md-0 about-image-custom">
                    <img src="image/about-image.jpg" alt="Tentang Kami" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION END -->

    <!-- SECTION WHY US START -->
    <section class="section-why-us py-5 text-white text-center">
        <div class="custom-container why-us-jarakAtasBawah">
            <small class="text-uppercase fw-semibold why-us-line-pertama">Penerbit Unggul</small>
            <h2 class="fw-bold my-3 why-us-line-kedua fs-3 fs-md-2 fs-lg-1">Kenapa Menerbitkan Di Sini?</h2>
            <div class="row mt-5">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="0">
                    <div class="icon-box">
                        <i class="bi bi-check-circle-fill fs-1 mb-3 why-us-mb-icon"></i><br>
                        <h5 class="fw-bold judul-why-us">Proses Profesional</h5>
                        <p class="deskripsi-why-us">Penerbitan dilakukan secara profesional dari editing hingga distribusi.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="icon-box">
                        <i class="bi bi-globe2 fs-1 mb-3 why-us-mb-icon"></i><br>
                        <h5 class="fw-bold judul-why-us">Jangkauan Luas</h5>
                        <p class="deskripsi-why-us">Karya Anda bisa diakses secara nasional hingga internasional secara digital.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="icon-box">
                        <i class="bi bi-people-fill fs-1 mb-3 why-us-mb-icon"></i><br>
                        <h5 class="fw-bold judul-why-us">Dukungan Penuh</h5>
                        <p class="deskripsi-why-us">Kami mendampingi penulis dari awal hingga terbit, termasuk promosi.</p>
                    </div>
                </div>


            </div>
        </div>
    </section>
    <!-- SECTION WHY US END -->

    <!-- SECTION LAYANAN START -->
    <section class="section-layanan py-5">
        <div class="custom-container section-layanan-jarakAtasBawah">
            <div class="row align-items-stretch">
                <!-- KIRI -->
                <div class="col-md-4 d-flex align-items-center justify-content-center text-center bg-maroon text-white">
                    <h2 class="fw-bold text-uppercase jenis-layanan-title fs-3 fs-md-2 fs-lg-1">Jenis Layanan</h2>
                </div>

                <!-- KANAN -->
                <div class="col-md-8">
                    <div class="row align-items-stretch">
                        <!-- Kolom 1 -->
                        <div class="col-md-6">
                            <div class="layanan-box " data-aos="fade-up" data-aos-delay="0">
                                <i class="bi bi-journal-plus icon-layanan"></i>
                                <h5 class="fw-bold">Pengajuan ISBN</h5>
                                <p>Kami akan membantu urus ISBN untuk setiap karya terbitan Anda.</p>
                            </div>
                            <div class="layanan-box" data-aos="fade-up" data-aos-delay="200">
                                <i class="bi bi-file-earmark-check icon-layanan"></i>
                                <h5 class="fw-bold">Cek Similarity</h5>
                                <p>Kami memeriksa orisinalitas naskah Anda sebelum diterbitkan.</p>
                            </div>
                            <div class="layanan-box" data-aos="fade-up" data-aos-delay="400">
                                <i class="bi bi-spellcheck icon-layanan"></i>
                                <h5 class="fw-bold">Cek Proofreading</h5>
                                <p>Kami akan memastikan tata bahasa dan ejaan Anda sudah tepat.</p>
                            </div>
                        </div>

                        <!-- Kolom 2 -->
                        <div class="col-md-6">
                            <div class="layanan-box" data-aos="fade-up" data-aos-delay="600">
                                <i class="bi bi-layout-text-window icon-layanan"></i>
                                <h5 class="fw-bold">Desain Layout</h5>
                                <p>Kami akan mengatur tata letak halaman yang menarik dan nyaman dibaca.</p>
                            </div>
                            <div class="layanan-box" data-aos="fade-up" data-aos-delay="800">
                                <i class="bi bi-image icon-layanan"></i>
                                <h5 class="fw-bold">Desain Cover</h5>
                                <p>Kami akan membuat cover menarik untuk menarik minat pembaca.</p>
                            </div>
                            <div class="layanan-box" data-aos="fade-up" data-aos-delay="1000">
                                <i class="bi bi-shield-check icon-layanan"></i>
                                <h5 class="fw-bold">Pendaftaran HKI</h5>
                                <p>Kami akan mendaftarkan hak cipta karya Anda secara resmi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION LAYANAN END -->

    <!-- SECTION PENGALAMAN KERJASAMA START -->
    <section class="section-pengalaman py-5 text-white text-center">
        <div class="custom-container section-pengalaman-jarakAtasBawah">
            <small class="text-uppercase fw-semibold pengalaman-line-pertama">KEPERCAYAAN YANG TERBANGUN</small>
            <h2 class="fw-bold my-3 pengalaman-line-kedua fs-3 fs-md-2 fs-lg-1">PENGALAMAN KERJASAMA</h2>
            <div class="row mt-5">

                <!-- Kolom Statistik -->
                <div class="col-md-4 mb-4">
                    <div class="stat-box">
                        <h1 class="stat-angka" data-target="112">112</h1>
                        <p class="stat-label">Jumlah Terbitan</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-box">
                        <h1 class="stat-angka" data-target="53">53</h1>
                        <p class="stat-label">Klien</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-box">
                        <h1 class="stat-angka" data-target="4">4</h1>
                        <p class="stat-label">Partner</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- SECTION PENGALAMAN KERJASAMA END -->


    <!-- REQUIRE FOOTER DETAIL START -->
    <?php
    require 'footer-detail.php';
    ?>
    <!-- REQUIRE FOOTER DETAIL END -->


    <!-- REQUIRE FOOTER COPYRIGHT START -->
    <?php
    require 'footer.php'
    ?>
    <!-- REQUIRE FOOTER COPYRIGHT END -->






    <!-- BAGIAN JAVASCRIPT START -->


    <!-- ANIMATION WHY US ICON BOX START -->
    <script src="js-aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000, // durasi animasi
            once: true // animasi hanya sekali saat scroll
        });
    </script>

    <!-- ANIMATION WHY US ICON BOX END -->

    <!-- COUNTER PENGALAMAN START -->
    <script>
        const counters = document.querySelectorAll('.stat-angka');

        const runCounter = (counter) => {
            const target = +counter.getAttribute('data-target');
            const duration = 3000; // dalam ms
            const frameRate = 60; // kira-kira 60 frame per detik
            const totalFrames = Math.round((duration / 1000) * frameRate);
            let frame = 0;

            const update = () => {
                frame++;
                const progress = frame / totalFrames;
                const current = Math.round(target * progress);

                counter.innerText = current;

                if (frame < totalFrames) {
                    requestAnimationFrame(update);
                } else {
                    counter.innerText = target;
                }
            };

            update();
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    runCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        counters.forEach(counter => observer.observe(counter));
    </script>
    <!-- COUNTER PENGALAMAN END -->


    <!-- BAGIAN JAVASCRIPT END -->


    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>