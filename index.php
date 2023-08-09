<!DOCTYPE html>
<html>
<head>
    <title>Landing Page</title>
    <!-- Include jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Welcome to the Landing Page</h1>
    <button id="fetchStudentsBtn">Fetch Students</button>
    <div id="studentList"></div>

    <script>
        // Attach event listener to the button
        $("#fetchStudentsBtn").click(function() {
            // Perform an AJAX request to fetch students
            $.ajax({
                url: "get_student.php",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Display students in the "studentList" div
                        var studentList = $("#studentList");
                        studentList.empty(); // Clear previous content

                        var students = response.students;
                        for (var i = 0; i < students.length; i++) {
                            var student = students[i];
                            var studentInfo = "ID: " + student.id + "<br>Name: " + student.name + "<br>Branch: " + student.branch + "<br><br>";
                            studentList.append(studentInfo);
                        }
                    } else {
                        $("#studentList").text("Failed to fetch students.");
                    }
                },
                error: function() {
                    $("#studentList").text("An error occurred while fetching students.");
                }
            });
        });
    </script>
</body>
</html>
