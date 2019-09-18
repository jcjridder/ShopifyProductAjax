<?php
header("Access-Control-Allow-Origin: *");
require $_SERVER['DOCUMENT_ROOT'] . "/classes/database/class.dbfunctions.php";
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
        if (isset($_GET["pagenr"]) && $_GET["pagenr"] != "") {
            $pageNumber = $_GET["pagenr"];
        } else {
            $pageNumber = 1;
        }
    
        $GetCollections = new DataFunctions();
        $GetCollections->Search("shopify_collections","handle", $thisCollection);
        $GetCollectionsArray = $GetCollections->FetchDbArray();
        $offesetPage = $pageNumber - 1;
        $offsetNumber = $offesetPage * 24;
        $GetProducts = new DataFunctions();        
        if ($sort != "") {
            if ($sort == "title-descending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY title DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            }else if ($sort == "title-ascending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=" . $GetCollectionsArray[0]["id"] . " AND pc.product_id = p.id ORDER BY title DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "price-ascending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY price ASC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "price-descending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY price DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "created-ascending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY published_at ASC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "created-descending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY published_at DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            }
        } else {
            $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY title ASC LIMIT 24 OFFSET " . $offsetNumber . "");
        }
        // $GetProducts->DoCustomQuery("SELECT * FROM shopify_products_collections as pc, shopify_products as p WHERE pc.collection_id=". $GetCollectionsArray[0]["id"]. " AND pc.product_id = p.id ORDER BY title LIMIT 24 OFFSET " . $offsetNumber . "");
        $GetProductsArray = $GetProducts->FetchDbArray();

        if (count($GetProductsArray) == 0) {
            echo "noresult";
        } else {
            if (isset($_GET["pagenr"]) && $_GET["pagenr"] != "") {
            } else {
                echo "<div class=\"product_wrapper\" data-page=\"1\">";
            }
            $productDiv = "";
                // $productDiv = $sort;
            
            for ($g = 0; $g < count($GetProductsArray); $g++) {

                $productDiv .= "<div class=\"product_div\">\r\n";
                $productDiv .= "<div class=\"product_div_overlay\" onclick=\"location.href = '/products/" . $GetProductsArray[$g]["handle"] . "'\">\r\n";
                $productDiv .= "<button class=\"product_div_open_product\">bekijken</button>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_top_wrap\">\r\n";
                $productDiv .= "<div class=\"product_div_image_wrap\">\r\n";
                $productDiv .= "<img class=\"product_div_image_thumb\" src='" . $GetProductsArray[$g]["image_src"] . "'>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_info\">\r\n";
                if (strlen($GetProductsArray[$g]["title"]) > 50) {
                    $shortened = substr($GetProductsArray[$g]["title"], 0, 47);
                    if (substr($shortened, -1) == "-") {
                        $shortened = substr($shortened, 0, -1);
                    }
                    $shortened = trim($shortened);
                    $productDiv .= "<p>" . $shortened . "..." . "</p>\r\n";
                } else {
                    $productDiv .= "<p>" . $GetProductsArray[$g]["title"] . "</p>\r\n";
                }
                
                $productDiv .= "</div>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_price\">\r\n";

                if ($GetProductsArray[$g]["compare_at_price"] > $GetProductsArray[$g]["price"]) {
                    $productDiv .= "<span class=\"product_div_compare_price\">Adviesprijs &euro;" . number_format($GetProductsArray[$g]["compare_at_price"], 2, ',', '.') . "</span>\r\n";
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($GetProductsArray[$g]["price"], 2, ',', '.') . "</span>\r\n";
                } else {
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($GetProductsArray[$g]["price"], 2, ',', '.') . "</span>\r\n";
                }
                $productDiv .= "</div>\r\n";
                $productDiv .= "</div>\r\n";
            }

            if(count($GetProductsArray) > 23){
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
                $productDiv .= "\r\n";
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
    }else{
        if(isset($_GET["pagenr"]) && $_GET["pagenr"] != ""){
            $pageNumber = $_GET["pagenr"];
        }else{
            echo "<div class=\"product_wrapper\" data-page=\"1\">";
            $pageNumber = 1;
        }
        $offesetPage = $pageNumber - 1;
        $offsetNumber = $offesetPage * 24;
        $GetProducts = new DataFunctions();
        if($sort != ""){
            if($sort == "title-descending"){
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY title DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            }else if ($sort == "price-ascending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY price ASC LIMIT 24 OFFSET " . $offsetNumber . "");
            }else if($sort == "price-descending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY price DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "created-ascending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY published_at ASC LIMIT 24 OFFSET " . $offsetNumber . "");
            } else if ($sort == "created-descending") {
                $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY published_at DESC LIMIT 24 OFFSET " . $offsetNumber . "");
            }
        }else{
            $GetProducts->DoCustomQuery("SELECT * FROM shopify_products ORDER BY title ASC LIMIT 24 OFFSET " . $offsetNumber . "");
        }
        $GetProductsArray = $GetProducts->FetchDbArray();
        
        if(count($GetProductsArray) == 0){
            echo "noresult";
        }else{
            $productDiv = "";
            // $productDiv = $sort;
            for ($g = 0; $g < count($GetProductsArray); $g++) {
                $productDiv .= "<div class=\"product_div\">\r\n";
                $productDiv .= "<div class=\"product_div_overlay\" onclick=\"location.href = '/products/" . $GetProductsArray[$g]["handle"] . "'\">\r\n";
                $productDiv .= "<button class=\"product_div_open_product\">bekijken</button>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_top_wrap\">\r\n";
                $productDiv .= "<div class=\"product_div_image_wrap\">\r\n";
                $productDiv .= "<img class=\"product_div_image_thumb\" src='" . $GetProductsArray[$g]["image_src"] . "'>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_info\">\r\n";
                if (strlen($GetProductsArray[$g]["title"]) > 50) {
                    $shortened = substr($GetProductsArray[$g]["title"], 0, 47);
                    if (substr($shortened, -1) == "-") {
                        $shortened = substr($shortened, 0, -1);
                    }
                    $shortened = trim($shortened);
                    $productDiv .= "<p>" . $shortened . "..." . "</p>\r\n";
                } else {
                    $productDiv .= "<p>" . $GetProductsArray[$g]["title"] . "</p>\r\n";
                }
                $productDiv .= "</div>\r\n";
                $productDiv .= "</div>\r\n";
                $productDiv .= "<div class=\"product_div_price\">\r\n";
                // $GetProductsVarionts = new DataFunctions();
                // $GetProductsVarionts->DoCustomQuery("SELECT * FROM shopify_products_variants WHERE product_id=" . $GetProductsArray[$g]["id"] . " ORDER BY price ASC LIMIT 1");
                // $GetProductsVariontsArray = $GetProductsVarionts->FetchDbArray();
                if ($GetProductsArray[$g]["compare_at_price"] > $GetProductsArray[$g]["price"]) {
                    $productDiv .= "<span class=\"product_div_compare_price\">Adviesprijs &euro;" . number_format($GetProductsArray[$g]["compare_at_price"], 2, ',', '.') . "</span>\r\n";
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($GetProductsArray[$g]["price"], 2, ',', '.') . "</span>\r\n";
                } else {
                    $productDiv .= "<span class=\"product_div_current_price\">&euro;" . number_format($GetProductsArray[$g]["price"], 2, ',', '.') . "</span>\r\n";
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
                $productDiv .= "var location = window.location.href;\r\n";
                $productDiv .= "var locationArray = location.split('#');\r\n";
                $productDiv .= "$.ajax({\r\n";
                $productDiv .= "cache: false,\r\n";
                $productDiv .= "type: \"GET\",\r\n";
                $productDiv .= "url: \"https://producten.gendtastic.nl/\",\r\n";
                $productDiv .= "data: 'source='+ locationArray[0] + '&pagenr='+pageNr,\r\n";
                $productDiv .= "success: function (data) {\r\n";
                $productDiv .= "\r\n";
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
