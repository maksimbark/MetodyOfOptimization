<?php

global $config;
global $a;
global $currentFindArray;

$log_file = fopen("log.txt", 'w');

function getini()
{
    global $config;
    $file = 'config2.txt';

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
    $file = 'graphdata2.txt';

    $f = fopen($file, "r");
    $a = Array();

	$i = 0;
    while (($data = fgets($f))) {
        $a0 = explode(PHP_EOL, $data);
        $a0 = explode(' ', $a0[0]);
        if (is_numeric(trim($a0[0])) && is_numeric(trim($a0[1]))) 
			$a[$i] = array("x" => $a0[0], "y" => $a0[1]);
		$i++;
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

function checkApproxParam($n1, $ny1, $n2, $ny2)
{
	//echo $n1 . " " . $ny1 . " " . $n2 . " " . $ny2 . "<br>";
	global $a;
    $sumOfRaznostSqr = abs($ny2);
	
	$y1 = $a[$n1]["y"] + $a[$n1]["normal"]["y"] * $ny1;
	$x1 = $a[$n1]["x"] + $a[$n1]["normal"]["x"] * $ny1;
	
	$y2 = $a[$n2]["y"] + $a[$n2]["normal"]["y"] * $ny2;
	$x2 = $a[$n2]["x"] + $a[$n2]["normal"]["x"] * $ny2;
	
	//echo $a[$n1]["y"] . " " . $a[$n1]["normal"]["y"] . " " . $step_y . "<br>";
	//echo $x1 . " " . $y1 . " " . $x2 . " " . $y2 . "<br><br>";
	
	$v["x"] = ($x2 - $x1);
	$v["y"] = $y2 - $y1;
	//echo $b . " " . $c . "<br>";
    //далее можно задать шаг проверки аппроксимации, например
    for ($i = $n1 + 1; $i <= $n2 - 1; $i += 1) {
        /*$idealValue = getCurrentValue($i);
        $approxValue = $b*$i + $c;
        $sumOfRaznostSqr += ($idealValue - $approxValue) * ($idealValue - $approxValue);*/
		//echo $a[$i]["x"] . " " . $a[$i]["y"] . " " . $a[$i]["normal"]["x"] . " " . $a[$i]["normal"]["y"] . " " . "<br>";
		//$sumOfRaznostSqr += abs((($c - $a[$i]["y"] + $a[$i]["normal"]["y"]/$a[$i]["normal"]["x"]*$a[$i]["x"])/($a[$i]["normal"]["y"]/$a[$i]["normal"]["x"] - $b) - $a[$i]["x"])/ $a[$i]["normal"]["x"]);
		$sumOfRaznostSqr += abs(($v["x"]*($a[$i]["y"] - $y1) - $v["y"]*($a[$i]["x"] - $x1)) / (-$v["x"]*$a[$i]["normal"]["y"] + $v["y"]*$a[$i]["normal"]["x"]));
    }
    return $sumOfRaznostSqr;
}


getini();
getGraph();

$a[0]["normal"] = array("x" => 0, "y" => 1);

for($n = 1; $n < count($a) - 1; $n++)
{
	$x1 = $a[$n - 1]["x"];
	$y1 = $a[$n - 1]["y"];
	$x2 = $a[$n]["x"];
	$y2 = $a[$n]["y"];
	$x3 = $a[$n + 1]["x"];
	$y3 = $a[$n + 1]["y"];
	$t = 2*(($y3 - $y1)*($x2 - $x1) - ($y2 - $y1)*($x3 - $x1));
	if($t != 0)
	{
		$y0 = (($x1**2 + $y1**2 - $x2**2 - $y2**2)*($x3 - $x1) + ($x3**2 + $y3**2 - $x1**2 - $y1**2)*($x2 - $x1))/$t;
		$x0 = -($x1**2 + $y1**2 - $x2**2 - $y2**2)/2/($x2 - $x1) - ($y2 - $y1)/($x2 - $x1) * $y0;
	}
	else
	{
		$y0 = 1;
		$x0 = -($y2-$y1)/($x2-$x1);
	}
	$a[$n]["normal"] = array("x" => ($x0-$x2)/sqrt(($x0-$x2)*($x0-$x2) + ($y0 - $y2)*($y0 - $y2)), "y" => ($y0 - $y2)/sqrt(($x0-$x2)*($x0-$x2) + ($y0 - $y2)*($y0 - $y2)));
}

$a[count($a) - 1]["normal"] = array("x" => 0, "y" => 1);

//var_dump($a);

$NORMAL_MIN = $config['xMin'];
$NORMAL_MAX = $config['xMax'];
$N_END = count($a) - 1;//$config['endX'];
$Y_END = $config['endY'];
$Y_MAX = $config['yMax'];
$step_x = $config['xStep'];
$step_y = $config['yStep'];
$normal_number = ($NORMAL_MAX-$NORMAL_MIN)/$step_x + 1;
$y_number = 2*$Y_MAX/$step_y + 1;
$paths = array();
$point_arr = array(array());

$answer = array("score" => INF, "prev" => false, "x" => count($a) - 1, "y" => 0);
$paths[0] = array("x" => 0, "y" => 0, "prev" => false);
$point_arr[0][0] = array("x" => 0, "y" => 0, "approxe" => array("approx_score" => 0, "ref_to_path" => &$paths[0]));

$path_prev_start_index = 0;
$path_prev_end_index = 1;
$path_curr_start_index = 1;
$path_curr_end_index = $path_curr_start_index;
while(count($point_arr) > 0)
{
	$new_point_arr = array();
	$new_normal_number = $point_arr[0][0]["x"] + $NORMAL_MIN;
	for($i = 0; $i < count($point_arr) + $normal_number - 1; $i++)
	{	
		if($new_normal_number + $NORMAL_MIN <= $N_END)
		{
			$new_y = -$Y_MAX;
			for($j = 0; $j < $y_number; $j++)
			{
				$paths[$path_curr_start_index + $i * $y_number + $j] = array("x" => $new_normal_number, "y" => $new_y);
				$new_point_arr[$i][$j] = array("x" => $new_normal_number, "y" => $new_y, "approxe" => array("approx_score" => INF, "ref_to_path" => &$paths[$path_curr_start_index + $i * $y_number + $j]));
				$new_y += $step_y;
			}
			$new_normal_number += $step_x;
			$path_curr_end_index += $y_number;
		}
		else
			break;
	}
	//fwrite($log_file, count($new_point_arr) . " ");
	//var_dump($new_point_arr);
	$cur_start = 0;
	if(count($new_point_arr) >= $normal_number)
	{
		$cur_end = $normal_number;
	}
	else
	{
		$cur_end = count($new_point_arr);
	}
	for($i = 0; $i < count($point_arr); $i++)
	{
		$normal = $point_arr[$i];
		if($normal[0]["x"] + 2*$NORMAL_MIN > $N_END)
		{
			if($normal[0]["x"] + $NORMAL_MAX >= $N_END)
			{
				$new_point = array("x" => $answer["x"], "y" => $answer["y"]);
				for($j = 0; $j < count($normal); $j++)
				{
					$point = $normal[$j];
					$approxe = $point["approxe"];
					$approx_score = $approxe["approx_score"] + checkApproxParam($point["x"], $point["y"], $new_point["x"], $new_point["y"]);
					if($approx_score < $answer["score"])
					{
						$answer["score"] = $approx_score;
						$answer["prev"] = &$approxe["ref_to_path"];
					}
					
				}
			}
			continue;
		}
		$new_point = array("x" => $normal[0]["x"] + $NORMAL_MIN, "y" => -1);
		for($j = $cur_start; $j < $cur_end; $j++)
		{
			for($k = 0; $k < count($normal); $k++)
			{
				$point = $normal[$k];
				$new_point["y"] = -$Y_MAX;
				for($l = 0; $l < $y_number; $l++)
				{
					$approxe = $point["approxe"];
					$approx_score = $approxe["approx_score"] + checkApproxParam($point["x"], $point["y"], $new_point["x"], $new_point["y"]);
					if($approx_score < $new_point_arr[$j][$l]["approxe"]["approx_score"])
					{
						$new_point_arr[$j][$l]["approxe"]["approx_score"] = $approx_score;
						$new_point_arr[$j][$l]["approxe"]["ref_to_path"]["prev"] = &$approxe["ref_to_path"];
						$new_point_arr[$j][$l]["approxe"]["ref_to_path"]["approx_score"] = $approx_score;
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
	$path_prev_start_index = $path_curr_start_index;
	$path_prev_end_index = $path_curr_end_index;
	$path_curr_start_index = $path_prev_end_index;
	$path_curr_end_index = $path_curr_start_index;
	$point_arr = $new_point_arr;
}


$x2 = $N_END;
$x1 = 0;
$curr_path_part = &$answer;
$i = 0;
$j = 0;
while($curr_path_part !== false)
{
	$x1 = $curr_path_part["x"];
	$y1 = $curr_path_part["y"];
	$curr_path_part = &$curr_path_part["prev"];
	$currentFindArray[] = array(floatval($a[$x1]["x"] + $y1 * $a[$x1]["normal"]["x"]), floatval($a[$x1]["y"] + $y1 * $a[$x1]["normal"]["y"]));
};

$currentFindArray = array_reverse($currentFindArray);

$i = count($a) - 2;
$a[2 + ($i)*8] = $a[$i + 1]["x"];
$a[2 + ($i)*8 + 1] = $a[$i + 1]["y"];


for(; $i >= 1; $i -= 1)
{
	$a[2 + ($i - 1)*8] = $a[$i]["x"];
	$a[2 + ($i - 1)*8 + 1] = $a[$i]["y"];

	$a[2 + ($i - 1)*8 + 2] = $a[$i]["x"] + 0.1*$Y_MAX * $a[$i]["normal"]["x"];
	$a[2 + ($i - 1)*8 + 3] = $a[$i]["y"] + 0.1*$Y_MAX * $a[$i]["normal"]["y"];

	$a[2 + ($i - 1)*8 + 4] = $a[$i]["x"] - 0.1*$Y_MAX * $a[$i]["normal"]["x"];
	$a[2 + ($i - 1)*8 + 5] = $a[$i]["y"] - 0.1*$Y_MAX * $a[$i]["normal"]["y"];

	$a[2 + ($i - 1)*8 + 6] = $a[$i]["x"];
	$a[2 + ($i - 1)*8 + 7] = $a[$i]["y"];
}
$a[1] = $a[0]["y"];
$a[0] = $a[0]["x"];

//var_dump($answer);
//var_dump($paths);
//var_dump($currentFindArray);

drawGraphWithApprox();

?>


