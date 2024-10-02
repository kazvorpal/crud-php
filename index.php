<?php
header("Content-Type: application/json; chairset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Congrol-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Congrol-Allow-Headers, Authorization, X-Requested-With");

//include_once 'src/Database.php';
//include_once 'src/Book.php';

//$database = 

$jsonFile = 'experience.json';
$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

function saveData($data) {
    global $jsonFile;
    $success = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    if ($success === false) {
        throw new Exception("Failed to write data to file");
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

try {
    switch ($method) {
        case 'GET':
            if (empty($path)) {
                echo json_encode($data);
            } else {
                $item = array_values(array_filter($data, function($job) use ($path) {
                    return $job['id'] === $path;
                }));
                if (empty($item)) {
                    throw new Exception("Job not found", 404);
                }
                echo json_encode($item[0]);
            }
            break;

        case 'POST':
            $newJob = json_decode(file_get_contents('php://input'), true);
            if (isset($newJob['id'])) {
                $data[] = $newJob;
                saveData($data);
                echo json_encode(['message' => 'Job created successfully']);
            } else {
                throw new Exception("Invalid job data", 400);
            }
            break;

        case 'PUT':
            $updatedJob = json_decode(file_get_contents('php://input'), true);
            if (isset($updatedJob['id'])) {
                $index = array_search($updatedJob['id'], array_column($data, 'id'));
                if ($index !== false) {
                    $data[$index] = $updatedJob;
                    saveData($data);
                    echo json_encode(['message' => 'Job updated successfully']);
                } else {
                    throw new Exception("Job not found", 404);
                }
            } else {
                throw new Exception("Invalid job data", 400);
            }
            break;

        case 'DELETE':
            if (!empty($path)) {
                $index = array_search($path, array_column($data, 'id'));
                if ($index !== false) {
                    array_splice($data, $index, 1);
                    saveData($data);
                    echo json_encode(['message' => 'Job deleted successfully']);
                } else {
                    throw new Exception("Job not found", 404);
                }
            } else {
                throw new Exception("Job ID not provided", 400);
            }
            break;

        default:
            throw new Exception("Invalid request method", 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}