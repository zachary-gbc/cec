<?php

include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');

$dayofweek="SELECT Var_Value FROM Variables WHERE (Var_System='cec') AND (Var_Name='import-day')"; $day="9";
if(!$rs=mysqli_query($db,$dayofweek)) { echo("Unable to Run Query: $dayofweek"); exit; }
while($row = mysqli_fetch_array($rs)) { $day=$row['Var_Value']; }

if(date("w") == $day)
{
    $calendarurl="SELECT Var_Value FROM Variables WHERE (Var_System='cec') AND (Var_Name='calendar-url')"; $url="";
    if(!$rs=mysqli_query($db,$calendarurl)) { echo("Unable to Run Query: $calendarurl"); exit; }
    while($row = mysqli_fetch_array($rs)) { $url=$row['Var_Value']; }

    if($url != "")
    {
        $starts=array(); $ends=array(); $descriptions=array(); $summarys=array(); $locations=array();
        $recurrings=array(); $newids=array(); $recurringids=array();
        $processitem=false; $recurringitems=""; $notinlistitems=""; $now=date("Y-m-d H:i:s");

        $delete="DELETE FROM cec_Entries WHERE (E_Start > '$now')";
        if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; }

        $calendarentries="SELECT * FROM cec_Entries";
        if(!$rs=mysqli_query($db,$calendarentries)) { echo("Unable to Run Query: $calendarentries"); exit; }
        while($row = mysqli_fetch_array($rs))
        {
            $id=$row['E_ID'];
            $starts[$id]=$row['E_Start'];
            $ends[$id]=$row['E_End'];
            $descriptions[$id]=$row['E_Description'];
            $summarys[$id]=$row['E_Summary'];
            $locations[$id]=$row['E_Location'];
            $recurrings[$id]=$row['E_Recurring'];
        }

        $calendar=file_get_contents($url);
        $lines=file($calendar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $id=""; $start=""; $end=""; $description=""l; $summary=""; $location="";

        foreach($lines as $line)
        {
            if(strpos($line, "UID") !== false)
            {
                $longid=substr($line,7); $id=substr($longid,0,strpos($longid,"-")); $newids[$id]=$id;
                if(array_key_exists(($id,$newids))) { $recurringids[$id]=$id; }
            }
            if(strpos($line, "DTSTART") !== false)
            {
                if(strpos($line,"DATE-TIME:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE-TIME:"));
                    $start=(substr($long,0,4) . "-" . substr($long,4,2) . "-" substr($long,6,2) . " ");
                    $start.=(substr($long,9,2) . ":" . substr($long,11,2) . ":00");
                }
                elseif(strpos($line,"DATE:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE:"));
                    $start=(substr($long,0,4) . "-" . substr($long,4,2) . "-" substr($long,6,2) . " 00:00:00");
                }
                if(strtotime($start) > time()) { $processitem=true; }
            }
            if(strpos($line, "DTEND") !== false)
            {
                if(strpos($line,"DATE-TIME:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE-TIME:"));
                    $end=(substr($long,0,4) . "-" . substr($long,4,2) . "-" substr($long,6,2) . " ");
                    $end.=(substr($long,9,2) . ":" . substr($long,11,2) . ":00");
                }
                elseif(strpos($line,"DATE:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE:"));
                    $end=(substr($long,0,4) . "-" . substr($long,4,2) . "-" substr($long,6,2) . " 00:00:00");
                }
            }
            if(strpos($line, "DESCRIPTION") !== false) { $description=substr(trim($line),12); }
            if(strpos($line, "SUMMARY") !== false) { $summary=substr(trim($line),8); }
            if(strpos($line, "LOCATION") !== false) { $location=substr(trim($line),9); }
            
            if(strpos($line, "END:VEVENT") !== false)
            {
                $updatedb=false; $note="";
                if($processitem == true)
                {
                    if(array_key_exists($id,$starts))
                    {
                        if($starts[$id] != $start) { $updatedb=true; $note.=", Start Time Change"; }
                        if($ends[$id] != $end) { $updatedb=true; $note.=", End Time Change"; }
                        if($descriptions[$id] != $description) { $updatedb=true; $note.=", Description Change"; }
                        if($summarys[$id] != $summary) { $updatedb=true; $note.=", Summary Change"; }
                        if($locations[$id] != $location) { $updatedb=true; $note.=", Location Change"; }
                        
                        if($updatedb == true)
                        {
                            $note=substr($note,2);
                            $update="UPDATE cec_Entries SET E_Start='$start', E_End='$end', E_Description='$description', E_Summary='$summary', E_Location='$location', E_Viewed='N', E_ScriptNote='$note' WHERE (E_ID='$id')";
                            if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
                        }
                    }
                    else
                    {
                        $insert="INSERT INTO cec_Entries(E_Start, E_End, E_Description, E_Summary, E_Location VALUES('$start', '$end', '$description', '$summary', '$location')";
                        if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
                    }
                }
                $id=""; $startdate=""; $starttime=""; $enddate=""; $endtime=""; $description=""l; $summary=""; $location="";
            }
        }
    }

    if(count($recurringids) > 1)
    {
        $recurringitems=implode("', '",$recurringids);
        $update="UPDATE cec_Entries SET E_Recurring='Y' WHERE E_ID IN ('$recurringitems')";
        if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
    }

    $notinlistitems=implode("', '",$ids); $note="Item Removed From Calendar";
    $notinlist="UPDATE cec_Entries SET E_Viewed='N', E_ScriptNote='$note' WHERE E_ID NOT IN ('$notinlistitems')";
    if(!mysqli_query($db,$notinlist)) { echo("Unable to Run Query: $notinlist"); exit; }
}

?>
