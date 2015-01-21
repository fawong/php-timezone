<?php
/*
Copyright (c) 2005 Richard Stanway

This software is provided 'as-is', without any express or implied warranty. In no event will the authors be held liable for any damages arising from the use of this software.

Permission is granted to anyone to use this software for any purpose, including commercial applications, and to alter it and redistribute it freely, subject to the following restrictions:

    1. The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment in the product documentation would be appreciated but is not required.

    2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.

    3. This notice may not be removed or altered from any source distribution.

*/
    if (strstr ($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml"))
        header ("Content-Type: application/xhtml+xml; charset=UTF-8");
    else
        header ("Content-Type: text/html; charset=UTF-8");

    if (isset ($_REQUEST['submit']))
    {
        define (NUM_ZONES, 11);
        
        $zones[] = 'akst';
        $zones[] = 'pst';
        $zones[] = 'mst';
        $zones[] = 'cst1';
        $zones[] = 'est';
        $zones[] = 'gmt';
        $zones[] = 'cet';
        $zones[] = 'eet';
        $zones[] = 'cst2';
        $zones[] = 'jst';
        $zones[] = 'aus';
        
        $offset['akst'] = -9;
        $offset['pst'] = -8;
        $offset['mst'] = -7;
        $offset['cst1'] = -6;
        $offset['est'] = -5;
        $offset['gmt'] = 0;
        $offset['cet'] = 1;
        $offset['eet'] = 2;
        $offset['cst2'] = 8;
        $offset['jst'] = 9;
        $offset['aus'] = 10;

        for ($i = 0; $i < NUM_ZONES; $i++)
        {
            if ($_REQUEST[$zones[$i]] != '')
            {
                if (isset ($zone))
                {
                    $errorString = "Multiple times were entered. You should only enter a single time.";
                    break;
                }
                else
                {
                    $zone = $zones[$i];
                }
            }
        }


        if (!isset($errorString))
        {
            if (!isset($zone))
            {
                $zone = 'gmt';
                $time = gmdate ('Hi');
            }
            else
            {    
                $time = $_REQUEST[$zone];
            }

            if (preg_match ("/^(\d+):(\d+)(am|pm)/i", $time, $matches))
            {
                $hr = $matches[1];
                $mn = $matches[2];
                $ampm = $matches[3];
            }
            else if (preg_match ("/^(\d+)(am|pm)/i", $time, $matches))
            {
                $hr = $matches[1];
                $mn = 0;
                $ampm = $matches[2];
            }
            else
            {
                $inttime = TRUE;
            }

            if (!isset($inttime))
            {
                if ($ampm == 'pm')
                {
                    if ($hr > 12)
                    {
                        $errorString = "Invalid time '$time'.";
                    }
                    $hr += 12;
                }
                else if ($ampm == 'am')
                {
                    if ($hr > 12)
                    {
                        $errorString = "Invalid time '$time'.";
                    }
                }
                
                if (!isset($errorString))
                {
                    $time = sprintf ("%02d%02d", $hr, $mn);
                }
            }
            
            if (!preg_match ("/^\d\d\d\d$/", $time))
            {
                $errorString = "Invalid time '$time'. Please check the format and try again.";
            }
        }

        if (!isset($errorString))
        {
            $hr = substr($time, 0, 2);
            $mn = substr($time, 2, 2);

            $gmt_hr = $hr - $offset[$zone];
            $gmt_mn = $mn;

            if (empty($_REQUEST['12hr']))
            {
                $ampm = TRUE;
            }
            else
            {
                $ampm = FALSE;
            }

            for ($i = 0; $i < NUM_ZONES; $i++)
            {
                $off_hr = $gmt_hr + $offset[$zones[$i]];

                if ($off_hr > 23)
                {
                    $off_hr -= 24;
                }
                else if ($off_hr < 0)
                {
                    $off_hr += 24;
                }

                if ($i == 5)
                    $gmttime = sprintf ("%02d%02d", $off_hr, $gmt_mn);
                
                if ($ampm)
                {
                    if ($off_hr > 12)
                    {
                        $times[$zones[$i]] = sprintf ("%d:%02dpm", $off_hr - 12, $gmt_mn);
                    }
                    else
                    {
                        if ($off_hr == 0)
                        {
                            $off_hr = 12;
                        }
                        $times[$zones[$i]] = sprintf ("%d:%02dam", $off_hr, $gmt_mn);
                    }
                }
                else
                {
                    $times[$zones[$i]] = sprintf ("%02d:%02d", $off_hr, $gmt_mn);
                }
            }
        }
    }
    else
    {
        header ("Last-Modified: Fri, 15 Apr 2005 18:56:00 GMT");
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Timezone Converter</title>
</head>
<body>
<h1>Timezone Convertor</h1>
<p>This application will allow you to enter a time in EST, CST, GMT, CET, KST/JST, etc and have the relevant time for all other zones displayed.</p>
<?php
if (!(isset($_REQUEST['submit']) && !isset($errorString)))
{
?>
<h2>Base Time</h2>
<p>Enter the time in a usable format (eg 10:00, 1800, 3:30pm) into one of the fields and press the calculate button. If you leave all fields empty, the current time will be used.</p>
<?php
}
if (isset($errorString))
{
$hideform = FALSE;
print "<p><strong>ERROR:</strong> $errorString</p>\n";
}
else
{
if (isset($_REQUEST['submit']))
{
$hideform = TRUE;
}
}
?>
<form method="get" action="<?php print $_SERVER['SCRIPT_NAME'];?>">
<table>
<tr>
<th><acronym title="Alaska Standard Time (GMT -9)">AKST</acronym></th>
<th><acronym title="Pacific Standard Time (GMT -8)">PST</acronym></th>
<th><acronym title="Mountain Standard Time (GMT -7)">MST</acronym></th>
<th><acronym title="Central Standard Time (GMT -6)">CST</acronym></th>
<th><acronym title="Eastern Standard Time (GMT -5)">EST</acronym></th>
<th><acronym title="Coordinated Universal Time/Greenwich Mean Time">UTC/GMT</acronym></th>
<th><acronym title="Central European Time (GMT +1)">CET</acronym></th>
<th><acronym title="Eastern European Time (GMT +2)">EET</acronym></th>
<th><acronym title="China Standard Time (GMT +8)">CST</acronym></th>
<th><acronym title="Korean Standard Time (GMT +9)">KST</acronym>/<acronym title="Japan Standard Time (GMT +9)">JST</acronym></th>
<th><acronym title="Australian Time (GMT +10)">AUS</acronym></th>
</tr>
<tr>
<td><input type="text" name="akst" value="<?=$times['akst']?>" size="6" /></td>
<td><input type="text" name="pst" value="<?=$times['pst']?>" size="6" /></td>
<td><input type="text" name="mst" value="<?=$times['mst']?>" size="6" /></td>
<td><input type="text" name="cst1" value="<?=$times['cst1']?>" size="6" /></td>
<td><input type="text" name="est" value="<?=$times['est']?>" size="6" /></td>
<td><input type="text" name="gmt" value="<?=$times['gmt']?>" size="6" /></td>
<td><input type="text" name="cet" value="<?=$times['cet']?>" size="6" /></td>
<td><input type="text" name="eet" value="<?=$times['eet']?>" size="6" /></td>
<td><input type="text" name="cst2" value="<?=$times['cst2']?>" size="6" /></td>
<td><input type="text" name="jst" value="<?=$times['jst']?>" size="6" /></td>
<td><input type="text" name="aus" value="<?=$times['aus']?>" size="6" /></td>
</tr>
</table>
<?php
if (!$hideform)
{?>
<p><input type="checkbox" name="12hr" value="1" /> 24 hour output</p>
<p><input type="submit" name="submit" value="Calculate Times" /></p>
<?php
}
else
{
$amval = $ampm ? "1" : "0";
?>
<div>
<input type="hidden" name="useampm" id="useampm" value="<?=$amval?>" />
<input type="hidden" name="gmtvalue" id="gmtvalue" value="<?=$gmttime?>" />
</div>

<div id="tzText"></div>
<script type="text/javascript">
// <![CDATA[
var offStr;
now = new Date();
offset = now.getTimezoneOffset();

offset = -offset;
offset /= 60;

if (offset > 0)
{
offStr = '+' + offset;
}
else
{
offStr = offset;
}

var gmtTime;
var gmtHour;

if (document.getElementById)
{
gmtTime = document.getElementById('gmtvalue').value;
}
else
{
gmtTime = document.all.gmtvalue.value;
}

gmtHour = parseInt(gmtTime.substring (0, 2), 10);

gmtHour += offset;

if (gmtHour < 0)
gmtHour += 24;
else if (gmtHour > 23)
gmtHour -= 24;

var useAmPm;
var ampm;

if (document.getElementById)
{
useAmPm = document.getElementById('useampm').value;
}
else
{
useAmPm = document.all.useampm.value;
}

if (useAmPm == 1)
{
if (gmtHour > 12)
{
gmtHour -= 12;
ampm = 'pm';
}
else
{
if (gmtHour == 0)
gmtHour = 12;
ampm = 'am';
}

timeStr = gmtHour + ':' + gmtTime.substring (2, 4) + ampm;
}
else
{
timeStr = gmtHour + ':' + gmtTime.substring (2, 4);
}

if (document.getElementById)
{
var tz = document.getElementById('tzText');
var tzTextText = document.createTextNode('Your current timezone appears to be GMT ' + offStr + '. Based on this, the time entered would be ' + timeStr + ' in your local time.');

while (tz.firstChild)
{
tz.removeChild(tz.firstChild);
}

tz.appendChild(tzTextText);
}
else
{
document.all.tzText.innerHTML = 'Your current timezone appears to be GMT ' + offStr + '. Based on this, the time entered would be ' + timeStr + ' in your local time.';
}
// ]]>
</script>
<p><a href="<?=$_SERVER['PHP_SELF']?>">Return to form</a>.</p>
<?php } ?>
</form>
<p><a style="text-decoration: none; font-size: x-small; color: #AAA" href="https://github.com/fawong/timezone.php/blob/master/index.php">source</a> <a style="text-decoration: none; font-size: x-small; color: #AAA" href="http://validator.w3.org/check/referer">xhtml</a></p>
</body>
</html>
