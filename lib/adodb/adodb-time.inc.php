<?php






define('ADODB_DATE_VERSION',0.35);

$ADODB_DATETIME_CLASS = (PHP_VERSION >= 5.2);



if (!defined('ADODB_ALLOW_NEGATIVE_TS')) define('ADODB_NO_NEGATIVE_TS',1);

function adodb_date_test_date($y1,$m,$d=13)
{
	$h = round(rand()% 24);
	$t = adodb_mktime($h,0,0,$m,$d,$y1);
	$rez = adodb_date('Y-n-j H:i:s',$t);
	if ($h == 0) $h = '00';
	else if ($h < 10) $h = '0'.$h;
	if ("$y1-$m-$d $h:00:00" != $rez) {
		print "<b>$y1 error, expected=$y1-$m-$d $h:00:00, adodb=$rez</b><br>";
		return false;
	}
	return true;
}

function adodb_date_test_strftime($fmt)
{
	$s1 = strftime($fmt);
	$s2 = adodb_strftime($fmt);

	if ($s1 == $s2) return true;

	echo "error for $fmt,  strftime=$s1, adodb=$s2<br>";
	return false;
}


function adodb_date_test()
{

	for ($m=-24; $m<=24; $m++)
		echo "$m :",adodb_date('d-m-Y',adodb_mktime(0,0,0,1+$m,20,2040)),"<br>";

	error_reporting(E_ALL);
	print "<h4>Testing adodb_date and adodb_mktime. version=".ADODB_DATE_VERSION.' PHP='.PHP_VERSION."</h4>";
	@set_time_limit(0);
	$fail = false;

		if (!defined('ADODB_TEST_DATES')) define('ADODB_TEST_DATES',1);

	$t = time();


	$fmt = 'Y-m-d H:i:s';
	echo '<pre>';
	echo 'adodb: ',adodb_date($fmt,$t),'<br>';
	echo 'php  : ',date($fmt,$t),'<br>';
	echo '</pre>';

	adodb_date_test_strftime('%Y %m %x %X');
	adodb_date_test_strftime("%A %d %B %Y");
	adodb_date_test_strftime("%H %M S");

	$t = adodb_mktime(0,0,0);
	if (!(adodb_date('Y-m-d') == date('Y-m-d'))) print 'Error in '.adodb_mktime(0,0,0).'<br>';

	$t = adodb_mktime(0,0,0,6,1,2102);
	if (!(adodb_date('Y-m-d',$t) == '2102-06-01')) print 'Error in '.adodb_date('Y-m-d',$t).'<br>';

	$t = adodb_mktime(0,0,0,2,1,2102);
	if (!(adodb_date('Y-m-d',$t) == '2102-02-01')) print 'Error in '.adodb_date('Y-m-d',$t).'<br>';


	print "<p>Testing gregorian <=> julian conversion<p>";
	$t = adodb_mktime(0,0,0,10,11,1492);
		if (!(adodb_date('D Y-m-d',$t) == 'Fri 1492-10-11')) print 'Error in Columbus landing<br>';

	$t = adodb_mktime(0,0,0,2,29,1500);
	if (!(adodb_date('Y-m-d',$t) == '1500-02-29')) print 'Error in julian leap years<br>';

	$t = adodb_mktime(0,0,0,2,29,1700);
	if (!(adodb_date('Y-m-d',$t) == '1700-03-01')) print 'Error in gregorian leap years<br>';

	print  adodb_mktime(0,0,0,10,4,1582).' ';
	print adodb_mktime(0,0,0,10,15,1582);
	$diff = (adodb_mktime(0,0,0,10,15,1582) - adodb_mktime(0,0,0,10,4,1582));
	if ($diff != 3600*24) print " <b>Error in gregorian correction = ".($diff/3600/24)." days </b><br>";

	print " 15 Oct 1582, Fri=".(adodb_dow(1582,10,15) == 5 ? 'Fri' : '<b>Error</b>')."<br>";
	print " 4 Oct 1582, Thu=".(adodb_dow(1582,10,4) == 4 ? 'Thu' : '<b>Error</b>')."<br>";

	print "<p>Testing overflow<p>";

	$t = adodb_mktime(0,0,0,3,33,1965);
	if (!(adodb_date('Y-m-d',$t) == '1965-04-02')) print 'Error in day overflow 1 <br>';
	$t = adodb_mktime(0,0,0,4,33,1971);
	if (!(adodb_date('Y-m-d',$t) == '1971-05-03')) print 'Error in day overflow 2 <br>';
	$t = adodb_mktime(0,0,0,1,60,1965);
	if (!(adodb_date('Y-m-d',$t) == '1965-03-01')) print 'Error in day overflow 3 '.adodb_date('Y-m-d',$t).' <br>';
	$t = adodb_mktime(0,0,0,12,32,1965);
	if (!(adodb_date('Y-m-d',$t) == '1966-01-01')) print 'Error in day overflow 4 '.adodb_date('Y-m-d',$t).' <br>';
	$t = adodb_mktime(0,0,0,12,63,1965);
	if (!(adodb_date('Y-m-d',$t) == '1966-02-01')) print 'Error in day overflow 5 '.adodb_date('Y-m-d',$t).' <br>';
	$t = adodb_mktime(0,0,0,13,3,1965);
	if (!(adodb_date('Y-m-d',$t) == '1966-01-03')) print 'Error in mth overflow 1 <br>';

	print "Testing 2-digit => 4-digit year conversion<p>";
	if (adodb_year_digit_check(00) != 2000) print "Err 2-digit 2000<br>";
	if (adodb_year_digit_check(10) != 2010) print "Err 2-digit 2010<br>";
	if (adodb_year_digit_check(20) != 2020) print "Err 2-digit 2020<br>";
	if (adodb_year_digit_check(30) != 2030) print "Err 2-digit 2030<br>";
	if (adodb_year_digit_check(40) != 1940) print "Err 2-digit 1940<br>";
	if (adodb_year_digit_check(50) != 1950) print "Err 2-digit 1950<br>";
	if (adodb_year_digit_check(90) != 1990) print "Err 2-digit 1990<br>";

		print "<p>Testing date formating</p>";

	$fmt = '\d\a\t\e T Y-m-d H:i:s a A d D F g G h H i j l L m M n O \R\F\C2822 r s t U w y Y z Z 2003';
	$s1 = date($fmt,0);
	$s2 = adodb_date($fmt,0);
	if ($s1 != $s2) {
		print " date() 0 failed<br>$s1<br>$s2<br>";
	}
	flush();
	for ($i=100; --$i > 0; ) {

		$ts = 3600.0*((rand()%60000)+(rand()%60000))+(rand()%60000);
		$s1 = date($fmt,$ts);
		$s2 = adodb_date($fmt,$ts);
				$pos = strcmp($s1,$s2);

		if (($s1) != ($s2)) {
			for ($j=0,$k=strlen($s1); $j < $k; $j++) {
				if ($s1[$j] != $s2[$j]) {
					print substr($s1,$j).' ';
					break;
				}
			}
			print "<b>Error date(): $ts<br><pre>
&nbsp; \"$s1\" (date len=".strlen($s1).")
&nbsp; \"$s2\" (adodb_date len=".strlen($s2).")</b></pre><br>";
			$fail = true;
		}

		$a1 = getdate($ts);
		$a2 = adodb_getdate($ts);
		$rez = array_diff($a1,$a2);
		if (sizeof($rez)>0) {
			print "<b>Error getdate() $ts</b><br>";
				print_r($a1);
			print "<br>";
				print_r($a2);
			print "<p>";
			$fail = true;
		}
	}

		print "<p>Testing random dates between 100 and 4000</p>";
	adodb_date_test_date(100,1);
	for ($i=100; --$i >= 0;) {
		$y1 = 100+rand(0,1970-100);
		$m = rand(1,12);
		adodb_date_test_date($y1,$m);

		$y1 = 3000-rand(0,3000-1970);
		adodb_date_test_date($y1,$m);
	}
	print '<p>';
	$start = 1960+rand(0,10);
	$yrs = 12;
	$i = 365.25*86400*($start-1970);
	$offset = 36000+rand(10000,60000);
	$max = 365*$yrs*86400;
	$lastyear = 0;

			print "Testing $start to ".($start+$yrs).", or $max seconds, offset=$offset: ";
	$cnt = 0;
	for ($max += $i; $i < $max; $i += $offset) {
		$ret = adodb_date('m,d,Y,H,i,s',$i);
		$arr = explode(',',$ret);
		if ($lastyear != $arr[2]) {
			$lastyear = $arr[2];
			print " $lastyear ";
			flush();
		}
		$newi = adodb_mktime($arr[3],$arr[4],$arr[5],$arr[0],$arr[1],$arr[2]);
		if ($i != $newi) {
			print "Error at $i, adodb_mktime returned $newi ($ret)";
			$fail = true;
			break;
		}
		$cnt += 1;
	}
	echo "Tested $cnt dates<br>";
	if (!$fail) print "<p>Passed !</p>";
	else print "<p><b>Failed</b> :-(</p>";
}

function adodb_time()
{
	$d = new DateTime();
	return $d->format('U');
}


function adodb_dow($year, $month, $day)
{

	if ($year <= 1582) {
		if ($year < 1582 ||
			($year == 1582 && ($month < 10 || ($month == 10 && $day < 15)))) $greg_correction = 3;
		 else
			$greg_correction = 0;
	} else
		$greg_correction = 0;

	if($month > 2)
	    $month -= 2;
	else {
	    $month += 10;
	    $year--;
	}

	$day =  floor((13 * $month - 1) / 5) +
	        $day + ($year % 100) +
	        floor(($year % 100) / 4) +
	        floor(($year / 100) / 4) - 2 *
	        floor($year / 100) + 77 + $greg_correction;

	return $day - 7 * floor($day / 7);
}



function _adodb_is_leap_year($year)
{
	if ($year % 4 != 0) return false;

	if ($year % 400 == 0) {
		return true;
		} else if ($year > 1582 && $year % 100 == 0 ) {
		return false;
	}

	return true;
}



function adodb_is_leap_year($year)
{
	return  _adodb_is_leap_year(adodb_year_digit_check($year));
}


function adodb_year_digit_check($y)
{
	if ($y < 100) {

		$yr = (integer) date("Y");
		$century = (integer) ($yr /100);

		if ($yr%100 > 50) {
			$c1 = $century + 1;
			$c0 = $century;
		} else {
			$c1 = $century;
			$c0 = $century - 1;
		}
		$c1 *= 100;
						if (($y + $c1) < $yr+30) $y = $y + $c1;
		else $y = $y + $c0*100;
	}
	return $y;
}

function adodb_get_gmt_diff_ts($ts)
{
	if (0 <= $ts && $ts <= 0x7FFFFFFF) { 		$arr = getdate($ts);
		$y = $arr['year'];
		$m = $arr['mon'];
		$d = $arr['mday'];
		return adodb_get_gmt_diff($y,$m,$d);
	} else {
		return adodb_get_gmt_diff(false,false,false);
	}

}


function adodb_get_gmt_diff($y,$m,$d)
{
static $TZ,$tzo;
global $ADODB_DATETIME_CLASS;

	if (!defined('ADODB_TEST_DATES')) $y = false;
	else if ($y < 1970 || $y >= 2038) $y = false;

	if ($ADODB_DATETIME_CLASS && $y !== false) {
		$dt = new DateTime();
		$dt->setISODate($y,$m,$d);
		if (empty($tzo)) {
			$tzo = new DateTimeZone(date_default_timezone_get());
				}
		return -$tzo->getOffset($dt);
	} else {
		if (isset($TZ)) return $TZ;
		$y = date('Y');
		
		if (empty($TZ)) $TZ = mktime(0,0,0,12,2,$y) - gmmktime(0,0,0,12,2,$y);
	}
	return $TZ;
}


function adodb_getdate($d=false,$fast=false)
{
	if ($d === false) return getdate();
	if (!defined('ADODB_TEST_DATES')) {
		if ((abs($d) <= 0x7FFFFFFF)) { 			if (!defined('ADODB_NO_NEGATIVE_TS') || $d >= 0) 				return @getdate($d);
		}
	}
	return _adodb_getdate($d);
}




$_month_table_normal = array("",31,28,31,30,31,30,31,31,30,31,30,31);
$_month_table_leaf = array("",31,29,31,30,31,30,31,31,30,31,30,31);

function adodb_validdate($y,$m,$d)
{
global $_month_table_normal,$_month_table_leaf;

	if (_adodb_is_leap_year($y)) $marr = $_month_table_leaf;
	else $marr = $_month_table_normal;

	if ($m > 12 || $m < 1) return false;

	if ($d > 31 || $d < 1) return false;

	if ($marr[$m] < $d) return false;

	if ($y < 1000 && $y > 3000) return false;

	return true;
}


function _adodb_getdate($origd=false,$fast=false,$is_gmt=false)
{
static $YRS;
global $_month_table_normal,$_month_table_leaf;

	$d =  $origd - ($is_gmt ? 0 : adodb_get_gmt_diff_ts($origd));
	$_day_power = 86400;
	$_hour_power = 3600;
	$_min_power = 60;

	if ($d < -12219321600) $d -= 86400*10; 
	$_month_table_normal = array("",31,28,31,30,31,30,31,31,30,31,30,31);
	$_month_table_leaf = array("",31,29,31,30,31,30,31,31,30,31,30,31);

	$d366 = $_day_power * 366;
	$d365 = $_day_power * 365;

	if ($d < 0) {

		if (empty($YRS)) $YRS = array(
			1970 => 0,
			1960 => -315619200,
			1950 => -631152000,
			1940 => -946771200,
			1930 => -1262304000,
			1920 => -1577923200,
			1910 => -1893456000,
			1900 => -2208988800,
			1890 => -2524521600,
			1880 => -2840140800,
			1870 => -3155673600,
			1860 => -3471292800,
			1850 => -3786825600,
			1840 => -4102444800,
			1830 => -4417977600,
			1820 => -4733596800,
			1810 => -5049129600,
			1800 => -5364662400,
			1790 => -5680195200,
			1780 => -5995814400,
			1770 => -6311347200,
			1760 => -6626966400,
			1750 => -6942499200,
			1740 => -7258118400,
			1730 => -7573651200,
			1720 => -7889270400,
			1710 => -8204803200,
			1700 => -8520336000,
			1690 => -8835868800,
			1680 => -9151488000,
			1670 => -9467020800,
			1660 => -9782640000,
			1650 => -10098172800,
			1640 => -10413792000,
			1630 => -10729324800,
			1620 => -11044944000,
			1610 => -11360476800,
			1600 => -11676096000);

		if ($is_gmt) $origd = $d;
						
				
		

		$lastsecs = 0;
		$lastyear = 1970;
		foreach($YRS as $year => $secs) {
			if ($d >= $secs) {
				$a = $lastyear;
				break;
			}
			$lastsecs = $secs;
			$lastyear = $year;
		}

		$d -= $lastsecs;
		if (!isset($a)) $a = $lastyear;

		
		for (; --$a >= 0;) {
			$lastd = $d;

			if ($leaf = _adodb_is_leap_year($a)) $d += $d366;
			else $d += $d365;

			if ($d >= 0) {
				$year = $a;
				break;
			}
		}
		

		$secsInYear = 86400 * ($leaf ? 366 : 365) + $lastd;

		$d = $lastd;
		$mtab = ($leaf) ? $_month_table_leaf : $_month_table_normal;
		for ($a = 13 ; --$a > 0;) {
			$lastd = $d;
			$d += $mtab[$a] * $_day_power;
			if ($d >= 0) {
				$month = $a;
				$ndays = $mtab[$a];
				break;
			}
		}

		$d = $lastd;
		$day = $ndays + ceil(($d+1) / ($_day_power));

		$d += ($ndays - $day+1)* $_day_power;
		$hour = floor($d/$_hour_power);

	} else {
		for ($a = 1970 ;; $a++) {
			$lastd = $d;

			if ($leaf = _adodb_is_leap_year($a)) $d -= $d366;
			else $d -= $d365;
			if ($d < 0) {
				$year = $a;
				break;
			}
		}
		$secsInYear = $lastd;
		$d = $lastd;
		$mtab = ($leaf) ? $_month_table_leaf : $_month_table_normal;
		for ($a = 1 ; $a <= 12; $a++) {
			$lastd = $d;
			$d -= $mtab[$a] * $_day_power;
			if ($d < 0) {
				$month = $a;
				$ndays = $mtab[$a];
				break;
			}
		}
		$d = $lastd;
		$day = ceil(($d+1) / $_day_power);
		$d = $d - ($day-1) * $_day_power;
		$hour = floor($d /$_hour_power);
	}

	$d -= $hour * $_hour_power;
	$min = floor($d/$_min_power);
	$secs = $d - $min * $_min_power;
	if ($fast) {
		return array(
		'seconds' => $secs,
		'minutes' => $min,
		'hours' => $hour,
		'mday' => $day,
		'mon' => $month,
		'year' => $year,
		'yday' => floor($secsInYear/$_day_power),
		'leap' => $leaf,
		'ndays' => $ndays
		);
	}


	$dow = adodb_dow($year,$month,$day);

	return array(
		'seconds' => $secs,
		'minutes' => $min,
		'hours' => $hour,
		'mday' => $day,
		'wday' => $dow,
		'mon' => $month,
		'year' => $year,
		'yday' => floor($secsInYear/$_day_power),
		'weekday' => gmdate('l',$_day_power*(3+$dow)),
		'month' => gmdate('F',mktime(0,0,0,$month,2,1971)),
		0 => $origd
	);
}

function adodb_tz_offset($gmt,$isphp5)
{
	$zhrs = abs($gmt)/3600;
	$hrs = floor($zhrs);
	if ($isphp5)
		return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
	else
		return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
}


function adodb_gmdate($fmt,$d=false)
{
	return adodb_date($fmt,$d,true);
}

function adodb_date2($fmt, $d=false, $is_gmt=false)
{
	if ($d !== false) {
		if (!preg_match(
			"|^([0-9]{4})[-/\.]?([0-9]{1,2})[-/\.]?([0-9]{1,2})[ -]?(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?|",
			($d), $rr)) return adodb_date($fmt,false,$is_gmt);

		if ($rr[1] <= 100 && $rr[2]<= 1) return adodb_date($fmt,false,$is_gmt);

				if (!isset($rr[5])) $d = adodb_mktime(0,0,0,$rr[2],$rr[3],$rr[1],false,$is_gmt);
		else $d = @adodb_mktime($rr[5],$rr[6],$rr[7],$rr[2],$rr[3],$rr[1],false,$is_gmt);
	}

	return adodb_date($fmt,$d,$is_gmt);
}



function adodb_date($fmt,$d=false,$is_gmt=false)
{
static $daylight;
global $ADODB_DATETIME_CLASS;
static $jan1_1971;


	if (!isset($daylight)) {
		$daylight = function_exists('adodb_daylight_sv');
		if (empty($jan1_1971)) $jan1_1971 = mktime(0,0,0,1,1,1971); 	}

	if ($d === false) return ($is_gmt)? @gmdate($fmt): @date($fmt);
	if (!defined('ADODB_TEST_DATES')) {
		if ((abs($d) <= 0x7FFFFFFF)) { 
			if (!defined('ADODB_NO_NEGATIVE_TS') || $d >= $jan1_1971) 				return ($is_gmt)? @gmdate($fmt,$d): @date($fmt,$d);

		}
	}
	$_day_power = 86400;

	$arr = _adodb_getdate($d,true,$is_gmt);

	if ($daylight) adodb_daylight_sv($arr, $is_gmt);

	$year = $arr['year'];
	$month = $arr['mon'];
	$day = $arr['mday'];
	$hour = $arr['hours'];
	$min = $arr['minutes'];
	$secs = $arr['seconds'];

	$max = strlen($fmt);
	$dates = '';

	$isphp5 = PHP_VERSION >= 5;

	
	for ($i=0; $i < $max; $i++) {
		switch($fmt[$i]) {
		case 'e':
			$dates .= date('e');
			break;
		case 'T':
			if ($ADODB_DATETIME_CLASS) {
				$dt = new DateTime();
				$dt->SetDate($year,$month,$day);
				$dates .= $dt->Format('T');
			} else
				$dates .= date('T');
			break;
				case 'L': $dates .= $arr['leap'] ? '1' : '0'; break;
		case 'r': 
									$dates .= gmdate('D',$_day_power*(3+adodb_dow($year,$month,$day))).', '
				. ($day<10?'0'.$day:$day) . ' '.date('M',mktime(0,0,0,$month,2,1971)).' '.$year.' ';

			if ($hour < 10) $dates .= '0'.$hour; else $dates .= $hour;

			if ($min < 10) $dates .= ':0'.$min; else $dates .= ':'.$min;

			if ($secs < 10) $dates .= ':0'.$secs; else $dates .= ':'.$secs;

			$gmt = adodb_get_gmt_diff($year,$month,$day);

			$dates .= ' '.adodb_tz_offset($gmt,$isphp5);
			break;

		case 'Y': $dates .= $year; break;
		case 'y': $dates .= substr($year,strlen($year)-2,2); break;
				case 'm': if ($month<10) $dates .= '0'.$month; else $dates .= $month; break;
		case 'Q': $dates .= ($month+3)>>2; break;
		case 'n': $dates .= $month; break;
		case 'M': $dates .= date('M',mktime(0,0,0,$month,2,1971)); break;
		case 'F': $dates .= date('F',mktime(0,0,0,$month,2,1971)); break;
				case 't': $dates .= $arr['ndays']; break;
		case 'z': $dates .= $arr['yday']; break;
		case 'w': $dates .= adodb_dow($year,$month,$day); break;
		case 'l': $dates .= gmdate('l',$_day_power*(3+adodb_dow($year,$month,$day))); break;
		case 'D': $dates .= gmdate('D',$_day_power*(3+adodb_dow($year,$month,$day))); break;
		case 'j': $dates .= $day; break;
		case 'd': if ($day<10) $dates .= '0'.$day; else $dates .= $day; break;
		case 'S':
			$d10 = $day % 10;
			if ($d10 == 1) $dates .= 'st';
			else if ($d10 == 2 && $day != 12) $dates .= 'nd';
			else if ($d10 == 3) $dates .= 'rd';
			else $dates .= 'th';
			break;

				case 'Z':
			$dates .= ($is_gmt) ? 0 : -adodb_get_gmt_diff($year,$month,$day); break;
		case 'O':
			$gmt = ($is_gmt) ? 0 : adodb_get_gmt_diff($year,$month,$day);

			$dates .= adodb_tz_offset($gmt,$isphp5);
			break;

		case 'H':
			if ($hour < 10) $dates .= '0'.$hour;
			else $dates .= $hour;
			break;
		case 'h':
			if ($hour > 12) $hh = $hour - 12;
			else {
				if ($hour == 0) $hh = '12';
				else $hh = $hour;
			}

			if ($hh < 10) $dates .= '0'.$hh;
			else $dates .= $hh;
			break;

		case 'G':
			$dates .= $hour;
			break;

		case 'g':
			if ($hour > 12) $hh = $hour - 12;
			else {
				if ($hour == 0) $hh = '12';
				else $hh = $hour;
			}
			$dates .= $hh;
			break;
				case 'i': if ($min < 10) $dates .= '0'.$min; else $dates .= $min; break;
				case 'U': $dates .= $d; break;
		case 's': if ($secs < 10) $dates .= '0'.$secs; else $dates .= $secs; break;
						case 'a':
			if ($hour>=12) $dates .= 'pm';
			else $dates .= 'am';
			break;
		case 'A':
			if ($hour>=12) $dates .= 'PM';
			else $dates .= 'AM';
			break;
		default:
			$dates .= $fmt[$i]; break;
				case "\\":
			$i++;
			if ($i < $max) $dates .= $fmt[$i];
			break;
		}
	}
	return $dates;
}


function adodb_gmmktime($hr,$min,$sec,$mon=false,$day=false,$year=false,$is_dst=false)
{
	return adodb_mktime($hr,$min,$sec,$mon,$day,$year,$is_dst,true);
}


function adodb_mktime($hr,$min,$sec,$mon=false,$day=false,$year=false,$is_dst=false,$is_gmt=false)
{
	if (!defined('ADODB_TEST_DATES')) {

		if ($mon === false) {
			return $is_gmt? @gmmktime($hr,$min,$sec): @mktime($hr,$min,$sec);
		}

						$usephpfns = (1970 < $year && $year < 2038
			|| !defined('ADODB_NO_NEGATIVE_TS') && (1901 < $year && $year < 2038)
			);


		if ($usephpfns && ($year + $mon/12+$day/365.25+$hr/(24*365.25) >= 2038)) $usephpfns = false;

		if ($usephpfns) {
				return $is_gmt ?
					@gmmktime($hr,$min,$sec,$mon,$day,$year):
					@mktime($hr,$min,$sec,$mon,$day,$year);
		}
	}

	$gmt_different = ($is_gmt) ? 0 : adodb_get_gmt_diff($year,$mon,$day);

	
	$mon = intval($mon);
	$day = intval($day);
	$year = intval($year);


	$year = adodb_year_digit_check($year);

	if ($mon > 12) {
		$y = floor(($mon-1)/ 12);
		$year += $y;
		$mon -= $y*12;
	} else if ($mon < 1) {
		$y = ceil((1-$mon) / 12);
		$year -= $y;
		$mon += $y*12;
	}

	$_day_power = 86400;
	$_hour_power = 3600;
	$_min_power = 60;

	$_month_table_normal = array("",31,28,31,30,31,30,31,31,30,31,30,31);
	$_month_table_leaf = array("",31,29,31,30,31,30,31,31,30,31,30,31);

	$_total_date = 0;
	if ($year >= 1970) {
		for ($a = 1970 ; $a <= $year; $a++) {
			$leaf = _adodb_is_leap_year($a);
			if ($leaf == true) {
				$loop_table = $_month_table_leaf;
				$_add_date = 366;
			} else {
				$loop_table = $_month_table_normal;
				$_add_date = 365;
			}
			if ($a < $year) {
				$_total_date += $_add_date;
			} else {
				for($b=1;$b<$mon;$b++) {
					$_total_date += $loop_table[$b];
				}
			}
		}
		$_total_date +=$day-1;
		$ret = $_total_date * $_day_power + $hr * $_hour_power + $min * $_min_power + $sec + $gmt_different;

	} else {
		for ($a = 1969 ; $a >= $year; $a--) {
			$leaf = _adodb_is_leap_year($a);
			if ($leaf == true) {
				$loop_table = $_month_table_leaf;
				$_add_date = 366;
			} else {
				$loop_table = $_month_table_normal;
				$_add_date = 365;
			}
			if ($a > $year) { $_total_date += $_add_date;
			} else {
				for($b=12;$b>$mon;$b--) {
					$_total_date += $loop_table[$b];
				}
			}
		}
		$_total_date += $loop_table[$mon] - $day;

		$_day_time = $hr * $_hour_power + $min * $_min_power + $sec;
		$_day_time = $_day_power - $_day_time;
		$ret = -( $_total_date * $_day_power + $_day_time - $gmt_different);
		if ($ret < -12220185600) $ret += 10*86400; 		else if ($ret < -12219321600) $ret = -12219321600; 	}
		return $ret;
}

function adodb_gmstrftime($fmt, $ts=false)
{
	return adodb_strftime($fmt,$ts,true);
}

function adodb_strftime($fmt, $ts=false,$is_gmt=false)
{
global $ADODB_DATE_LOCALE;

	if (!defined('ADODB_TEST_DATES')) {
		if ((abs($ts) <= 0x7FFFFFFF)) { 			if (!defined('ADODB_NO_NEGATIVE_TS') || $ts >= 0) 				return ($is_gmt)? @gmstrftime($fmt,$ts): @strftime($fmt,$ts);

		}
	}

	if (empty($ADODB_DATE_LOCALE)) {
	
				$dstr = gmstrftime('%x',31366800); 		$sep = substr($dstr,2,1);
		$tstr = strtoupper(gmstrftime('%X',31366800)); 		$hasAM = strrpos($tstr,'M') !== false;

		$ADODB_DATE_LOCALE = array();
		$ADODB_DATE_LOCALE[] =  strncmp($tstr,'30',2) == 0 ? 'd'.$sep.'m'.$sep.'y' : 'm'.$sep.'d'.$sep.'y';
		$ADODB_DATE_LOCALE[]  = ($hasAM) ? 'h:i:s a' : 'H:i:s';

	}
	$inpct = false;
	$fmtdate = '';
	for ($i=0,$max = strlen($fmt); $i < $max; $i++) {
		$ch = $fmt[$i];
		if ($ch == '%') {
			if ($inpct) {
				$fmtdate .= '%';
				$inpct = false;
			} else
				$inpct = true;
		} else if ($inpct) {

			$inpct = false;
			switch($ch) {
			case '0':
			case '1':
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
			case 'E':
			case 'O':
				
				$inpct = true;
				break;

			case 'a': $fmtdate .= 'D'; break;
			case 'A': $fmtdate .= 'l'; break;
			case 'h':
			case 'b': $fmtdate .= 'M'; break;
			case 'B': $fmtdate .= 'F'; break;
			case 'c': $fmtdate .= $ADODB_DATE_LOCALE[0].$ADODB_DATE_LOCALE[1]; break;
			case 'C': $fmtdate .= '\C?'; break; 			case 'd': $fmtdate .= 'd'; break;
			case 'D': $fmtdate .= 'm/d/y'; break;
			case 'e': $fmtdate .= 'j'; break;
			case 'g': $fmtdate .= '\g?'; break; 			case 'G': $fmtdate .= '\G?'; break; 			case 'H': $fmtdate .= 'H'; break;
			case 'I': $fmtdate .= 'h'; break;
			case 'j': $fmtdate .= '?z'; $parsej = true; break; 			case 'm': $fmtdate .= 'm'; break;
			case 'M': $fmtdate .= 'i'; break;
			case 'n': $fmtdate .= "\n"; break;
			case 'p': $fmtdate .= 'a'; break;
			case 'r': $fmtdate .= 'h:i:s a'; break;
			case 'R': $fmtdate .= 'H:i:s'; break;
			case 'S': $fmtdate .= 's'; break;
			case 't': $fmtdate .= "\t"; break;
			case 'T': $fmtdate .= 'H:i:s'; break;
			case 'u': $fmtdate .= '?u'; $parseu = true; break; 			case 'U': $fmtdate .= '?U'; $parseU = true; break;			case 'x': $fmtdate .= $ADODB_DATE_LOCALE[0]; break;
			case 'X': $fmtdate .= $ADODB_DATE_LOCALE[1]; break;
			case 'w': $fmtdate .= '?w'; $parseu = true; break; 			case 'W': $fmtdate .= '?W'; $parseU = true; break;			case 'y': $fmtdate .= 'y'; break;
			case 'Y': $fmtdate .= 'Y'; break;
			case 'Z': $fmtdate .= 'T'; break;
			}
		} else if (('A' <= ($ch) && ($ch) <= 'Z' ) || ('a' <= ($ch) && ($ch) <= 'z' ))
			$fmtdate .= "\\".$ch;
		else
			$fmtdate .= $ch;
	}
		if ($ts === false) $ts = time();
	$ret = adodb_date($fmtdate, $ts, $is_gmt);
	return $ret;
}
