<?php
require_once "tasks_api.php";



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");


$data = [];

$fn = $_REQUEST["fn"] ?? null;
$id = $_REQUEST["id"] ?? 0;
$taskName = $_REQUEST["taskName"] ?? null;
$taskDescription = $_REQUEST["taskDescription"] ?? null;
$taskStatus = $_REQUEST["taskStatus"] ?? null;


$tasks = new Tasks();
$tasks->setId($id);

if($fn === "create" && $taskName != null && $taskDescription != null){
    $tasks->setTaskName($taskName);
    $tasks->setTaskDescription($taskDescription);
    $tasks->setTaskStatus($taskStatus);

    $data["tasks"] = $tasks->create();
}
if($fn === "read"){
    $data["tasks"] = $tasks->read();
}
if($fn === "update" && $id > 0 && $taskName != null && $taskDescription != null && $taskStatus != null){
    $tasks->setTaskName($taskName);
    $tasks->setTaskDescription($taskDescription);
    $tasks->setTaskStatus($taskStatus);
    $data["tasks"] = $tasks->update();
}
if($fn === "delete" && $id > 0){
    $data["tasks"] = $tasks->delete();
}

die(json_encode($data));
