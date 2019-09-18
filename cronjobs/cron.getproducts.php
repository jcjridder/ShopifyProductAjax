<?php

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../');

require $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'] . "/classes/database/class.dbfunctions.php";
require $_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/class.mailing.php";

if (php_sapi_name() == "cli") {
    $startExiting = new DataFunctions();
    $startExiting->GetAll("shopify_collections");
    $startExitingArray = $startExiting->FetchDbArray();

    $productsFound = array();



    $idArray = array();

    for ($i=0; $i <count($startExitingArray); $i++) { 
        $ch = curl_init("https://gendtastic.myshopify.com/admin/products.json?limit=250&collection_id=" . $startExitingArray[$i]["id"] . "?fields=id,title,handle,image,body_html,product_type,updated_at,published_at,variants");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Basic  ' . base64_encode("0e46fd9e650c9d92dad6aa679e19acc4:")
        ));
    
        $server_output = curl_exec($ch);
        curl_close($ch);
    
        $decordedOutput = html_entity_decode($server_output);
        $returnArray = json_decode($decordedOutput, true);

        $updated = 0;
        $added = 0;
        $deleted = 0;
        

    
        for ($g = 0; $g < count($returnArray["products"]); $g++) {

            if($returnArray["products"][$g]["published_at"] != null && $returnArray["products"][$g]["published_at"]  != ""){

                $checkIfExists = new DataFunctions();
                $checkIfExists->Search("shopify_products", "id", $returnArray["products"][$g]["id"]);
                $checkIfExistsArray = $checkIfExists->FetchDbArray();
    
                if(count($checkIfExistsArray) == 0){
    
                    echo "nieuw" . $returnArray["products"][$g]["id"] . " " . $returnArray["products"][$g]["title"] . "<br>";
    
    
                    $insertArray = array();
                    array_push($insertArray, $returnArray["products"][$g]["id"]);
                    array_push($insertArray, $returnArray["products"][$g]["title"]);
                    array_push($insertArray, $returnArray["products"][$g]["handle"]);
                    array_push($insertArray, $returnArray["products"][$g]["image"]["width"]);
                    array_push($insertArray, $returnArray["products"][$g]["image"]["height"]);
                    array_push($insertArray, $returnArray["products"][$g]["image"]["id"]);
                    array_push($insertArray, $returnArray["products"][$g]["image"]["src"]);
                    array_push($insertArray, $returnArray["products"][$g]["body_html"]);
                    array_push($insertArray, $returnArray["products"][$g]["product_type"]);
                    array_push($insertArray, $returnArray["products"][$g]["updated_at"]);
                    array_push($insertArray, $returnArray["products"][$g]["published_at"]);
    
        
                    $rightCount = 0;
                    $rightPrice = $returnArray["products"][$g]["variants"][0]["price"];
                    for ($pv = 0; $pv < count($returnArray["products"][$g]["variants"]); $pv++) {
                        if($returnArray["products"][$g]["variants"][$pv]["price"] < $rightPrice){
                            $rightCount = $pv;
                        }
                    }
                    
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["id"]);
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["inventory_quantity"]);
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["created_at"]);
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["updated_at"]);
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["price"]);
                    array_push($insertArray, $returnArray["products"][$g]["variants"][$rightCount]["compare_at_price"]);
                    $insertProduct = new DataFunctions();
                    $insertProduct->Insert("shopify_products", $insertArray, true);

                    
                    $insertProductCol = new DataFunctions();
                    $insertProductCol->Insert("shopify_products_collections", array($returnArray["products"][$g]["id"], $startExitingArray[$i]["id"]));
                }else{
    
                    echo "bestaat".$returnArray["products"][$g]["id"]  . " " . $returnArray["products"][$g]["title"] . "";
    
                    if($checkIfExistsArray[0]["updated_at"] != $returnArray["products"][$g]["updated_at"]){


    
                        echo " Geupdate OUD: " . $checkIfExistsArray[0]["updated_at"] . "  NIEUW:" . $returnArray["products"][$g]["updated_at"];
                        
    
                        $updateProduct = new DataFunctions();
        
                        $rightCount = 0;
                        $rightPrice = $returnArray["products"][$g]["variants"][0]["price"];
                        for ($pv = 0; $pv < count($returnArray["products"][$g]["variants"]); $pv++) {
                            if ($returnArray["products"][$g]["variants"][$pv]["price"] < $rightPrice) {
                                $rightCount = $pv;
                            }
                        }
                        
                        $updateProduct->Update("shopify_products", "title", $returnArray["products"][$g]["title"],"id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "handle", $returnArray["products"][$g]["handle"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "image_width", $returnArray["products"][$g]["image"]["width"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "image_height", $returnArray["products"][$g]["image"]["height"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "image_id", $returnArray["products"][$g]["image"]["id"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "image_src", $returnArray["products"][$g]["image"]["src"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "body_html", $returnArray["products"][$g]["body_html"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "product_type", $returnArray["products"][$g]["product_type"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "updated_at", $returnArray["products"][$g]["updated_at"], "id", $returnArray["products"][$g]["id"]);
                        // $updateProduct->Update("shopify_products", "published_at", $returnArray["products"][$g]["published_at"], "id", $returnArray["products"][$g]["id"]);
        
                        $updateProduct->Update("shopify_products", "variant_id", $returnArray["products"][$g]["variants"][$rightCount]["id"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "inventory_quantity", $returnArray["products"][$g]["variants"][$rightCount]["inventory_quantity"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "variant_created_at", $returnArray["products"][$g]["variants"][$rightCount]["created_at"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "variant_updated_at", $returnArray["products"][$g]["variants"][$rightCount]["updated_at"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "price", $returnArray["products"][$g]["variants"][$rightCount]["price"], "id", $returnArray["products"][$g]["id"]);
                        $updateProduct->Update("shopify_products", "compare_at_price", $returnArray["products"][$g]["variants"][$rightCount]["compare_at_price"], "id", $returnArray["products"][$g]["id"]);

                        $insertProductCol = new DataFunctions();
                        $insertProductCol->Update("shopify_products_collections", "collection_id" , $returnArray["products"][$g]["id"], "product_id" , $startExitingArray[$i]["id"]);

                    }else{
                        $insertDoesItExist = new DataFunctions();
                        $insertDoesItExist->SearchMore("shopify_products_collections", array("product_id", "collection_id"), array($returnArray["products"][$g]["id"], $startExitingArray[$i]["id"]));
                        $insertDoesItExistArray = $insertDoesItExist->FetchDbArray();

                        if(count($insertDoesItExistArray) == 0){
                            $insertProductCol = new DataFunctions();
                            $insertProductCol->Insert("shopify_products_collections", array($returnArray["products"][$g]["id"], $startExitingArray[$i]["id"]));
                        }
                    }


                    echo "<br>";
                    
                }
              
                if(!in_array($returnArray["products"][$g]["id"], $idArray)){
                    array_push($idArray, $returnArray["products"][$g]["id"]);
                }
                
        
            }
            
        }
    
    
    }

    $checkIfExists = new DataFunctions();
    $checkIfExists->GetAll("shopify_products");
    $checkIfExistsArray = $checkIfExists->FetchDbArray();
    $existingIdArray = array();
    for ($i = 0; $i < count($checkIfExistsArray); $i++) {
        array_push($existingIdArray, $checkIfExistsArray[$i]["id"]);
    }


    $leftoverIds = array_diff($existingIdArray, $idArray);

    array_multisort($leftoverIds, SORT_ASC);

    for ($g = 0; $g < count($leftoverIds); $g++) {
        $deleteCollection = new DataFunctions();
        $deleteCollection->DeleteSearched("shopify_products", "id", $leftoverIds[$g]);
        $deleted++;

        $deleteCollection = new DataFunctions();
        $deleteCollection->DeleteSearched("shopify_products_collections", "product_id", $leftoverIds[$g]);
    }



    // $FinalExising = new DataFunctions();
    // $FinalExising->GetAll("shopify_products");
    // $FinalExisingArray = $FinalExising->FetchDbArray();

    // $mailHtml = "";
    // $mailHtml .= "<!DOCTYPE html>\r\n";
    // $mailHtml .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n";
    // $mailHtml .= "<style type=\"text/css\">\r\n";
    // $mailHtml .= "html{\r\n";
    // $mailHtml .= "margin: 0px 0px 0px 0px;\r\n";
    // $mailHtml .= "padding: 0px 0px 0px 0px;\r\n";
    // $mailHtml .= "}\r\n";
    // $mailHtml .= "body{\r\n";
    // $mailHtml .= "margin: 0px 0px 0px 0px;\r\n";
    // $mailHtml .= "padding: 0px 0px 0px 0px;\r\n";
    // $mailHtml .= "text-align: center;\r\n";
    // $mailHtml .= "background-color: #FFFFFF;\r\n";
    // $mailHtml .= "}\r\n";
    // $mailHtml .= "div#container{\r\n";
    // $mailHtml .= "margin: 0px auto;\r\n";
    // $mailHtml .= "padding: 0px 0px 0px 0px;\r\n";
    // $mailHtml .= "background: #FFFFFF;\r\n";
    // $mailHtml .= "width: 800px;\r\n";
    // $mailHtml .= "text-align: left;\r\n";
    // $mailHtml .= "z-index: 0;\r\n";
    // $mailHtml .= "}\r\n";
    // $mailHtml .= ".deviceWidth { width: 100%!important; min-width: 320px!important; }\r\n";
    // $mailHtml .= "</style>\r\n";
    // $mailHtml .= "</head>\r\n";
    // $mailHtml .= "<body>\r\n";

    // $mailHtml .= "<table id=\"container\"  align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"deviceWidth\" style=\"margin: auto; height: auto; min-height: 550px;\">\r\n";
    // $mailHtml .= "<tr>";
    // $mailHtml .= "<td>";
    // $mailHtml .= "<p style=\"text-align: center; width: 300px; display:block; margin:auto\"><img width=\"300\" style=\"max-width: 300px;\" src=\"https://producten.gendtastic.nl/images/Color_logo_-_no_background.png\" /></p>\r\n";
    // $mailHtml .= "</td>";
    // $mailHtml .= "</tr>";

    // $mailHtml .= "<tr>";
    // $mailHtml .= "<td>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\">De collectie inventarisatie heeft zojuist gedraaid</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\">&nbsp;</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\">Hieronder staan de resultaten van de invantarisatie</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\">&nbsp;</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\"><span style=\"font-weight: bold;\">Begin aantal collecties:</span><br />";
    // $mailHtml .= "" . count($startExitingArray) . "</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\"><span style=\"font-weight: bold;\">Aantal collecties toegevoegd:</span><br />";
    // $mailHtml .= "" . $added . "</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\"><span style=\"font-weight: bold;\">Aantal bestaande collecties aangepast/gechekt:</span><br />";
    // $mailHtml .= "" . $updated . "</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\"><span style=\"font-weight: bold;\">Aantal collecties verwijdert:</span><br />";
    // $mailHtml .= "" . $deleted . "</p>";
    // $mailHtml .= "<p style=\"text-align: center;color: #333; font-family: Arial; font-size: 16px;\"><span style=\"font-weight: bold;\">Eind aantal collecties:</span></span><br />";
    // $mailHtml .= "" . count($FinalExisingArray) . "</p>";
    // $mailHtml .= "<p>&nbsp;</p>";

    // $mailHtml .= "</td>";
    // $mailHtml .= "</tr>";
    //     #-- Dit is een automatisch gegenereerde email --
    // $mailHtml .= "<p style=\"text-align: center;color: #999; font-family: Arial; font-size: 13px; font-style: italic;\">Dit is een automatisch gegenereerde E-mail</p>";
    // $mailHtml .= "<tr>";
    // $mailHtml .= "<td>";

    // $mailHtml .= "</td>";
    // $mailHtml .= "</tr>";
    // $mailHtml .= "</table>";

    // $mailHtml .= "</body>\r\n";
    // $mailHtml .= "</html>\r\n";


    // $now = date('d-m-Y H:i:s');

    // $mailing = new Mailing();
    // $mailing->sendThisEmail("jessiedenridder@gmail.com", $mailHtml, "Update collectie lijst | ". $now,"");
    // $mailing->sendThisEmail("info@gendtastic.nl", $mailHtml, "Update collectie lijst | " . $now, "");
} else {
    echo "acces denied";
}