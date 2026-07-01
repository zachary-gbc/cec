<?php include('other/pretitle.php'); ?>
<title>Calendar Entry Checker</title>
<?php include('other/posttitle.php'); ?>

<h2>Calendar Entry Checker</h2><br>

<?php

$numbers="SELECT COUNT(E_ID) AS CountEntries FROM cec_Entries WHERE (E_Viewed='N')"; $number=0;
if(!$rs=mysqli_query($db,$numbers)) { echo("Unable to Run Query: $numbers"); exit; }
while($row = mysqli_fetch_array($rs)) { $number=$row['CountEntries']; }

echo("<h4>$number Unviewed Entries - <a href='checkentries.php'>View Entries</a></h4>\n");

?>

<?php include('other/footer.php'); ?>
