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

    $bestApproxArray = $currentFindArray;

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

function checkApproxParabola($x1, $i1, $y1, $x2, $i2)
{
    $sumOfRaznostSqr = 0;
	$a = ($i2-$i1)/($x2-$x1)/2;
	$b = $i1 - 2*$a*$x1;
	$c = $y1 - $a*$x1*$x1 -$b*$x1;
    //далее можно задать шаг проверки аппроксимации, например
    for ($i = $x1; $i <= $x2; $i += 0.1) {
        $idealValue = getCurrentValue($i);
        $approxValue = $a*$i*$i + $b*$i + $c;
        $sumOfRaznostSqr += ($idealValue - $approxValue) * ($idealValue - $approxValue);
    }
    return $sumOfRaznostSqr;
}


getini();
getGraph();

//firstStep();
//furtherSteps();
//furtherSteps();
//furtherSteps();



$X_MIN = $config['xMin'];
$X_MAX = $config['xMax'];
$X_END = $config['endX'];
//$Y_END = $config['endY'];
$Y_END = getCurrentValue($X_END);
$Y_MAX = $config['yMax'];
$I_START = $config['iStart'];
$step_x = $config['xStep'];
$step_y = $config['yStep'];
$eps = $config['epsilon'];
$vertical_number = ($X_MAX-$X_MIN)/$step_x + 1;
$y_number = 2*$Y_MAX/$step_y + 1;
$paths = array();
$point_arr = array(array());
$i = 0;
$answer = array("score" => INF, "prev" => false, "x" => $X_END, "y" => $Y_END);
$answer["curr_point"] = &$answer;
for($point_y = getCurrentValue($config['startX'])-$Y_MAX; $point_y <= getCurrentValue($config['startX']+$Y_MAX); $point_y += $step_y)
{
	$paths[$i] = array("x" => $config['startX'], "y" => $point_y, "connections" => array(array("i" => $I_START, "prev" => false)));
	$paths[$i]["connections"][0]["curr_point"] = &$paths[$i];
	$point_arr[0][$i] = array("x" => $config['startX'], "y" => $point_y, "approxes" => array(array("i" => $I_START, "approx_score" => 0, "ref_to_path" => &$paths[$i]["connections"][0])));
	$i++;
}
$path_prev_start_index = 0;
$path_prev_end_index = $y_number;
$path_curr_start_index = $y_number;
$path_curr_end_index = $path_curr_start_index;
while(count($point_arr) > 0)
{
	$new_point_arr = array();
	$new_x = $point_arr[0][0]["x"] + $X_MIN;
	for($i = 0; $i < count($point_arr) + $vertical_number - 1; $i++)
	{	
		if($new_x + $X_MIN <= $X_END)
		{
			$new_y = getCurrentValue($new_x)-$Y_MAX;
			for($j = 0; $j < $y_number; $j++)
			{
				$paths[$path_curr_start_index + $i * $y_number + $j] = array("x" => $new_x, "y" => $new_y, "connections" => array());
				//$paths[$path_curr_start_index + $i * $y_number + $j]["connections"]["curr_point"] = &$paths[$path_curr_start_index + $i * $y_number + $j];
				$new_point_arr[$i][$j] = array("x" => $new_x, "y" => $new_y, "approxes" => array());
				$new_y += $step_y;
			}
			$new_x += $step_x;
			$path_curr_end_index += $y_number;
		}
		else
			break;
	}
	//var_dump($new_point_arr);
	$cur_start = 0;
	if(count($new_point_arr) >= $vertical_number)
	{
		$cur_end = $vertical_number;
	}
	else
	{
		$cur_end = count($new_point_arr);
	}
	for($i = 0; $i < count($point_arr); $i++)
	{
		$vertical = $point_arr[$i];
		if($vertical[0]["x"] + 2*$X_MIN > $X_END)
		{
			if($vertical[0]["x"] + $X_MAX >= $X_END)
			{
				$new_point = array("x" => $X_END, "y" => $Y_END);
				for($j = 0; $j < count($vertical); $j++)
				{
					$point = $vertical[$j];
					foreach($point["approxes"] as $approxe)
					{
						$old_i = $approxe["i"];
						$new_i = 2*($new_point["y"] - $point["y"])/($new_point["x"] - $point["x"]) - $old_i;
						$approx_score = $approxe["approx_score"] + checkApproxParabola($point["x"], $old_i, $point["y"], $new_point["x"], $new_i);
						if($approx_score < $answer["score"])
						{
							$answer["score"] = $approx_score;
							$answer["prev"] = &$approxe["ref_to_path"];
							$answer["i"] = $new_i;
						}
					}
				}
			}
			continue;
		}
		$new_point = array("x" => $vertical[0]["x"] + $X_MIN, "y" => getCurrentValue($vertical[0]["x"] + $X_MIN)-$Y_MAX);
		for($j = $cur_start; $j < $cur_end; $j++)
		{
			for($k = 0; $k < count($vertical); $k++)
			{
				$point = $vertical[$k];
				$new_point["y"] = getCurrentValue($new_point["x"])-$Y_MAX;
				for($l = 0; $l < $y_number; $l++)
				{
					foreach($point["approxes"] as $approxe)
					{
						$old_i = $approxe["i"];
						$new_i = 2*($new_point["y"] - $point["y"])/($new_point["x"] - $point["x"]) - $old_i;
						//посчитать i конечный - готово
						$approx_score = $approxe["approx_score"] + checkApproxParabola($point["x"], $old_i, $point["y"], $new_point["x"], $new_i);//+ функция, которой еще нет))00
						$found_near = false;
						foreach($new_point_arr[$j][$l]["approxes"] as $new_point_approx) //это массив ее i и показателей аппроксимации
						{
							if(abs($new_i - $new_point_approx["i"]) < $eps)
							{
								//$found_near = true
							}
						}
						if(!$found_near)
						{
							$new_index = count($paths[$path_curr_start_index + $j * $y_number + $l]["connections"]);
							$paths[$path_curr_start_index + $j * $y_number + $l]["connections"][$new_index] = array("i" => $new_i,  "prev" => &$approxe["ref_to_path"], "curr_point" => &$paths[$path_curr_start_index + $j * $y_number + $l]);
							$new_point_arr[$j][$l]["approxes"][] = array("i" => $new_i, "approx_score" => $approx_score, "ref_to_path" => &$paths[$path_curr_start_index + $j * $y_number + $l]["connections"][$new_index]);
						}
					}
					$new_point["y"] += $step_y;
				}
			}	
			$new_point["x"] += $step_x;
		}
		$cur_start++;
		if($cur_end < count($new_point_arr))
		{
			$cur_end++;
		}

	}
	$path_prev_start_index = $path_curr_start_index;
	$path_prev_end_index = $path_curr_end_index;
	$path_curr_start_index = $path_prev_end_index;
	$path_curr_end_index = $path_curr_start_index;
	$point_arr = $new_point_arr;
}

$graph_step = 0.1;
$x2 = $X_END;
$x1 = 0;
$curr_path_part = &$answer;
$i = 0;
$j = 0;
do
{
	$i2 = $curr_path_part["i"];
	$y2 = $curr_path_part["curr_point"]["y"];
	$x2 = $curr_path_part["curr_point"]["x"];
	$curr_path_part = &$curr_path_part["prev"];
	$i1 = $curr_path_part["i"];
	$y1 = $curr_path_part["curr_point"]["y"];
	$x1 = $curr_path_part["curr_point"]["x"];
	$a1 = ($i2-$i1)/($x2-$x1)/2;
	$b = $i1 - 2*$a1*$x1;
	$c = $y1 - $a1*$x1*$x1 -$b*$x1;
	for($x = $x2;$x > $x1; $x -= $graph_step)
	{
		$currentFindArray[] = array(floatval($x), floatval($a1*$x*$x + $b*$x + $c));
	}
	$currentFindArray[] = array(floatval($x1), floatval($y1));
} while($curr_path_part["prev"] !== false);

$currentFindArray = array_reverse($currentFindArray);

//var_dump($answer);
//var_dump($paths);
//var_dump($currentFindArray);

drawGraphWithApprox();
//var_dump($currentFindArray);
//пример массива точек

/*$qwe [0][] = [1.5, 1.5];
$qwe[0][] = [2.5, 2.5];
$qwe [1][] = [2, 4];
$qwe[1][] = [8, 64];*/

//echo getCurrentValueOfArray($qwe[1],4.3);
//echo getCurrentValue(1.2);
//*/

//echo checkApprox($qwe[0]);
//drawGraph();

?>


