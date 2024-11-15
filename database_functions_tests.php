<?php
include_once "unitTestingHelper.php";
include_once "hw5mod.php"; // Ensure compatibility with hw5.php

// Tests
function test_database_connection_can_be_made() {
    $dbc = getDatabaseConnection();
    assertTrue($dbc !== false, "test_database_connection_can_be_made");
}