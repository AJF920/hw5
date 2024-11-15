<?php
include_once "hw5mod.php";

// Tests
function test_validate_scores_with_valid_data() {
    $scores = [75, 89, 103, 55, 100];
    $result = validateScores($scores);
    assertTrue($result, "test_validate_scores_with_valid_data");
}

function test_validate_scores_with_invalid_data() {
    $scores = [75, -5, 120, 55, 100];
    $result = validateScores($scores);
    assertFalse($result, "test_validate_scores_with_invalid_data");
}
