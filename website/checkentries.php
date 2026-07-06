<?php include('other/pretitle.php'); ?>
<title>View Entries</title>
<?php include('other/posttitle.php'); ?>

<?php

if(isset($_POST['submit']))
{
    $unviewed="SELECT * FROM cec_Entries WHERE (E_Viewed='N')"; $markviewed="";
    if(!$rs=mysqli_query($db,$unviewed)) { echo("Unable to Run Query: $unviewed"); exit; }
    while($row = mysqli_fetch_array($rs)) { $id=$row['E_ID']; if(isset($_POST["$id-checkbox"])) { $markviewed.=", '$id'"; } }

    if($markviewed != "")
    {
        $markviewed=substr($markviewed,2);
        $update="UPDATE cec_Entries SET E_Viewed='Y' WHERE E_ID IN ($markviewed)";
        if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
    }
}

$recurringandnote=array(); $recurring=array(); $notes=array(); $new=array(); $starts=array(); $ends=array();
$descriptions=array(); $summaries=array(); $locations=array();
$unviewed="SELECT * FROM cec_Entries WHERE (E_Viewed='N')";
if(!$rs=mysqli_query($db,$unviewed)) { echo("Unable to Run Query: $unviewed"); exit; }
while($row = mysqli_fetch_array($rs))
{
    $id=$row['E_ID'];
    if($row['E_Recurring'] == "Y" && trim($row['E_ScriptNote']) != "") { $recurringandnote[$id]=$id; }
    elseif($row['E_Recurring'] == "Y") { $recurring[$id]=$id; }
    elseif(trim($row['E_ScriptNote']) != "") { $notes[$id]=$id; }
    else { $new[$id]=$id; }
    $starts[$id]=$row['E_Start'];
    $ends[$id]=$row['E_End'];
    if(trim($row['E_Description']) != "") { $descriptions[$id]=$row['E_Description']; } else { $descriptions[$id]=""; }
    if(trim($row['E_Summary']) != "") { $summaries[$id]=$row['E_Summary']; } else { $summaries[$id]=""; }
    if(trim($row['E_Location']) != "") { $locations[$id]=("(".$row['E_Location'].")"); } else { $locations[$id]=""; }
    $scriptnotes[$id]=$row['E_ScriptNote'];
}

if(count($new) > 0)
{
    echo("<h3>New Items:</h3>\n<form method='post' action=''>\n");
    foreach($new as $id)
    {
        echo("<input type='checkbox' name='$id-checkbox' checked='checked' /> ");
        echo("<strong>$summaries[$id]</strong> at " . substr($starts[$id],0,-3) . " $locations[$id]");
        echo("<br> &nbsp; &nbsp; Description: $descriptions[$id]<br><br>\n");
    }
    echo("<input type='submit' name='submit' value='Mark New Items As Viewed' /></form><br><br>");
}

if(count($recurring) > 0)
{
    echo("<h3>New Recurring Items:</h3>\n<form method='post' action=''>\n");
    foreach($recurring as $id)
    {
        echo("<input type='checkbox' name='$id-checkbox' checked='checked' /> ");
        echo("<strong>$summaries[$id]</strong> at " . substr($starts[$id],0,-3) . " $locations[$id]");
        echo("<br> &nbsp; &nbsp; Description: $descriptions[$id]<br><br>\n");
    }
    echo("<input type='submit' name='submit' value='Mark New Recurring Items As Viewed' /></form><br><br>");
}

if(count($notes) > 0)
{
    echo("<h3>Script Note Items:</h3>\n<form method='post' action=''>\n");
    foreach($notes as $id)
    {
        echo("<input type='checkbox' name='$id-checkbox' checked='checked' /> ");
        echo("<a style='color:red'>[ $scriptnotes[$id] ]<a> - <strong>$summaries[$id]</strong> at ");
        echo(substr($starts[$id],0,-3) . " $locations[$id]<br> &nbsp; &nbsp; Description: $descriptions[$id]<br><br>\n");
    }
    echo("<input type='submit' name='submit' value='Mark Noted Items As Viewed' /></form><br><br>");
}

if(count($recurringandnote) > 0)
{
    echo("<h3>Script Note Recurring Items:</h3>\n<form method='post' action=''>\n");
    foreach($recurringandnote as $id)
    {
        echo("<input type='checkbox' name='$id-checkbox' checked='checked' /> ");
        echo("<a style='color:red'>[ $scriptnotes[$id] ]<a> - <strong>$summaries[$id]</strong> at ");
        echo(substr($starts[$id],0,-3) . " $locations[$id]<br> &nbsp; &nbsp; Description: $descriptions[$id]<br><br>\n");
    }
    echo("<input type='submit' name='submit' value='Mark Recurring Noted Items As Viewed' /></form><br><br>");
}

if(count($new) == 0 && count($recurring) == 0 && count($notes) == 0 && count($recurringandnote) == 0)
{
    echo("<h2> No New or Changed Items!</h2>");
}
?>

<?php include('other/footer.php'); ?>
