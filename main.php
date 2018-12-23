<?php

global $config;
global $a;
global $currentFindArray;

function getini()
{
    global $config;
    $file = 'config.ini';

    $f = fopen($file, "r");
    $a = Array();

    while (($data = fgets($f))) {
        $a0 = explode(PHP_EOL, $data);
        $a0 = explode(' ', $a0[0]);

        $config[$a0[0]] = $a0[1];

    }
    fclose($f);
}

;

function getGraph()
{
    global $a;
    //считываем данные по графику
    $file = 'graphdata.txt';

    $f = fopen($file, "r");
    $a = Array();

    while (($data = fgets($f))) {
        $a0 = explode(PHP_EOL, $data);
        $a0 = explode(' ', $a0[0]);
        foreach ($a0 as $item)
            if (is_numeric(trim($item))) $a[] = $item;
    }
    fclose($f);
}

;

function drawGraph()
{
    global $a;
    require('firstpart.html');

    echo("['x','y'],");
    for ($item = 0; $item < count($a); $item += 2) {
        echo '[' . $a[$item] . ',' . $a[$item + 1] . ']';
        if ($item + 2 != count($a)) echo ',' . PHP_EOL;
    }


    require('secondpart.html');
}

function drawGraphWithApprox()
{
    global $a;
    global $currentFindArray;

    $bestApproxValue = 999999999999;

    foreach ($currentFindArray as $item) {
        if (checkApprox($item) < $bestApproxValue) {
            $bestApproxValue = checkApprox($item);
            $bestApproxArray = $item;
        }
    }

    require('firstpart.html');
    //var_dump($bestApproxArray);
    echo("['x','Graph','Approximation'],");

    $i = 0;
    $j = 0;
    while (($i < count($a)) || ($j < count($bestApproxArray))) {
        if (($i < count($a)) && ($j < count($bestApproxArray))) {
            if ($a[$i] == $bestApproxArray[$j][0]) {
                echo '[' . $a[$i] . ',' . $a[$i + 1] . ',' . $bestApproxArray[$j][1] . ']';
                //запятуху!
                $i += 2;
                ++$j;
                if (($i < count($a)) || ($j < count($bestApproxArray))) {
                    echo ',' . PHP_EOL;
                }
            } else if ($a[$i] < $bestApproxArray[$j][0]) {
                echo '[' . $a[$i] . ',' . $a[$i + 1] . ',null]';
                //запятуху!
                $i += 2;
                if (($i < count($a)) || ($j < count($bestApproxArray))) {
                    echo ',' . PHP_EOL;
                }
            } else {
                echo '[' . $bestApproxArray[$j][0] . ',null,' . $bestApproxArray[$j][1] . ']';
                //запятуху!
                ++$j;
                if (($i < count($a)) || ($j < count($bestApproxArray))) {
                    echo ',' . PHP_EOL;
                }
            }


        } else if ($i < count($a)) {
            echo '[' . $a[$i] . ',' . $a[$i + 1] . ',null]';
            //запятуху!
            $i += 2;
            if ($i < count($a)) {
                echo ',' . PHP_EOL;
            }
        } else {
            echo '[' . $bestApproxArray[$j][0] . ',null,' . $bestApproxArray[$j][1] . ']';
            //запятуху!
            ++$j;
            if ($j < count($bestApproxArray)) {
                echo ',' . PHP_EOL;
            }
        }
    }
      require('secondpart.html');
}


function getCurrentValue($point)
{
    global $a;
    $mini = 0;
    $miniX = 0;
    $maxi = 0;
    $maxiX = 0;
    $inited = false;

    for ($item = 0; $item < count($a); $item += 2) {
        if ($a[$item] == $point) return $a[$item + 1];

        if ($a[$item] < $point) {
            $mini = $a[$item + 1];
            $miniX = $a[$item];
        }
        if ($a[$item] > $point) {
            $maxi = $a[$item + 1];
            $maxiX = $a[$item];
            $inited = true;
            break;
        }

    }

    if (!$inited) {
        $mini = $a[count($a) - 3];
        $miniX = $a[count($a) - 4];

        $maxi = $a[count($a) - 1];
        $maxiX = $a[count($a) - 2];
    }

    $distance = $maxiX - $miniX;
    $height = $maxi - $mini;

    return $mini + ((($point - $miniX) * $height) / $distance);
}

function getCurrentValueOfArray($array, $point)
{
    $mini = 0;
    $miniX = 0;
    $maxi = 0;
    $maxiX = 0;
    $inited = false;

    foreach ($array as $currentPoint) {


        if ($currentPoint[0] == $point) return $currentPoint[1];

        if ($currentPoint[0] < $point) {
            $mini = $currentPoint[1];
            $miniX = $currentPoint[0];
        }
        if ($currentPoint[0] > $point) {
            $maxi = $currentPoint[1];
            $maxiX = $currentPoint[0];
            $inited = true;
            break;
        }


    }

    if (!$inited) {
        $mini = $array[count($array) - 2][1];
        $miniX = $array[count($array) - 2][0];

        $maxi = $array[count($array) - 1][1];
        $maxiX = $array[count($array) - 1][0];
    }

    $distance = $maxiX - $miniX;
    $height = $maxi - $mini;

    return $mini + ((($point - $miniX) * $height) / $distance);
}

function checkApprox($array)
{
    $final = $array[count($array) - 1][0];

    $first = $array[0][0];

    $sumOfRaznostSqr = 0;
    //далее можно задать шаг проверки аппроксимации, например
    for ($i = $first; $i <= $final; $i += 0.1) {
        $idealValue = getCurrentValue($i);
        $approxValue = getCurrentValueOfArray($array, $i);
        $sumOfRaznostSqr += ($idealValue - $approxValue) * ($idealValue - $approxValue);
    }
    return $sumOfRaznostSqr;
}

function firstStep()
{
    global $config;
    global $currentFindArray;

    // echo('Starting at X = ' . $config['startX'] . ' trying to start on y = ' . PHP_EOL);
    $y = getCurrentValue($config['startX']);

    for ($i = $y - $config['yMax']; $i <= $y + $config['yMax']; $i += $config['step']) {
        $startArray[] = [$config['startX'], $i];
    }

    // echo('To the points:' . PHP_EOL);

    for ($i = $config['startX'] + $config['xMin']; $i <= $config['startX'] + $config['xMax']; $i += $config['step']) {
        $y = getCurrentValue($i);
        for ($j = $y - $config['yMax']; $j <= $y + $config['yMax']; $j += $config['step']) {
            //echo($i . ' ' . $j . PHP_EOL);
            $bestApproxValue = 999999999999;

            foreach ($startArray as $element) {

                $letsFind [] = $element;
                $letsFind [] = [$i, $j];
                // var_dump($letsFind);
                if (checkApprox($letsFind) < $bestApproxValue) {
                    $bestApproxValue = checkApprox($letsFind);
                    $bestApproxArray = $letsFind;
                };
                unset($letsFind);

            }
            $currentFindArray [] = $bestApproxArray;
        }
    }


}

;

function furtherSteps()
{
    global $currentFindArray;
    global $config;

    $firstX = $currentFindArray[0][count($currentFindArray[0]) - 1][0];
    $lastX = $currentFindArray[count($currentFindArray) - 1][count($currentFindArray[0]) - 1][0];
    //echo $firstX . ' ' . $lastX;

    for ($i = $firstX + $config['xMin']; $i <= $lastX + $config['xMax']; $i += $config['step']) {
        $y = getCurrentValue($i);
        for ($j = $y - $config['yMax']; $j <= $y + $config['yMax']; $j += $config['step']) {
            //echo($i . ' ' . $j . PHP_EOL);
            $bestApproxValue = 999999999999;

            foreach ($currentFindArray as $element) {
                if ((($i - $element[count($element) - 1][0]) >= $config['xMin']) && (($i - $element[count($element) - 1][0]) <= $config['xMax'])) {

                    $letsFind = $element;
                    $letsFind [] = [$i, $j];

                    if (checkApprox($letsFind) < $bestApproxValue) {
                        $bestApproxValue = checkApprox($letsFind);
                        $bestApproxArray = $letsFind;
                    };
                    unset($letsFind);
                }

            }
            $newFindArray [] = $bestApproxArray;
        }
    }

    $currentFindArray = $newFindArray;

}

;


getini();
getGraph();
firstStep();
furtherSteps();
furtherSteps();
furtherSteps();


drawGraphWithApprox();
//var_dump($currentFindArray);
//пример массива точек

$qwe [0][] = [1.5, 1.5];
$qwe[0][] = [2.5, 2.5];
$qwe [1][] = [2, 4];
$qwe[1][] = [8, 64];

//echo getCurrentValueOfArray($qwe[1],4.3);
//echo getCurrentValue(1.2);
//*/

//echo checkApprox($qwe[0]);
//drawGraph();

?>


