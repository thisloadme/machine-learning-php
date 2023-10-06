<!DOCTYPE html>
<html>

<head>
    <title>K Means Clustering</title>
</head>

<body>
    <?php
    $baseUrl = $_SERVER['PHP_SELF'] ?? '';

    $isProses = isset($_POST['do_proses']);

    if ($isProses) {
        ini_set("memory_limit", '10240M');
        $fileImage = $_FILES['file_image'] ?? [];
        $kValue = $_POST['k_value'];

        if (count($fileImage) == 0) {
            echo 'image kosong';
            exit();
        }

        if ($fileImage['size'] == 0) {
            echo 'image invalid';
            exit();
        }

        $newName = 'a' . rand(10, 100) . basename($fileImage["name"]);
        move_uploaded_file($fileImage["tmp_name"], $newName);

        $size = getimagesize($newName);
        if (!$size) {
            return [];
        }

        function getColourVector($size, $fileName)
        {
            $palet = [];

            switch ($size['mime']) {
                case 'image/jpeg':
                    $img = imagecreatefromjpeg($fileName);
                    break;
                case 'image/png':
                    $img = imagecreatefrompng($fileName);
                    break;
                default:
                    return [];
            }

            if (!$img) {
                return [];
            }

            if ($size[0] * $size[1] > 44000) {
                echo 'image terlalu besar';
                exit();
            }

            for ($i = 0; $i < $size[0]; $i++) {
                for ($j = 0; $j < $size[1]; $j++) {
                    $warnaSaatIni = imagecolorat($img, $i, $j);
                    $rgb = imagecolorsforindex($img, $warnaSaatIni);

                    $palet[] = [$rgb['red'], $rgb['green'], $rgb['blue']];
                }
            }

            return $palet;
        }

        $vector = getColourVector($size, $newName);

        class KMeans
        {
            public $iterasi = 100;
            public $kluster;
            public $nElemen;
            public $centroids;

            function __construct($kluster)
            {
                $this->kluster = $kluster;
            }

            function inisialisasiCentroid($vectors)
            {
                $centroidSaatIni = [];
                for ($c = 0; $c < $this->kluster; $c++) {
                    $randIdx = mt_rand(0, count($vectors) - 1);
                    $centroidSaatIni[] = $vectors[$randIdx];
                    $this->nElemen = count($vectors[$randIdx]);
                }
                return $centroidSaatIni;
            }

            function hitungJarak($vectors, $centroids)
            {
                $jarak = [];
                foreach ($vectors as $vector) {
                    $jarakSaatIni = [];
                    for ($i = 0; $i < $this->kluster; $i++) {
                        $nilaiSaatIni = 0;
                        $centroidSaatIni = $centroids[$i];
                        for ($v = 0; $v < $this->nElemen; $v++) {
                            $nilaiSaatIni += abs($vector[$v] - $centroidSaatIni[$v]) ** 2;
                        }
                        $nilaiSaatIni = sqrt($nilaiSaatIni);
                        $jarakSaatIni[] = $nilaiSaatIni;
                    }
                    $jarak[] = $jarakSaatIni;
                }
                return $jarak;
            }

            function indexJarakTerdekat($jarak)
            {
                $idxTerdekat = [];
                foreach ($jarak as $jar) {
                    $idxTerdekat[] = array_search(min($jar), $jar);
                }
                return $idxTerdekat;
            }

            function hitungCentroidBaru($vectors, $indexLabel)
            {
                $centroids = [];
                for ($i = 0; $i < $this->kluster; $i++) {
                    $currentCentroids = [];
                    $arrayVectors = array_filter($vectors, fn ($idx) => $indexLabel[$idx] == $i, ARRAY_FILTER_USE_KEY);
                    for ($j = 0; $j < $this->nElemen; $j++) {
                        $nilaiPerIndex = array_map(function ($item) use ($j) {
                            return $item[$j];
                        }, $arrayVectors);

                        $currentCentroids[] = array_sum($nilaiPerIndex) / count($nilaiPerIndex);
                    }
                    $centroids[] = $currentCentroids;
                }
                return $centroids;
            }

            function getNewVectors($x)
            {
                $this->centroids = $this->inisialisasiCentroid($x);
                for ($iter = 0; $iter < $this->iterasi; $iter++) {
                    $centroidLama = $this->centroids;
                    $jarak = $this->hitungJarak($x, $centroidLama);
                    $indexLabel = $this->indexJarakTerdekat($jarak);
                    $this->centroids = $this->hitungCentroidBaru($x, $indexLabel);
                    if ($this->centroids == $centroidLama && $iter > 50) {
                        break;
                    }
                }

                $finalVectors = array_map(function ($item) {
                    return $this->centroids[$item];
                }, $indexLabel);
                return $finalVectors;
            }
        }

        $kMeans = new KMeans($kValue);
        $newVectors = $kMeans->getNewVectors($vector);

        function createNewImage($size, $newVectors, $newName)
        {
            $width = $size[0];
            $height = $size[1];
            $currentVectorIndex = 0;

            $img = imagecreatetruecolor($width, $height);
            for ($i = 0; $i < $width; $i++) {
                for ($j = 0; $j < $height; $j++) {
                    $color = imagecolorallocate($img, $newVectors[$currentVectorIndex][0], $newVectors[$currentVectorIndex][1], $newVectors[$currentVectorIndex][2]);
                    imagesetpixel($img, $i, $j, $color);
                    $currentVectorIndex++;
                }
            }

            $finalName = 'filtered_' . $newName;

            // header('Content-Type: ' . $size['mime']);
            switch ($size['mime']) {
                case 'image/jpeg':
                    imagejpeg($img, $finalName);
                    break;
                case 'image/png':
                    imagepng($img, $finalName);
                    break;
                default:
                    return 'format tidak valid';
            }
            imagedestroy($img);

            return $finalName;
        }

        $finalFileName = createNewImage($size, $newVectors, $newName);
    }

    ?>

    <h1>Image Simpler Coloring</h1>

    <div>
        <form action="<?= $baseUrl ?>" method="post" enctype="multipart/form-data">
            file image : <input type="file" name="file_image">
            <br>
            <small>(ukuran panjang x lebar tidak boleh melebihi 44000)</small>
            <br>
            <br>
            Jumlah warna : <input type="number" name="k_value" value="<?= $isProses ? $kValue : null ?>">
            <br>
            <input type="submit" value="proses" name="do_proses">
            <br><br>
            <?php if ($isProses && file_exists($finalFileName)) : ?>
                <p>Gambar Asli</p>
                <img src="<?= $newName ?>" alt="image">
                <p>Gambar Hasil Simpler Coloring</p>
                <img src="<?= $finalFileName ?>" alt="image">
            <?php endif ?>
        </form>
    </div>
</body>

</html>