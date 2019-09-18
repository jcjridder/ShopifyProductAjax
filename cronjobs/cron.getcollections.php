<?php

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../');

require $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'] . "/classes/database/class.dbfunctions.php";
require $_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/class.mailing.php";

if (php_sapi_name() == "cli") {
    $ch = curl_init("https://gendtastic.myshopify.com/admin/custom_collections.json");
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


    $startExiting = new DataFunctions();
    $startExiting->GetAll("shopify_collections");
    $startExitingArray = $startExiting->FetchDbArray();

    $updated = 0;
    $added = 0;
    $deleted = 0;
    $idArray = array();
    for ($i = 0; $i < count($returnArray["custom_collections"]); $i++) {

        array_push($idArray, $returnArray["custom_collections"][$i]["id"]);
        $checkIfExists = new DataFunctions();
        $checkIfExists->Search("shopify_collections", "id",$returnArray["custom_collections"][$i]["id"]);
        $checkIfExistsArray = $checkIfExists->FetchDbArray();
        if(count($checkIfExistsArray) != 0){
            $updateCollection = new DataFunctions();
            $updateCollection->Update("shopify_collections", "title", $returnArray["custom_collections"][$i]["title"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "updated_at", $returnArray["custom_collections"][$i]["updated_at"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "body_html", $returnArray["custom_collections"][$i]["body_html"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "published_at", $returnArray["custom_collections"][$i]["published_at"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "sort_order", $returnArray["custom_collections"][$i]["sort_order"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "handle", $returnArray["custom_collections"][$i]["handle"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "template_suffix", $returnArray["custom_collections"][$i]["template_suffix"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updateCollection->Update("shopify_collections", "admin_graphql_api_id", $returnArray["custom_collections"][$i]["admin_graphql_api_id"], "id", $returnArray["custom_collections"][$i]["id"]);
            $updated ++;
        }else{
            $insertArray = array();
            array_push($insertArray, $returnArray["custom_collections"][$i]["id"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["handle"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["title"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["updated_at"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["body_html"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["published_at"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["sort_order"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["template_suffix"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["published_scope"]);
            array_push($insertArray, $returnArray["custom_collections"][$i]["admin_graphql_api_id"]);
            $insertCollection = new DataFunctions();
            $insertCollection->Insert("shopify_collections", $insertArray, true);
            $added ++;
        }

    }

    $checkIfExists = new DataFunctions();
    $checkIfExists->GetAll("shopify_collections");
    $checkIfExistsArray = $checkIfExists->FetchDbArray();
    $existingIdArray = array();
    for ($i = 0; $i < count($checkIfExistsArray); $i++) {
        array_push($existingIdArray, $checkIfExistsArray[$i]["id"]);
    }


    $leftoverIds = array_diff($existingIdArray, $idArray);

    array_multisort($leftoverIds, SORT_ASC);

    for ($g = 0; $g < count($leftoverIds); $g++) {
        $deleteCollection = new DataFunctions();
        $deleteCollection->DeleteSearched("shopify_collections","id", $leftoverIds[$g]);
        $deleted ++;
    }


    // $FinalExising = new DataFunctions();
    // $FinalExising->GetAll("shopify_collections");
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
}else{
    echo "acces denied";
}