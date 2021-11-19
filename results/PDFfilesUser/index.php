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