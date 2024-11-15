<?php
include_once "hw5mod.php";

function insertNewStudent($dbc) {
    $newId = 9999;
    $firstName = "TestFirstName";
    $lastName = "TestLastName";

    while (lookUpStudentName($dbc, $newId)) {
        $newId--;
    }
    $query = "INSERT INTO studentInfo (id, firstName, lastName) VALUES (?, ?, ?)";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("iss", $newId, $firstName, $lastName);
    $stmt->execute();
    return [$newId, $firstName, $lastName];
}

function deleteStudent($dbc, $id) {
    $queries = [
        "DELETE FROM quizzes WHERE id = ?",
        "DELETE FROM homeworks WHERE id = ?",
        "DELETE FROM exams WHERE id = ?",
        "DELETE FROM studentInfo WHERE id = ?",
    ];
    foreach ($queries as $query) {
        $stmt = $dbc->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
