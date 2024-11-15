<?php
function getDatabaseConnection() {
    $servername = "localhost";
    $username = "csc350";  
    $password = "xampp";  
    $dbname = "grades"; 

    $dbc = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($dbc->connect_error) {
        die("Connection failed: " . $dbc->connect_error);
    }
    return $dbc;
}

$dbc = getDatabaseConnection();


// Error handling for SQL queries
function executeQuery($dbc, $query, $params) {
    $stmt = $dbc->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . $dbc->error);
    }
    $stmt->bind_param(...$params);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }
    return $stmt;
}

// Validation functions
function validateScores($scores) {
    foreach ($scores as $score) {
        if (!is_numeric($score) || $score < 0 || $score > 110) {
            return false;
        }
    }
    return true;
}

function validateExamScore($score) {
    return is_numeric($score) && $score >= 0 && $score <= 110;
}

function calculateFinalGrade($homeworks, $quizzes, $midterm, $final) {
    // Drop the lowest quiz score if there are multiple scores
    if (count($quizzes) > 1) {
        $lowestQuiz = min($quizzes);
        $quizzes = array_filter($quizzes, function($score) use ($lowestQuiz) {
            return $score !== $lowestQuiz; // Filter out the lowest score
        });
    }
    // Calculate averages
    $quizAvg = count($quizzes) ? array_sum($quizzes) / count($quizzes) : 0;
    $hwAvg = count($homeworks) ? array_sum($homeworks) / count($homeworks) : 0;

    // Weighted final grade calculation
    $overallGrade = ($final * 0.4) + ($midterm * 0.3) + ($quizAvg * 0.1) + ($hwAvg * 0.2);
    
    // Ensure result is rounded to 2 decimal places
    return round($overallGrade, 2);
}



// Process form submission
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $quizzes = [intval($_POST['quiz1']), intval($_POST['quiz2']), intval($_POST['quiz3']), intval($_POST['quiz4']), intval($_POST['quiz5'])];
    $homeworks = [intval($_POST['hw1']), intval($_POST['hw2']), intval($_POST['hw3']), intval($_POST['hw4']), intval($_POST['hw5'])];
    $midterm = intval($_POST['midterm']);
    $final = intval($_POST['final']);

    if (!validateScores($quizzes) || !validateScores($homeworks) || !validateExamScore($midterm) || !validateExamScore($final)) {
        die("Invalid input detected. Please ensure all scores are between 0 and 110.");
    }

    // Insert quizzes
    foreach ($quizzes as $i => $score) {
        $query = "REPLACE INTO quizzes (id, quiz_number, score) VALUES (?, ?, ?)";
        executeQuery($dbc, $query, ["iii", $id, $i + 1, $score]);
    }

    // Insert homeworks
    foreach ($homeworks as $i => $score) {
        $query = "REPLACE INTO homeworks (id, hw_number, score) VALUES (?, ?, ?)";
        executeQuery($dbc, $query, ["iii", $id, $i + 1, $score]);
    }

    // Insert exams
    executeQuery($dbc, "REPLACE INTO exams (id, exam_type, score) VALUES (?, 'midterm', ?)", ["ii", $id, $midterm]);
    executeQuery($dbc, "REPLACE INTO exams (id, exam_type, score) VALUES (?, 'final', ?)", ["ii", $id, $final]);

    // Refresh the page to display updated information
    echo "<meta http-equiv='refresh' content='0'>";
}

// Form for entering grades
echo '
<form method="post" action="">
    <label>ID: <input type="number" name="id" required></label><br>
    <label>Quiz 1: <input type="number" name="quiz1" required></label>
    <label>Quiz 2: <input type="number" name="quiz2" required></label>
    <label>Quiz 3: <input type="number" name="quiz3" required></label>
    <label>Quiz 4: <input type="number" name="quiz4" required></label>
    <label>Quiz 5: <input type="number" name="quiz5" required></label><br>
    <label>HW 1: <input type="number" name="hw1" required></label>
    <label>HW 2: <input type="number" name="hw2" required></label>
    <label>HW 3: <input type="number" name="hw3" required></label>
    <label>HW 4: <input type="number" name="hw4" required></label>
    <label>HW 5: <input type="number" name="hw5" required></label><br>
    <label>Midterm: <input type="number" name="midterm" required></label><br>
    <label>Final: <input type="number" name="final" required></label><br>
    <button type="submit">Submit Grades</button>
</form>
';

// Display student information
$query = "
    SELECT 
        s.id, s.lastName, s.firstName, 
        q1.score AS quiz1, q2.score AS quiz2, q3.score AS quiz3, q4.score AS quiz4, q5.score AS quiz5,
        ROUND(((q1.score + q2.score + q3.score + q4.score + q5.score - LEAST(q1.score, q2.score, q3.score, q4.score, q5.score)) / 4), 2) AS quizAvg,
        h1.score AS hw1, h2.score AS hw2, h3.score AS hw3, h4.score AS hw4, h5.score AS hw5,
        ROUND((h1.score + h2.score + h3.score + h4.score + h5.score) / 5, 2) AS hwAvg,
        e1.score AS midterm, e2.score AS final,
        ROUND((e2.score * 0.4 + e1.score * 0.3 + ((q1.score + q2.score + q3.score + q4.score + q5.score - LEAST(q1.score, q2.score, q3.score, q4.score, q5.score)) / 4) * 0.1 +
        ((h1.score + h2.score + h3.score + h4.score + h5.score) / 5) * 0.2), 2) AS overallGrade
    FROM studentInfo s
    LEFT JOIN quizzes q1 ON s.id = q1.id AND q1.quiz_number = 1
    LEFT JOIN quizzes q2 ON s.id = q2.id AND q2.quiz_number = 2
    LEFT JOIN quizzes q3 ON s.id = q3.id AND q3.quiz_number = 3
    LEFT JOIN quizzes q4 ON s.id = q4.id AND q4.quiz_number = 4
    LEFT JOIN quizzes q5 ON s.id = q5.id AND q5.quiz_number = 5
    LEFT JOIN homeworks h1 ON s.id = h1.id AND h1.hw_number = 1
    LEFT JOIN homeworks h2 ON s.id = h2.id AND h2.hw_number = 2
    LEFT JOIN homeworks h3 ON s.id = h3.id AND h3.hw_number = 3
    LEFT JOIN homeworks h4 ON s.id = h4.id AND h4.hw_number = 4
    LEFT JOIN homeworks h5 ON s.id = h5.id AND h5.hw_number = 5
    LEFT JOIN exams e1 ON s.id = e1.id AND e1.exam_type = 'midterm'
    LEFT JOIN exams e2 ON s.id = e2.id AND e2.exam_type = 'final'
";

$result = $dbc->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Last Name</th><th>First Name</th><th>ID</th>";
    for ($i = 1; $i <= 5; $i++) echo "<th>Quiz $i</th>";
    echo "<th>Quiz Avg</th>";
    for ($i = 1; $i <= 5; $i++) echo "<th>HW $i</th>";
    echo "<th>HW Avg</th><th>Midterm</th><th>Final</th><th>Overall Grade</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['lastName']}</td><td>{$row['firstName']}</td><td>{$row['id']}</td>";
        for ($i = 1; $i <= 5; $i++) echo "<td>{$row['quiz' . $i]}</td>";
        echo "<td>{$row['quizAvg']}</td>";
        for ($i = 1; $i <= 5; $i++) echo "<td>{$row['hw' . $i]}</td>";
        echo "<td>{$row['hwAvg']}</td><td>{$row['midterm']}</td><td>{$row['final']}</td><td>{$row['overallGrade']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No records found.";
}

$dbc->close();
?>
