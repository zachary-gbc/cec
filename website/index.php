<?php include('other/pretitle.php'); ?>
<title>Calendar Entry Checker</title>
<?php include('other/posttitle.php'); ?>

<h2>Calendar Entry Checker</h2>

<?php

$numbers="SELECT COUNT(E_ID) AS CountEntries FROM cec_Entries WHERE (E_Viewed='N')"; $number=0;
if(!$rs=mysqli_query($db,$numbers)) { echo("Unable to Run Query: $numbers"); exit; }
while($row = mysqli_fetch_array($rs)) { $number=$row['CountEntries']; }

if($number == 0) { echo("<h4>No Unviewed Entries - <a href='checkentries.php'>View Entries</a></h4>\n"); }
else { echo("<h4 style='color:red'>$number Unviewed Entries - <a href='checkentries.php'>View Entries</a></h4>\n"); }

?>

<?php include('other/footer.php'); ?>
