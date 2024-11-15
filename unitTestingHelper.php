<?php

// Helper functions to handle unit test assertions

/*
This function tests that the given arguments are the same and prints out an error if not.

The first argument is the value to be tested. The second is the expected value. 
The third parameter is the name of the test so the output will show the test that succeeded or failed.
*/
function assertEqual($actual, $expected, $testName = '') {
    if ($actual === $expected) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true) . "\n";
    }
}

/*
This function tests that the given argument is true and prints out an error if not.

The first argument is the value to be tested. 
The second parameter is the name of the test so the output will show the test that succeeded or failed.
*/
function assertTrue($condition, $testName = '') {
    if ($condition) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected true, got false\n";
    }
}

function assertFalse($condition, $testName = '') {
    if (!$condition) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected false, got true\n";
    }
}


/*
This function tests that the given arrays are the same and prints out an error if not. Every item in the 
arrays must be identical, including the order.

The first argument is the array to be tested. The second is the expected array. 
The third parameter is the name of the test so the output will show the test that succeeded or failed.
*/
function assertArrayEqual($actual, $expected, $testName = '') {
    if ($actual === $expected) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected: " . json_encode($expected) . ", Got: " . json_encode($actual) . "\n";
    }
}

/*
This function tests that the given function throws the expected exception and prints out an error if the function
does not throw an exception or if the exception thrown is of a different type than the given exception.

The first argument is the function to be called. The second is the expected exception. 
The third parameter is the name of the test so the output will show the test that succeeded or failed.
*/
function assertThrows(callable $fn, $expectedException, $testName = '') {
    try {
        $fn();
        print "   FAIL: $testName - Expected exception of type $expectedException\n";
    } catch (Exception $e) {
        if (get_class($e) === $expectedException) {
            print "   PASS: $testName\n";
        } else {
            print "   FAIL: $testName - Expected exception of type $expectedException, got " . get_class($e) . "\n";
        }
    }
}

/*
This function is to be called if the output (i.e. print or print) of a function is to be captured into a variable
so that it can be compared or searched through. The parameter is the function to be called and the result is the 
output of the function (if any).
*/
function captureOutput(callable $fn) {
    ob_start();
    $fn();
    $output = ob_get_clean();
    return $output;
}

/*
This function tests that the given variable contains the expected contents (i.e. if a variable obtained
by capturing the output of a function contains certain HTML tags).

The first argument is the output to be searched.
The second argument is the string to search for in the output.
The third parameter is the name of the test so the output will show the test that succeeded or failed.
*/
function assertHtmlContains($output, $expectedHtml, $testName = '') {
    if (strpos($output, $expectedHtml) !== false) {
        print "   PASS: $testName\n";
    } else {
        print "   FAIL: $testName - Expected HTML fragment: " . htmlspecialchars($expectedHtml) . "\n";
    }
}




/*
This function runs all unit tests. There is a requirement: the unit test functions *must* begin with "test_".
This function will go through a list of all functions and run only those that start with "test_". It then 
prints out the file in which the function can be found and the output of the unit test (which usually lists
the result of the test).
*/
function runTests() {
    $allFunctions = get_defined_functions()['user'];
    $functionsToRun = [];
    foreach ($allFunctions as $function) {
        if (strpos($function, 'test_') === 0) { // Only run functions prefixed with "test_"
            $details = new ReflectionFunction($function);
            $filenameParts = explode(DIRECTORY_SEPARATOR, $details->getFileName());
            $functionsToRun[$filenameParts[array_key_last($filenameParts)]][] = $function;
        }
    }
    foreach($functionsToRun as $fileName => $testFunctionsInFile) {
        $header = "\nTests from file '$fileName':\n";
        print $header . str_repeat('-', strlen($header)) . "\n";
        foreach ($testFunctionsInFile as $testFunction) {
            try {
                trim($testFunction());
            } catch (Throwable $e) {
                print "   FAIL: $testFunction got Exception: " . $e->getMessage() . "\n";
            }
        }
    }
}
