<!DOCTYPE html>
<html>
<head>
    <title>GPS API Test</title>
</head>
<body>

<form action="api/save.php" method="POST">

Device Code<br>
<input type="text" name="device_code" value="ESP32-001"><br><br>

Latitude<br>
<input type="text" name="latitude" value="17.614500"><br><br>

Longitude<br>
<input type="text" name="longitude" value="121.726800"><br><br>

Altitude<br>
<input type="text" name="altitude" value="25"><br><br>

Speed<br>
<input type="text" name="speed" value="15"><br><br>

Satellites<br>
<input type="text" name="satellites" value="10"><br><br>

GPS Status<br>
<input type="text" name="gps_status" value="GPS FIX"><br><br>

<input type="submit" value="Send GPS Data">

</form>

</body>
</html>