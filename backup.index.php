<?php
header("Access-Control-Allow-Origin: *");
    $urlTestForSort = explode("?", $_GET["source"]);
    $sort = "";
    if(count($urlTestForSort) > 1){
        $urlArray = explode("/collections/", $urlTestForSort[0]);
        $thisCollection = $urlArray[1];
        $thisUrl = $urlArray[0];
        $sortArray = explode("=", $urlTestForSort[1]);
        $sort = $sortArray[1];
    }else{
        $urlArray = explode("/collections/", $_GET["source"]);
        $thisCollection = $urlArray[1];
        $thisUrl = $urlArray[0];
    }
    

    if($thisCollection != "all"){
        echo "<div class=\"product_wrapper\">";
    
        $ch = curl_init("https://gendtastic.myshopify.com/admin/custom_collections.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Basic  ' . base64_encode("0e46fd9e650c9d92dad6aa679e19acc4:5f69772d3cae7e0a9db22ea33006b02e")
        ));
    
        $server_output = curl_exec($ch);
        curl_close($ch);
    
        $decordedOutput = html_entity_decode($server_output);
        $returnArray = json_decode($decordedOutput, true);
    
        for ($i=0; $i < count($returnArray["custom_collections"]); $i++) {
            if($returnArray["custom_collections"][$i]["handle"] == $thisCollection){
                $thisCollectionId = $returnArray["custom_collections"][$i]["id"]; 
            }
        }
        if($sort != ""){
            $chGet = curl_init("https://gendtastic.myshopify.com/admin/products.json?collection_id=". $thisCollectionId);
        }else{
            $chGet = curl_init("https://gendtastic.myshopify.com/admin/products.json?collection_id=" . $thisCollectionId);
        }
        curl_setopt($chGet, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chGet, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($chGet, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Basic  ' . base64_encode("0e46fd9e650c9d92dad6aa679e19acc4:5f69772d3cae7e0a9db22ea33006b02e")
        ));
    
        $server_output_new = curl_exec($chGet);
        curl_close($chGet);
        
        $decordedNewOutput = html_entity_decode($server_output_new);
        $returnArrayNew = json_decode($decordedNewOutput, true);
    
        $productDiv = "";
        for ($g=0; $g < count($returnArrayNew["products"]); $g++) {
            $productDiv .= "<div class=\"product_div\">\r\n";
            $productDiv .= "<div class=\"product_div_overlay\" onclick=\"location.href = '". $thisUrl ."/products/". $returnArrayNew["products"][$g]["handle"] ."'\">\r\n";
            $productDiv .= "<button class=\"product_div_open_product\">bekijken</button>\r\n";
            $productDiv .= "</div>\r\n";
            $productDiv .= "<div class=\"product_div_top_wrap\">\r\n";
            $productDiv .= "<div class=\"product_div_image_wrap\">\r\n";
            $productDiv .= "<img class=\"product_div_image_thumb\" src='". $returnArrayNew["products"][$g]["images"][0]["src"] ."'>\r\n";
            $productDiv .=  "</div>\r\n";
            $productDiv .= "<div class=\"product_div_info\">\r\n";
            if(strlen($returnArrayNew["products"][$g]["title"]) > 50 ){
                $shortened = substr($returnArrayNew["products"][$g]["title"], 0, 47);
                if(substr($shortened, -1) == "-"){
                    $shortened = substr($shortened, 0, -1);
                }
                $shortened = trim($shortened);
                $productDiv .= "<p>" . $shortened . "..." . "</p>\r\n";
            }else{
                $productDiv .=  "<p>". $returnArrayNew["products"][$g]["title"] ."</p>\r\n";
            }
            $productDiv .= "</div>\r\n";
            $productDiv .= "</div>\r\n";
            $productDiv .= "<div class=\"product_div_price\">\r\n";
            if($returnArrayNew["products"][$g]["variants"][0]["compare_at_price"] > $returnArrayNew["products"][$g]["variants"][0]["price"]){
                $productDiv .= "<span class=\"product_div_compare_price\">Adviesprijs &euro;" . number_format($returnArrayNew["products"][$g]["variants"][0]["compare_at_price"], 2, ',', '.') . "</span>\r\n";
                $productDiv .= "<span class=\"product_div_current_price\">&euro;". number_format($returnArrayNew["products"][$g]["variants"][0]["price"], 2, ',', '.')  . "</span>\r\n";
            }else{
                $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($returnArrayNew["products"][$g]["variants"][0]["price"], 2, ',', '.') . "</span>\r\n";
            }
            $productDiv .= "</div>\r\n";
            $productDiv .= "</div>\r\n";
        }
    }else{
        if(isset($_GET["pagenr"]) && $_GET["pagenr"] != ""){
            $pageNumber = $_GET["pagenr"];
        }else{
            echo "<div class=\"product_wrapper\" data-page=\"1\">";
            $pageNumber = 1;
        }
        if ($sort != "") {
            $chGet = curl_init("https://gendtastic.myshopify.com/admin/products.json?limit=24&page=".$pageNumber);
        }else{
            $chGet = curl_init("https://gendtastic.myshopify.com/admin/products.json?limit=24&page=" . $pageNumber);
        }
        curl_setopt($chGet, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chGet, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($chGet, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Basic  ' . base64_encode("0e46fd9e650c9d92dad6aa679e19acc4:5f69772d3cae7e0a9db22ea33006b02e")
        ));

        $server_output_new = curl_exec($chGet);
        curl_close($chGet);
        
        $decordedNewOutput = html_entity_decode($server_output_new);
        $returnArrayNew = json_decode($decordedNewOutput, true);
        
        if(count($returnArrayNew["products"]) == 0){
            echo "noresult";
        }else{
            $productDiv = "";
            // $productDiv = $sort;
            for ($g = 0; $g < count($returnArrayNew["products"]); $g++) {
                $productDiv .= "<div class=\"product_div\">\r\n";
                $productDiv .= "<div class=\"product_div_overlay\" onclick=\"location.href = '" . $thisUrl . "/products/" . $returnArrayNew["products"][$g]["handle"] . "'\">\r\n";
                $productDiv .= "<button class=\"product_div_open_product\">bekijken</button>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_top_wrap\">\r\n";
                $productDiv .= "<div class=\"product_div_image_wrap\">\r\n";
                $productDiv .= "<img class=\"product_div_image_thumb\" src='" . $returnArrayNew["products"][$g]["images"][0]["src"] . "'>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_info\">\r\n";
                if (strlen($returnArrayNew["products"][$g]["title"]) > 50) {
                    $shortened = substr($returnArrayNew["products"][$g]["title"], 0, 47);
                    if (substr($shortened, -1) == "-") {
                        $shortened = substr($shortened, 0, -1);
                    }
                    $shortened = trim($shortened);
                    $productDiv .= "<p>" . $shortened . "..." . "</p>\r\n";
                } else {
                    $productDiv .= "<p>" . $returnArrayNew["products"][$g]["title"] . "</p>\r\n";
                }
                $productDiv .= "</div>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_price\">\r\n";
                if ($returnArrayNew["products"][$g]["variants"][0]["compare_at_price"] > $returnArrayNew["products"][$g]["variants"][0]["price"]) {
                    $productDiv .= "<span class=\"product_div_compare_price\">Adviesprijs &euro;" . number_format($returnArrayNew["products"][$g]["variants"][0]["compare_at_price"], 2, ',', '.') . "</span>\r\n";
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($returnArrayNew["products"][$g]["variants"][0]["price"], 2, ',', '.') . "</span>\r\n";
                } else {
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($returnArrayNew["products"][$g]["variants"][0]["price"], 2, ',', '.') . "</span>\r\n";
                }
                $productDiv .= "</div>\r\n";
                $productDiv .= "</div>\r\n";
            }
            
                $productDiv .= "<div id=\"extraProductLoader\">\r\n";
                $productDiv .= "<script>\r\n";
                $productDiv .= "$(window).scroll(function(){\r\n";
                $productDiv .= "if($(window).scrollTop() + $(window).height() > $(document).height() - $(\".site-footer\").outerHeight() - 100) {\r\n";
                $productDiv .= "$(window).off('scroll');\r\n";
                $productDiv .= "var pageNr = parseInt($(\".product_wrapper\").attr(\"data-page\")) + 1;\r\n";
                $productDiv .= "$(\".product_wrapper\").append(\"<div class='loaderWrap'><div class='lds-roller'><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>\");\r\n";
                $productDiv .= "$.ajax({\r\n";
                $productDiv .= "cache: false,\r\n";
                $productDiv .= "type: \"GET\",\r\n";
                $productDiv .= "url: \"https://producten.gendtastic.nl/\",\r\n";
                $productDiv .= "data: 'source='+ window.location + '&pagenr='+pageNr,\r\n";
                $productDiv .= "success: function (data) {\r\n";
                $productDiv .= "console.log(data)\r\n";
                $productDiv .= "if(data.trim() == \"noresult\"){\r\n";
                $productDiv .= "$(\"#extraProductLoader\").remove();\r\n";
                $productDiv .= "$(\".loaderWrap\").remove()\r\n";
                $productDiv .= "}else{\r\n";
                $productDiv .= "$(\".product_wrapper\").append(data);\r\n";
                $productDiv .= "$(\".product_hiden_div\").html('');\r\n";
                $productDiv .= "$(\".product_wrapper\").attr(\"data-page\",pageNr);\r\n";
                $productDiv .= "$(\".loaderWrap\").remove()\r\n";
                $productDiv .= "$(\"#extraProductLoader\").remove();\r\n";
                $productDiv .= "}\r\n";
                $productDiv .= "},\r\n";
                $productDiv .= "error: function (e) {\r\n";
                $productDiv .= "if (e.message != \"undefined\" && e.message != null) {\r\n";
                $productDiv .= "alert(e.message);\r\n";
                $productDiv .= "}\r\n";
                $productDiv .= "}\r\n";
                $productDiv .= "});\r\n";
                $productDiv .= "}\r\n";
                $productDiv .= "});\r\n";
                
                $productDiv .= "</script>\r\n";
                $productDiv .= "</div>\r\n";
            
        }
    }

    echo $productDiv;
    
    ?>
