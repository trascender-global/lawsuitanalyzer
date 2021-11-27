<?php
    session_start();

    //Only for admin Users
    if(isset($_SESSION['user_rol'])){
        if ($_SESSION['user_rol'] == 'admin') {
            header('location: admin/index.php');
        } 
    };
    

    //Validate if LK is typed
    if (isset($_GET['lk']) && !empty($_GET['lk'])){
        $license = $_GET['lk'];
        if (strlen($license) == 0 ) {
            //No license typed
            exit();
        } else {
            //Check for an existing user
            require 'API/cnt.php';
            $user = $license . '@lawsuitanalysis.com';
            $pass = $license . '@lawsuitanalysis.com';
            if($PreResultado = $mysqli->prepare("SELECT * FROM users where user_login = ? and user_status = ?")){
                $status = 'ACTIVE';
                $PreResultado->bind_param('ss', $user, $status);
                $PreResultado->execute();
                $resultado = $PreResultado->get_result();
                $num_rows = mysqli_num_rows($resultado);
                //If user exist
                if ($num_rows  >= 1){
                    $data = $resultado->fetch_all();
                    for ($i=0; $i < $num_rows; $i++) { 
                        # code...
                        if ( password_verify($pass,$data[$i][2])){
                            $_SESSION['user_id'] = $data[$i][0];
                            $_SESSION['user'] = $data[$i][3];
                            $_SESSION['email'] = $data[$i][4];
                            $_SESSION['user_status'] = $data[$i][6];
                            $_SESSION['user_rol'] = $data[$i][8];
                            $_SESSION['wck'] = 'ck_0b3b4ac24f2fadd6811260e7b04f7841feaee86f';
                            $_SESSION['wcs'] = 'cs_c1170ee6621d8a5a02087f26f875ed3ecc91c878';
                            $_SESSION['lk'] = $license;                            
                        }
                    }
                }else {
                //If User doesn't exist

                    //Validate License Key
                    $curl = curl_init();
                    $ck = "ck_cdfdbcc314b56083fc142a2ff04dfbc94a11b3f7";
                    $cs = "cs_9f4938b2cddf0b876dab41394e49bb5c54035438";
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://lawsuitanalysis.com/wp-json/lmfwc/v2/licenses/". $license ."?consumer_key=" . $ck . "&consumer_secret=" . $cs . "",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => false,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    if ($err) {
                        exit();
                    } else {
                        $findSucess   = '"success":true';
                        $findFalse = "License Key: ". $license . " could not be found.";
                        $posSuc = strpos($response, $findSucess);
                        $posFal = strpos($response, $findFalse);
                        //If license doesn't exist
                        if ($posFal === false) {
                        } else {
                            exit();
                        } ;
                        //If license exist
                        if ($posSuc === false) {
                        } else{
                            //Create a New Active User
                            $status = 'ACTIVE' ;
                            $confirmation = $license;
                            $rol = 'CUSTOMER';
                            $password= password_hash($pass , PASSWORD_DEFAULT, array("cost" => 15));
                            $PreResultado = $mysqli->prepare("INSERT INTO users (user_login,user_pass,user_nicename,user_email,user_status,user_confirmation,user_rol) 
                                                                VALUES (?,?,?,?,?,?,?)");
                            $PreResultado->bind_param('sssssss', $user,$password,$license,$user,$status,$confirmation,$rol);
                            $PreResultado->execute();
                            $num_rows = $mysqli->affected_rows;
                            if ($num_rows > 0) {
                                sleep(4);
                                //Retrieve the New User ID
                                $PreResultado = $mysqli->prepare("SELECT * FROM users where user_login = ? and user_status = ?");
                                $status = 'ACTIVE';
                                $PreResultado->bind_param('ss', $user, $status);
                                $PreResultado->execute();
                                $resultado = $PreResultado->get_result();
                                $num_rows = mysqli_num_rows($resultado);
                                if ($num_rows  >= 1){
                                    $data = $resultado->fetch_all();
                                    for ($i=0; $i < $num_rows; $i++) { 
                                        if ( password_verify($pass,$data[$i][2])){
                                            $_SESSION['user_id'] = $data[$i][0];
                                            $_SESSION['user'] = $data[$i][3];
                                            $_SESSION['email'] = $data[$i][4];
                                            $_SESSION['user_status'] = $data[$i][6];
                                            $_SESSION['user_rol'] = $data[$i][8];
                                            $_SESSION['wck'] = 'ck_0b3b4ac24f2fadd6811260e7b04f7841feaee86f';
                                            $_SESSION['wcs'] = 'cs_c1170ee6621d8a5a02087f26f875ed3ecc91c878';
                                            $_SESSION['lk'] = $license;
                                            //Activate License, Only one-time
                                            $curl = curl_init();
                                            curl_setopt_array($curl, array(
                                                CURLOPT_URL => "https://lawsuitanalysis.com/wp-json/lmfwc/v2/licenses/activate/". $license ."?consumer_key=" . $ck . "&consumer_secret=" . $cs . "",
                                                CURLOPT_RETURNTRANSFER => true,
                                                CURLOPT_ENCODING => "",
                                                CURLOPT_MAXREDIRS => 10,
                                                CURLOPT_TIMEOUT => 0,
                                                CURLOPT_FOLLOWLOCATION => false,
                                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                CURLOPT_CUSTOMREQUEST => "GET",
                                            ));
                                            $response = curl_exec($curl);
                                            $err = curl_error($curl);   
                                            curl_close($curl);                                  
                                        };
                                    };
                                };
                            };
                        };
                    };
                };
            };
        };
    } else {
        exit();
    }

?>


<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Lawsuit Analyzer</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/jquery.steps.css">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="shortcut icon" type="image/png" href="../analyzer/assets/cropped-Logo-scales-Maroon.jpg" >
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
        <script src="lib/modernizr-2.6.2.min.js"></script>
        <script src="lib/jquery-3.5.1.min.js"></script>
        <script src="lib/jquery.cookie-1.3.1.js"></script>
        <script src="js/bootstrap.js"  > </script>
        <script src="build/jquery.steps.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js" integrity="sha512-YcsIPGdhPK4P/uRW6/sruonlYj+Q7UHWeKfTAkBW+g83NKM+jMJFJ4iAPfSnVp7BKD4dKMHmVSvICUbE/V1sSw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body style="font-family: 'Montserrat';">

    <style>
        /* Style the form */
        #Step1,#Step2,#Step3,#Step4,#Step5,#Step6,#Step7 {
            /*text-align: ;background-color: #ffffff;*/
            margin: auto;
            padding-top: 15px;
            width: 80%;
            min-width: 300px;
            display: contents;
        }

        /* Style the input fields */
        input {
            padding: 10px;
            width: 100%;
            font-size: 17px;
            border: 'none';
            border-bottom: 1px solid #aaaaaa;
        }

        /* Mark form elements that gets an error on validation: */
        input.invalid,textarea.invalid,select.invalid {
            background-color: #ffdddd;
        }
        input.valid,textarea.valid,select.valid {
            background-color: white;
        }
        /* Hide all steps by default: */
        .tab {
            display: none;
            margin: 3% 5% 3% 5%;
        }
        
        .form-group {
            text-align: left;
        }

        .form-check-label {
            color: #212529;
        }

        small {
            text-align: center;
            margin-bottom: 1%;
        }

        .badge-info-ls{
            background-color: #8fc2d1;
            color: black;
            border: 2px solid;
            border-color: #135594;
        }

        .badge-primary-ls{
            background-color: #3c5895;
            color: white;
        }

        /* Make circles that indicate the steps of the form: */
        .step {
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbbbbb;
            border: none;
            border-radius: 50%;
            /*display: inline-block;*/
            opacity: 0.5;
        }

        /* Mark the active step: */
        .step.active {
            opacity: 1;
        }

        /* Mark the steps that are finished and valid: */
        .step.finish {
            background-color: #4CAF50;
        }

        html { font-size: 1rem; }
        h1,h3 { color: #9E2D2D ; }
        label,h4 { color: #345B99;}
        strong {color: #8fc2d1;}
        .helpimg {
            cursor: help;
        }

        /* Payment Instructions*/
        .dot {
            height: 30px;
            width: 30px;
            background-color: #9E2D2D;
            color: white;
            border-radius: 50%;
            display: inline-block;
        }

        @media (max-width: 576px) {
            html { font-size: 1rem; }
            h2 {font-size: 6vw}
            h3 {font-size: 4vw}
            .wizard > .content {min-height: auto/*45em*/ }
            #StepCollapse { display: block; }
            .wizard.vertical > .steps {display: none;}
            .helpimg {
                width: 50px;
            }
            .form-check-label {
                font-size: 0.6rem;
            }
            .instructionTitle {
                text-align: left !important;
            }
        }
        @media (max-width: 768px) {
            html { font-size: 1rem; }
            h2 {font-size: 3vw}
            h3 {font-size: 2vw}
            .wizard > .content {min-height: auto/*45em*/ }
            .wizard.vertical > .steps {display: inline;}
            .helpimg {
                width: 80px;
            }
            .form-check-label {
                font-size: 0.8rem;
            }
            .instructionTitle {
                text-align: left !important;
            }   
        }
        @media (min-width: 1200px) {
            html { font-size: 1.25rem; }
            .wizard > .content {min-height: auto/*25em*/ }
            .wizard.vertical > .steps {display: inline;}
            .helpimg {
                width: 80px;
            }
            .form-check-label {
                font-size: 0.8rem;
            }            
        }
    </style>



        <header>
            <?php  
                //define( 'WP_USE_THEMES', false );
                //get_header(); 
            ?>
            <div class="row" style="margin: 0px;">
                <div class="col" style="text-align: end;">
                    <a href="logout.php">
                        Logout
                        <img class="img-fluid" src="assets/close.png" alt="Close"  width="50" height="50"  >
                    </a>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col">
                    <h1 style="text-align: center;">Lawsuit Analyzer<sup>©</sup> </h1>
                </div>
            </div>

        </header>

        <div class="content">
            <script>
                stepsWizard = $(function ()
                    {
                        $("#wizard").steps({
                            headerTag: "h2",
                            bodyTag: "section",
                            stepsOrientation: "vertical",
                            //enableAllSteps: true,
                            enablePagination: false,
                        });
                    });
            </script>
            <div class="container-fluid">
                <div id = "btnPaymentInstructions" class = "row" style="display: none; margin: 0px;">
                    <p style="text-align: center;"> <br><br>Hello! Thanks for using the Analyzer. We hope it is being useful in the assessment of your case. 
                        <br><br>Unfortunately, you have reached the usage limit for this license key.
                        <br><br>We'd love to keep you using the analyzer and you can do it by completing another payment process.
                        <br>
                    </p>
                    <p style="text-align: right;">
                        -- The Lawsuit Analysis Team.
                    </p>
                    <div class="row" style="background-color: #dbdbdb; margin-top: 35px; text-align: justify; position: absolute; width: 100%; bottom: 0;">
                        <div class = "col-lg-12"  style="margin-top: 20px;">
                            <p style="font-size: small;"> Self-help services may not be permitted in all states. The information provided on this site is not legal advice, does not constitute a lawyer referral service, and no attorney-client or confidential relationship is or will be formed by use of the site. The attorney listings on this site are paid attorney advertising. In some states, the information on this website may be considered a lawyer referral service. Please reference the Terms of Use and the Supplemental Terms for specific information related to your state. Your use of this website constitutes acceptance of the Terms of Use, Disclaimer, Supplemental Terms, Privacy Policy and Cookie Policy</p>
                        </div>
                    </div>

                </div>
                <div id="wizard">
                    <div class="row">
                        <button id="StepCollapse" class="btn btn-info w-100" type="button" style="background-color: #9E2D2D;border-color: none; ">
                            <i class="fa fa-align-left"></i>
                            <span id = "ToggleBtnText">Show Steps</span>
                        </button>             
                    </div>

                    <h2>Legal Evaluation</h2>
                    <section>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 1. Legal Evaluation</h2>
                                        <div class="col-lg-10 sm-6"> 
                                            <h3>What is your likelihood of winning your case?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(1)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>
                                    <div class="row"  > 
                                        <div class="col" style="text-align: center;" >
                                            <form id="Step1" >
                                                <!-- One "tab" for each step in the form: -->
                                                <div id="Pre-Q1" class = "tab" >
                                                    <p style="color: #345B99" >Click on the Help Button for important information that will help you answer these questions </p>
                                                    <div id = "Pre_Q1_Check" class="form-group" >
                                                        <div class="form-check form-check-inline" style="text-align: justify !important;">
                                                            <input id = "check_terms" name="check_terms" class="form-check-input" type="checkbox" value="true" style="width: 20%;">
                                                            <label class="form-check-label" for="check_terms" >
                                                                Check here to indicate that you have read and agree to the <a href="#"><strong>terms of use</strong></a> this Analyzer.
                                                            </label>
                                                        </div>
                                                    </div>                                                
                                                </div>

                                                <div id="Q1" class="tab">
                                                    <div class="form-group">
                                                        <label for="textarea-1-1" class="form-control-label">What obligations did your adversary owe you?
                                                            
                                                        </label>
                                                        <small id="HelpBlock-1-1" class="form-text">
                                                            Enter below, and remember to access <strong>Help</strong>.
                                                        </small>
                                                        <textarea id="textarea-1-1" name="textarea-1-1" required="required" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                                <div id="Q2" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-1-2">Did your adversary perform these obligations in a satisfactory manner?
                                                            <sup class="text-danger"></sup>
                                                        </label>
                                                        <select id="select-1-2" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="Q3" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-1-3">Have you sustained legally recognized damages as a direct result of your adversary's conduct?
                                                            
                                                        </label>
                                                        <select id="select-1-3" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>                                            
                                                </div>
                                                <div id="Q4" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-1-4">Did you have a prior obligation to your adversary?
                                                            
                                                        </label>
                                                        <select id="select-1-4" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                    <div id = "PriorObligation_yes" class="form-group">
                                                        <label id="label-1-4-1" for="textarea-1-4-1">Please describe
                                                            
                                                        </label>
                                                        <textarea id="textarea-1-4-1" class="form-control" name="" rows="3"></textarea>
                                                    </div>                                            
                                                </div>
                                                <div id="Q5" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-1-5">Did you perform your obligation in a satisfactory manner?
                                                            
                                                        </label>
                                                        <select id="select-1-5" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>                                               
                                                </div>
                                                
                                                <div id="ResultStep1" class = "results" style="display: none; margin:10% 0% 10% 0%;" >
                                                    <h4 id="ResultStep1Text1"></h4>
                                                    <br>
                                                    <h3 id="ResultStep1Text2"></h3>
                                                    <br>
                                                    <p id="ResultStep1Text3"></p>  
                                                </div>
                                                
                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step1" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step1" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn1" class="btn btn-info" onclick="nextPrev(-1,0)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn1" class="btn btn-info" onclick="nextPrev(1,0)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>

                                            </form>
                                            

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>


                    <h2>Case Value Assessment</h2>
                    <section>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 2. Case Value Assessment</h2>
                                        <div class="col-lg-10 sm-6"> 
                                            <h3>How much are you likely to recover?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(2)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>                                 
                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step2">
                                                <div id = "Pre-Q7" class = "tab" >
                                                    <p style="color: #345B99" >Click on the Help Button for important information that will help you answer these questions </p>
                                                </div>
                                                <div id ="Q7" class="tab">
                                                    <div class="form-group">
                                                        <label for="input-2-7">What is the amount of your damages?</label>
                                                        <input id="input-2-7" class="form-control" type="text" name="" placeholder="Enter $ amount">
                                                        <small id="HelpBlock-2-7-1" class="form-text text-muted">
                                                        Remember to access <strong>Help</strong> to guide you to your answer.
                                                        </small>
                                                    </div>
                                                </div>                                                
                                                <div id ="Q8" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-2-8">Were you a cause of the situation that caused your damages?
                                                            
                                                        </label>
                                                        <select id="select-2-8" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                    <div id="CauseOfSituation_yes" class="form-group">
                                                        <small class="form-text">
                                                            Amount to be deducted from your damages
                                                        </small>
                                                        <input id="input-2-8-1" class="form-control" type= "text" placeholder="$ Amount" ></input>
                                                    </div>                                            
                                                </div>
                                                <div id ="Q9" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-2-9">Did you receive a substantial benefit from the situation?
                                                            
                                                        </label>
                                                        <select id="select-2-9" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                    <div id="RecieveBenefit_yes" class="form-group">
                                                        <small class="form-text">
                                                            Enter the $ amount your conduct contributed to your damages 
                                                        </small>
                                                        <small class="form-text">
                                                            (use half of your damages if unsure)  
                                                        </small>
                                                        <input id="input-2-9-1" class="form-control" type= "text" ></input>
                                                        <small id="HelpBlock-2-9-1" class="form-text">
                                                            Click on <strong>Help</strong> and enter the $ amount.
                                                        </small>
                                                    </div>     
                                                </div>
                                                <div id ="Q10" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-2-10">Is there a damage defining provision in a written agreement that pertains to this dispute?
                                                            
                                                        </label>
                                                        <select id="select-2-10" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                    <div id="DamageProvision_yes" class="form-group">
                                                        <small class = "form-text" >
                                                            Liquidated Damages or Damage Limit ?
                                                        </small>

                                                        <select id="select-2-10-1" class="form-control" name="">
                                                            <option value="default" >Please choose an option</option>
                                                            <option value="Liquidated-Damages">Liquidated Damages</option>
                                                            <option value="Damage-Limit">Damage Limit</option>
                                                        </select>
                                                        <small id="label-2-10-2" class = "form-text" >
                                                            Please choose an option
                                                        </small>
                                                        <input id="input-2-10-2" class="form-control" type= "text" ></input>
                                                    </div>  
                                                </div>
                                                <div id ="Q11" class="tab">
                                                    <div class="form-group">
                                                        <label id="label-2-11" for="input-2-11">How much are you likely to recover?
                                                        </label>
                                                        <small class="form-text" style="margin-bottom: 4%;" >
                                                            After deduction for contributing to your damages and any substantial benefit you received, and factoring in your damage defining provision, if applicable
                                                        </small>
                                                        <input id="input-2-11" class="form-control" type= "text"  value="0"  disabled></input>
                                                    </div>  
                                                </div>
                                                <div id ="Q12" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-2-12">Will you be represented by an attorney?
                                                            
                                                        </label>
                                                        <select id="select-2-12" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>   
                                                </div>
                                                <div id ="Q13" class="tab">
                                                    <div id="attorney_yes" class="form-group" style="text-align: justify;">
                                                        <label>Select ONE of the following options:</label>
                                                        <div class="form-check form-check-inline">
                                                            <input id="radio-2-13-1" class="form-check-input" type="radio" name="attorney" value="value-1" style="width: auto;">
                                                            <label for="radio-2-13-1" class="form-check-label" style="text-align: justify;">Does the agreement signed by you and your adversary contain both an attorneys’ fee and binding arbitration provision?</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input id="radio-2-13-2" class="form-check-input" type="radio" name="attorney" value="value-2" style="width: auto;">
                                                            <label for="radio-2-13-2" class="form-check-label"style="text-align: justify;">Does the agreement signed by you and your adversary contain an attorneys’ fee provision, but no binding arbitration provision? (this results in a 10% case value reduction)</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input id="radio-2-13-3" class="form-check-input" type="radio" name="attorney" value="value-3" style="width: auto;">
                                                            <label for="radio-2-13-3" class="form-check-label"style="text-align: justify;">Does the agreement signed by you and your adversary contain a binding arbitration provision, but no attorneys’ fee provision? (this results in a 15% case value reduction)</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input id="radio-2-13-4" class="form-check-input" type="radio" name="attorney" value="value-4" style="width: auto;">
                                                            <label for="radio-2-13-4" class="form-check-label"style="text-align: justify;">There is no attorneys’ fee or binding arbitration provision in a written agreement between you and your adversary. (this results in a 35% case value reduction)</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input id="radio-2-13-5" class="form-check-input" type="radio" name="attorney" value="value-5" style="width: auto;">
                                                            <label for="radio-2-13-5" class="form-check-label"style="text-align: justify;">There is no written agreement between you and your adversary. (this results in a 35% case value reduction)</label>
                                                        </div>
                                                    </div>
                                                    <div id="attorney_no" class="form-group">
                                                        <label id="label-2-13" for="input-2-13">Because you will not be represented by an Attorney, we have deducted 20% as a margin of error from your Recoverable Damages.
                                                        </label>
                                                        <input id="input-2-13" class="form-control" type= "text"  value="0"  disabled></input>
                                                    </div>
                                                </div>
                                                <div id ="Q14" class="tab">
                                                    <div class="form-group">
                                                        <label id="label-2-14" for="select-2-14">Will you invest substantial time into your case?
                                                            
                                                        </label>
                                                        <select id="select-2-14" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                    <div id="moneytime_yes" class="form-group">
                                                        <small class = "form-text" >
                                                            What is the approximate value of the time you will invest?
                                                        </small>
                                                        <input id="input-2-14-1" class="form-control" type= "text" placeholder="Enter $ Amount" ></input>

                                                        <small class = "form-text" >
                                                            This amount is divided by two to reflect that this is time not money you are losing.
                                                        </small>
                                                        <input id="input-2-14-2" class="form-control" type= "text" value="0" disabled></input>
                                                    </div>
                                                </div>

                                                <div id="ResultStep2" class = "results" style="display: none; margin:5% 5% 5% 5%; text-align: left;">
                                                    <h5 id="ResultStep2Text1"></h5>
                                                    <br>
                                                    <h5 id="ResultStep2Text2"></h5>
                                                    <p>the amount of your claim after apportionment of fault, whether you received a substantial benefit, and if there is a damage defining provision.</p>
                                                    <br>
                                                    <h5 id="ResultStep2Text3"></h5>
                                                    <p>the amount you are likely to net depending on whether you hire an attorney and have a right to attorneys fee reimbursement, whether you will go to arbitration, if you will invest substantial time of your own.</p>
                                                    <br>
                                                    <p>This result is part of an algorithm that will be factored into your Comprehensive Case Analysis in Phase 5.</p>
                                                </div>

                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step2" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step2" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn2" class="btn btn-info" onclick="nextPrev(-1,1)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn2" class="btn btn-info" onclick="nextPrev(1,1)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h2>Legal Options Assessment</h2>
                    <section>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 3. Legal Options Assesment</h2>    
                                        <div class="col-lg-10 sm-6">
                                            <h3>What are your legal options?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(3)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step3">
                                                <div id="Prev-Step3" class = "tab" >
                                                    <p style="color: #345B99" >Click on the Help Button for important information that will help you answer these questions </p>
                                                </div>                                                
                                                <div id="Q16-Prev" class="tab">
                                                    <div class="form-group">
                                                        <label id="label-3-16" for="select-3-16">Select your state to look up your Small Claims Limit
                                                        </label>
                                                        <select id="select-3-16" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                        </select>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-6 col-sm-12">
                                                            <label for="SmallClaimLimitResult">Small Claims Limit in your State</label>
                                                            <h5> <span id = "SmallClaimLimitResult" class="badge badge-light">0,000</span> </h5>
                                                        </div>
                                                        <div class="col-lg-6 col-sm-12">
                                                            <label for="SmallClaimLinkResult">More Information is available for this state</label>
                                                            <h6 id = "SmallClaimLinkResult" ></h6>
                                                        </div>
                                                    </div>                                                
                                                </div>
                                                <div id="Q16" class="tab" >
                                                    <div class="form-group">
                                                        <label for="input-3-16-1">Recoverable Damages</label>
                                                        <input id="input-3-16-1" class="form-control" type="text" disabled>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col">
                                                            <div id="WhitinEvaluation"></div> 
                                                        </div>
                                                    </div>
                                                    <div id="AboveLimit"  >
                                                        <div class="col-lg-12 col-md-12">
                                                            <div class="form-group">
                                                                <label for="select-3-16-2">Are you willing to reduce your claim to fit the Small Claims Limit?</label>
                                                                </label>
                                                                <select id="select-3-16-2" class="form-control" name="">
                                                                    <option value="" >Please choose an option</option>
                                                                    <option value="yes">Yes</option>
                                                                    <option value="no">No</option>
                                                                </select>
                                                            </div>
                                                        </div>  
                                                    </div>
                                                    <div id="BellowLimit"  >
                                                        <div class="form-group">
                                                            <label for="select-3-16-3">Do you have a binding arbitration provision in a written agreement with your adversary?</label>
                                                            </label>
                                                            <select id="select-3-16-3" class="form-control" name="">
                                                                <option value="" >Please choose an option</option>
                                                                <option value="yes">Yes</option>
                                                                <option value="no">No</option>
                                                            </select>
                                                        </div>                                
                                                        <div id="ExcludeSmallClaims" class="form-group">
                                                            <label for="select-3-16-4">Does it exclude Small Claims?</label>                                                            
                                                            </label>
                                                            <select id="select-3-16-4" class="form-control" name="">
                                                                <option value="" >Please choose an option</option>
                                                                <option value="yes">Yes</option>
                                                                <option value="no">No</option>
                                                            </select>
                                                        </div>                                                                                
                                                    </div> 
                                                </div>  
                                                <div id="Q17" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-3-17">Do you have a written agreement signed by your adversary that contains an attorneys’ fee provision?
                                                        </label>
                                                        <select id="select-3-17" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="Q18" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-3-18">Do you have an agreement signed by you and your adversary that requires binding arbitration?
                                                        </label>
                                                        <select id="select-3-18" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="Q19" class="tab">
                                                    <div class="form-group">
                                                        <label for="select-3-19">Do you have an agreement signed by you and your adversary that requires mediation?
                                                        </label>
                                                        <select id="select-3-19" class="form-control" name="">
                                                            <option value="" >Please choose an option</option>
                                                            <option value="yes">Yes</option>
                                                            <option value="no">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div id="ResultStep3" class = "results" style="display: none; margin:5% 0% 10% 0%;">
                                                    <h4 id="ResultStep3Text1"></h4> 
                                                    <h4 id="ResultStep3Text2"></h4>
                                                    <br>
                                                    <p id="ResultStep3Text3"></p>                                            
                                                </div>

                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step3" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step3" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn3" class="btn btn-info" onclick="nextPrev(-1,2)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn3" class="btn btn-info" onclick="nextPrev(1,2)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>                                                
                                            </form>
                                   
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>

                    <h2>Collectability Assessment</h2>
                    <section>
                        <div class="container-fluid">                            
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 4. Collectability Assesment</h2>    
                                        <div class="col-lg-10 sm-6">
                                            <h3>What are your chances of collecting on your judgment?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(4)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step4">
                                                <div id="Prev-Q21" class = "tab" >
                                                    <p style="color: #345B99" >Click on the Help Button for important information that will help you answer these questions. </p>
                                                </div> 
                                                <div id="Q21" class = "tab">                                        
                                                    <div id = 'adversary_check' class="form-group" style="text-align: justify;">
                                                        <label>21. Select the first option that relates to your adversary:</label>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-1" class="form-check-input" type="radio" name="adversary" value="value-1" style="width: auto;">
                                                            <label for="radio-4-21-1" class="form-check-label" style="text-align: justify;">a corporation</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-2" class="form-check-input" type="radio" name="adversary" value="value-2" style="width: auto;">
                                                            <label for="radio-4-21-2" class="form-check-label"style="text-align: justify;">represented by an insurance company</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-3" class="form-check-input" type="radio" name="adversary" value="value-3" style="width: auto;">
                                                            <label for="radio-4-21-3" class="form-check-label"style="text-align: justify;">an operating business other than a corporation, like an LLC, or Sole Proprietorship</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-4" class="form-check-input" type="radio" name="adversary" value="value-4" style="width: auto;">
                                                            <label for="radio-4-21-4" class="form-check-label"style="text-align: justify;">own real property</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-5" class="form-check-input" type="radio" name="adversary" value="value-5" style="width: auto;">
                                                            <label for="radio-4-21-5" class="form-check-label"style="text-align: justify;">have assets and is salaried</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-6" class="form-check-input" type="radio" name="adversary" value="value-6" style="width: auto;">
                                                            <label for="radio-4-21-6" class="form-check-label"style="text-align: justify;">have assets and works for self as sole proprietor</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-7" class="form-check-input" type="radio" name="adversary" value="value-7" style="width: auto;">
                                                            <label for="radio-4-21-7" class="form-check-label"style="text-align: justify;">have assets and none of above apply</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-8" class="form-check-input" type="radio" name="adversary" value="value-8" style="width: auto;">
                                                            <label for="radio-4-21-8" class="form-check-label"style="text-align: justify;">none of above or below apply</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-9" class="form-check-input" type="radio" name="adversary" value="value-9" style="width: auto;">
                                                            <label for="radio-4-21-9" class="form-check-label"style="text-align: justify;">have judgments against them</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-10" class="form-check-input" type="radio" name="adversary" value="value-10" style="width: auto;">
                                                            <label for="radio-4-21-10" class="form-check-label"style="text-align: justify;">have no assets or means of support</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input id="radio-4-21-11" class="form-check-input" type="radio" name="adversary" value="value-11" style="width: auto;">
                                                            <label for="radio-4-21-11" class="form-check-label"style="text-align: justify;">have bankruptcy proceedings pending</label>
                                                        </div>

                                                    </div>   
                                                </div>
                                                <div id="ResultStep4" class = "results" style="display: none; margin:5% 0% 10% 0%;">
                                                    <h4 id="ResultStep4Text1"></h4> 
                                                    <h4 id="ResultStep4Text2"></h4>
                                                    <br>
                                                    <p id="ResultStep4Text3"></p>                                            
                                                </div>                                              

                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step4" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step4" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn4" class="btn btn-info" onclick="nextPrev(-1,3)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn4" class="btn btn-info" onclick="nextPrev(1,3)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>
                                            
                                            </form>

                                        </div>                                     
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h2>Comprehensive Case Analysis</h2>
                    <section>
                        <div class="container-fluid">                            
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 5. Comprehensive Case Analysis</h2>
                                        <div class="col-lg-10 sm-6"> 
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(5)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step5">
                                                <div id="Q23" class = "results">                                        
                                                    <div id="ResultStep5" class="table-responsive" style="text-align: initial;">
                                                        <table class="table table-bordered table-sm">
                                                            <thead style="color: #345B99;">
                                                                <tr>
                                                                    <th colspan="2" scope="col">Results Carried Forward:</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Phase 1, Legal Evaluation [Range: 0 or 350]</td>
                                                                    <td id="tbl-row-1"> 0 </td>   
                                                                </tr>
                                                                <!--
                                                                <tr>
                                                                    <td>Phase 2, Case Value Assessment [Unique to your case]</td>
                                                                    <td id="tbl-row-2"> 0 </td>   
                                                                </tr>
                                                                -->
                                                                <tr>
                                                                    <td>Phase 2, Damage Assessment [Range: 5-50]</td>
                                                                    <td id="tbl-row-3"> 0 </td>   
                                                                </tr>
                                                                <tr>
                                                                    <td>Phase 3, Legal Options Assessment [Range: 0-190]</td>
                                                                    <td id="tbl-row-4"> 0 </td>   
                                                                </tr>
                                                                <tr>
                                                                    <td>Phase 4, Collectability Assessment [Range 20-150]</td>
                                                                    <td id="tbl-row-5"> 0 </td>   
                                                                </tr>
                                                            </tbody>
                                                            <thead style="color: #345B99;">                         
                                                                <tr>
                                                                    <th colspan="2" scope="col">Comprehensive Case Assessment:</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Case Feasibility Assessment [Range 4-100%]</td>
                                                                    <td id="tbl-row-6">  </td>   
                                                                </tr>                                                            
                                                                <tr>
                                                                    <td>Gross Damages</td>
                                                                    <td id="tbl-row-7">0</td>   
                                                                </tr>
                                                                <tr>
                                                                    <td>Recoverable Damages</td>
                                                                    <td id="tbl-row-8"> 0 </td>   
                                                                </tr>
                                                                <tr>
                                                                    <td>Net Damages</td>
                                                                    <td id="tbl-row-9"> 0 </td>   
                                                                </tr>
                                                                <tr>
                                                                    <td >Pre-Litigation Settlement Amount</td>
                                                                    <td id="tbl-row-10">0</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <p>These results and those in the nest two Phases will be emailed to you when you complete Lawsuit Analyzer©</p>
                                                    </div>
                                                </div>
                                                                                          
                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step5" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step5" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn5" class="btn btn-info" onclick="nextPrev(-1,4)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn5" class="btn btn-info" onclick="nextPrev(1,4)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>                                             
                                                                                       
                                            </form>
                                        </div>                                     
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h2>Feasibility Assessment Chart</h2>
                    <section>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 6. Feasibility Assessment Chart</h2>
                                        <div class="col-lg-10 sm-6"> 
                                            <h3>How Feasible is your case to pursue?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(6)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step6">
                                                <div id="Q33Chart" class="results">
                                                    <p id="NoteStep6"> <strong>Lawsuit Analzyer</strong><sup>©</sup> has calculated your Case Feasibility based on your answers to be:</p>
                                                    <br>
                                                    <div id="ResultStep6"> </div>
                                                    <br>
                                                    <p >Click on the Help Button to access what this means to you</p>    
                                                </div>

                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step6" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step6" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="prevBtn6" class="btn btn-info" onclick="nextPrev(-1,5)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f;" >&uarr;</button>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="nextBtn6" class="btn btn-info" onclick="nextPrev(1,5)" style="border-radius: 0 !important; background-color: #80b5b5;margin: 0px;border-color: #5f615f">&darr;</button>    
                                                    </div>
                                                </div>                                                  

                                            </form>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </section>

                    <h2>Forum Assessment</h2>
                    <section>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col">
                                    <div class="row" style="align-items: flex-end;">
                                        <h2>PHASE 7. Forum Assessment</h2>
                                        <div class="col-lg-10 sm-6"> 
                                            <h3>Where will you pursue your dispute?</h3>
                                        </div>
                                        <div class="col-lg-2 sm-6" style="text-align: right;">
                                            <img onclick="callHelpPage(7)" class = "helpimg" src="assets/helpimage.png" alt="Help Image">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col" style="text-align: center;">
                                            <form id="Step7">
                                                <div id="Q34Last" class="results">
                                                    <div class="row">
                                                        <div class="col-lg-5 col-md-12 d-flex justify-content-center justify-content-md-end" >
                                                            <h4>Forum:</h4>
                                                        </div>
                                                        <div class="col-lg-6 col-sm-12 d-flex justify-content-center justify-content-md-center" >
                                                            <p id="ResultStep7_1"></p>
                                                        </div>
                                                    </div>
                                                    <div id = "MediationYes" style="display: none;">
                                                        <div class = "row">
                                                            <div class="col d-flex justify-content-center" >
                                                                <p style="color: #9E2D2D;" >If Mediation is unsuccessful, your next step is the Forum below.</p>
                                                            </div>
                                                        </div>
                                                        <div class = "row" >
                                                            <div class="col-lg-5 col-sm-12 d-flex justify-content-center justify-content-md-end" >
                                                                <h4>Forum:</h4>
                                                            </div>
                                                            <div class="col-lg-6 col-sm-12 d-flex justify-content-center justify-content-md-center" >
                                                                <p id="ResultStep7_2"></p>
                                                            </div>                                                        
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col d-flex justify-content-start">
                                                            <p style="color: #9E2D2D;" >You have completed the Lawsuit Analyzer©. You can still edit your answers and change your outcome. Once you click the Go to button below, you will no longer be able to edit and your results will be emailed you.</p>
                                                        </div>
                                                        <input id = "emailSummary" class="form-control" type="email" name="emailSummary" placeholder="Please enter your email address in format: yourname@example.com">
                                                        <p id = "emailSummary_error" style="color: #9E2D2D; display: none;" >Please enter your email address in format: yourname@example.com </p>                    
                                                    </div>                                                
                                                </div>
                                                <div style="overflow:auto; margin: 2%;">
                                                    <div style="float:center;">
                                                        <button type="button" id="nextBtn7" class="badge badge-info-ls" onclick="gotolink()">Go to </button>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-row justify-content-end">
                                                    <div style="width: 120px; background: #5f615f;" > 
                                                        <p id = "progress_text_Step7" style="color: white; margin-top: 5px;margin-bottom: 7px;font-size: 0.6rem;" ></p>
                                                        <div class="progress" style="background: #D8D9DD; margin:5% 5% 5% 5%; height: 5px;" >
                                                            <div id="progress_Step7" class="progress-bar" style="background: #345B99;"></div>                                                    
                                                        </div>
                                                    </div>
                                                </div>                                             
                                            
                                            </form>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </section>              
                </div>
            </div>
        </div>

        <div id = "email_result" class = "container-fluid" style="display: none;">
            <div class="row" style="margin: 0px;">
                <div class="col" style="text-align: center;">
                    <img src="https://lawsuitanalysis.com/wp-content/uploads/2021/03/Logo-Capital-Lawyer-red-3-6-2021-02-e1615055972503.png" width="180" alt="Lawsuit Analyzer - Be Your Own Lawyer" title="Logo Capital Lawyer red 3 6 2021-02">
                </div>
            </div>

            <div class="row" style="margin: 0px;">
                <div class="col" style="text-align: center;">
                    <div style="width:100%; border-top: 2.5px solid; border-top-color: #547391;margin-top: 2%;margin-bottom: 1%;"></div>
                    <p style="color: #547391; font-size: 1.5rem; font-weight: bold;">PHASE 5: Comprehensive Case Analysis</p>
                    <div style="width:100%; border-top: 2.5px solid; border-top-color: #547391;margin-top: 1%;margin-bottom: 2%;"></div>
                </div>
            </div>

            <div class="content" style="margin: 2%;">
                <br>
                <br>
                <br>
                <table>
                    <thead style="color: #547391;">
                        <tr>
                            <th colspan="2" scope="col" style="text-align: left; font-weight: bold;"> <p style="color: #547391;">Results Carried Forward:</p> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td > Phase 1, Legal Evaluation [Range: 0 or 350]...................................................................</td>
                            <td id="tbl-row-1-result"></td>   
                        </tr>
                        <tr>
                            <td >Phase 2, Damage Assessment [Range: 5-50]................................................................</td>
                            <td id="tbl-row-3-result"></td>   
                        </tr>
                        <tr>
                            <td >Phase 3, Legal Options Assessment [Range: 0-190].................................................</td>
                            <td id="tbl-row-4-result"></td>   
                        </tr>
                        <tr>
                            <td >Phase 4, Collectability Assessment [Range 20-150]..................................................</td>
                            <td id="tbl-row-5-result"></td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table>
                    <thead style="color: #547391;">                         
                        <tr>
                            <th colspan="2" scope="col" style="text-align: left;font-weight: bold;"><p style="color: #547391;">Comprehensive Case Assessment:</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Case Feasibility Assessment [Range 4-100%]................................................................</td>
                            <td id="tbl-row-6-result"></td>   
                        </tr>                                                            
                        <tr>
                            <td>Gross Damages..................................................................................................................................</td>
                            <td id="tbl-row-7-result"></td>   
                        </tr>
                        <tr>
                            <td>Recoverable Damages..................................................................................................................</td>
                            <td id="tbl-row-8-result"></td>   
                        </tr>
                        <tr>
                            <td>Net Damages.......................................................................................................................................</td>
                            <td id="tbl-row-9-result"></td>   
                        </tr>
                        <tr>
                            <td>Pre-Litigation Settlement Amount......................................................................................</td>
                            <td id="tbl-row-10-result"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div id="modal_data_clear" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" >
                <div class="modal-content">
                    <div class="modal-body" style="text-align: center;">
                        Data wiped successfully
                    </div>
                </div>
            </div>
        </div>


    </body>



</html>

<script>
    //function to show the first Questions of every Step by 'body current' class
    $(function() {
        (function($) {
            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

            $.fn.attrchange = function(callback) {
                if (MutationObserver) {
                    var options = {
                        subtree: false,
                        attributes: true
                    };

                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(e) {
                            callback.call(e.target, e.attributeName);
                        });
                    });

                    return this.each(function() {
                        observer.observe(this, options);
                    });

                }
            }
        })(jQuery);

        //Now you need to append event listener
        $('#wizard-p-0').attrchange(function(attrName) {
            if(attrName=='class'){
                if (this.className == 'body current'){
                    ShowFirstOnly('Step1');
                }
            };
        });
        $('#wizard-p-1').attrchange(function(attrName) {
            if(attrName=='class'){
                if (this.className == 'body current'){
                    ShowFirstOnly('Step2');
                }
            };
        });
        $('#wizard-p-2').attrchange(function(attrName) {
            if(attrName=='class'){
                if (this.className == 'body current'){
                    ShowFirstOnly('Step3');
                }
            };
        });
        $('#wizard-p-3').attrchange(function(attrName) {
            if(attrName=='class'){
                if (this.className == 'body current'){
                    ShowFirstOnly('Step4');
                }
            };
        });

        function ShowFirstOnly(Step){
            var stp = document.getElementById(Step);
            var y = stp.getElementsByClassName("results");
            y[0].style.display = "none";
            showTab(0,Step);
        };

    });

    // executes when HTML-Document is loaded and DOM is ready
    $(document).ready(function () {
        //Hide results Text
        $('#ResultStep1,#ResultStep2').hide();
        //Hide Dependency Question
        //**Step2 */
        $('#PriorObligation_yes,#CauseOfSituation_yes,#RecieveBenefit_yes').hide();
        $('#DamageProvision_yes,#attorney_yes,#attorney_no,#moneytime_yes').hide();
        //** Step 3 */
        $('#AboveLimit,#BellowLimit,#ExcludeSmallClaims').hide();

        $("#select-1-4").change(function(){
            if ($(this).val() == 'yes'){
                $('#PriorObligation_yes').hide();
                $('#textarea-1-4-1').text('NULL');
            }else {
                $('#PriorObligation_yes').hide();
                $('#textarea-1-4-1').text('NULL');
            };
        });

        $('#select-2-8').change(function () { 
            if ($(this).val() == 'yes'){
                $('#CauseOfSituation_yes').show();          
            }
            if ($(this).val() == 'no') {
                $('#CauseOfSituation_yes').hide();
                $('#input-2-8-1').val(0);
                EasyAdvance('Step2')
            };
        });

        $('#select-2-9').change(function () { 
            if ($(this).val() == 'yes'){
                $('#RecieveBenefit_yes').show();        
            }else {
                $('#RecieveBenefit_yes').hide();
                $('#input-2-9-1').val(0);
                EasyAdvance('Step2')
            };
        });

        $('#select-2-10').change(function () { 
            if ($(this).val() == 'yes'){
                $('#DamageProvision_yes').show();        
            }
            if ($(this).val() == 'no') {
                $('#DamageProvision_yes').hide();
                $('#select-2-10-1').val('default');
                $('#input-2-10-2').val(0); 
                EasyAdvance('Step2')
            };
        });

        $('#select-2-10-1').change(function () { 
            if ($(this).val() == 'Liquidated-Damages'){
                $('#label-2-10-2').text('Enter the Liquidated Damages amount from your contract.');        
            }
            if ($(this).val() == 'Damage-Limit') {
                $('#label-2-10-2').text('Enter the Damage-Limit amount from your contract.');        
            };
            if ($(this).val() == 'default') {
                $('#label-2-10-2').text('Please choose an option.');        
            };
        });


        $('#select-2-12').change(function () { 
            if ($(this).val() == 'yes'){
                $('#attorney_no').hide();
                $('#attorney_yes').css("display","block");
                $('#attorney_yes').show();
            }else {
                $('#attorney_yes').hide();
                $('#attorney_no').css("display","block");
                $('#attorney_no').show(); 
                
            };
        });

        $('#select-2-14').change(function () { 
            if ($(this).val() == 'yes'){
                $('#moneytime_yes').show();
            }else {
                $('#moneytime_yes').hide();
                $('#input-2-14-1').val(0);
                EasyAdvance('Step2');
            };
        });

        $('#select-3-16-2').change(function () { 
            if ($(this).val() == 'yes'){
                $('#BellowLimit').show();
                $('#select-3-16-3').val('');
                $('#select-3-16-4').val('');         
            }else {
                $('#BellowLimit').hide();
                $('#select-3-16-3').val('no');
                $('#select-3-16-4').val('no');
                EasyAdvance('Step3');
            };
        });

        $('#select-3-16-3').change(function () { 
            if ($(this).val() == 'yes'){
                $('#ExcludeSmallClaims').show();
                $('#select-3-16-4').val('');
            }else {
                $('#ExcludeSmallClaims').hide();
                $('#select-3-16-4').val('no');
                EasyAdvance('Step3');
            };
        });

        $('#select-3-16-4').change(function () { 
            if ($(this).val() !== '' && $('#select-3-16-3').val() !== ''){
                EasyAdvance('Step3');
            };
        });

        //Format numbers
        $("#input-2-7,#input-2-8-1,#input-2-9-1,#input-2-10-2,#input-2-14-1").on({
            "focus": function (event) {
                $(event.target).select();
            },
            "keyup": function (event) {
                $(event.target).val(function (index, value ) {
                    return value.replace(/\D/g, "")
                                //.replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                });
            }
        });   

        $('#input-2-14-1').change(function () { 
            if (!isNaN(parseInt($(this).val().replace(",",""),10))){
                var val = parseInt($(this).val().replace(",",""),10) / 2
                $('#input-2-14-2').val(SeparadorMiles(val));
            }
        });

        //Easy advance behaviour Step1
        $('#select-1-2,#select-1-3,#select-1-4,#select-1-5').change(function () { 
            EasyAdvance('Step1');
        });

        //Easy advance behaviour Step2
        $('#select-2-12').change(function () { 
            EasyAdvance('Step2');
        });

        $('#input-2-7,#input-2-8-1,#input-2-9-1,#input-2-10-2,#input-2-13,#input-2-14-1').keypress(function (e) { 
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode == '13'){
                EasyAdvance('Step2')
            }        
        });
        
        $("input[type='radio'][name='attorney']").click(function(){
            var selected = $("input[type='radio'][name='attorney']:checked");
            if (selected.length > 0) {
                EasyAdvance('Step2')
            } 
        });
        
        //Easy advance behaviour Step3
        $('#select-3-17,#select-3-18,#select-3-19').change(function () { 
            EasyAdvance('Step3');
        });

        //Easy advance behaviour Step4
        $("input[type='radio'][name='adversary']").click(function(){
            var selected = $("input[type='radio'][name='adversary']:checked");
            if (selected.length > 0) {
                EasyAdvance('Step4')
            } 
        });

        //Prevent Submit on phase 7
        $('#emailSummary').keydown(function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });

        $("#StepCollapse").click(function () { 
            if ( $(".steps").is(":visible")) {
                $(".steps").hide();
                $("#ToggleBtnText").text('Show Steps');
            } else {
                $(".steps").show();
                $("#ToggleBtnText").text('Hide Steps');
            }
        });

        function EasyAdvance(step){
            validateForm(step);
            var lastChar = step.substr(step.length - 1)
            nextPrev(1,lastChar-1)
        }

        //SmallClaimData
        var SmallClaimsTable = [
            {
                Abrev: "AL",
                State: "Alabama",
                Dollar_Limit: "6,000",
                Link: "http://judicial.alabama.gov/library/SmallClaimsRules"
            },
            {
                Abrev: "AK",
                State: "Alaska",
                Dollar_Limit: "10,000",
                Link: "http://www.state.ak.us/courts/forms/sc-100.pdf"
            },
            {
                Abrev: "AZ",
                State: "Arizona",
                Dollar_Limit: "3,500",
                Link: "https://www.azcourts.gov/selfservicecenter/Small-Claims"
            },
            {
                Abrev: "AR",
                State: "Arkansas",
                Dollar_Limit: "5,000",
                Link: "http://www.arlegalservices.org/smallclaimspacket"
            },
            {
                Abrev: "CA",
                State: "California",
                Dollar_Limit: "10,000",
                "Additional Information": "Limit for Businesses (except sole proprietors) and public entities is $5,000. You can only file 2 claims in a calendar year seeking more than $2,500. See CA Small Claims Court for more exceptions related to actions against guarantors. ",
                Link: "https://www.courts.ca.gov/selfhelp-smallclaims.htm"
            },
            {
                Abrev: "CO",
                State: "Colorado",
                Dollar_Limit: "7,500",
                Link: "https://www.courts.state.co.us/Self_Help/smallclaims/"
            },
            {
                Abrev: "CT",
                State: "Connecticut",
                Dollar_Limit: "5,000",
                "Additional Information": "Except in landlord-tenant security deposit claims",
                Link: "http://www.jud.state.ct.us/faq/smallclaims.html"
            },
            {
                Abrev: "DE",
                State: "Delaware",
                Dollar_Limit: "15,000",
                Link: "http://courts.delaware.gov/jpcourt/"
            },
            {
                Abrev: "DC",
                State: "District of Columbia",
                Dollar_Limit: "10,000",
                Link: "http://www.dccourts.gov/services/civil-matters"
            },
            {
                Abrev: "FL",
                State: "Florida",
                Dollar_Limit: "8,000",
                Link: "http://www.flcourts.org/resources-and-services/family-courts/family-law-self-help-information/small-claims.stml"
            },
            {
                Abrev: "GA",
                State: "Georgia",
                Dollar_Limit: "15,000",
                "Additional Information": "No limit in eviction cases.",
                Link: "http://consumer.georgia.gov/consumer-topics/magistrate-court"
            },
            {
                Abrev: "HI",
                State: "Hawaii",
                Dollar_Limit: "5,000",
                "Additional Information": "No limit on landlord-tenant residential security deposit cases. For the return of leased or rented personal property, the property must not be worth more than $5,000.",
                Link: "http://www.courts.state.hi.us/self-help/small_claims_info"
            },
            {
                Abrev: "ID",
                State: "Idaho",
                Dollar_Limit: "5,000",
                Link: "https://www.isc.idaho.gov/irsca"
            },
            {
                Abrev: "IL",
                State: "Illinois",
                Dollar_Limit: "10,000",
                Link: "http://www.ag.state.il.us/consumers/smlclaims.html"
            },
            {
                Abrev: "IN",
                State: "Indiana",
                Dollar_Limit: "6,000",
                "Additional Information": "$8,000 in Marion County",
                Link: "http://www.in.gov/judiciary/2710.htm"
            },
            {
                Abrev: "IA",
                State: "Iowa",
                Dollar_Limit: "6,500",
                Link: "http://www.iowacourts.gov/Court_Rules__Forms/Small_Claims_Forms/"
            },
            {
                Abrev: "KS",
                State: "Kansas",
                Dollar_Limit: "4,000",
                Link: "http://www.kscourts.org/rules-procedures-forms/small-claims-information/"
            },
            {
                Abrev: "KY",
                State: "Kentucky",
                Dollar_Limit: "2,500",
                Link: "http://courts.ky.gov/resources/publicationsresources/Publications/P6SmallClaimsHandbookweb.pdf"
            },
            {
                Abrev: "LA",
                State: "Louisiana",
                Dollar_Limit: "5,000",
                Link: "http://brgov.com/dept/citycourt/"
            },
            {
                Abrev: "ME",
                State: "Maine",
                Dollar_Limit: "6,000",
                Link: "http://www.courts.state.me.us/maine_courts/specialized/small_claims/index.shtml"
            },
            {
                Abrev: "MD",
                State: "Maryland",
                Dollar_Limit: "5,000",
                Link: "https://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=3&cad=rja&uact=8&sqi=2&ved=0CC4QFjAC&url=http%3A%2F%2Fwww.courts.state.md.us%2Flegalhelp%2Fsmallclaims.html&ei=QF69VNDiB_SRsQSlj4GQBA&usg=AFQjCNE5DKWuSxYbgHYG8YWG5_leo5N9yg"
            },
            {
                Abrev: "MA",
                State: "Massachusetts",
                Dollar_Limit: "7,000",
                "Additional Information": "No limit for property damage caused by a motor vehicle.",
                Link: "http://www.mass.gov/ago/consumer-resources/consumer-assistance/small-claims-court.html"
            },
            {
                Abrev: "MI",
                State: "Michigan",
                Dollar_Limit: "6,000",
                Link: "https://michiganlegalhelp.org/self-help-tools/money-and-debt/i-have-small-claims-case"
            },
            {
                Abrev: "MN",
                State: "Minnesota",
                Dollar_Limit: "15,000",
                "Additional Information": "$4,000 for claims involving consumer credit transactions",
                Link: "http://www.mncourts.gov/selfhelp/?page=313"
            },
            {
                Abrev: "MS",
                State: "Mississippi",
                Dollar_Limit: "3,500",
                Link: "https://courts.ms.gov/trialcourts/justicecourt/justicecourt.php"
            },
            {
                Abrev: "MO",
                State: "Missouri",
                Dollar_Limit: "5,000",
                Link: "http://www.mobar.org/uploadedFiles/Home/Publications/Legal_Resources/Brochures_and_Booklets/small claims.pdf"
            },
            {
                Abrev: "MT",
                State: "Montana",
                Dollar_Limit: "7,000",
                Link: "http://dojmt.gov/consumer/guide-to-small-claims-court/"
            },
            {
                Abrev: "NE",
                State: "Nebraska",
                Dollar_Limit: "3,600",
                "Additional Information": "Through June 30, 2020 (adjusted every five years based on the Consumer Price Index",
                Link: "https://supremecourt.nebraska.gov/self-help/small-claims/filing-small-claims-case-nebraska"
            },
            {
                Abrev: "NV",
                State: "Nevada",
                Dollar_Limit: "10,000",
                Link: "https://www.civillawselfhelpcenter.org/self-help/small-claims"
            },
            {
                Abrev: "NH",
                State: "New Hampshire",
                Dollar_Limit: "10,000",
                Link: "http://www.courts.state.nh.us/district/eclaims/"
            },
            {
                Abrev: "NJ",
                State: "New Jersey",
                Dollar_Limit: "3,000",
                "Additional Information": "For claims relating to security deposits; certain landlord-tenant suits cannot be brought",
                Link: "https://njcourts.gov/selfhelp/small_claims.html"
            },
            {
                Abrev: "NM",
                State: "New Mexico",
                Dollar_Limit: "10,000",
                Link: "http://www.nmcourts.gov/Self-Help/self-help-guide.aspx"
            },
            {
                Abrev: "NY",
                State: "New York",
                Dollar_Limit: "10,000",
                Link: "http://www.courts.state.ny.us/courthelp/smallClaims/index.shtml"
            },
            {
                Abrev: "NC",
                State: "North Carolina",
                Dollar_Limit: "10,000",
                Link: "http://www.nccourts.org/Courts/Trial/Sclaims/"
            },
            {
                Abrev: "ND",
                State: "North Dakota",
                Dollar_Limit: "15,000",
                Link: "https://www.ndcourts.gov/legal-self-help/small-claims"
            },
            {
                Abrev: "OH",
                State: "Ohio",
                Dollar_Limit: "6,000",
                Link: "http://www.ohiojudges.org/Document.ashx?DocGuid=932074c6-0f9b-42d9-9979-1864ad693f5b"
            },
            {
                Abrev: "OK",
                State: "Oklahoma",
                Dollar_Limit: "10,000",
                Link: "http://www.oklahomacounty.org/158/Small-Claims"
            },
            {
                Abrev: "OR",
                State: "Oregon",
                Dollar_Limit: "10,000",
                Link: "https://www.courts.oregon.gov/forms/Pages/small-claims.aspx"
            },
            {
                Abrev: "PA",
                State: "Pennsylvania",
                Dollar_Limit: "12,000",
                Link: "http://www.pacourts.us/courts/minor-courts/"
            },
            {
                Abrev: "RI",
                State: "Rhode Island",
                Dollar_Limit: "2,500",
                Link: "https://www.courts.ri.gov/Courts/districtcourt/Pages/smallclaims.aspx"
            },
            {
                Abrev: "SC",
                State: "South Carolina",
                Dollar_Limit: "7,500",
                Link: "http://www.sccourts.org/selfhelp/FAQMagistrate.pdf"
            },
            {
                Abrev: "SD",
                State: "South Dakota",
                Dollar_Limit: "12,000",
                Link: "http://ujs.sd.gov/Small_Claims/"
            },
            {
                Abrev: "TN",
                State: "Tennessee",
                Dollar_Limit: "25,000",
                "Additional Information": "No limit in eviction suits or suits to recover personal property",
                Link: "http://tncourts.gov/programs/self-help-center"
            },
            {
                Abrev: "TX",
                State: "Texas",
                Dollar_Limit: "10,000",
                "Additional Information": "Until August 31, 2020; $20,000 effective September 1, 2020",
                Link: "https://guides.sll.texas.gov/small-claims"
            },
            {
                Abrev: "UT",
                State: "Utah",
                Dollar_Limit: "11,000",
                Link: "http://www.utcourts.gov/howto/smallclaims/"
            },
            {
                Abrev: "VT",
                State: "Vermont",
                Dollar_Limit: "5,000",
                Link: "http://www.vermontjudiciary.org/SRL/smallclaims/default.aspx"
            },
            {
                Abrev: "VA",
                State: "Virginia",
                Dollar_Limit: "5,000",
                Link: "http://www.courts.state.va.us/resources/small_claims_court_procedures.pdf"
            },
            {
                Abrev: "WA",
                State: "Washington",
                Dollar_Limit: "10,000",
                "Additional Information": "If brought by natural person; $5,000 all other cases",
                Link: "http://www.courts.wa.gov/newsinfo/resources/?fa=newsinfo_jury.scc&altMenu=smal"
            },
            {
                Abrev: "WV",
                State: "West Virginia",
                Dollar_Limit: "10,000",
                Link: "http://www.courtswv.gov/lower-courts"
            },
            {
                Abrev: "WI",
                State: "Wisconsin",
                Dollar_Limit: "10,000",
                "Additional Information": "No limit in eviction suits",
                Link: "http://www.wicourts.gov/services/public/selfhelp/smallclaims.htm"
            },
            {
                Abrev: "WY",
                State: "Wyoming",
                Dollar_Limit: "6,000",
                Link: "https://www.courts.state.wy.us/court_rule/rules-and-forms-governing-small-claims-cases/"
            }
        ]

        //Look for Small Claims Limit
        $('#select-3-16').change(function () { 
            validateSCL($(this).val());
        });

        function validateSCL(val){
            if ($('#select-3-16').val() !== "" ) {
                var StateLimitResults = VLookUp(SmallClaimsTable,val,'Abrev', 'Dollar_Limit' );
                var StateLinkResults = VLookUp(SmallClaimsTable,val,'Abrev', 'Link')
                $('#SmallClaimLimitResult').text(SeparadorMiles(StateLimitResults));
                $('#SmallClaimLinkResult').html('<a href="' + StateLinkResults + '" target="_blank" >  <span class="badge badge-info-ls">Click Here</span> </a>');
            }else{
                $('#SmallClaimLimitResult').text('0')
                $('#SmallClaimLinkResult').text('Please choose an option')
            }
        };

        //SmallClaimLoad
        for (var i = 0; i <= SmallClaimsTable.length; i++) {
            $('#select-3-16').append('<option value="' + SmallClaimsTable[i]['Abrev'] + '">' + SmallClaimsTable[i]['State'] + '</option>');
        };
        
    });

    // executes when complete page is fully loaded, including all frames, objects and images
    $(window).on('load',function(){
        localStorage.setItem('user_id',get_session('user_id'))
        localStorage.setItem('user' , get_session('user'))
        localStorage.setItem('email' , get_session('email'))
        get_last_response(get_session('user_id'));
        $.ajax({
            type: "GET",
            url: "https://ip-api.com/json/",
            success: function (response) {
                localStorage.setItem('ip',response.query);
            },
            error(error) {
                localStorage.setItem('ip','Failed to GET IP. Details: ' + error.statusText);
            }
        });
        
    });
    
    //Script to End the Analyzer
    function gotolink() {
        var textButton =  $('#nextBtn7').text();
        var FinalURL = '';
        var mailTo = $('#emailSummary').val();
        if (validateMail(mailTo)){
            print2pdf('email_result',mailTo);
            if (textButton.includes('Go To Mediation')) { FinalURL = 'https://lawsuitanalysis.com/mediation-resource/' }
            if (textButton.includes('Go To Binding Arbitration')) { FinalURL = 'https://lawsuitanalysis.com/arbitration-resource/' }
            if (textButton.includes('Go To Small Claims')) { FinalURL = 'https://lawsuitanalysis.com/small-claims-court-resource/'      }
            if (textButton.includes('Go To Upper Civil Court')) { FinalURL = 'https://lawsuitanalysis.com/upper-civil-courts-resource/' }
            var win = window.open(FinalURL, '_blank');
            win.focus();
            EndQuiz();
            setTimeout(() => {
                window.location.href = "https://lawsuitanalysis.com/"                
            }, 7000);
        } else {
            $('#emailSummary').css('background-color', '#ffdddd');
            $('#emailSummary_error').show();
            setTimeout(() => {
                $('#emailSummary').css('background-color', '#ffffff');
                $('#emailSummary_error').hide();                
            }, 2500);
        }
    }

    //Scrip to Download and Email the Results
    function print2pdf(id,emailTo) {
        var license = get_session('lk');
        var element = document.getElementById(id);
        var sequence6 = '0'
        var sequence7 = '0'

        //Show phase6 condition
        if ($('#ResultStep6').text().includes('91% and above')){sequence6 = '1'};// $("#phase-6-mail-1").show();}
        if ($('#ResultStep6').text().includes('81% to 90%')){sequence6 = '2'};// $("#phase-6-mail-2").show();}
        if ($('#ResultStep6').text().includes('71% to 80%')){sequence6 = '3'};// $("#phase-6-mail-3").show();}
        if ($('#ResultStep6').text().includes('61% to 70%')){sequence6 = '4'};// $("#phase-6-mail-4").show();}
        if ($('#ResultStep6').text().includes('51% to 60%')){sequence6 = '5'};// $("#phase-6-mail-5").show();}
        if ($('#ResultStep6').text().includes('50% and below')){sequence6 = '6'};// $("#phase-6-mail-6").show();}
        //Show phase7 condition
        if ($('#ResultStep7_1').text().includes('Small Claims')){sequence7 = '1'};//$("#phase-7-mail-1").show();}
        if ($('#ResultStep7_1').text().includes('Binding Arbitration')){sequence7 = '2'};//$("#phase-7-mail-2").show();}
        if ($('#ResultStep7_1').text().includes('Upper Civil Court')){sequence7 = '3'};//$("#phase-7-mail-3").show();}
        if ($('#ResultStep7_1').text().includes('Mediation')){
            if($('#ResultStep7_2').text().includes('Small Claims')){sequence7 = '4'};//$("#phase-7-mail-4").show();}
            if($('#ResultStep7_2').text().includes('Binding Arbitration')){sequence7 = '5'};//$("#phase-7-mail-5").show();}
            if($('#ResultStep7_2').text().includes('Upper Civil Court')){sequence7 = '6'};//$("#phase-7-mail-6").show();}
        };        
        $('#' + id).show();
        //html2pdf().from(element).save();
        info_PDf =  $('#' + id).html();
        $.ajax({
            url: 'results/PDFfilesUser/sml.php',
            data: {file: info_PDf,mailTo: emailTo,sequence: sequence6+sequence7},
            dataType: 'json',
            type: 'POST',
            success: function(data){
                console.log('Finish.');
            }
        });

        setTimeout(() => {
            $('#' + id).hide();
            $("#phase-6-mail-1").hide();
            $("#phase-6-mail-2").hide();
            $("#phase-6-mail-3").hide();
            $("#phase-6-mail-4").hide();
            $("#phase-6-mail-5").hide();
            $("#phase-6-mail-6").hide(); 
            $("#phase-7-mail-1").hide();
            $("#phase-7-mail-2").hide();
            $("#phase-7-mail-3").hide();
            $("#phase-7-mail-4").hide();
            $("#phase-7-mail-5").hide();
            $("#phase-7-mail-6").hide();                
        },250);
    };

    //Validate mail before send
    function validateMail(field){
        var resp = false;
        var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if (field.match(validRegex)) {
            if (field.includes('example.com') || field.includes('domain.com') || field.includes('yourdomain.com') ){
                resp = false;
            } else {
                resp = true;
            }
        } else {
            resp = false;
        };
        return resp;
    };

    //Script functions to DB
    function get_answers_form(user_lr){
        var user_lr = user_lr;
        $.ajax({
            type: "GET",
            url: "API/gla.php",
            data: {user_lr: user_lr},
            cache: 'false',
            success: function (response) {
                $validacion = response
                if (response) {
                    print_answers(response)
                };
            }
        });

    }

    function get_last_response(User_Id){
            var usid = User_Id
            $.ajax({
                type: "GET",
                url: "API/glr.php",
                data: {user_id: usid},
                cache: 'false',
                success: function (response) {
                    $validacion = JSON.parse(response);
                    if (response) {
                        var st = 10
                        if ($validacion['st'] === 'Paid') {st = 10};
                        if ($validacion['st'] === 'Completed') {st = 100};
                        localStorage.setItem('user_lr', $validacion['lr'])
                        localStorage.setItem('st', st)
                        get_answers_form($validacion['lr'])
                    } else {
                        localStorage.setItem('user_lr', 0)
                    }
                }
            });
    }

    function get_session(key){
        var result = null
        if (key == 'user_id'){
            result = "<?php if(isset($_SESSION['user_id'])){ echo $_SESSION['user_id'] ; }  ?>";
            if (result !== '') {return  result; }
        }    
        if (key == 'user'){
            result = "<?php if(isset($_SESSION['user'])){ echo $_SESSION['user'] ; }  ?>";
            if (result !== '') {return  result }
        } 
        if (key == 'user_status'){
            result = "<?php if(isset($_SESSION['user_status'])){ echo $_SESSION['user_status'] ; }  ?>";
            if (result !== '') {return  result } 
        }    
        if (key == 'user_rol'){
            result = "<?php if(isset($_SESSION['user_rol'])){ echo $_SESSION['user_rol'] ; }  ?>";
            if (result !== '') {return  result } 
        }
        if (key == 'user_lr'){
            result = "<?php if(isset($_SESSION['user_lr'])){ echo $_SESSION['user_lr'] ; } ?>";
            if (result !== '') {return  result; }
        }
        if (key == 'user_ls'){
            result = "<?php if(isset($_SESSION['user_ls'])){ echo $_SESSION['user_ls'] ; } ?>";
            if (result !== '') {return  result }        
        }
        if (key == 'user_st'){
            result = "<?php if(isset($_SESSION['user_st'])){ echo $_SESSION['user_st'] ; } ?>";
            if (result !== '') {return  result } 
        }
        if (key == 'wck'){
            result = "<?php if(isset($_SESSION['wck'])){ echo $_SESSION['wck'] ; } ?>";
            if (result !== '') {return  result } 
        }
        if (key == 'wcs'){
            result = "<?php if(isset($_SESSION['wcs'])){ echo $_SESSION['wcs'] ; } ?>";
            if (result !== '') {return  result } 
        }
        if (key == 'email'){
            result = "<?php if(isset($_SESSION['email'])){ echo $_SESSION['email'] ; } ?>";
            if (result !== '') {return  result } 
        }
        if (key == 'lk'){
            result = "<?php if(isset($_SESSION['lk'])){ echo $_SESSION['lk'] ; } ?>";
            if (result !== '') {return  result } 
        }
        return result  
    }

    function PutAnswData(Step,DataForm){
        get_last_response(get_session('user_id'))
        var dataPut = [];
        var posit = 0;
        var uslr = get_session('user_lr');
        var usid = get_session('user_id');
        if (uslr == ''){uslr = localStorage.getItem('user_lr') }
        if (Step == 'Step1') {posit = 0}
        if (Step == 'Step2') {posit = 1}
        if (Step == 'Step3') {posit = 2}
        if (Step == 'Step4') {posit = 3}
        if (Step == 'Step5') {posit = 4}
        if (Step == 'Step6') {posit = 5}
        if (Step == 'Step7') {posit = 6}
        for (let index = 1; index <= 57; index++) {
            if (index == 55 && posit == 2){
                //Special Case... State for Small Claims
                dataPut.push({
                    "form_questions_id": index,
                    "answer": DataForm[6]['Values'][index]
                    } 
                )                
            } else if (index == 56) {
                //Special Case... Save agrre to terms
                dataPut.push({
                    "form_questions_id": index,
                    "answer": 'Yes from Ip: ' + localStorage.getItem('ip')
                    }
                )
            } else {
                dataPut.push({
                    "form_questions_id": index,
                    "answer": DataForm[posit]['Values'][index]
                    } 
                )
            }
        };
        $.ajax({
            type: "POST",
            url: "API/upans.php?act=upt&uslr=" + uslr + "&usls=" + (posit+1),
            dataType: 'json',
            data: {data: dataPut }  ,
            cache: 'false',
            success: function (response) {
            }
        });



    }

    function EndQuiz() {
        var uslr = get_session('user_lr');
        if (uslr == ''){uslr = localStorage.getItem('user_lr') };
        $.ajax({
            type: "POST",
            url: "API/upans.php?act=end&uslr=" + uslr,
            dataType: 'json',
            cache: 'false',
            success: function (response) {
            }
        });        
    }

    //Printing Result From DB
    function print_answers(response){
        var obj = JSON.parse(response)
        localStorage.setItem('lr',response)
        //Step1
        if (String(obj[0][1]).length > 0 ){$('#textarea-1-1').text(String(obj[0][1]));}
        if (String(obj[3][1]).length > 0 ){$('#select-1-4').val(String(obj[3][1]));}
        if (String(obj[2][1]).length > 0 ){$('#select-1-3').val(String(obj[2][1]));}
        if (String(obj[1][1]).length > 0 ){$('#select-1-2').val(String(obj[1][1]));}
        if (String(obj[4][1]).length > 0 ){$('#textarea-1-4-1').text(String(obj[4][1]));}
        if (String(obj[5][1]).length > 0 ){$('#select-1-5').val(String(obj[5][1]));}
        if (String(obj[6][1]).length > 0 ){$('#ResultStep1Text1').text(String(obj[6][1]));}
        //Step2
        if (Number.isInteger(parseInt(obj[7][1] ,10))) {
            $('#input-2-7').val(SeparadorMiles(parseInt(obj[7][1] ,10)));
        };
        $('#select-2-8').val(String(obj[8][1]));
        if (Number.isInteger(parseInt(obj[9][1]))){
            $('#input-2-8-1').val(SeparadorMiles(parseInt(obj[9][1])));
        };
        $('#select-2-9').val(String(obj[10][1]));
        if (Number.isInteger(parseInt(obj[11][1]))){
            $('#input-2-9-1').val(SeparadorMiles(obj[11][1]));
        };
        //
        if (String(obj[13][1]).length > 0 ){
            $('#select-2-10').val(String(obj[13][1]));
                if (String(obj[13][1]) == 'yes'){
                    $('#DamageProvision_yes').show();} 
                else {
                    $('#DamageProvision_yes').hide();
                    $('#select-2-10-1').val('default');
                    $('#input-2-10-2').val(0); 
                }
        };
        if (String(obj[14][1]).length > 0 ){$('#select-2-10-1').val(String(obj[14][1]));}
        if (Number.isInteger(parseInt(obj[15][1]))){
            $('#input-2-10-2').val(SeparadorMiles(parseInt(obj[15][1])));
        };
        if (Number.isInteger(parseInt(obj[16][1]))){
            $('#input-2-11').val(SeparadorMiles(parseInt(obj[16][1])));
        };
        if (String(obj[17][1]).length > 0 ){$('#select-2-12').val(String(obj[17][1]));}
        document.querySelector("[name=attorney][value=" + String(obj[18][1]).substring(0,7) + "]").checked = true;
        if (String(obj[19][1]).length > 0 ){$('#input-2-13').val(String(obj[19][1]));}
        if (String(obj[20][1]).length > 0 ){
            $('#select-2-14').val(String(obj[20][1]));
                if (String(obj[20][1]) == 'yes'){
                    $('#moneytime_yes').show();
                }else {
                    $('#moneytime_yes').hide();
                    $('#input-2-14-1').val(0);
                };
        };
        if (Number.isInteger(parseInt(obj[21][1]))){
            $('#input-2-14-1').val(SeparadorMiles(parseInt(obj[21][1])));
        };
        if (Number.isInteger(parseInt(obj[22][1]))){
            $('#input-2-14-2').val(SeparadorMiles(parseInt(obj[22][1])));
        };
        //Step3
        if (String(obj[54][1]).length > 0 ){
            $('#select-3-16').val(String(obj[54][1]));
            $('#select-3-16').trigger('change')
        }
        if (Number.isInteger(parseInt(obj[23][1]))){
            $('#input-3-16-1').val(SeparadorMiles(parseInt(obj[23][1])));
        };
        //
        //
        if (String(obj[26][1]).length > 0 ){$('#select-3-16-2').val(String(obj[26][1]));}
        if (String(obj[27][1]).length > 0 ){$('#select-3-16-3').val(String(obj[27][1]));}
        if (String(obj[28][1]).length > 0 ){$('#select-3-16-4').val(String(obj[28][1]));}
        //
        if (String(obj[30][1]).length > 0 ){$('#select-3-17').val(String(obj[30][1]));}
        //
        if (String(obj[32][1]).length > 0 ){$('#select-3-18').val(String(obj[32][1]));}
        //
        if (String(obj[34][1]).length > 0 ){$('#select-3-19').val(String(obj[34][1]));}
        //
        //
        //Step4
        document.querySelector("[name=adversary][value=" + String(obj[37][1]) + "]").checked = true;

    }

    //Final BD


    //Script For Step Behaviour
    var currentTab = 0; 
    var previousTab = 0;
    showTab(currentTab,'Step1'); 
    showTab(currentTab,'Step2'); 
    showTab(currentTab,'Step3');
    showTab(currentTab,'Step4');
    showTab(currentTab,'Step5');
    showTab(currentTab,'Step6');
    showTab(currentTab,'Step7');

    var DataForm;

    //Step6Table
    var Step6Table = [
        {
            Range: 6,
            Title: "91% and above",
            Text: "Case feasibility is very good. You should proceed forward with your case."
        },
        {
            Range: 5,
            Title: "81% to 90%",
            Text: "Case feasibility is good. You should proceed forward with your case, but make best attempts to resolve your dispute through Last Chance Exercise and Mediation first."
        },
        {
            Range: 4,
            Title: "71% to 80%",
            Text: "Case feasibility is reasonably good. If binding arbitration is your route, move forward but if there is an attorney fees provision, be aware that if you lose, you must pay your opponent’s attorney fees. If binding arbitration is not your route, you should proceed forward with your case only after proposing a Multi-Step <a href='#'>ADR Agreement </a> or <a href='#'>Binding Arbitration Agreement </a> to your adversary. If your adversary will not agree to these alternative dispute resolution methods, pursue your case only after you have repeatedly attempted settlement. And remember: you may be in this category with a winning case and the best chances of collection, but since you have to travel the court maze, your feasibility is reduced.."
        },
        {
            Range: 3,
            Title: "61% to 70%",
            Text: "Your case feasibility is mid-range. If you have a Binding Arbitration Agreement but no attorney fee provision, proceed forward. The dispute will be determined soon and you won’t be paying your opponent’s attorney fees if you lose. But first, attempt settlement and mediation. If you have a binding arbitration provision and an attorney fees provision: 1. If you are at the high end of this category, you should proceed forward with the reminder that if you lose you will pay your adversary’s attorney fees. 2.  If you are at the low end of this category, you should carefully consider your risk of losing and being hit with your opponent’s attorney fee tab. Do everything you can to settle your case. Proceed cautiously if you must thereafter. If there is an attorney fee provision and no binding arbitration provision, you should lean more in favor of pursuing your dispute only by settlement. It may not be worthwhile to take this case into the expensive court forum, especially considering your exposure with an attorney fee provision. Proceed cautiously if you must. If there is no attorney fee provision or binding arbitration provision, proceed only if you are at the high end of this category. Otherwise, it may not be worthwhile to take this case into the expensive court forum. Proceed cautiously if you must..."
        },
        {
            Range: 2,
            Title: "51% to 60%",
            Text: "Make every attempt to settle your dispute at the suggested settlement figure. Always remember: if there’s an attorney fee provision and you lose, you’ll have to pay your adversary’s attorney fees. If you have slim chances of collecting on a judgment, perhaps it doesn’t make sense to proceed. If your case value is low and you have to travel the demanding court system, you may very well not want to go the long haul. If you are on the low side of this range, your feasibility indicates you should not pursue your case. Otherwise, proceed very cautiously if you must..."
        },
        {
            Range: 1,
            Title: "50% and below",
            Text: "You are discouraged from pursuing your case to judgment, but don’t let your opponent know that or you’ll never reach settlement. Encourage settlement again and again. After that, one mediation session is warranted. In view of mediation’s 85% success rate and your realistic attitude (given your low feasibility rating), the chances of success at mediated settlement are very high..."
        },
        {
            Range: 0,
            Title: "Default Title",
            Text: "Default Text"
        }
    ]

    var RouteCache = [
        {id: "Step1",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step2",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step3",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step4",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step5",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step6",IsCurrent: 0,PrevTab: 0,LastTab: 0},
        {id: "Step7",IsCurrent: 0,PrevTab: 0,LastTab: 0}
    ];

    var Result1, Result2, Result3, StepResultText1, StepResultText2, StepResultText3 ;

    //Check for current Step. Advance to the final step (Edit case)
    function CheckStep(Step,FirstTime){
        if ( Step == 'Step1') {
            GetStepsData(); 
            //if (FirstTime){ firstTimeStep();};
            StepResultText1 = DataForm[0]['Values'][7];
            var StepResultText2_Details = '0 points out of 350'
            if (StepResultText1.includes("Feasible")) {
                StepResultText2_Details = '350 points out of 350'
            }
            StepResultText2 = 'For your Legal Evaluation you have received ' + StepResultText2_Details;
            StepResultText3 = 'This result is part of an algorithm that will be factored into your Comprehensive Case Analysis Phase 5';
        };
        if ( Step == 'Step2') {
            GetStepsData();
            //if (FirstTime){ firstTimeStep();};
            Result1 = parseInt($('#input-2-7').val().replaceAll(",","")  ,10)
            if (!isNaN(DataForm[1]['Values'][17])) {
                var Result2 = DataForm[1]['Values'][17]
            }
            if (!isNaN(DataForm[1]['Values'][24])) {
                var Result3 = DataForm[1]['Values'][24]
            }        
            StepResultText1 = '$ ' + SeparadorMiles(Result1) + ' is your GROSS DAMAGES.';
            StepResultText2 = '$ ' + SeparadorMiles(Result2) + ' is your RECOVERABLE DAMAGES,';
            StepResultText3 = '$ ' + SeparadorMiles(Result3) + ' is your NET DAMAGES,';
        };
        if ( Step == 'Step3') {
            GetStepsData();
            //if (FirstTime){ firstTimeStep();};
            StepResultText1 = 'You have received ' + DataForm[2]['Values'][37] + ' points ';
            StepResultText2 = 'out of 190';
            StepResultText3 = 'This result is part of an algorithm that will be factored into your Comprehensive Case Analysis in Phase 5';
        };
        if ( Step == 'Step4') {
            GetStepsData();
            //if (FirstTime){firstTimeStep();};
            StepResultText1 = 'You have received ' + DataForm[3]['Values'][40] + ' points ';
            StepResultText2 = 'out of 150';
            StepResultText3 = 'This result is part of an algorithm that will be factored into your Comprehensive Case Analysis in Phase 5.';
        };
        if ( Step == 'Step5') {
            GetStepsData();
            //LoadTable Step5
            $('#tbl-row-1').text(DataForm[4]['Values'][41]);
            $('#tbl-row-2').text('');
            $('#tbl-row-3').text(DataForm[4]['Values'][43]);
            $('#tbl-row-4').text(DataForm[4]['Values'][44]);
            $('#tbl-row-5').text(DataForm[4]['Values'][45]);
            var num = DataForm[4]['Values'][50]
            $('#tbl-row-6').text((Math.round((num + Number.EPSILON) * 100)) + ' %' ); //Blank Space
            $('#tbl-row-7').text('$' + SeparadorMiles(DataForm[4]['Values'][47]));
            $('#tbl-row-8').text('$' + SeparadorMiles(DataForm[4]['Values'][48]));
            $('#tbl-row-9').text('$' + SeparadorMiles(DataForm[4]['Values'][42]) );
            $('#tbl-row-10').text( '$' + SeparadorMiles(Math.round(DataForm[4]['Values'][49])) );
            //Results for email
            $('#tbl-row-1-result').text($('#tbl-row-1').text() + ' points');
            $('#tbl-row-3-result').text($('#tbl-row-3').text() + ' points');
            $('#tbl-row-4-result').text($('#tbl-row-4').text() + ' points');
            $('#tbl-row-5-result').text($('#tbl-row-5').text() + ' points');
            $('#tbl-row-6-result').text($('#tbl-row-6').text()); //Blank Space
            $('#tbl-row-7-result').text($('#tbl-row-7').text());
            $('#tbl-row-8-result').text($('#tbl-row-8').text());
            $('#tbl-row-9-result').text($('#tbl-row-9').text());
            $('#tbl-row-10-result').text($('#tbl-row-10').text());
        };
        if ( Step == 'Step6') {
            GetStepsData();
            //Refresh Results
            $('#ResultStep6').html('<p style = "font-weight: bold;" > '+  DataForm[5]['Values'][51] + ' out of 100%')
            $('#ResultStep7_1').text( DataForm[6]['Values'][57] );
            $('#ResultStep7_2').text( DataForm[6]['Values'][53] );
            //Results for email
            $('#ResultStep7_1-result').text( $('#ResultStep7_1').text() );
            $('#ResultStep7_2-result').text( $('#ResultStep7_2').text() );
        };
        var resp = Step.substr(Step.length - 1, Step.length)
        resp = parseInt(resp)        
        if (FirstTime){
            PutAnswData(Step,DataForm);
        } else if (FirstTime == false && resp >= 4 ) {
            PutAnswData(Step,DataForm);
        }
    }

    var Value_6,
        Value_8, Value_9, Value_8_plus_9, 
        Value_11, selectedVal_Step2, selectedVal_Step2_text, Value_13_2, Value_14_2, Value_15, 
        Value_16_1, Value_16_Value, Value_17_Value, Value_18_Value, Value_19_Value, 
        selectedVal_Step4 , Value_21_Value , Value_22_Value,
        Value_24, Value_25,Value_26,Value_28,Value_29,Value_31,
        Value_33_Chart_Title,Value_33_Chart_Text,
        Value_34_Text, Value_38_Text
    ;

    function GetStepsData() {
        //** Step 1 Calculations */
            if($('#select-1-2').val() == 'yes' || 
                ($('#select-1-2').val() == 'no' && $('#select-1-3').val() == 'no') || 
                ($('#select-1-2').val() == 'no' && $('#select-1-3').val() == 'no' && $('#select-1-4').val() == 'no') ||
                ($('#select-1-2').val() == 'no' && $('#select-1-3').val() == 'yes' && $('#select-1-4').val() == 'yes' && $('#select-1-5').val() == 'no' ) ){
                Value_6 = 'Your Case is Weak';
            }else {
                Value_6 = 'Your Case is Feasible';
            };
        //** Step 1 End */

        //** Step2 Calculations**/
            if (isNaN(parseInt($('#input-2-8-1').val().replaceAll(",",""),10))){
                Value_8 = 0
            } else {
                Value_8 = parseInt($('#input-2-8-1').val().replaceAll(",",""),10)
            }

            if (isNaN(parseInt($('#input-2-9-1').val().replaceAll(",",""),10))){
                Value_9 = 0
            } else {
                Value_9 = parseInt($('#input-2-9-1').val().replaceAll(",",""),10)
            }        
            var Value_8_plus_9 = Value_8 +  Value_9
            if ($('#select-2-10').val() == 'yes') {
                //damageLimits
                if ($('#select-2-10-1').val() == 'Liquidated-Damages') {
                    Value_11 = parseInt($('#input-2-10-2').val().replaceAll(",","")  ,10);
                };
                if ($('#select-2-10-1').val() == 'Damage-Limit') {
                    var initialdamage = parseInt($('#input-2-7').val().replaceAll(",","")  ,10) - Value_8_plus_9
                    var diff =  parseInt($('#input-2-10-2').val().replaceAll(",","")  ,10) - initialdamage
                    if (diff >= 0){
                        Value_11 = initialdamage
                    } else {
                        Value_11 = parseInt($('#input-2-10-2').val().replaceAll(",","")  ,10);
                    }
                };
            } else {
                Value_11 = parseInt($('#input-2-7').val().replaceAll(",","")  ,10) - Value_8_plus_9
            }

            var selected = $("input[type='radio'][name='attorney']:checked");
            if (selected.length > 0) {
                selectedVal_Step2 = selected.val();
                selectedVal_Step2_text = selected.next('label').text();
            }
            if ($('#select-2-12').val() == 'yes'){
                if (selectedVal_Step2 == 'value-1'){Value_13_2 = Value_11 };
                if (selectedVal_Step2 == 'value-2'){Value_13_2 = Value_11 * 0.9};
                if (selectedVal_Step2 == 'value-3'){Value_13_2 = Value_11 * 0.85};
                if (selectedVal_Step2 == 'value-4'){Value_13_2 = Value_11 * 0.65};
                if (selectedVal_Step2 == 'value-5'){Value_13_2 = Value_11 * 0.65};
            }else {
                Value_13_2 = Value_11 * 0.8 ;
            };
            Value_13_2 = Math.round(Value_13_2)
            if (isNaN(parseInt($('#input-2-14-1').val().replaceAll(",","")  ,10))) {
                Value_14_2 = 0
            }else {
                Value_14_2 = parseInt($('#input-2-14-1').val().replaceAll(",","")  ,10) / 2
            }
            Value_14_2 = Math.round(Value_14_2)
            Value_15 = Value_13_2 - Value_14_2
        //** Step 2 End **/

        //** Step 3 Calculations*/
            var diff = $('#SmallClaimLimitResult').text().replaceAll(",","") - Value_11
            var Step3_16_Val_1 = 0 , Step3_16_Val_2 = 0;
            if (diff < 0) {
                Value_16_1 = 'Are Not Within'
                if ($('#select-3-16-2').val() == 'yes') {
                    if ($('#select-3-16-3').val() == 'yes' &&  $('#select-3-16-4').val() == 'no') {
                        Step3_16_Val_1 = 85
                    }
                    if ($('#select-3-16-3').val() == 'yes' &&  $('#select-3-16-4').val() == 'yes') {
                        Step3_16_Val_1 = 125
                    }
                    if ($('#select-3-16-3').val() == 'no'){
                        Step3_16_Val_1 = 125
                    }                
                }
            }else{
                Value_16_1 = 'Are Within'
                if ($('#select-3-16-3').val() == 'no') {
                    Step3_16_Val_2 = 125
                } else if ($('#select-3-16-4').val() == 'yes') {
                    Step3_16_Val_2 = 125
                } else {
                    Step3_16_Val_1 = 85
                }
            }
            Value_16_Value =  Step3_16_Val_1 + Step3_16_Val_2
            Value_17_Value = 0
            if ($('#select-3-17').val() == 'yes') {
                if ($('#select-2-12').val() == 'yes') {
                    Value_17_Value = 50
                } else {
                    Value_17_Value = 25
                }
            } else {
                if ($('#select-2-12').val() == 'yes') {
                    Value_17_Value = 0
                } else {
                    Value_17_Value = 25
                }       
            }
            Value_18_Value = 0
            if ($('#select-3-16-3').val() == 'no') {
                if ( $('#select-3-18').val() == 'yes' ) { Value_18_Value = 85 }
            }
            Value_19_Value = 0
            if (  $('#select-3-19').val() == 'yes' ) { Value_19_Value = 15}



        //** Step 3 End */
        
        //** Step 4 Calculations*/
            var adversary_value = [
                {name: 'value-1',value: 150,description: 'a corporation'},
                {name: 'value-2',value: 150,description: 'represented by an insurance company'},
                {name: 'value-3',value: 140,description: 'an operating business other than a corporation, like an LLC, or Sole Proprietorship'},
                {name: 'value-4',value: 130,description: 'own real property'},
                {name: 'value-5',value: 120,description: 'have assets and is salaried'},
                {name: 'value-6',value: 110,description: 'have assets and works for self as sole proprietor'},
                {name: 'value-7',value: 90,description: 'have assets and none of above apply'},
                {name: 'value-8',value: 75,description: 'none of above or below apply'},
                {name: 'value-9',value: 50,description: 'have judgments against them'},
                {name: 'value-10',value: 40,description: 'have no assets or means of support'},
                {name: 'value-11',value: 20,description: 'have bankruptcy proceedings pending'}
            ]

            var selected = $("input[type='radio'][name='adversary']:checked");
            if (selected.length > 0) {
                selectedVal_Step4 = selected.val();
                Value_21_Value = VLookUp(adversary_value, selectedVal_Step4,'name','description')
                Value_22_Value = VLookUp(adversary_value, selectedVal_Step4,'name','value')
            }

        

        //** Step 4 End*/

        //** Step 5 Calculations */
            if (diff >= 0 || $('#select-3-16-2').val() == 'yes' ){
                Value_24 = 50
            } else if (Value_15 >= 0 && Value_15 <= 10000 ){ Value_24 = 5 
            } else if (Value_15 >= 10001 && Value_15 <= 15000 ){ Value_24 = 10 
            } else if (Value_15 >= 15001 && Value_15 <= 20000 ){ Value_24 = 15 
            } else if (Value_15 >= 20001 && Value_15 <= 25000 ){ Value_24 = 20 
            } else if (Value_15 >= 25001 && Value_15 <= 30000 ){ Value_24 = 25 
            } else if (Value_15 >= 30001 && Value_15 <= 35000 ){ Value_24 = 30 
            } else if (Value_15 >= 35000 && Value_15 <= 40000 ){ Value_24 = 35 
            } else if (Value_15 >= 40001 && Value_15 <= 45000 ){ Value_24 = 40 
            } else if (Value_15 >= 45001 && Value_15 <= 50000 ){ Value_24 = 45 
            } else if (Value_15 >= 50001 ){ Value_24 = 50 
            } else {Value_15 = 0}

            if (Value_6 == 'Your Case is Weak') {
                Value_25 = 0
            } else { Value_25 = 350 }

            Value_26 = Value_16_Value + Value_17_Value + Value_18_Value + Value_19_Value
            Value_28 = Value_24 + Value_25 + Value_16_Value + Value_17_Value + Value_18_Value + Value_19_Value + Value_22_Value
            Value_29 = (Value_24 + Value_25 + Value_16_Value + Value_17_Value + Value_18_Value + Value_19_Value + Value_22_Value) / 700
            if (Value_29 > 1){Value_29 = 1}
            if (Value_29 < 0.92){
                Value_31 = Value_11 * Value_29
            } else {
                Value_31 = Value_11 * 0.92
            }
            

        //** Step 5 End */

        //** Step 6 Calculations */
            var range = 0;
            var temp = Math.round(Value_29,2)
            if (Value_29 <= 0.5 ) {range = 1}
            if (Value_29 > 0.5 && Value_29 <= 0.6 ) {range = 2}
            if (Value_29 > 0.6 && Value_29 <= 0.7 ) {range = 3}
            if (Value_29 > 0.7 && Value_29 <= 0.8 ) {range = 4}
            if (Value_29 > 0.8 && Value_29 <= 0.9 ) {range = 5}
            if (Value_29 > 0.9) {range = 6}
            Value_33_Chart_Title = VLookUp( Step6Table,range,'Range','Title' )
            Value_33_Chart_Text = VLookUp( Step6Table,range,'Range','Text' )
        //** Step 6 End */

        //** Step 7 Calculations */
            var TempVal1,TempVal2,TempVal3,TempVal4 = 'N';
            if ( $('#select-3-18').val() == 'yes') {
                TempVal1 = 'Y'
            } else {
                TempVal1 = 'N'
            };
            if (diff >= 0 || $('#select-3-16-2').val() == 'yes') {
                TempVal2 = 'Y'
            } else {
                TempVal2 = 'N'
            };
            if ($('#select-3-16-3').val() == 'yes') {
                TempVal3 = 'Y'
            } else {
                TempVal3 = 'N'
            };
            if ($('#select-3-16-4').val() == 'yes') {
                TempVal4 = 'Y'
            } else {
                TempVal4 = 'N'
            };
            var concat =  TempVal1 + TempVal2 + TempVal3 + TempVal4 ;
            if (concat == 'YNNN') {Value_34_Text = 'Binding Arbitration' ,Value_38_Text = 'Binding Arbitration' };
            if (concat == 'NYNN') {Value_34_Text = 'Small Claims',Value_38_Text = 'Small Claims'};
            if (concat == 'NNNN') {Value_34_Text = 'Upper Civil Court',Value_38_Text = 'Upper Civil Court' };
            if (concat == 'NYYN') {Value_34_Text = 'Binding Arbitration',Value_38_Text = 'Binding Arbitration'};
            if (concat == 'NYYY') {Value_34_Text = 'Small Claims',Value_38_Text = 'Small Claims' };

            if ($('#select-3-19').val() == 'yes') {
                Value_38_Text = 'Mediation';
                $('#MediationYes').show();
                $('#nextBtn7').text('Go To Mediation');
                //Result for email
                $('#MediationYes-result').show();
                $('#nextBtn7-result').text('Go To Mediation');
                $('#nextBtn7-result').attr("href", "https://lawsuitanalysis.com/mediation-resource/");
            } else {
                $('#MediationYes').hide();
                $('#nextBtn7').text('Go To ' + Value_38_Text);
                //result for email
                $('#MediationYes-result').hide();
                $('#nextBtn7-result').text('Go To ' + Value_38_Text);
                var textButton = $('#nextBtn7-result').text();
                var FinalURL = '';
                if (textButton.includes('Go To Binding Arbitration')) { FinalURL = 'https://lawsuitanalysis.com/arbitration-resource/' }
                if (textButton.includes('Go To Small Claims')) { FinalURL = 'https://lawsuitanalysis.com/small-claims-resource/'      }
                if (textButton.includes('Go To Upper Civil Court')) { FinalURL = 'https://lawsuitanalysis.com/upper-civil-courts-resource/' }
                $('#nextBtn7-result').attr("href", FinalURL);
            }

        //** Step 7 End */
        DataForm = [
            {id: "Step1",
                Values: {
                    //Step1
                    1: $('#textarea-1-1').val(),
                    2: $('#select-1-2').val(),
                    3: $('#select-1-3').val(),
                    4: $('#select-1-4').val(),
                    5: $('#textarea-1-4-1').val(),
                    6: $('#select-1-5').val(),
                    7: Value_6
                }
            },
            {id: "Step2",
                Values: {
                    //Step2
                    8: parseInt($('#input-2-7').val().replaceAll(",","")  ,10),
                    9: $('#select-2-8').val(),
                    10: parseInt($('#input-2-8-1').val().replaceAll(",","")  ,10),
                    11: $('#select-2-9').val(),
                    12: parseInt($('#input-2-9-1').val().replaceAll(",","")  ,10),
                    13: Value_8_plus_9,
                    14: $('#select-2-10').val(),
                    15: $('#select-2-10-1').val(),
                    16: parseInt($('#input-2-10-2').val().replaceAll(",","")  ,10),
                    17: Value_11,
                    18: $('#select-2-12').val(),
                    19: selectedVal_Step2 + ' - ' + selectedVal_Step2_text,
                    20: Value_13_2,
                    21: $('#select-2-14').val(),
                    22: parseInt($('#input-2-14-1').val().replaceAll(",","")  ,10),
                    23: Value_14_2,
                    24: Value_15
                }           
            },  
            {id: "Step3",
                Values: {
                    //Step3
                    25: $('#SmallClaimLimitResult').text().replaceAll(",",""),
                    26: Value_16_1,
                    27: $('#select-3-16-2').val(),
                    28: $('#select-3-16-3').val(),
                    29: $('#select-3-16-4').val(),
                    30: Value_16_Value,
                    31: $('#select-3-17').val(),
                    32: Value_17_Value,
                    33: $('#select-3-18').val(),
                    34: Value_18_Value,
                    35: $('#select-3-19').val(),
                    36: Value_19_Value,
                    37: Value_16_Value + Value_17_Value + Value_18_Value + Value_19_Value 
                }

            },
            {id: "Step4",
                Values: {
                    //Step4
                    38: selectedVal_Step4,
                    39: Value_21_Value,
                    40: Value_22_Value
                }

            },
            {id: "Step5",
                Values: {
                    41: Value_25,
                    42: Value_15,
                    43: Value_24,
                    44: Value_26,
                    45: Value_22_Value,
                    46: '',
                    47: parseInt($('#input-2-7').val().replaceAll(",","")  ,10),
                    48: Value_11,
                    49: Value_31,
                    50: Value_29
                }
            },
            {id: "Step6",
                Values: {
                    51: Value_33_Chart_Title,
                    52: Value_33_Chart_Text
                }
            },
            {id: "Step7",
                Values: {
                    53: Value_34_Text,
                    54: '',
                    55: $('#select-3-16').val(), //Special Case... State for Small Claims
                    56: '',
                    57: Value_38_Text
                }
            }
        ]
    }

    function firstTimeStep(){
        //GetStepsData();
        //var LastStep = $("#wizard").steps('getCurrentIndex')
        var tablist = document.querySelector('[role="tablist"]');
        var tabs = tablist.getElementsByTagName('li');
        var ActiveTab = 0;
        var lastdone = 0;
        for (let index = 0; index < tabs.length; index++) {
            if (tabs[index].className.includes('current')) { ActiveTab =  index  } ;
            if (tabs[index].className.includes('done')) {lastdone = index } ; 
        }
        if (lastdone != 0 && ActiveTab < lastdone  ){  
            //Fast Advance only up to Step 5
            if (lastdone > 4 && ActiveTab < 4 ) {
                lastdone = 4
            }
            //Advance to last Step
            for (let index = 0; index < (lastdone - ActiveTab); index++) {
                $("#wizard").steps('next');
                showTab(RouteCache[ActiveTab + index].LastTab,RouteCache[ActiveTab + index].id);
            }
            RouteCache[lastdone].IsCurrent = 1
            showTab(RouteCache[lastdone].LastTab,RouteCache[lastdone].id);
            //recalculate
            for (let index = 0; index <= lastdone; index++) {
                var localstep = 'Step' + (index + 1)
                CheckStep(localstep,false)
            }
        } else {
            RouteCache[ActiveTab + 1].IsCurrent = 1
            showTab(0,RouteCache[ActiveTab + 1].id);
            $("#wizard").steps('next'); //Show the Next Step.
            
    
        }
    }

    ///*****SCRIPTS TO GET SEPARATE QUESTIONS */

    function showTab(n,step) {
        // This function will display the specified tab of the form...
        var stp = document.getElementById(step)
        var x = stp.getElementsByClassName("tab");
        //Progress BAR
        var resp = step.substr(step.length - 1, step.length)
        resp = parseInt(resp)
        var NumTabs,ActualTab = n;
        if (x.length == 0 ){
            NumTabs = 1
            ActualTab = 0
        } else  {    
            NumTabs = x.length
            if (ActualTab > x.length){ActualTab = n - 1}
        }
        var progr = Math.round( ( (((ActualTab+1) / NumTabs) * 0.143 ) + (0.143 * (resp - 1)) ) * 100 )
        
        //if (progr == 100 ){progr = 95}
        $('#progress_' + step).css('width', progr.toString() + '%');
        $('#progress_text_' + step).text(progr.toString() + '% complete');
        $('#progress_' + step).addClass('bg-success');
        if (n >= x.length || x.length == 0) {
            y = stp.getElementsByClassName("results");
            y[0].style.display = "block"
        } else {
            x[n].style.display = "block";
        }
        //... and fix the Previous/Next buttons:
        if (n == 0) {
            //Hide others TAB
            for (let index = 0; index < x.length; index++) {
                if (index > 0 ){ x[index].style.display = 'none'  };
            }
            $('#prevBtn' + resp).hide()
        } else {
            if (step == 'Step1'){$('#prevBtn1').show()};
            if (step == 'Step2'){$('#prevBtn2').show()};
            if (step == 'Step3'){$('#prevBtn3').show()};
            if (step == 'Step4'){$('#prevBtn4').show()};
            if (step == 'Step5'){$('#prevBtn5').show()};
            if (step == 'Step6'){$('#prevBtn6').show()};
        }


        /*
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = "Evaluate";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
        }
        */
        //... and run a function that will display the correct step indicator:
        //fixStepIndicator(n)
    }

    function getCurrentPositions(stepID){
        //Get Current Step
        var StepList = document.querySelector('[role="tablist"]');
        var Steps = StepList.getElementsByTagName('li');
        var lastStepdone = 0;
        var ActiveTab  = -1;
        var step = 'Step' + ( stepID + 1 )
        var stp = document.getElementById(step);
        var x = stp.getElementsByClassName("tab");
        for (let i = 0; i < x.length; i++) {
            if (x[i].style.display === 'block'){
                ActiveTab = i
                RouteCache[stepID].LastTab = i
            }
        };
        RouteCache.forEach(element => {
            element.IsCurrent = 0
        });
        RouteCache[stepID].IsCurrent = 1
    }

    function nextPrev(n,stepID){
        getCurrentPositions(stepID);
        var step,stp,x;
        RouteCache.forEach(element => {
            if( element.IsCurrent == 1 ){
                step = element.id.toString();
                currentTab = element.LastTab;
                previousTab = element.PrevTab;
            }
        });
        stp = document.getElementById(step);
        x = stp.getElementsByClassName("tab");    
        
        if (n == -1){
            if (RouteCache[stepID].LastTab == 0 && RouteCache[stepID].PrevTab == 0){
                if (x.length !== 0) {
                /*                
                    y = stp.getElementsByClassName("results");
                    y[0].style.display = "none"
                } else {
                    */
                   // x[currentTab].style.display = "none";
                }
                RouteCache[stepID].IsCurrent = 0;
                $("#wizard").steps('previous');
                const prevStepID = stepID - 1
                const prevStep = "Step" + (prevStepID + 1)
                RouteCache[prevStepID].IsCurrent = 1;
                previousTab = RouteCache[prevStepID].PrevTab;
                currentTab = RouteCache[prevStepID].LastTab + 1;
                RouteCache[prevStepID].LastTab = currentTab;
                if (stepID <= 4){
                    stp2 = document.getElementById(prevStep);
                    y = stp2.getElementsByClassName("results");
                    y[0].style.display = "none";  
                    previousTab = 0;
                    currentTab = 0;
                    RouteCache[prevStepID].PrevTab = 0
                    RouteCache[prevStepID].LastTab = currentTab;
                    showTab(RouteCache[prevStepID].LastTab,prevStep);
                } else {
                    showTab(RouteCache[prevStepID].LastTab,prevStep);
                }
                return            
            } else {
                if (RouteCache[stepID].LastTab >= x.length ){
                    y = stp.getElementsByClassName("results");
                    y[0].style.display = "none"

                    if (x.length == 0){
                        y[0].style.display = "block"
                        $("#wizard").steps('previous');
                        const prevStepID = stepID - 1
                        const prevStep = "Step" + (prevStepID + 1)
                        RouteCache[prevStepID].IsCurrent = 1;
                        previousTab = RouteCache[prevStepID].PrevTab;
                        currentTab = RouteCache[prevStepID].LastTab + 1;
                        RouteCache[prevStepID].LastTab = currentTab;
                        showTab(RouteCache[prevStepID].LastTab,prevStep)
                        return
                    }

                } else {
                    x[currentTab].style.display = "none"
                    if (x[currentTab].id == 'Q14' &&  $('#select-2-12').val() == 'yes'){
                        $('#attorney_yes').show();
                        $('#attorney_yes').css("display","block");
                        $('#attorney_no').hide();
                    } else {            
                        $('#attorney_yes').hide();
                        $('#attorney_no').show();
                        $('#attorney_no').css("display","block");
                    }
                    if (x[currentTab].id == 'Q19' && ($('#select-3-16-2').val() == 'yes' || $('#select-3-16-3').val() == 'no')){
                        previousTab = previousTab - 1
                        RouteCache[stepID].PrevTab = RouteCache[stepID].PrevTab - 1
                        if ($('#select-3-16-2').val() == 'no'){
                            previousTab = previousTab + 1
                            RouteCache[stepID].PrevTab = RouteCache[stepID].PrevTab + 1
                        }
                    }
                }

                if (previousTab < (currentTab -1) ) {
                    currentTab = previousTab
                } else {
                    previousTab = currentTab
                    currentTab = currentTab + n;
                }
                
                
                showTab(RouteCache[stepID].PrevTab,step);
                currentTab = RouteCache[stepID].PrevTab
                previousTab = currentTab + n;
                if (previousTab < 0){previousTab = 0};
                RouteCache[stepID].LastTab = currentTab;
                RouteCache[stepID].PrevTab = previousTab;
                return
            }
        };
        var IsValid = validateForm(step);
        // Exit the function if any field in the current tab is invalid:
        if (n == 1 && !IsValid) return false;

        //Check Especial Cases for Steps
        if (step == 'Step1' && n == 1 && currentTab < x.length ){
            if ((x[currentTab].id == 'Q2' &&  $('#select-1-2').val() == 'yes') || x[currentTab].id == 'Q3'){
                var PaymentStatus = localStorage.getItem('st');
                if (PaymentStatus !== "10") {
                    GetStepsData();
                    PutAnswData('Step1',DataForm);                    
                    $('#btnPaymentInstructions').css('display', 'grid');
                    $('#wizard').detach();
                    var url = "https://lawsuitanalysis.com/product/lawsuit_analyzer/";
                    /*
                    $('#PaymentProcess').show();
                    setTimeout(() => {
                        $('#PaymentProcess').hide();
                        window.open(url, '_blank').focus();
                    }, 5000);
                    */
                };
            }
            if ( (x[currentTab].id == 'Q2' &&  $('#select-1-2').val() == 'yes')
                || (x[currentTab].id == 'Q3' &&  $('#select-1-3').val() == 'no') 
                || (x[currentTab].id == 'Q4' &&  $('#select-1-4').val() == 'no') ){
                x[currentTab].style.display = "none";
                previousTab = currentTab;
                currentTab = x.length; //Skip to End
            }else{
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n;
            }
        }
        if (step == 'Step2' && n == 1 && currentTab < x.length ){   
            GetStepsData();
            $('#input-2-11').val(SeparadorMiles(Value_11));     
            if (x[currentTab].id == 'Q10' &&  $('#select-2-10').val() == 'no') {
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = 5; //Skip to Q12
            }else if (x[currentTab].id == 'Q12' &&  $('#select-2-12').val() == 'yes'){
                $('#attorney_no').hide();
                $('#attorney_yes').show();
                $('#attorney_yes').css("display","block");
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n; 
            } else {
                $('#attorney_yes').hide();
                $('#attorney_no').show();
                $('#attorney_no').css("display","block");
                $('#input-2-13').val(SeparadorMiles(Math.round(Value_11 * 0.8)));
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n;             
            }
        }
        if (step == 'Step3' && n == 1 && currentTab < x.length ){
            var diff = $('#SmallClaimLimitResult').text().replaceAll(",","") - Value_11
            if (x[currentTab].id == 'Q16-Prev') {
                $('#input-3-16-1').val(SeparadorMiles(Value_11));
                if (diff < 0) {
                    $('#WhitinEvaluation').html('<h6>Your Recoverable Damages <span class="badge badge-primary-ls">Are Not Within</span> your state&#39s small claim limit.</h6>');
                    $('#BellowLimit').hide();
                    $('#AboveLimit').show();
                }else{
                    $('#WhitinEvaluation').html('<h6>Your Recoverable Damages <span class="badge badge-primary-ls">Are Within</span> your state&#39s small claim limit.</h6>');                
                    $('#AboveLimit').hide();
                    $('#BellowLimit').show();
                }
                if ($('#select-3-16-2').val() == 'yes' ){
                    $('#BellowLimit').show();        
                }else {
                    if (diff < 0 ) { $('#BellowLimit').hide() };
                    $('#select-3-16-3').val('no');
                    $('#select-3-16-4').val('no');
                };
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n;
                $('#ok_step3').hide();                  
            } else if (x[currentTab].id == 'Q17' &&  ($('#select-3-16-2').val() == 'yes' || diff > 0 || $('#select-3-16-3').val() == 'no')) {
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n + 1;
                if ($('#select-3-16-2').val() == 'no') {
                    currentTab = currentTab - 1
                } else {
                    $('#select-3-18').val('no');
                }           
            }else {
                x[currentTab].style.display = "none";
                previousTab = currentTab
                currentTab = currentTab + n;  
            } 
        }
        if (step == 'Step4' && n == 1 && currentTab < x.length ) {
            x[currentTab].style.display = "none";
            previousTab = currentTab
            currentTab = currentTab + n;         
        }
        if (step == 'Step5' && n == 1 && currentTab < x.length ) {
            //x[currentTab].style.display = "none";
            previousTab = currentTab
            currentTab = currentTab + n;         
        }
        if (step == 'Step6' && n == 1 && currentTab < x.length ) {
            //x[currentTab].style.display = "none";
            previousTab = currentTab
            currentTab = currentTab + n;         
        }
        RouteCache[stepID].PrevTab = previousTab;
        RouteCache[stepID].LastTab = currentTab;
        // if you have reached the end of the form STEP... 
        //allow to show final Results
        if (currentTab == x.length) {
            CheckStep(step,false);
            if (step == 'Step1') {
                //StepResults StepView
                $('#ResultStep1Text1').text(StepResultText1);
                $('#ResultStep1Text2').text(StepResultText2);
                $('#ResultStep1Text3').text(StepResultText3);
                $('#ResultStep1').css("display","block"); //Trick for Help Page
                $('#ResultStep1').show();
            }
            if (step == 'Step2') {
                //StepResults StepView
                $('#ResultStep2Text1').text(StepResultText1);
                $('#ResultStep2Text2').text(StepResultText2);
                $('#ResultStep2Text3').text(StepResultText3);
                $('#ResultStep2').css("display","block"); //Trick for Help Page
                $('#ResultStep2').show();
            }
            if (step == 'Step3') {
                //StepResults StepView
                $('#ResultStep3Text1').text(StepResultText1);
                $('#ResultStep3Text2').text(StepResultText2);
                $('#ResultStep3Text3').text(StepResultText3);
                $('#ResultStep3').css("display","block"); //Trick for Help Page
                $('#ResultStep3').show();
            }
            if (step == 'Step4') {
                //StepResults StepView
                $('#ResultStep4Text1').text(StepResultText1);
                $('#ResultStep4Text2').text(StepResultText2);
                $('#ResultStep4Text3').text(StepResultText3);
                $('#ResultStep4').css("display","block"); //Trick for Help Page
                $('#ResultStep4').show();
                //LoadTable Step5
                $('#ResultStep5').css("display","block"); //Trick for Help Page
                $('#tbl-row-1').text(DataForm[4]['Values'][41]);
                $('#tbl-row-2').text('');
                $('#tbl-row-3').text(DataForm[4]['Values'][43]);
                $('#tbl-row-4').text(DataForm[4]['Values'][44]);
                $('#tbl-row-5').text(DataForm[4]['Values'][45]);
                var num = DataForm[4]['Values'][50]
                $('#tbl-row-6').text((Math.round((num + Number.EPSILON) * 100)) + ' %' ); //Blank Space
                $('#tbl-row-7').text('$' + SeparadorMiles(DataForm[4]['Values'][47]));
                $('#tbl-row-8').text('$' + SeparadorMiles(DataForm[4]['Values'][48]));
                $('#tbl-row-9').text('$' + SeparadorMiles(DataForm[4]['Values'][42]) );
                $('#tbl-row-10').text( '$' + SeparadorMiles(Math.round(DataForm[4]['Values'][49]))  );         
                $('#prevBtn4').show();
                //Results for email
                $('#tbl-row-1-result').text($('#tbl-row-1').text());
                $('#tbl-row-3-result').text($('#tbl-row-3').text());
                $('#tbl-row-4-result').text($('#tbl-row-4').text());
                $('#tbl-row-5-result').text($('#tbl-row-5').text());
                $('#tbl-row-6-result').text($('#tbl-row-6').text()); //Blank Space
                $('#tbl-row-7-result').text($('#tbl-row-7').text());
                $('#tbl-row-8-result').text($('#tbl-row-8').text());
                $('#tbl-row-9-result').text($('#tbl-row-9').text());
                $('#tbl-row-10-result').text($('#tbl-row-10').text());

            }
            if (step == 'Step5') {
                $('#Q33Chart').css("display","block"); //Trick for Help Page
                $('#ResultStep6').html('<p style = "font-weight: bold;" > '+  DataForm[5]['Values'][51] + ' out of 100%')
                //RouteCache[stepID].IsCurrent = 0
                $("#wizard").steps('next');            
            }
            if (step == 'Step6') {
                //RouteCache[stepID].IsCurrent = 0
                $('#Q34Last').css("display","block"); //Trick for Help Page
                $("#wizard").steps('next');
            }
            //previousTab = currentTab
            currentTab = currentTab + n;
            //RouteCache[stepID].PrevTab = previousTab
            RouteCache[stepID].LastTab = currentTab;
            return
        }
        if (currentTab > x.length) {
            // ... the form gets submitted:
            CheckStep(step,true);
            firstTimeStep();
            //RouteCache[stepID + 1].IsCurrent = 1
            //previousTab = 0
            //currentTab = 0//Reset Ordinal
            //PROGRESS BAR
            var resp = step.substr(step.length - 1, step.length)
            resp = parseInt(resp)
            var progr = Math.round( (0.143 * resp) * 100)
            $('#progress_' + step).css('width', progr.toString() + '%');
            $('#progress_' + step).addClass('bg-success');
            $('#progress_text_' + step).text(progr.toString() + '% complete');
            RouteCache[stepID].IsCurrent = 0
            RouteCache[stepID].PrevTab = previousTab
            RouteCache[stepID].LastTab = currentTab - 1
            return false;
        }
        // Otherwise, display the correct tab:
        if (n == 1){
            RouteCache[stepID].PrevTab = previousTab
            RouteCache[stepID].LastTab = currentTab  
            showTab(currentTab,step);
        }
        /*
        if (step == 'Step1' && n == 1){showTab(currentTab,'Step1');}
        if (step == 'Step2' && n == 1){showTab(currentTab,'Step2');}
        */
    }

    function validateForm(step) {
        // This function deals with validation of the form fields
        var stp = document.getElementById(step)
        var x, y, i, j, valid = true;
        x = stp.getElementsByClassName("tab");
        try {
            y = x[currentTab].getElementsByTagName("input");
            // A loop that checks every input field in the current tab:
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value == "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                } else {
                    y[i].className += " valid";
                }
            }
        }catch(err){
            valid = true
        }
        try {
            if (step == 'Step1' && x[currentTab].id == 'Pre-Q1'){
                var selected = $("input[type='checkbox'][name='check_terms']:checked");
                if (selected.length == 0) {
                    $('#Pre_Q1_Check').css('background-color', '#ffdddd');;
                    valid = false;
                } else {
                    $('#Pre_Q1_Check').css('background-color', '');;
                }
            }
            if (step == 'Step2' && x[currentTab].id == 'Q13' && $('#select-2-12').val() == 'yes'){
                var selected = $("input[type='radio'][name='attorney']:checked");
                if (selected.length == 0) {
                    $('#attorney_yes').css('background-color', '#ffdddd');;
                    valid = false;
                }
            }
            if (step == 'Step4' && x[currentTab].id == 'Q21' ){
                var selected = $("input[type='radio'][name='adversary']:checked");
                if (selected.length == 0) {
                    $('#adversary_check').css('background-color', '#ffdddd');
                    valid = false;
                }
            }
        } catch (error) {
            valid = true
        }
        try {
            z = x[currentTab].getElementsByTagName("textarea");
            // A loop that checks every textarea field in the current tab:
            for (i = 0; i < z.length; i++) {
                // If a field is empty...
                if (z[i].value == "") {
                    // add an "invalid" class to the field:
                    z[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                } else {
                    z[i].className += " valid";
                }
            }   
        }catch (err){
            valid = true
        }

        try {
            s = x[currentTab].getElementsByTagName("select");
            // A loop that checks every select field in the current tab:
            for (i = 0; i < s.length; i++) {
                // If a field is empty...
                if (s[i].value == "") {
                    // add an "invalid" class to the field:
                    s[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                    if (step == 'Step3' && x[currentTab].id == 'Q16'){
                        if ($('#select-3-16-3').val() != '' && $('#select-3-16-4').val() != '' && $('#select-3-16-2').is(':hidden') ) {
                            s[i].className += " valid";
                            valid = true        
                        }
                    }
                } else {
                    s[i].className += " valid";
                }
            }        
        }catch (err){
            valid = true
        }
        // If the valid status is true, mark the step as finished and valid:
        /*
        if (valid) {
            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        */
        return valid; // return the valid status
    }

    function validateOrder(){
        var email =  localStorage.getItem('email') //$('#validationEmail').val();
        var email2,email3,status = ''
        var prdct,orderWC = 0;
        var valid = true;
        var order = $('#validationOrder').val();
        var wck = get_session('wck');
        var wcs = get_session('wcs');
        var valResultTxt = '';
        if (email.length == 0){
            /*
            $('#validationEmail').css('background-color', '#ffdddd');
            setTimeout(() => {
                $('#validationEmail').css('background-color', 'white');
            }, 1700);
            */
            valid = false;
        }
        if (order.length == 0){
            $('#validationOrder').css('background-color', '#ffdddd');
            setTimeout(() => {
                $('#validationOrder').css('background-color', 'white');
            }, 1700);
            valid = false;
        }
        if (!valid) {return}
        $('#btnValidation').text('Validating...');
        $('#btnValidation').css('background-color', '#FFCC00');
        $('#btnValidation').css('box-shadow', 'none');
        $('#btnValidation').css('border-color', '#FFCC00');

        $.ajax({
            type: "GET",
            url: "https://lawsuitanalysis.com/wp-json/wc/v3/orders/" + order,
            timeout: 0,
            headers: {
                "Authorization": "Basic " + btoa(wck + ":" + wcs)
            },
            success: function (response) {
                status = response.status
                orderWC = response.id
                prdct = response.line_items[0].product_id
                email2 = response.billing.email
                valResultTxt = validationMsg(orderWC,prdct,status,email,email2,email3);
                localStorage.setItem('msg', valResultTxt)
                if (response.customer_id !== 0){
                    $.ajax({
                        type: "GET",
                        url: "https://lawsuitanalysis.com/wp-json/wc/v3/customers/" + response.customer_id,
                        timeout: 0,
                        headers: {
                            "Authorization": "Basic " + btoa(wck + ":" + wcs)
                        },                 
                        success: function (response2) {
                            email3 = response2.email
                            valResultTxt = validationMsg(orderWC,prdct,status,email,email2,email3);
                            localStorage.setItem('msg', valResultTxt)
                        },
                        error (error) {
                            valResultTxt = validationMsg(orderWC,prdct,status,email,email2,email3);
                            localStorage.setItem('msg', valResultTxt);
                        }
                    });
                }
            },
            error(error){
                valResultTxt = validationMsg(orderWC,prdct,status,email,email2,email3);
                localStorage.setItem('msg', valResultTxt)
            }
        });

        setTimeout(() => {
            $('#btnValidation').text('Validate');
            $('#btnValidation').css('background-color', '#138496');
            $('#validationMsg').css('display', 'grid');
            setTimeout(() => {
                $('#validationMsg').css('display', 'none');
            }, 15000);
        }, 4000);


    };

    function validationMsg(order,prdct,status,email,email2,email3) {
        var emailUser = get_session('email');
        var uslr =  get_session('user_lr');
        var usid = get_session('user_id');
        if (uslr == ''){uslr = localStorage.getItem('user_lr') }
        var valResult = ''
        if (status == 'processing' ){
            if (prdct == 6121) {
                if (emailUser == email){
                    if (email == email2 || email == email3){
                        var dataPut = [];
                        dataPut.push({
                            "ord": order,
                            "sts": 'processing'
                            } 
                        ) 
                        $.ajax({
                            type: "POST",
                            url: "API/uppay.php?uslr=" + uslr + "&usid="+usid+"&ord="+order,
                            cache: 'false',
                            data: {data: dataPut},
                            success: function (response) {
                                valResult = "Thank you for being our valued customer. We are so grateful for the pleasure of serving you and hope we met your expectations.";
                                $('#validationMsg').text(valResult);
                                $('#validationMsg').css('display', 'grid');
                                $('#btnValidation').css('display', 'none');
                                $('#btnSuccess').css('display', 'grid');
                                return valResult
                            },
                            error (err) {
                                console.log('Error updating customer data.')
                            }
                        });
                    }
                }
            }
        }
    }

    function callHelpPage(Phase) {
        var HelpPage = getActiveQuestion(Phase);
        var PhaseURL = Phase;
        var FinalURL = 'https://lawsuitanalysis.com/p' + PhaseURL + "-hp" + HelpPage
        var win = window.open(FinalURL, '_blank');
        win.focus();
    }

    function getActiveQuestion(Phase) {
        var hp = '0';
        var step = 'Step' + Phase;
        var stp = document.getElementById(step);
        var x = stp.querySelectorAll(".tab,.results");
        for (let i = 0; i < x.length; i++) {
            if (x[i].style.display === 'block') {
                if (x[i].id === 'Pre-Q1'){hp = '0'};
                if (x[i].id === 'Q1'){hp = '1'};
                if (x[i].id === 'Q2'){hp = '2'};
                if (x[i].id === 'Q3'){hp = '3'};
                if (x[i].id === 'Q4'){hp = '4'};
                if (x[i].id === 'Q5'){hp = '5'};
                if (x[i].id === 'ResultStep1'){hp = '5a'};
                //step2
                if (x[i].id === 'Pre-Q7'){hp = '6'};
                if (x[i].id === 'Q7'){hp = '7'};
                if (x[i].id === 'Q8'){hp = '8'};
                if (x[i].id === 'Q9'){hp = '9'};
                if (x[i].id === 'Q10'){hp = '10'};
                if (x[i].id === 'Q11'){hp = '11'};
                if (x[i].id === 'Q12'){hp = '12'};
                if (x[i].id === 'Q13'){
                    var y = x[i].getElementsByClassName("form-group")
                    for (let index = 0; index < y.length; index++) {
                        if (y[index].style.display === 'block') {
                            if (y[index].id == 'attorney_yes'){hp = '13a'};
                            if (y[index].id == 'attorney_no'){hp = '13'};
                        } 
                    }
                };
                if (x[i].id === 'Q14'){hp = '14'};
                if (x[i].id === 'ResultStep2'){hp = '14b'};
                //step3
                if (x[i].id === 'Prev-Step3'){hp = '14d'};
                if (x[i].id === 'Q16-Prev'){hp = '15'};
                if (x[i].id === 'Q16'){hp = '16'};
                if (x[i].id === 'Q17'){hp = '17'};
                if (x[i].id === 'Q18'){hp = '18'};
                if (x[i].id === 'Q19'){hp = '19'};
                if (x[i].id === 'ResultStep3'){hp = '20'};
                //Step4
                if (x[i].id === 'Prev-Q21'){hp = '21'};
                if (x[i].id === 'Q21'){hp = '21a'};
                if (x[i].id === 'ResultStep4'){hp = '22'};
                //Step5
                if (x[i].id === 'Q23'){hp = '23'};
                //Step6
                if (x[i].id === 'Q33Chart'){
                    if ($('#ResultStep6').text().includes('91% and above')){hp = '24';}
                    if ($('#ResultStep6').text().includes('81% to 90%')){hp = '24-1';}
                    if ($('#ResultStep6').text().includes('71% to 80%')){hp = '24-2';}
                    if ($('#ResultStep6').text().includes('61% to 70%')){hp = '24-3';}
                    if ($('#ResultStep6').text().includes('51% to 60%')){hp = '24-4';}
                    if ($('#ResultStep6').text().includes('50% and below')){hp = '24-5';}                
                };
                //Step7
                if (x[i].id === 'Q34Last'){
                    if ($('#ResultStep7_1').text().includes('Small Claims')){hp = '25';}
                    if ($('#ResultStep7_1').text().includes('Binding Arbitration')){hp = '25-1';}
                    if ($('#ResultStep7_1').text().includes('Upper Civil Court')){hp = '25-2';}
                    if ($('#ResultStep7_1').text().includes('Mediation')){
                        if($('#ResultStep7_2').text().includes('Small Claims')){hp = '25-3';}
                        if($('#ResultStep7_2').text().includes('Binding Arbitration')){hp = '25-4';}
                        if($('#ResultStep7_2').text().includes('Upper Civil Court')){hp = '25-5';}
                    };
                };
            };            
        };
        return hp
    }

    function SeparadorMiles(valor){
        if (Number.isInteger(valor)){
            var texto = valor.toString();
            var textoabs = texto.replace("-", "");
        }
        else {
            var texto = valor ;
            var textoabs = Math.round(valor);
        };
        if ( textoabs.length > 6 ){
            var textoFinal = texto.substr(0,texto.length - 6) + ',' + texto.substr(texto.length - 6,3) + ',' + texto.substr(texto.length - 3,3)  ;
        }
        else if (textoabs.length <= 6 && textoabs.length > 3) {
            var textoFinal =  texto.substr(0,texto.length - 3) + ',' + texto.substr(texto.length - 3,3)  ;
        } else {
            var textoFinal = texto ;
        };
        /*
        if (isNaN(textoFinal)){
            return texto
        } else {
            return textoFinal ;
        }
        */
        return textoFinal ;
    };

    function VLookUp(object,value,attSearch,attResult){
        var TempArray = object
        var result = 'Not Found'
        for (var i = 0; i <= TempArray.length; i++) {
            if (TempArray[i][attSearch] == value ) {
                result = TempArray[i][attResult]
                return result
            }
        }
                
    }

</script>
