<!DOCTYPE html>
<html>

<head>
    <title>Cosine Similarity</title>
</head>

<body>
    <?php
    $baseUrl = $_SERVER['PHP_SELF'] ?? '';

    $isPrediksi = isset($_POST['do_prediksi']);

    if ($isPrediksi) {
        $kataDatabase = $_POST['database'];
        $kataTypo = $_POST['typo'];

        $arrayKata = explode(' ', $kataDatabase);

        $semuaHuruf = [];
        foreach ($arrayKata as $kata) {
            $semuaHuruf = array_merge($semuaHuruf, str_split($kata));
        }
        $semuaHuruf = array_unique($semuaHuruf);
        sort($semuaHuruf);

        function strCountArray($str, $semuaHuruf)
        {
            $arrayHuruf = str_split($str);
            $strCountValue = array_map(function ($item) use ($arrayHuruf) {
                return in_array($item, $arrayHuruf) ? 1 : 0;
            }, $semuaHuruf);

            return $strCountValue;
        }
        $strCountKataInput = strCountArray($kataTypo, $semuaHuruf);

        function getCosine($a, $b)
        {
            $hasilKaliSesamaIndex = [];
            $hasilKuadratA = [];
            $hasilKuadratB = [];
            for ($i=0; $i < count($a); $i++) {
                $hasilKaliSesamaIndex[] = $a[$i] * $b[$i];

                $hasilKuadratA[] = pow($a[$i], 2);
                $hasilKuadratB[] = pow($b[$i], 2);
            }
            $sumHasilKaliSesamaIndex = array_sum($hasilKaliSesamaIndex);
            $sumHasilKuadratA = array_sum($hasilKuadratA);
            $sumHasilKuadratB = array_sum($hasilKuadratB);

            $cosineSimilarity = $sumHasilKaliSesamaIndex / sqrt($sumHasilKuadratA) * sqrt($sumHasilKuadratB);
            return $cosineSimilarity;
        }

        $allCosine = [];
        $cosineTertinggi = 0;
        foreach ($arrayKata as $kata) {
            if (array_sum($strCountKataInput) == 0) {
                break;
            }

            $cosine = getCosine($strCountKataInput, strCountArray($kata, $semuaHuruf));
            $allCosine[$kata] = $cosine;

            if ($cosine > $cosineTertinggi) {
                $cosineTertinggi = $cosine;
            }
        }

        $prediksi = $cosineTertinggi < 0.7 ? null : array_search($cosineTertinggi, $allCosine);
    }

    ?>

    <h1>Typo Correction</h1>

    <div>
        <form action="<?= $baseUrl ?>" method="post">
            database kata : <br> <textarea name="database" id="database" cols="30" rows="4"><?= $isPrediksi ? $kataDatabase : null ?></textarea>
            <br>
            kata yang typo : <input type="text" name="typo" value="<?= $isPrediksi ? $kataTypo : null ?>">
            <br>
            <br>
            prediksi kata yang benar : <b><?= $isPrediksi ? $prediksi : null ?></b>
            <br>
            <input type="submit" value="prediksi" name="do_prediksi">
        </form>
    </div>
</body>

</html>