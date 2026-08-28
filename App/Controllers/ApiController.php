<?php
namespace App\Controllers;

use Components\QueryBuilder;
use Core\Response;

class ApiController
{

    const AZURE_DOCINT_ENDPOINT="https://ai-docint-we-dev.cognitiveservices.azure.com/";
    const AZURE_DOCINT_API_KEY="ebc505b16603434c95a64090d1f430a4";
    const SUITEFILES_AGENT_ENDPOINT = "https://func-hackathon-suitefiles-c2fdgfefepcrdtbh.westeurope-01.azurewebsites.net";

    public function analyze()
    {
    $uploadDir = __DIR__ . "/uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!isset($_FILES['document'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "No document uploaded"
        ]);
        exit;
    }

    $file = $_FILES['document'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Upload failed"
        ]);
        exit;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid("receipt_", true) . "." . $extension;
    $filePath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Failed saving file"
        ]);
        exit;
    }
    $response = $this->analyzeDocument($filePath);

if (!$response["success"]) {
    return Response::json($response);
}

$text = "";

if (isset($response["data"]["content"])) {
    $text = $response["data"]["content"];
} elseif (isset($response["data"]["pages"])) {
    foreach ($response["data"]["pages"] as $page) {
        foreach ($page["lines"] ?? [] as $line) {
            $text .= $line["content"] . "\n";
        }
    }
}

$agent = $this->thinkAgent($text);

return Response::json([
    "success" => true,
    "document" => $text,
    "agent" => $agent
]);

}

public function refine()
{
    $body = json_decode(file_get_contents("php://input"), true);

    if (!$body) {
        return Response::json([
            "success" => false,
            "error" => "Invalid JSON"
        ]);
    }

    $result = $this->refineAgent(
        $body["user_message"] ?? "",
        $body["previous_response"] ?? "",
        $body["content_text"] ?? "",
        $body["user_role"] ?? "firm_user",
        $body["user_id"] ?? "firm_user_darko",
        $body["recent_events"] ?? []
    );

    return Response::json($result);
}

    public function status(int $id)
    {
        return Response::json(["Status" => "Status Ok"]);
    }



private function analyzeDocument($filePath)
{
    $endpoint = self::AZURE_DOCINT_ENDPOINT;
    $apiKey = self::AZURE_DOCINT_API_KEY;

    $model = "prebuilt-read";

    $url = rtrim($endpoint, "/") .
        "/documentintelligence/documentModels/" .
        $model .
        ":analyze?api-version=2024-11-30";


    if (!file_exists($filePath)) {
        return [
            "url" => $url,
            "success" => false,
            "error" => "File not found"
        ];
    }

    $fileData = base64_encode(file_get_contents($filePath));
    $payload = ["base64Source" => $fileData];
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Ocp-Apim-Subscription-Key: " . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);


    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    $this->removeOcrGeometry($body);



    if ($httpCode !== 202) {
        return [
            "url" => $url,
            "success" => false,
            "status" => $httpCode,
            "response" => $body
        ];
    }


    // Extract operation URL
    preg_match(
        "/Operation-Location:\s*(.*)/i",
        $headers,
        $matches
    );


    if (!isset($matches[1])) {
        return [
            "success" => false,
            "error" => "Missing Operation-Location header"
        ];
    }


    $operationUrl = trim($matches[1]);


    // Poll result
    for ($i = 0; $i < 30; $i++) {

        sleep(2);


        $ch = curl_init($operationUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Ocp-Apim-Subscription-Key: " . $apiKey
            ]
        ]);


        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $json = json_decode($result, true);


        if (
            isset($json["status"]) &&
            $json["status"] === "succeeded"
        ) {

            return [
                "success" => true,
                "data" => $json["analyzeResult"] ?? $json
            ];

        }


        if (
            isset($json["status"]) &&
            $json["status"] === "failed"
        ) {

            return [
                "success" => false,
                "error" => $json
            ];

        }
    }


    return [
        "success" => false,
        "error" => "Analysis timeout"
    ];
}



private function thinkAgent(
    ?string $contentText = "",
    ?string $userText = "",
    string $userRole = "firm_user",
    string $userId = "firm_user_darko",
    array $recentEvents = []
) {
    $url = self::SUITEFILES_AGENT_ENDPOINT . "/api/agent/think";

    $payload = [
        "content_text" => $contentText,
        "user_text" => $userText,
        "user_role" => $userRole,
        "user_id" => $userId
    ];

    if (!empty($recentEvents)) {
        $payload["recent_events"] = $recentEvents;
    }

    $payload = array_filter($payload, function ($value) {
        return $value !== null;
    });

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return [
            "success" => false,
            "error" => curl_error($ch)
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $json = json_decode($response, true);

    if ($httpCode >= 400) {
        return [
            "success" => false,
            "status" => $httpCode,
            "response" => $json
        ];
    }

    // Save the raw JSON string for /refine
    $previousResponse = $json["response"] ?? "";

    // Decode the nested JSON for the iOS UI
    if (isset($json["response"])) {
        $json["response"] = json_decode($json["response"], true);
    }

    return [
        "success" => true,
        "previous_response" => $previousResponse,
        "data" => $json
    ];
}

private function refineAgent(
    string $userMessage,
    string $previousResponse,
    ?string $contentText = "",
    string $userRole = "firm_user",
    string $userId = "firm_user_darko",
    array $recentEvents = []
) {
    $url = self::SUITEFILES_AGENT_ENDPOINT . "/api/agent/refine";

    $payload = [
        "user_message" => $userMessage,
        "previous_response" => $previousResponse,
        "content_text" => $contentText,
        "user_role" => $userRole,
        "user_id" => $userId
    ];

    if (!empty($recentEvents)) {
        $payload["recent_events"] = $recentEvents;
    }

    $payload = array_filter($payload, function ($value) {
        return $value !== null;
    });

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return [
            "success" => false,
            "error" => curl_error($ch)
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $json = json_decode($response, true);

    if ($httpCode >= 400) {
        return [
            "success" => false,
            "status" => $httpCode,
            "response" => $json
        ];
    }

    if (isset($json["response"])) {
        $json["response"] = json_decode($json["response"], true);
    }

    return [
        "success" => true,
        "data" => $json
    ];
}

private function removeOcrGeometry(&$data)
{
    if (!is_array($data)) {
        return;
    }

    unset(
        $data['pages'],
        $data['polygon'],
        $data['boundingRegions'],
        $data['boundingBox'],      // older APIs
        $data['spans'],
        $data['confidence']        // optional, remove if you don't need it
    );

    foreach ($data as &$value) {
        $this->removeOcrGeometry($value);
    }
}


}