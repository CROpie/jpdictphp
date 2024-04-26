<?php

$db = new SQLite3('./dictionary.db');

$uri = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

$response = array(
    "success" => false,
    "message" => ""
);

switch ($method | $uri) {
    /**
     * Path: GET /api
     * Query String: ?word=""
     * Task: Query the string in the database
     */
    case ($method == "GET" && strpos($uri, '/api') !== false):
        header("Content-Type: application/json");
        $queryString = $_GET["word"];

        if (isset($queryString)) {
            // do database stuff
            $response["data"] = getData($db, $queryString);


            if (empty($response["data"])) {
                $response["message"] = "No rows found!";
            } else {
                $response["success"] = true;  
            }

            echo json_encode($response);
        } else {
            http_response_code(400);
            $response["message"] = "Couldn't find query parameter";
            echo json_encode($response);
        }
        break;

    /**
     * Path: ?
     * Task: this path doesn't match any of the defined paths
     *       throw an error
     */
    default:
        http_response_code(400);
        $response["message"] = "Not sure what you were trying to do there! No API at that location.";
        echo json_encode($response);
        break;
}

$db->close();


function getData($db, $queryString) {
    $query = "SELECT *
        FROM words
        WHERE kanji = '$queryString'
    ";

    $result = $db->query($query);

    $queryData = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $queryData[] = $row;
    }

    if (empty($queryData)) {
        $query = "SELECT *
            FROM words
            WHERE kanji
            LIKE '%$queryString%'
        "; 

        $result = $db->query($query);

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $queryData[] = $row;
        }
    }

    return $queryData;
}

?>