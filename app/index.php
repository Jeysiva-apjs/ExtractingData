<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run PHP Code</title>
</head>
<body>

    <form action="" method="post">
        <input type="submit" name="run_php" value="Click To Extract">
    </form>

    <?php
    if (isset($_POST['run_php'])) {
        // Helper function to check if a string contains any numeric digit
        function containsNumber($input) {
            return preg_match('/\d/', $input);
        }

        // Function to extract tags from a given input text
        function extractTags($input, $tag) {
            preg_match_all("/\b$tag\w*\b/", $input, $matches);
            $uniqueMatches = array_unique($matches[0]);
            $filteredMatches = array_filter($uniqueMatches, function($match) {
                return strlen($match) < 12 && containsNumber($match);
            });
            return $filteredMatches;
        }

        // Function to check if the ID is in the specified range
        function isInRange($id) {
            $parsedId = intval($id);
            return ($parsedId >= 50000 && $parsedId < 60000) || ($parsedId >= 14000000 && $parsedId < 15000000);
        }

        // Main function to process files and store data in the database
        function processData() {
            $headPath = 'C:/xampp/htdocs/1/data';
            $filePaths = glob($headPath . '/*.txt');

            // Database connection parameters
            $servername = "localhost";
            $username = "root";
            $password = "1234";
            $dbname = "test";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            echo "Processing...";
            foreach ($filePaths as $filePath) {
                $allTexts = file_get_contents($filePath);
                
                // Extract object type from file path
                preg_match('/([^\/]+)\.txt$/', $filePath, $matches);
                $objectType = strtolower($matches[1]);

                // Split the text based on object type
                $splits = preg_split('/\s*OBJECT\s+' . ucfirst($objectType) . '\s*/i', $allTexts, null, PREG_SPLIT_NO_EMPTY);

                foreach ($splits as $i => $split) {
                    // Extract tags for IB, IS, PD
                    $ibTags = extractTags($split, 'IB');
                    $isTags = extractTags($split, 'IS');
                    $pdTags = extractTags($split, 'PD');

                    // Extract ID and Name
                    preg_match('/(\d+)\s*(.*)/', $split, $matches);
                    $id = $matches[1];
                    $name = trim($matches[2]);

                    // Check if the ID is in range
                    if (!isInRange($id)) {
                        // Combine all tags into a single array
                        $allTags = array_merge($ibTags, $isTags, $pdTags);
                        $allTagsStr = implode(',', $allTags);

                        // Check if any tags are present
                        if (!empty($allTags)) {
                            // Store data in the database
                            $sql = "INSERT INTO ObjectData (Object_ID, Object_Name, Tags, Object_Type)
                                    VALUES ('$id', '$name', '$allTagsStr', '$objectType')";

                            if ($conn->query($sql) === TRUE) {
                                
                            } else {
                                echo "Error: " . $sql . "<br>" . $conn->error;
                            }
                        }
                    }
                }
            }

            echo "Done...";

            // Close database connection
            $conn->close();
        }

        // Process data and store in the database
        processData();
    }
    ?>

</body>
</html>
