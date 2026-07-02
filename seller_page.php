<?php
session_start();
include 'db.php';

if (!isset($_SESSION['seller_email'])) {
    header("Location: login.php");
    exit();
}

$seller_email = $_SESSION['seller_email'];
$seller_stmt = $conn->prepare("SELECT seller_id FROM sellers WHERE email = ?");
$seller_stmt->bind_param("s", $seller_email);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Handle POST (Add/Update book)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = $_POST['book_id'] ?? '';
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $book_condition = $_POST['book_condition'];
    $subject = $_POST['subject'];
    $semester = $_POST['semester'];
    $course = $_POST['course'];
    $description = $_POST['description'];

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'jfif'];
        if (in_array($ext, $allowed)) {
            $newName = time() . rand(1000, 9999) . '.' . $ext;
            $uploadPath = 'E:/XAMPP/htdocs/MIS/book_images/' . $newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = $newName; // Save only filename to DB
            }
        }
    }

    if ($book_id) {
        if ($imagePath) {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, book_condition=?, subject=?, semester=?, course=?, description=?, image=? WHERE book_id=? AND seller_id=?");
            $stmt->bind_param("ssdssisssii", $title, $author, $price, $book_condition, $subject, $semester, $course, $description, $imagePath, $book_id, $seller_id);
        } else {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, book_condition=?, subject=?, semester=?, course=?, description=? WHERE book_id=? AND seller_id=?");
            $stmt->bind_param("ssdssissii", $title, $author, $price, $book_condition, $subject, $semester, $course, $description, $book_id, $seller_id);
        }
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO books (seller_id, title, author, price, book_condition, subject, semester, course, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssisss", $seller_id, $title, $author, $price, $book_condition, $subject, $semester, $course, $description, $imagePath);
        $stmt->execute();
    }
}

// Get seller's books
$book_stmt = $conn->prepare("SELECT * FROM books WHERE seller_id = ?");
$book_stmt->bind_param("i", $seller_id);
$book_stmt->execute();
$books_result = $book_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Seller Page</title>
    <style>
        body {
            margin: 0;
            font-family: Arial;
            background-color: #121212;
            color: white;
            display: flex;
        }

        .nav-buttons {
            width: 80px;
            background-color: #1a1a1a;
            padding: 20px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            border-right: 2px solid #2a2a2a;
        }

        .nav-btn {
            text-decoration: none;
            color: white;
            background-color: #333;
            border: 1px solid #444;
            padding: 10px 5px;
            border-radius: 10px;
            width: 100%;
            text-align: center;
            font-size: 16px;
            transition: background-color 0.2s ease;
        }

        .nav-btn:hover {
            background-color: #555;
            cursor: pointer;
        }

        .left {
            width: 65%;
            padding: 20px;
        }

        .left h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .card {
            position: relative;
            width: 300px;
            background-color: #1c1c1c;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(255,255,255,0.05);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background-image: url('book_images/delete.png');
            background-size: cover;
            cursor: pointer;
        }

        .right {
            width: 30%;
            padding: 20px;
            margin: 10px;
            background-color: #1e1e1e;
            box-shadow: -2px 0 10px rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        form input, form select, form textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: none;
            border-radius: 5px;
            background-color: #2a2a2a;
            color: white;
        }

        .radio-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ccc;
        }

        #addBtn {
            width: 100%;
            padding: 10px;
            background-color: red;
            border: none;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            cursor: not-allowed;
        }

        #addBtn.enabled {
            background-color: #00cc66;
            cursor: pointer;
        }

        #formTitleBtn {
            background-color: #333;
            color: white;
            padding: 10px;
            border: none;
            font-size: 18px;
            margin-bottom: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="nav-buttons">
    <a href="seller_account.php" class="nav-btn">👤<br>Account</a>
    <a href="seller_money.php" class="nav-btn">💰<br>Money</a>
</div>

<div class="left">
    <h1>My Books</h1>
    <div class="cards" id="cardContainer">
        <?php while ($book = $books_result->fetch_assoc()): ?>
            <div class="card" data-id="<?= $book['book_id'] ?>" onclick='editBook(<?= json_encode($book) ?>)'>
                <div class="delete-btn" onclick="deleteBook(event, <?= $book['book_id'] ?>)"></div>
                <img src="book_images/<?= htmlspecialchars($book['image']) ?>" alt="Book image">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                <p><strong>Price:</strong> Rs.<?= $book['price'] ?></p>
                <p><strong>Condition:</strong> <?= $book['book_condition'] ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="right">
    <button id="formTitleBtn" onclick="clearForm()">Add New Book</button>
    <form method="POST" enctype="multipart/form-data" id="bookForm">
        <input type="hidden" name="book_id" id="book_id">
        <input type="text" name="title" id="title" placeholder="Title" required>
        <input type="text" name="author" id="author" placeholder="Author" required>
        <input type="number" step="0.01" name="price" id="price" placeholder="Price" required>
        <div class="radio-group">
            <span>Condition:</span>
            <label><input type="radio" name="book_condition" value="new" required> New</label>
            <label><input type="radio" name="book_condition" value="used"> Used</label>
        </div>

        <select name="subject" id="subject" required>
            <option value="">Select Subject</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Science">Science</option>
            <option value="English">English</option>
            <option value="Computer">Computer</option>
            <option value="Social">Social</option>
        </select>
        <select name="semester" id="semester" required>
            <option value="">Select Semester</option>
            <?php for ($i = 1; $i <= 8; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="course" id="course" required>
            <option value="">Select Course</option>
            <option value="BCA">BCA</option>
            <option value="BBA">BBA</option>
            <option value="BIM">BIM</option>
            <option value="BHM">BHM</option>
            <option value="BBM">BBM</option>
        </select>
        <textarea name="description" id="description" placeholder="Description" rows="4" required></textarea>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.jfif">
        <button type="submit" name="add_book" id="addBtn">Add Book</button>
    </form>
</div>

<script>
    const form = document.getElementById("bookForm");
    const btn = document.getElementById("addBtn");
    const formTitleBtn = document.getElementById("formTitleBtn");

    function clearForm() {
        form.reset();
        document.getElementById('book_id').value = '';
        btn.textContent = "Add Book";
        formTitleBtn.textContent = "Add New Book";
        btn.classList.remove("enabled");
        btn.disabled = true;
        btn.style.cursor = "not-allowed";
    }

    function editBook(book) {
        document.getElementById("book_id").value = book.book_id;
        document.getElementById("title").value = book.title;
        document.getElementById("author").value = book.author;
        document.getElementById("price").value = book.price;
        document.getElementById("subject").value = book.subject;
        document.getElementById("semester").value = book.semester;
        document.getElementById("course").value = book.course;
        document.getElementById("description").value = book.description;
        document.querySelectorAll("input[name='book_condition']").forEach(el => {
            el.checked = (el.value === book.book_condition);
        });
        btn.textContent = "Update Book";
        formTitleBtn.textContent = "Editing: " + book.title;
        btn.classList.add("enabled");
        btn.disabled = false;
        btn.style.cursor = "pointer";
    }

    form.addEventListener("input", () => {
        const title = document.getElementById("title").value.trim();
        const author = document.getElementById("author").value.trim();
        const price = document.getElementById("price").value.trim();
        const subject = document.getElementById("subject").value;
        const semester = document.getElementById("semester").value;
        const course = document.getElementById("course").value;
        const description = document.getElementById("description").value.trim();
        const conditionSelected = document.querySelector("input[name='book_condition']:checked");

        const isFilled = title && author && price && subject && semester && course && description && conditionSelected;

        if (isFilled) {
            btn.classList.add("enabled");
            btn.disabled = false;
            btn.style.cursor = "pointer";
        } else {
            btn.classList.remove("enabled");
            btn.disabled = true;
            btn.style.cursor = "not-allowed";
        }
    });

    window.addEventListener("load", () => {
        form.dispatchEvent(new Event("input"));
    });

    function deleteBook(e, bookId) {
        e.stopPropagation();
        if (confirm("Are you sure you want to delete this book?")) {
            fetch('delete_book.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'book_id=' + bookId
            })
            .then(res => res.text())
            .then(response => {
                if (response.trim() === 'success') {
                    document.querySelector(`.card[data-id='${bookId}']`).remove();
                } else {
                    alert('Error deleting book.');
                }
            });
        }
    }
</script>

</body>
</html>
