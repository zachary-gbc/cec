<?php

include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');

$dayofweek="SELECT Var_Value FROM Variables WHERE (Var_System='cec') AND (Var_Name='import-day')"; $day="9";
if(!$rs=mysqli_query($db,$dayofweek)) { echo("Unable to Run Query: $dayofweek"); exit; }
while($row = mysqli_fetch_array($rs)) { $day=$row['Var_Value']; }

if(str_contains($day,date("w")))
{
    $calendarurl="SELECT Var_Value FROM Variables WHERE (Var_System='cec') AND (Var_Name='calendar-url')"; $url=""; $ids=array();
    if(!$rs=mysqli_query($db,$calendarurl)) { echo("Unable to Run Query: $calendarurl"); exit; }
    while($row = mysqli_fetch_array($rs)) { $url=$row['Var_Value']; }

    if($url != "")
    {
        $starts=array(); $ends=array(); $descriptions=array(); $summarys=array(); $locations=array();
        $recurrings=array(); $newids=array(); $recurringids=array(); $i=0; $u=0;
        $processitem=false;  $recurringitems=""; $notinlistitems=""; $now=date("Y-m-d H:i:s");

        $delete="DELETE FROM cec_Entries WHERE (E_Start < '$now')";
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

        if(file_exists("/var/www/html/cec/uploads/calendar")) { unlink("/var/www/html/cec/uploads/calendar"); }
        if(!copy($url,"/var/www/html/cec/uploads/calendar")) { echo"unable to copy file"; exit; }
        $file=fopen("/var/www/html/cec/uploads/calendar", "r");
        $id=""; $start=""; $end=""; $description=""; $summary=""; $location="";

        while(($line=fgets($file)) !== false)
        {
            if(strpos($line, "UID") !== false)
            {
                $longid=substr($line,7); $id=substr($longid,0,strpos($longid,"-")); $ids[$id]=$id;
                if(array_key_exists($id,$newids)) { $recurringids[$id]=$id; }
            }
            if(strpos($line, "DTSTART") !== false)
            {
                if(strpos($line,"DATE-TIME:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE-TIME:")+10);
                    $start=(substr($long,0,4) . "-" . substr($long,4,2) . "-" . substr($long,6,2) . " ");
                    $start.=(substr($long,9,2) . ":" . substr($long,11,2) . ":00");
                }
                elseif(strpos($line,"DATE:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE:")+5);
                    $start=(substr($long,0,4) . "-" . substr($long,4,2) . "-" . substr($long,6,2) . " 00:00:00");
                }
                if(strtotime($start) > time()) { $processitem=true; }
            }
            if(strpos($line, "DTEND") !== false)
            {
                if(strpos($line,"DATE-TIME:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE-TIME:")+10);
                    $end=(substr($long,0,4) . "-" . substr($long,4,2) . "-" . substr($long,6,2) . " ");
                    $end.=(substr($long,9,2) . ":" . substr($long,11,2) . ":00");
                }
                elseif(strpos($line,"DATE:") !== false)
                {
                    $long=substr($line,strpos($line,"DATE:")+5);
                    $end=(substr($long,0,4) . "-" . substr($long,4,2) . "-" . substr($long,6,2) . " 00:00:00");
                }
            }
            if(strpos($line, "DESCRIPTION") !== false)
            {
                $description=str_replace("'","''",substr(trim($line),12));
                $description=str_replace("\\n","",$description);
                $description=str_replace("\\","",$description);
            }
            if(strpos($line, "SUMMARY") !== false)
            {
                $summary=str_replace("'","''",substr(trim($line),8));
                $summary=str_replace("\\n","",$summary);
                $summary=str_replace("\\","",$summary);
            }
            if(strpos($line, "LOCATION") !== false)
            {
                $location=str_replace("'","''",substr(trim($line),9));
                $location=str_replace("\\n","",$location);
                $location=str_replace("\\","",$location);
            }

            if(strpos($line, "END:VEVENT") !== false)
            {
                $updatedb=false; $note=""; $viewed="";
                if($processitem == true)
                {
                    if(array_key_exists($id,$starts))
                    {
                        if($recurrings[$id] == "N")
                        {
                            if(trim($starts[$id]) != trim($start))
                            { $updatedb=true; $viewed=", E_Viewed='N'"; $note.=", Start Time Change"; }
                            if(trim($ends[$id]) != trim($end))
                            { $updatedb=true; $viewed=", E_Viewed='N'"; $note.=", End Time Change"; }
                        }
                        if(trim(str_replace("'","''",$descriptions[$id])) != trim($description))
                        { $updatedb=true; $note.=", Description Change"; $viewed=", E_Viewed='N'"; }
                        if(trim(str_replace("'","''",$summarys[$id])) != trim($summary))
                        { $updatedb=true; $note.=", Summary Change"; $viewed=", E_Viewed='N'"; }
                        if(trim(str_replace("'","''",$locations[$id])) != trim($location))
                        { $updatedb=true; $note.=", Location Change"; $viewed=", E_Viewed='N'"; }

                        if(array_key_exists($id,$recurrings) && $recurrings[$id] == "Y") { $updatedb=true; }

                        if($updatedb == true)
                        {
                            if(strlen($note) > 3) { $note=substr($note,2); }
                            $update="UPDATE cec_Entries SET E_Start='$start', E_End='$end', E_Description='$description', E_Summary='$summary', E_Location='$location', E_ScriptNote='$note'$viewed WHERE (E_ID='$id')"; $u++;
                            if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
                        }
                    }
                    elseif(!array_key_exists($id,$newids))
                    {
                        $newids[$id]=$id;
                        $insert="INSERT INTO cec_Entries(E_ID, E_Start, E_End, E_Description, E_Summary, E_Location) VALUES('$id', '$start', '$end', '$description', '$summary', '$location')"; $i++;
                        if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
                    }
                }
                $id=""; $startdate=""; $starttime=""; $enddate=""; $endtime="";
                $description=""; $summary=""; $location=""; $processitem=false; 
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

    if(file_exists("/var/www/html/cec/uploads/calendar")) { unlink("/var/www/html/cec/uploads/calendar"); }
    $now=date("Y-m-d H:i:s",time()); $update="UPDATE Variables SET Var_Value='$now' WHERE (Var_System='cec') AND (Var_Name='last-import')";
    if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }

}

?>
