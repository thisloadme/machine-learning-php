<?php

$keahlian = [
    '1' => 'PHP',
    '2' => 'Javascript',
    '3' => 'Python',
    '4' => 'Java',
    '5' => 'C++',
    '6' => 'C#',
    '7' => 'Ruby',
    '8' => 'Go',
    '9' => 'Swift',
    '10' => 'Kotlin',
    '11' => 'Rust',
    '12' => 'Scala',
    '13' => 'Perl',
    '14' => 'R',
    '15' => 'HTML',
    '16' => 'CSS',
    '17' => 'SQL',
    '18' => 'NoSQL',
    '19' => 'MongoDB',
];

$petaUserKeahlian = [
    'User1' => [1, 11, 2, 8, 0],
    'User2' => [6, 9, 19, 10, 0],
    'User3' => [19, 17, 15, 10, 16],
    'User4' => [2, 17, 13, 2, 0],
    'User5' => [3, 13, 14, 6, 0],
    'User6' => [13, 2, 15, 9, 0],
    'User7' => [15, 8, 10, 17, 0],
    'User8' => [11, 2, 4, 17, 0],
    'User9' => [2, 8, 12, 1, 10],
    'User10' => [4, 9, 1, 15, 0],
    'User11' => [3, 1, 7, 0, 0],
    'User12' => [16, 11, 1, 8, 4],
    'User13' => [14, 5, 9, 3, 3],
    'User14' => [5, 8, 9, 7, 19],
    'User15' => [4, 3, 16, 0, 0],
    'User16' => [1, 17, 7, 6, 16],
    'User17' => [19, 17, 10, 12, 18],
    'User18' => [2, 5, 8, 10, 11],
    'User19' => [10, 17, 19, 16, 13],
    'User20' => [5, 4, 18, 11, 1],
];

$keahlianProjek = [10, 5, 2, 0, 0];


$dataset = array_values(array_merge([$keahlianProjek], $petaUserKeahlian));

$max = 5;
$jmlUser = 20;
$maxOrangPerCluster = 3;
$jmlCluster = ceil(($jmlUser + 1) / ($maxOrangPerCluster + 1));

class KMeans
{
    public $maxIterasi = 100;
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
        // echo "<pre>" . print_r($vectors, true) . "</pre>";
        for ($i = 0; $i < $this->kluster; $i++) {
            $currentCentroids = [];
            $arrayVectors = array_filter($vectors, fn($idx) => $indexLabel[$idx] == $i, ARRAY_FILTER_USE_KEY);
            // echo "<pre>" . print_r($arrayVectors, true) . "</pre>";
            // echo "<pre>" . print_r($this->kluster, true) . "</pre>";
            for ($j = 0; $j < $this->nElemen; $j++) {
                $nilaiPerIndex = array_map(function ($item) use ($j) {
                    return $item[$j];
                }, $arrayVectors);

                $currentCentroids[] = count($nilaiPerIndex) > 0 ? (array_sum($nilaiPerIndex) / count($nilaiPerIndex)) : 0;
            }
            $centroids[] = $currentCentroids;
        }
        return $centroids;
    }

    function getNewVectors($x)
    {
        $this->centroids = $this->inisialisasiCentroid($x);
        for ($iter = 0; $iter < $this->maxIterasi; $iter++) {
            $centroidSaatIni = $this->centroids;
            $jarak = $this->hitungJarak($x, $centroidSaatIni);
            $indexLabel = $this->indexJarakTerdekat($jarak);
            // echo "<pre>" . print_r('index label:' . $iter, true) . "</pre>";
            // echo "<pre>" . print_r($indexLabel, true) . "</pre>";
            $this->centroids = $this->hitungCentroidBaru($x, $indexLabel);
            // echo "<pre>" . print_r($this->centroids, true) . "</pre>";
            // exit();
            if (($this->centroids == $centroidSaatIni && $iter > 50)) {
                break;
            }
        }

        $finalVectors = array_map(function ($item) {
            return $this->centroids[$item];
        }, $indexLabel);
        return $finalVectors;
    }
}

$kMeans = new KMeans($jmlCluster);
$newVectors = $kMeans->getNewVectors($dataset);

$newVectorKeahlianProjek = $newVectors[0];
$vectorLainSetara = array_filter($newVectors, fn($idx) => $newVectors[$idx] == $newVectorKeahlianProjek && $idx != 0, ARRAY_FILTER_USE_KEY);

$userIdLain = array_keys($vectorLainSetara);
$userYangSetara = array_map(function ($idx) use ($petaUserKeahlian) {
    return array_keys($petaUserKeahlian)[$idx + 1];
}, $userIdLain);
$userYangSetara = array_splice($userYangSetara, 0, $maxOrangPerCluster);
// array_keys($petaUserKeahlian);
// echo "<pre>".print_r($newVectors,true)."</pre>";
// echo "<pre>".print_r($newVectorKeahlianProjek,true)."</pre>";

echo 'Kebutuhan Projek:';
echo "\n";
foreach ($keahlianProjek as $idx) {
    if ($idx > 0) {
        echo $keahlian[$idx];
        echo "\n";
    }
}
echo "\n";
echo 'User Tersedia:';
echo "\n";
foreach ($userYangSetara as $data) {
    echo $data;
    echo "\n";
}
// echo "<pre>".print_r($userYangSetara,true)."</pre>";