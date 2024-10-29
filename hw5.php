<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grades";

$dbc = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($dbc->connect_error) {
    die("Connection failed: " . $dbc->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST['id']);
    $quiz1 = intval($_POST['quiz1']);
    $quiz2 = intval($_POST['quiz2']);
    $quiz3 = intval($_POST['quiz3']);
    $quiz4 = intval($_POST['quiz4']);
    $quiz5 = intval($_POST['quiz5']);
    $hw1 = intval($_POST['hw1']);
    $hw2 = intval($_POST['hw2']);
    $hw3 = intval($_POST['hw3']);
    $hw4 = intval($_POST['hw4']);
    $hw5 = intval($_POST['hw5']);
    $midterm = intval($_POST['midterm']);
    $final = intval($_POST['final']);

    $checkExistence = $dbc->prepare("SELECT id FROM quizzes WHERE id = ?");
    $checkExistence->bind_param("i", $id);
    $checkExistence->execute();
    $result = $checkExistence->get_result();

    if ($result->num_rows === 0) {

        $insertQuizzes = $dbc->prepare(
            "INSERT INTO quizzes (id, quiz1, quiz2, quiz3, quiz4, quiz5, midterm, final) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insertQuizzes->bind_param(
            "iiiiiiii", $id, $quiz1, $quiz2, $quiz3, $quiz4, $quiz5, $midterm, $final
        );
        $insertQuizzes->execute();

        $insertHomeworks = $dbc->prepare(
            "INSERT INTO homeworks (id, hw1, hw2, hw3, hw4, hw5) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insertHomeworks->bind_param(
            "iiiiii", $id, $hw1, $hw2, $hw3, $hw4, $hw5
        );
        $insertHomeworks->execute();
    } else {
        // Update the existing records
        $updateQuizzes = $dbc->prepare(
            "UPDATE quizzes SET quiz1 = ?, quiz2 = ?, quiz3 = ?, quiz4 = ?, quiz5 = ?, 
             midterm = ?, final = ? WHERE id = ?"
        );
        $updateQuizzes->bind_param(
            "iiiiiiii", $quiz1, $quiz2, $quiz3, $quiz4, $quiz5, $midterm, $final, $id
        );
        $updateQuizzes->execute();

        $updateHomeworks = $dbc->prepare(
            "UPDATE homeworks SET hw1 = ?, hw2 = ?, hw3 = ?, hw4 = ?, hw5 = ? WHERE id = ?"
        );
        $updateHomeworks->bind_param(
            "iiiiii", $hw1, $hw2, $hw3, $hw4, $hw5, $id
        );
        $updateHomeworks->execute();
    }

    $sql = "SELECT studentInfo.id, firstName, lastName, quizAvg, hwAvg, midterm, final
            FROM studentInfo
            JOIN quizzes ON studentInfo.id = quizzes.id
            JOIN homeworks ON studentInfo.id = homeworks.id
            WHERE studentInfo.id = ?";
    
    $stmt = $dbc->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        $grade = round(
            ($student['hwAvg'] * 0.20) +
            ($student['quizAvg'] * 0.10) +
            ($student['midterm'] * 0.30) +
            ($student['final'] * 0.40)
        );

        $insertOrUpdateRecords = $dbc->prepare(
            "REPLACE INTO records (id, hwAvg, quizAvg, midterm, final, grade) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insertOrUpdateRecords->bind_param(
            "iiiiii", $id, $student['hwAvg'], $student['quizAvg'], 
            $student['midterm'], $student['final'], $grade
        );
        $insertOrUpdateRecords->execute();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Grades</title>
</head>
<body>
    <h2>Enter Student Grades</h2>
    <form method="post" action="">
        Student ID: <input type="number" name="id" required><br>

        <h3>Quizzes (0-100)</h3>
        Quiz 1: <input type="number" name="quiz1" min="0" max="100" required><br>
        Quiz 2: <input type="number" name="quiz2" min="0" max="100" required><br>
        Quiz 3: <input type="number" name="quiz3" min="0" max="100" required><br>
        Quiz 4: <input type="number" name="quiz4" min="0" max="100" required><br>
        Quiz 5: <input type="number" name="quiz5" min="0" max="100" required><br>

        <h3>Homeworks (0-100)</h3>
        Homework 1: <input type="number" name="hw1" min="0" max="100" required><br>
        Homework 2: <input type="number" name="hw2" min="0" max="100" required><br>
        Homework 3: <input type="number" name="hw3" min="0" max="100" required><br>
        Homework 4: <input type="number" name="hw4" min="0" max="100" required><br>
        Homework 5: <input type="number" name="hw5" min="0" max="100" required><br>

        <h3>Midterm and Final (0-100)</h3>
        Midterm: <input type="number" name="midterm" min="0" max="100" required><br>
        Final: <input type="number" name="final" min="0" max="100" required><br>

        <input type="submit" value="Submit">
    </form>

    <?php if (isset($student)): ?>
        <h2>Student Information</h2>
        <p>ID: <?= $student['id'] ?></p>
        <p>First Name: <?= htmlspecialchars($student['firstName']) ?></p>
        <p>Last Name: <?= htmlspecialchars($student['lastName']) ?></p>
        <p>Quiz Average: <?= $student['quizAvg'] ?></p>
        <p>Homework Average: <?= $student['hwAvg'] ?></p>
        <p>Midterm: <?= $student['midterm'] ?></p>
        <p>Final: <?= $student['final'] ?></p>
        <p>Grade: <?= $grade ?></p>
    <?php endif; ?>

</body>
</html>
