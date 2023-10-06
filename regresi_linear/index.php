<!DOCTYPE html>
<html>

<head>
    <title>Linear Regression</title>
</head>

<body>
    <?php
    $baseUrl = $_SERVER['PHP_SELF'] ?? '';

    $isPrediksi = isset($_POST['do_prediksi']);

    if ($isPrediksi) {
        $trainingNominal = $_POST['nominal'];
        $hariDiprediksi = $_POST['hari_diprediksi'];

        class LN
        {
            public $iter = 10;
            public $lr = 0.1;
            public $weight = 0;
            public $bias = 0;
            public $nilaiSampelTertinggi = 0;

            function train($arrayNominal)
            {
                $x = array_keys($arrayNominal);
                $this->nilaiSampelTertinggi = max($x);
                $x = array_map(function ($item) {
                    return ($item + 1) / ($this->nilaiSampelTertinggi + 1);
                }, $x);

                $y = array_values($arrayNominal);

                $jumlahSampel = count($x);

                for ($i = 0; $i < $this->iter; $i++) {
                    // prediksi target
                    $prediksi = array_map(function ($item) {
                        return ($this->weight * $item) + $this->bias;
                    }, $x);

                    // hitung selisih prediksi dengan nilai sebenarnya
                    $selisihPrediksiDanNilaiAsli = [];
                    foreach ($y as $idx => $y_) {
                        $selisihPrediksiDanNilaiAsli[$idx] = $prediksi[$idx] - $y[$idx];
                    }

                    // hitung new weight
                    $koefisienWeight = 0;
                    foreach ($x as $idx => $x_) {
                        $koefisienWeight += $x[$idx] * $selisihPrediksiDanNilaiAsli[$idx];
                    }
                    $koefisienWeight = $koefisienWeight / $jumlahSampel;
                    $this->weight = $this->weight - ($this->lr * $koefisienWeight);

                    // hitung new bias
                    $koefisienBias = array_sum($selisihPrediksiDanNilaiAsli) / $jumlahSampel;
                    $this->bias = $this->bias - ($this->lr * $koefisienBias);
                }
            }

            function doPrediksi($hariKe)
            {
                $item = ($hariKe + 1) / ($this->nilaiSampelTertinggi + 1);
                return ($this->weight * $item) + $this->bias;
            }
        }

        $ln = new LN();
        $ln->train($trainingNominal);
        $prediksi = $ln->doPrediksi($hariDiprediksi);
    }

    ?>

    <h1>Prediksi Penjualan Warung Paijo</h1>

    <div>
        <form action="<?= $baseUrl ?>" method="post">
            hari ke 1 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[0] : null ?>">
            <br>
            hari ke 2 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[1] : null ?>">
            <br>
            hari ke 3 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[2] : null ?>">
            <br>
            hari ke 4 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[3] : null ?>">
            <br>
            hari ke 5 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[4] : null ?>">
            <br>
            hari ke 6 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[5] : null ?>">
            <br>
            hari ke 7 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[6] : null ?>">
            <br>
            hari ke 8 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[7] : null ?>">
            <br>
            hari ke 9 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[8] : null ?>">
            <br>
            hari ke 10 : <input type="number" name="nominal[]" value="<?= $isPrediksi ? $trainingNominal[9] : null ?>">
            <br>
            <br>
            prediksi hari ke <input type="number" name="hari_diprediksi" value="<?= $isPrediksi ? $hariDiprediksi : null ?>"> : <b><?= $isPrediksi ? $prediksi : null ?></b>
            <br>
            <input type="submit" value="prediksi" name="do_prediksi">
        </form>
    </div>
</body>

</html>