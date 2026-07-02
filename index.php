<?php
// index.php
include 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book Grid</title>
  <style>
    body {
      margin: 0;
      background-color: #111;
      font-family: Arial, sans-serif;
      color: white;
      overflow-y: auto;
    }

    .navbar {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      background-color: #1c1c1c;
      padding: 10px 20px;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar select,
    .navbar .cart-button,
    .navbar .user-button {
      margin-left: 15px;
      padding: 5px 10px;
      background-color: #333;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .navbar .cart-button img {
      width: 24px;
      height: 24px;
      vertical-align: middle;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 60px 20px;
      padding: 40px;
      justify-items: center;
    }

    .card-link {
      text-decoration: none;
      color: inherit;
    }

    .card {
      display: flex;
      width: 300px;
      height: 220px;
      background-color: #d3d3d3;
      border-radius: 8px;
      overflow: hidden;
      color: #fff;
      transition: transform 0.2s;
    }

    .card:hover {
      transform: scale(1.03);
    }

    .card img {
      width: 150px;
      height: 100%;
      object-fit: cover;
    }

    .book-info {
      padding: 10px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-color: #111;
      width: 100%;
    }

    .book-info h4 {
      margin: 0 0 10px 0;
      font-size: 16px;
      color: #fff;
    }

    .book-info p {
      margin: 0;
      font-size: 14px;
      color: #ccc;
    }
  </style>
</head>
<body>

  <div class="navbar">
    <select id="courseDropdown">
      <option value="">Course</option>
      <option value="bca">BCA</option>
      <option value="bba">BBA</option>
      <option value="bim">BIM</option>
      <option value="bhm">BHM</option>
      <option value="bbm">BBM</option>
    </select>

    <select id="subjectDropdown">
      <option value="">Subject</option>
      <option value="mathematics">Mathematics</option>
      <option value="science">Science</option>
      <option value="english">English</option>
      <option value="computer">Computer</option>
      <option value="social">Social Studies</option>
    </select>

    <!-- Cart Button -->
    <button class="cart-button" onclick="location.href='cart.php'">
      <img src="book_images/cart.png" alt="Cart" />
    </button>

    <!-- History Button -->
    <button class="user-button" onclick="location.href='history.php'">
    🕙
    </button>

    <!-- User Icon -->
    <button class="user-button" onclick="location.href='account.php'">
      👤
    </button>
  </div>

  <div class="grid" id="book-container">
    <!-- Books are be dynamically -->
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const courseDropdown = document.getElementById("courseDropdown");
      const subjectDropdown = document.getElementById("subjectDropdown");
      const bookContainer = document.getElementById("book-container");

      function fetchBooks(course = "", subject = "") {
        const query = new URLSearchParams({ course, subject }).toString();
        fetch(`fetch_books.php?${query}`)
          .then(response => response.json())
          .then(data => {
            bookContainer.innerHTML = ""; // Clear previous books
            data.forEach(book => {
              const cardLink = document.createElement("a");
              cardLink.href = `book-detail.php?id=${book.book_id}`;
              cardLink.classList.add("card-link");

              cardLink.innerHTML = `
                <div class="card">
                  <img src="book_images/${book.image}" alt="${book.title}" />
                  <div class="book-info">
                    <h4>${book.title}</h4>
                    <p>Price: Rs. ${book.price}</p>
                  </div>
                </div>
              `;
              bookContainer.appendChild(cardLink);
            });
          })
          .catch(err => console.error("Failed to fetch books:", err));
      }

      // Initial load
      fetchBooks();

      // Trigger fetch on dropdown change
      courseDropdown.addEventListener("change", () => {
        fetchBooks(courseDropdown.value, subjectDropdown.value);
      });

      subjectDropdown.addEventListener("change", () => {
        fetchBooks(courseDropdown.value, subjectDropdown.value);
      });
    });
  </script>

</body>
</html>
