<?php
include_once "hw5mod.php";

function generateGoodScores() {
    $homeworks = [75, 89, 103, 55, 100];
    $quizzes = [65, 78, 99, 76, 69];
    $midterm = 86;
    $final = 90;
    return [$homeworks, $quizzes, $midterm, $final];
}

// New assert function to handle floating-point comparisons
function assertCloseEnough($actual, $expected, $testName = '', $tolerance = 0.01) {
    if (abs($actual - $expected) < $tolerance) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected: $expected, Got: $actual\n";
    }
}

// Grading tests using assertCloseEnough due to floating-point calculations
function test_calculation_is_correct() {
    [$homeworks, $quizzes, $midterm, $final] = generateGoodScores();
    $result = calculateFinalGrade($homeworks, $quizzes, $midterm, $final);
    assertCloseEnough($result, 86.73, "test_calculation_is_correct");
}

function test_calculation_succeeds_with_no_homeworks() {
    [$homeworks, $quizzes, $midterm, $final] = generateGoodScores();
    $homeworks = [];
    $result = calculateFinalGrade($homeworks, $quizzes, $midterm, $final);
    assertCloseEnough($result, 69.85, "test_calculation_succeeds_with_no_homeworks");
}

function test_calculation_succeeds_with_no_quizzes() {
    [$homeworks, $quizzes, $midterm, $final] = generateGoodScores();
    $quizzes = [];
    $result = calculateFinalGrade($homeworks, $quizzes, $midterm, $final);
    assertCloseEnough($result, 78.68, "test_calculation_succeeds_with_no_quizzes");
}
