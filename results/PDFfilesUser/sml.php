<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    use iio\libmergepdf\Merger;
    use \Jurosh\PDFMerge\PDFMerger;

    require '../../API/cnt.php';
    session_start();  
    try {
        //code...
        $pdf_file = $_POST['file'];
        $mailTo = $_POST['mailTo'];
        $sequence = $_POST['sequence'];
        $sequence6 = substr($sequence,0,1);
        $sequence7 = substr($sequence,1,1);
        $license = $_SESSION['lk'];
        require '../../vendor/autoload.php';
        require_once('../../vendor/SetaPDF/Autoload.php');    
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [215.9, 279.4],'default_font_size' => 12,'default_font' => 'montserrat']);
        $mpdf->SetHTMLFooter('<div>
                                    <div style="width:100%; border-top: 2px solid; border-top-color: #547391; margin-bottom: 0px;"></div>
                                    <p style="margin-top: 0px; font-size: 12px;font-family: montserrat;">Lawsuit Analyzer© results are projections based on your input, not legal advice. The objective of this assessment is to introduce you to some legal reasoning that may or may not apply to your dispute, to be used as a reference tool, not as a determination.
                                    </p>
                                </div>
                            ');
        $mpdf->WriteHTML($pdf_file);
        //$pdf = $mpdf->Output("../results/Lawsuit_Analyzer_Results-" . $license . ".pdf", \Mpdf\Output\Destination::FILE);
        $pdf = $mpdf->Output("2.Results.pdf", \Mpdf\Output\Destination::FILE);
        

    /*
        # Ruta de los documentos
        download_remote_file("https://lawsuitanalysis.com/wp-content/uploads/2021/08/ARBITRATION-MECHANICS.pdf","1.Welcome.pdf");
        download_remote_file("https://lawsuitanalysis.com/wp-content/uploads/2021/08/ARBITRATION-COACH.pdf","3.Phase 6-1.pdf");
        download_remote_file("https://lawsuitanalysis.com/wp-content/uploads/2021/08/CALCULATING-DAMAGES-COACH.pdf","4.Phase 7-1.pdf");
        
        # OPTION 1
        $output_file = "../Lawsuit_Analyzer_Results-" . $license . ".pdf";
        //$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=$output_file ";
        $cmd = "gswin32 -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=";

        $documentos = ["1.Welcome.pdf"
                        ,"2.Results.pdf"
                        ,"3.Phase 6-1.pdf"
                        ,"4.Phase 7-1.pdf"];

        foreach ($documentos as $file) {
            //$cmd .= " $file";
            $exec = $cmd . "NEWFile.pdf" . " ". $file;
            shell_exec($exec);
        }
    */
        

        /*
        $documentos = ["1.Welcome.pdf"
                        ,"2.Results.pdf"
                        ,"3.Phase 6-1.pdf"
                        ,"4.Phase 7-1.pdf"];
        */
    /*
        # Crear el "combinador"
        $combinador = new Merger;

        # Agregar archivo en cada iteración
        foreach ($documentos as $documento) {
            $combinador->addFile($documento);
        }

        # Y combinar o unir
        $salida = $combinador->merge();
        $bytesEscritos = file_put_contents("../Lawsuit_Analyzer_Results-" . $license . ".pdf", $salida);
        //OPTION 1
    */
    
        ///LAST OPTION
        $merger = new SetaPDF_Merger();
        $merger->addFile('Cover Page.pdf');
        $merger->addFile('2.Results.pdf');
        if ($sequence6 == '1') { $merger->addFile('P6-1.pdf');}
        if ($sequence6 == '2') { $merger->addFile('P6-2.pdf');}
        if ($sequence6 == '3') { $merger->addFile('P6-3.pdf');}
        if ($sequence6 == '4') { $merger->addFile('P6-4.pdf');}
        if ($sequence6 == '5') { $merger->addFile('P6-5.pdf');}
        if ($sequence6 == '6') { $merger->addFile('P6-6.pdf');}
        if ($sequence7 == '1') { $merger->addFile('P7-1.pdf');}
        if ($sequence7 == '2') { $merger->addFile('P7-2.pdf');}
        if ($sequence7 == '3') { $merger->addFile('P7-3.pdf');}
        if ($sequence7 == '4') { $merger->addFile('P7-4.pdf');}
        if ($sequence7 == '5') { $merger->addFile('P7-5.pdf');}
        if ($sequence7 == '6') { $merger->addFile('P7-6.pdf');}
        $merger->merge();
        //$merger->merge('file', "../Lawsuit_Analyzer_Results-" . $license . ".pdf");
        $document = $merger->getDocument();
        $pages = $document->getCatalog()->getPages();

        for ($i = 1; $i <= $pages->count(); $i++) {
            $stamper = new SetaPDF_Stamper($document);
            //$font = SetaPDF_Core_Font_Standard_TimesRoman::create($document);
            $font = new SetaPDF_Core_Font_TrueType_Subset($document, "../../vendor/mpdf/mpdf/ttfonts/Montserrat-Medium.ttf" ); 
            $stamp = new SetaPDF_Stamper_Stamp_Text($font, 9);
            $stamp->setAlign(SetaPDF_Core_Text::ALIGN_CENTER);
            $stamp->setText( $i . ' of ' . $pages->count());
            $stamper->addStamp($stamp, array(
                'showOnPage' =>  $i,
                'position' => SetaPDF_Stamper::POSITION_CENTER_BOTTOM,
                'translateY' => 5
            ));
            $stamper->stamp();
            //echo 'Page Index: ' . $i . ' -> Page Label: ' . $pageLabels->getPageLabelByPageNo($i) . "\n";
        }

        $document->setWriter(new SetaPDF_Core_Writer_File("../Lawsuit_Analyzer_Results-" . $license . ".pdf", true));
        $document->save()->finish();
        ///
        /*
        $pdf_merger = new PDFMerger;
        // add as many pdfs as you want
        $pdf_merger->addPDF('1.Welcome.pdf')
                    ->addPDF('2.Results.pdf')
                    ->addPDF('3.Phase 6-1.pdf')
                    ->addPDF('4.Phase 7-1.pdf');

        // call merge, output format `file`
        $pdf_merger->merge('file', "../Lawsuit_Analyzer_Results-" . $license . ".pdf");
        */


        $mail = new PHPMailer(true);
        //Recipients
        $mail->setFrom('affiliates@lawsuitanalysis.com', 'Lawsuit Analyzer');
        $mail->addAddress($mailTo);               //Name is optional
        $mail->addBCC('diegoveloza@trascenderglobal.com');

        //Attachments
        $mail->addAttachment("../Lawsuit_Analyzer_Results-" . $license . ".pdf");         //Add attachments
        //Content
        $mail->isHTML(true);                                //Set email format to HTML
        $mail->CharSet = "UTF-8";
        $mail->Encoding = 'base64';
        $mail->Subject = 'Your results from Lawsuit Analyzer';
        $mail->Body    = '
                <html>
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                    <title>Lawsuit Analyzer</title>
                </head>

                <body>

                <div  style=" border:1px solid grey; padding:20px;">
                        <div style="font-size: 28px; margin: 30px;color:#C0392B; text-align: center;">
                            <p><strong>Lawsuit Analyzer<sup>©</sup>!</strong></p> 
                        </div>
                        <p> Dear User,<br><br>
                        You have completed Lawsuit Analyzer©. We hope you have found the process helpful.
                        </p>
                        <br>
                        <br><br>
                        <div>
                            Self-help services may not be permitted in all states. The information provided on this site is not legal advice, does not constitute a lawyer referral service, and no attorney-client or confidential relationship is or will be formed by use of the site. The attorney listings on this site are paid attorney advertising. In some states, the information on this website may be considered a lawyer referral service. Please reference the Terms of Use and the Supplemental Terms for specific information related to your state. Your use of this website constitutes acceptance of the Terms of Use, Disclaimer, Supplemental Terms, Privacy Policy and Cookie Policy.
                        </div>

                </div>

                </body>
                </html>';
        $mail->send();

    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode(array("success" => false, "message" => 'Error sending results email. ', "Details" => $th->getMessage() . "-" .$exec));
    }


    function download_remote_file($file_url, $save_to) {
        $content = file_get_contents($file_url);
        file_put_contents($save_to, $content);
    }

?>