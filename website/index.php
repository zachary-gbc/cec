<?php include('other/pretitle.php'); ?>
<title>Calendar Entry Checker</title>
<?php include('other/posttitle.php'); ?>

<h2>Calendar Entry Checker</h2>

<?php

$numbers="SELECT COUNT(E_ID) AS CountEntries FROM cec_Entries WHERE (E_Viewed='N')"; $number=0;
if(!$rs=mysqli_query($db,$numbers)) { echo("Unable to Run Query: $numbers"); exit; }
while($row = mysqli_fetch_array($rs)) { $number=$row['CountEntries']; }

if($number == 0) { echo("<h4>No Unviewed Entries</h4>\n"); }
else { echo("<h4 style='color:red'>$number Unviewed Entries - <a href='checkentries.php'>View Entries</a></h4>\n"); }

$lastimport="SELECT Var_Value FROM Variables WHERE (Var_System='$system') AND (Var_Name='last-import')"; $lastimportdate=0;
if(!$rs=mysqli_query($db,$lastimport)) { echo("Unable to Run Query: $lastimport"); exit; }
while($row = mysqli_fetch_array($rs)) { $lastimportdate=$row['Var_Value']; }

echo("<h3>Last Import: " . date("F n, Y g:i a",strtotime($lastimportdate)) . "</h3>\n");

?>

<?php include('other/footer.php'); ?>
