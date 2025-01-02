 // Search students
    function searchStudents() {
        let input = document.getElementById("searchBar");
        let filter = input.value.toLowerCase();  // Convert search input to lowercase
        let rows = document.getElementById("studentTable").getElementsByTagName("tr");

        // Loop through each row in the table
        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByTagName("td");
            let match = false;  // Variable to track if the row matches the search term
            
            // Loop through each cell in the row
            for (let j = 0; j < cells.length; j++) {
                // Compare each cell's text to the search term
                if (cells[j].innerText.toLowerCase().indexOf(filter) > -1) {
                    match = true;  // If the text matches, flag it as a match
                    break;  // No need to check other cells in this row
                }
            }

            // Show or hide the row based on whether it matched the search term
            if (match) {
                rows[i].style.display = "";  // Display row if it matches
            } else {
                rows[i].style.display = "none";  // Hide row if it doesn't match
            }
        }
    }

    // Delete student
    function deleteStudent(id) {
        if (confirm("Are you sure you want to delete this student?")) {
            window.location.href = `?delete_id=${id}`;
        }
    }

    // Switch sections
    function switchSection(section) {
        let sections = document.querySelectorAll('.section');
        sections.forEach(s => s.classList.add('hidden'));
        document.getElementById(section).classList.remove('hidden');
    }

    // Toggle theme
    function toggleTheme() {
        document.body.classList.toggle('dark-theme');
        let themeText = document.getElementById("themeText");
        let themeToggle = document.getElementById("themeToggle");

        if (document.body.classList.contains('dark-theme')) {
            themeText.innerText = "Dark Mode";
            themeToggle.style.backgroundColor = "#4f6d7a";
        } else {
            themeText.innerText = "Light Mode";
            themeToggle.style.backgroundColor = "#dddddd";
        }
    }

