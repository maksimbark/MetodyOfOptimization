<?php

global $config;
global $a;
global $currentFindArray;

$log_file = fopen("log.txt", 'w');

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


;

function checkApproxLine($x1, $y1, $x2, $y2)
{
    $sumOfRaznostSqr = 0;
	$b = ($y2 - $y1)/($x2 - $x1);
	$c = $y1 - $b * $x1;
    //далее можно задать шаг проверки аппроксимации, например
    for ($i = $x1; $i <= $x2; $i += 0.1) {
        $idealValue = getCurrentValue($i);
        $approxValue = $b*$i + $c;
        $sumOfRaznostSqr += ($idealValue - $approxValue) * ($idealValue - $approxValue);
    }
    return $sumOfRaznostSqr;
}


getini();
getGraph();





$X_MIN = $config['xMin'];
$X_MAX = $config['xMax'];
$X_END = $config['endX'];
//$Y_END = $config['endY'];
$Y_END = getCurrentValue($X_END);
$Y_MAX = $config['yMax'];
$step_x = $config['xStep'];
$step_y = $config['yStep'];
$vertical_number = round(($X_MAX-$X_MIN)/$step_x - 0.5) + 1;
$y_number = round(2*$Y_MAX/$step_y - 0.5) + 1;
$paths = array();
$point_arr = array(array());
$i = 0;
$answer = array("score" => INF, "prev" => false, "x" => $X_END, "y" => $Y_END);
for($point_y = getCurrentValue($config['startX'])-$Y_MAX; $point_y <= getCurrentValue($config['startX']+$Y_MAX); $point_y += $step_y)
{
	$paths[$i] = array("x" => $config['startX'], "y" => $point_y, "prev" => false);
	//$paths[$i]["connections"][0]["curr_point"] = &$paths[$i];
	$point_arr[0][$i] = array("x" => $config['startX'], "y" => $point_y, "approxe" => array("approx_score" => 0, "ref_to_path" => &$paths[$i]));
	$i++;
}
//$path_prev_start_index = 0;
//$path_prev_end_index = $y_number;
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
				$paths[$path_curr_start_index + $i * $y_number + $j] = array("x" => $new_x, "y" => $new_y);
				$new_point_arr[$i][$j] = array("x" => $new_x, "y" => $new_y, "approxe" => array("approx_score" => INF, "ref_to_path" => &$paths[$path_curr_start_index + $i * $y_number + $j]));
				$new_y += $step_y;
			}
			$new_x += $step_x;
			$path_curr_end_index += $y_number;
		}
		else
			break;
	}
	fwrite($log_file, count($new_point_arr) . " ");
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
					$approxe = $point["approxe"];
					$approx_score = $approxe["approx_score"] + checkApproxLine($point["x"], $point["y"], $new_point["x"], $new_point["y"]);
					if($approx_score < $answer["score"])
					{
						$answer["score"] = $approx_score;
						$answer["prev"] = &$approxe["ref_to_path"];
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
					$approxe = $point["approxe"];
					$approx_score = $approxe["approx_score"] + checkApproxLine($point["x"], $point["y"], $new_point["x"], $new_point["y"]);
					if($approx_score < $new_point_arr[$j][$l]["approxe"]["approx_score"])
					{
						$new_point_arr[$j][$l]["approxe"]["approx_score"] = $approx_score;
						$new_point_arr[$j][$l]["approxe"]["ref_to_path"]["prev"] = &$approxe["ref_to_path"];
						//$new_point_arr[$j][$l]["approxe"]["ref_to_path"]["score"] = $approx_score;
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
	//$path_prev_start_index = $path_curr_start_index;
	//$path_prev_end_index = $path_curr_end_index;
	$path_curr_start_index = $path_curr_end_index;
	//$path_curr_end_index = $path_curr_start_index;
	$point_arr = $new_point_arr;
}


//$x2 = $X_END;
//$x1 = 0;
$curr_path_part = &$answer;
//$i = 0;
//$j = 0;
while($curr_path_part !== false)
{
	$x1 = $curr_path_part["x"];
	$y1 = $curr_path_part["y"];
	$curr_path_part = &$curr_path_part["prev"];
	$currentFindArray[] = array(floatval($x1), floatval($y1));
};

$currentFindArray = array_reverse($currentFindArray);

//var_dump($answer);
//var_dump($paths);
//var_dump($currentFindArray);

drawGraphWithApprox();

?>


