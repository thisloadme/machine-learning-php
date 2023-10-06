<!DOCTYPE html>
<html>

<head>
    <title>Fungsi Aktivasi</title>
</head>

<body>
    <?php
    $baseUrl = $_SERVER['PHP_SELF'] ?? '';

    $isPrediksi = isset($_POST['do_hitung']);

    if ($isPrediksi) {
        $input = $_POST['in'];
        $fn = $_POST['fn'];

        function sigmoid_($x)
        {
            return 1 / (1 + exp(- ($x)));
        }

        function tanh_($x)
        {
            return (2 / (1 + exp(- (2 * $x)))) - 1;
        }

        function relu_($x)
        {
            return max(0, $x);
        }

        switch ($fn) {
            case 'sigmoid':
                $output = sigmoid_($input);
                break;
            case 'tanh':
                $output = tanh_($input);
                break;
            case 'relu':
                $output = relu_($input);
                break;

            default:
                $output = 0;
                break;
        }
    }

    ?>

    <h1>Implementasi Fungsi Aktivasi</h1>

    <div>
        <form action="<?= $baseUrl ?>" method="post">
            <table style="width: 50%;">
                <tr>
                    <td>
                        <input type="number" name="in" value="<?= $isPrediksi ? $input : null ?>">
                    </td>
                    <td>
                        <select name="fn">
                            <option value="relu" <?php if ($isPrediksi && $fn == 'relu') : ?> selected <?php endif ?>>ReLU</option>
                            <option value="sigmoid" <?php if ($isPrediksi && $fn == 'sigmoid') : ?> selected <?php endif ?>>Sigmoid</option>
                            <option value="tanh" <?php if ($isPrediksi && $fn == 'tanh') : ?> selected <?php endif ?>>Tanh</option>
                        </select>
                    </td>
                    <td>
                        <input type="submit" value="Hitung Output" name="do_hitung">
                    </td>
                    <td>
                        <input type="number" name="out" value="<?= $isPrediksi ? $output : null ?>">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>