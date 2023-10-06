<!DOCTYPE html>
<html>

<head>
    <title>Logistic Regression</title>
</head>

<body>
    <?php
    $baseUrl = $_SERVER['PHP_SELF'] ?? '';

    $isPrediksi = isset($_POST['do_prediksi']);

    if ($isPrediksi) {
        $trainingBerat = $_POST['berat'];
        $trainingTinggi = $_POST['tinggi'];
        $beratPred = $_POST['berat_pred'];
        $tinggiPred = $_POST['tinggi_pred'];

        // [[1,2], [2,3] ... 10x]
        $xTrain = [];
        $yTrain = [];
        $jumlahSampel = count($trainingBerat);

        for ($i = 0; $i < $jumlahSampel; $i++) {
            $xTrain[] = [$trainingBerat[$i], $trainingTinggi[$i]];
            $yTrain[] = $_POST['is_scabies_' . $i] ?? 0;
        }

        $xPred = [$beratPred, $tinggiPred];

        class LogisticRegression
        {
            public $iter = 50;
            public $lr = 0.001;
            public $weight = [0, 0];
            public $bias = 0;

            function sigmoid($x)
            {
                $hasilSigmoid = [];
                foreach ($x as $k => $v) {
                    $hasilSigmoid[$k] = 1 / (1 + exp(- ($v)));
                }

                return $hasilSigmoid;
            }

            function transpose($array)
            {
                try {
                    array_unshift($array, null);
                    return call_user_func_array('array_map', $array);
                } catch (\Throwable $th) {
                    return [];
                }
            }

            function train($x, $y)
            {
                $nSampel = count($x);

                for ($iter = 0; $iter < $this->iter; $iter++) {
                    // prediksi linear target
                    $arrayPred = [];
                    for ($i = 0; $i < $nSampel; $i++) {
                        $nilaiSampelSaatIni = 0;
                        for ($j = 0; $j < count($x[$i]); $j++) {
                            $nilaiSampelSaatIni += (floatval($x[$i][$j]) * $this->weight[$j]);
                        }

                        $nilaiSampelSaatIni += $this->bias;
                        $arrayPred[$i] = $nilaiSampelSaatIni;
                    }

                    // sigmoid
                    $arraySigmoid = $this->sigmoid($arrayPred);

                    // hitung selisih prediksi dengan nilai sebenarnya
                    $selisihPrediksiDanNilaiAsli = [];
                    foreach ($y as $idx => $y_) {
                        $selisihPrediksiDanNilaiAsli[$idx] = (floatval($arraySigmoid[$idx]) - floatval($y_));
                    }

                    // transpose input X
                    $transposeX = $this->transpose($x);

                    // hitung koefisien weight
                    $koefisienWeight = [];
                    for ($i = 0; $i < count($transposeX); $i++) {
                        $nilaiKoefisienSaatIni = 0;
                        for ($j = 0; $j < count($transposeX[$i]); $j++) {
                            $nilaiKoefisienSaatIni += (floatval($transposeX[$i][$j]) * $selisihPrediksiDanNilaiAsli[$j]);
                        }

                        $nilaiKoefisienSaatIni *= (1 / $nSampel);
                        $koefisienWeight[$i] = $nilaiKoefisienSaatIni;
                    }

                    // hitung new weight
                    foreach ($this->weight as $idx => $weight) {
                        $this->weight[$idx] = $weight - ($this->lr * $koefisienWeight[$idx]);
                    }

                    // hitung new bias
                    $koefisienBias = array_sum($selisihPrediksiDanNilaiAsli) / $nSampel;
                    $this->bias = $this->bias - ($this->lr * $koefisienBias);
                }
            }

            function doPrediksi($x)
            {
                // prediksi linear target
                $nilaiPrediksi = 0;
                for ($j = 0; $j < count($x); $j++) {
                    $nilaiPrediksi += (floatval($x[$j]) * $this->weight[$j]);
                }

                $nilaiPrediksi += $this->bias;

                // sigmoid
                $arraySigmoid = $this->sigmoid([$nilaiPrediksi]);
                $yPrediksi = $arraySigmoid[0];

                // prediksi
                return ($yPrediksi > 0.5);
            }
        }

        $ln = new LogisticRegression();
        $ln->train($xTrain, $yTrain);

        // get akurasi
        $predBenar = 0;
        foreach ($xTrain as $key => $xVal) {
            $yVal = $ln->doPrediksi($xVal);
            if ($yVal == $yTrain[$key]) {
                $predBenar++;
            }
        }
        $akurasi = $predBenar / count($xTrain) * 100;

        // do prediksi
        $prediksi = $ln->doPrediksi($xPred);
        $prediksi = $prediksi ? 'scabies' : 'tidak scabies';
    }

    ?>

    <h1>Prediksi Scabies Pada Kucing</h1>

    <div>
        <form action="<?= $baseUrl ?>" method="post">
            Kucing 1<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[0] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[0] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_0" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_0'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 2<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[1] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[1] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_1" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_1'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 3<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[2] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[2] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_2" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_2'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 4<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[3] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[3] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_3" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_3'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 5<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[4] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[4] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_4" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_4'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 6<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[5] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[5] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_5" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_5'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 7<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[6] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[6] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_6" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_6'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 8<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[7] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[7] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_7" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_7'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 9<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[8] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[8] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_8" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_8'])) : ?> checked <?php endif ?>>
            <br>
            Kucing 10<br>
            Berat : <input type="number" name="berat[]" value="<?= $isPrediksi ? $trainingBerat[9] : null ?>">
            Tinggi : <input type="number" name="tinggi[]" value="<?= $isPrediksi ? $trainingTinggi[9] : null ?>">
            Terkena Scabies : <input type="checkbox" name="is_scabies_9" value="1" <?php if ($isPrediksi && isset($_POST['is_scabies_9'])) : ?> checked <?php endif ?>>
            <br>
            <br>
            Prediksi kucing dengan berat : <input type="number" name="berat_pred" value="<?= $isPrediksi ? $beratPred : null ?>"> dan tinggi : <input type="number" name="tinggi_pred" value="<?= $isPrediksi ? $tinggiPred : null ?>">
            <br>
            <input type="submit" value="prediksi" name="do_prediksi">
            <?php if ($isPrediksi) : ?>
                <br>
                Hasil : <br>
                Status Scabies : <b><?= $prediksi ?></b>
                <br>
                Akurasi Prediksi : <?= $akurasi ?>%
            <?php endif ?>
        </form>
    </div>
</body>

</html>