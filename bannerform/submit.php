<!-- PHP for sending the banner form info via mail -->
<!-- Made by: Thomas Dyrholm Siemsen -->

<?php
$mailResult = "";
$displayClass = "";

function monthToString($monthInt){
    if($monthInt == "01"){return "Jan";}
    if($monthInt == "02"){return "Feb";}
    if($monthInt == "03"){return "Mar";}
    if($monthInt == "04"){return "Apr";}
    if($monthInt == "05"){return "May";}
    if($monthInt == "06"){return "Jun";}
    if($monthInt == "07"){return "Jul";}
    if($monthInt == "08"){return "Aug";}
    if($monthInt == "09"){return "Sep";}
    if($monthInt == "10"){return "Oct";}
    if($monthInt == "11"){return "Nov";}
    if($monthInt == "12"){return "Dec";}
}

if(isset($_POST['submit'])){
    // --- Creating the Variables
    // The empty variables that are used
    $bannerSpecsOutput = "";
    $firstDeadlineOutput = "";
    $finalDeadlineOutput = "";
    $serverLinkOutput = "intet link givet <br>";
    $extraCommentsOutput = "";
    $attachments = array();
    $briefFilePath = "";

    // Collecting the data from the form
    $client = $_POST['client'];
    $jobNr = $_POST['jobNr'];
    $contact = $_POST['contact'];
    $clientEmail = $_POST['clientEmail'];
    $firstDeadline = $_POST['firstDeadline'];
    $finalDeadline = $_POST['finalDeadline'];
    $briefText = $_POST['briefText'];
    $fileSize = $_POST['fileSize']; 
    $platform = $_POST["platform"];
    $sizeInput = $_POST["sizeInput"];
    $extraWidth = $_POST["extraWidth"];
    $extraHeight = $_POST["extraHeight"];
    $extraCheck = $_POST["extraCheck"];
    $serverLink = $_POST["serverLink"];
    $briefFile = $_FILES["briefFile"];
    $extraComments = $_POST['extraComments'];
    $coworkerEmail = $_POST['coworkerEmail'];
    $sender = $_POST['sender'];
    $detailsCntr = $_POST['detailsCntr'];
    $platformCntr = array_map('intval', explode('_', $_POST['platformCntr'])); //it's a string of an array in html so we have to process it
    

    // ---Formatting the data
    // Sun server link
    if (filter_var($serverLink, FILTER_VALIDATE_URL))  { 
        $serverLinkOutput = "<a href=" . $serverLink . ">" . $serverLink . "</a>";
    }

    // Banner details
    $tempBannerCntr = 0; // the variable that counts how many banners we have in total
    // we use the detailsCntr we got from the js to find out what the max nr of banneres there are
    for ($i = 0; $i < $detailsCntr; $i++) {
        // If it finds a type with the specific number then it starts the process
        if(!empty($_POST["type$i"])){
            $y = 0; // We declare the variable for the loop here so we can change the start 
            $tempBannerCntr++;
            $bannerSpecsOutput .= "Banner nr. $tempBannerCntr <br>"; 
            $bannerSpecsOutput .= 'Type: ' . $_POST["type$i"] . '<br>';
            // Making the destination a link if it's a link
            if(filter_var($_POST["destination$i"], FILTER_VALIDATE_URL))  { 
                $_POST["destination$i"] = "<a href=" . $_POST["destination$i"] . ">" . $_POST["destination$i"] . "</a>";
            }
            $bannerSpecsOutput .= 'Kampagnelink: ' . $_POST["destination$i"] .'<br>';
            $bannerSpecsOutput .= 'Max størrelse pr zipfil: ' . $_POST["fileSize$i"] .'<br>';
            // since we know there is a type at this $i index, we also know there is a platform[$i][] array
            for ($x = 0; $x < count($platform[$i]); $x++) { 
                $bannerSpecsOutput .= "Platform: " . $platform[$i][$x] . " og dens størrelserne: <br>";
                // we use the platformCntr[$i] we got from the js to find out what the max nr of sizesArrays there are
                for ($y; $y < $platformCntr[$i]; $y++) { 
                    // If it finds a size with the specific number then it starts the process
                    if(!empty($sizeInput[$i][$y]) || !empty($extraCheck[$i][$y])){
                        if(!empty($sizeInput[$i][$y])){
                            foreach ($sizeInput[$i][$y] as $value) {
                                $bannerSpecsOutput .= "- $value <br>";
                            }
                        }
                        if (!empty($extraCheck[$i][$y])) {
                            for ($z = 0; $z < count($extraWidth[$i][$y]); $z++) {
                                if($extraWidth[$i][$y][$z] !== ""){
                                    $bannerSpecsOutput .= " - " . $extraWidth[$i][$y][$z] . " x " . $extraHeight[$i][$y][$z] . "<br>";
                                }
                            }
                        }  
                        $y++; // we update the y variable so the next time we loop we don't loop through the same shit
                        break; // we only want it to add to the bannerSpecsOutput once so we break the loop
                    }
                }
            }
            $bannerSpecsOutput .= "<br>";
        }
    }

    // Extra comments info
    if(!empty($extraComments)){
        $extraCommentsOutput = "Der er yderlige følgende kommentarer til opgaven: <br> - $extraComments <br><br>";
    }

    // Brieffile
    if(!empty($briefFile)){
        $targetDir = WP_CONTENT_DIR . "/themes/salient-child/uploads";
        $tmpFile = $briefFile["tmp_name"];
        $fileName = $briefFile["name"];
        move_uploaded_file($tmpFile, "$targetDir/" . strtolower($fileName));
        $briefFilePath = "$targetDir/" . strtolower($fileName);
    }

    // Deadlines
    $firstMonth = monthToString(substr($firstDeadline, 5, 2));
    $finalMonth = monthToString(substr($finalDeadline, 5, 2));
    $firstDeadlineOutput = substr($firstDeadline, 0, 5) . $firstMonth . substr($firstDeadline, 7, 3);
    $finalDeadlineOutput = substr($finalDeadline, 0, 5) . $finalMonth . substr($finalDeadline, 7, 3);


    // ---Putting the data into the email
    $to = $coworkerEmail;
    $emailSubject = "Banner bestilling fra: $client";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = $briefFilePath;
    $emailBody = "E-mail er sendt af: $sender. <br>".
        "Ny banner bestilling fra <strong>$client</strong>, job nr.: $jobNr <br>".
        "Kontaktperson: $contact, kontaktmail: $clientEmail. <br> <br>".
        "Første deadline: $firstDeadlineOutput. Endelige deadline: $finalDeadlineOutput <br>". 
        "Sun server link: $serverLinkOutput <br>".
        "<strong> Bannerspecifikationer: </strong><br> $bannerSpecsOutput". 
        "<strong> Kundebrief: </strong> <br> - $briefText <br><br>".
        "$extraCommentsOutput";
    
    // Sending the email
    if(wp_mail($to, $emailSubject, $emailBody, $headers, $attachments)){
        $displayClass = "visible";
        $mailResult = '<h2>Success!</h2><h3>Du fik sendt e-mailen.</h3>';
    } else { // It says if its unable to send the e-mail
        $displayClass = "visible";
        $mailResult = '<h2>Uh oh!</h2><h3>Noget gik galt, du fik ikke sendt e-mailen.</h3>';
    }

    // After the mail has been sent delete the upload
    if($fileName !== ""){
        unlink($briefFilePath);
    }

    /* ---Testing area, echoing and vardumping to see what i get
    echo "<br><strong>Det er sådan den sendte mail ser ud: </strong><br>";
    echo "$emailSubject <br><br> $emailBody"; */
    //var_dump(explode('_', $_POST['platformCntr']));
    //echo "<pre>"; var_dump($platform); echo "</pre>";
}
?>