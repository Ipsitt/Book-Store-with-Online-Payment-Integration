<?php
include 'db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "No book ID provided.";
    exit;
}

$book_id = $_GET['id'];

// Replace this with actual buyer_id from your login system
$buyer_id = $_SESSION['buyer_id'] ?? 1; // Defaulting to 1 for testing

// If Add to Cart is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $check_stmt = $conn->prepare("SELECT * FROM cart_items WHERE buyer_id = ? AND book_id = ?");
    $check_stmt->bind_param("ii", $buyer_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO cart_items (buyer_id, book_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $buyer_id, $book_id);
        $stmt->execute();
        echo "<script>alert('Book added to cart!'); window.location.href='book-detail.php?id=$book_id';</script>";
        exit;
    } else {
        echo "<script>alert('Book is already in cart.'); window.location.href='book-detail.php?id=$book_id';</script>";
        exit;
    }
}

// Fetch book info
$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Book not found.";
    exit;
}

$book = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Book Details</title>
  <style>
    body {
      margin: 0;
      padding: 20px;
      background-color: #111;
      color: white;
      font-family: Arial, sans-serif;
    }

    .container {
      max-width: 800px;
      margin: auto;
      background-color: #1c1c1c;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    .book-header {
      display: flex;
      flex-direction: row;
      gap: 20px;
    }

    .book-header img {
      height: 300px;
      border-radius: 8px;
    }

    .book-info {
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .book-info h1 {
      margin: 0 0 10px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .add-cart-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
    }

    .add-cart-btn img {
      width: 28px;
      height: 28px;
      vertical-align: middle;
    }

    .price {
      color: #00ff88;
      font-size: 20px;
      font-weight: bold;
      margin-top: 10px;
    }

    .details p {
      margin: 6px 0;
    }

    .description {
      margin-top: 20px;
      line-height: 1.6;
    }

    a.back-link {
      display: inline-block;
      margin-top: 20px;
      color: #ccc;
      text-decoration: none;
    }

    a.back-link:hover {
      text-decoration: underline;
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Add to Cart click event
    $(document).ready(function() {
      $(".add-to-cart-form").submit(function(e) {
        e.preventDefault(); 

        var bookId = $("input[name='book_id']").val(); // Get the book ID
        $.ajax({
          type: "POST",
          url: "book-detail.php?id=" + bookId, // The current page (itself) for processing
          data: { book_id: bookId }, // Send the book ID to add to the cart
          success: function(response) {
          
            alert("Book added to cart!");
          },
          error: function(xhr, status, error) {
            alert("An error occurred. Please try again.");
          }
        });
      });
    });
  </script>
</head>
<body>

  <div class="container">
    <div class="book-header">
      <img src="book_images/<?php echo htmlspecialchars($book['image']); ?>" alt="Book Cover" />
      <div class="book-info">
        <h1>
          <?php echo htmlspecialchars($book['title']); ?>
          <form class="add-to-cart-form" method="post" action="book-detail.php?id=<?php echo $book['book_id']; ?>" style="display:inline;">
            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
            <button class="add-cart-btn" type="submit" title="Add to Cart">
              <img src="book_images/cart.png" alt="Cart" />
            </button>
          </form>
        </h1>
        <div class="price">Price: Rs. <?php echo number_format($book['price'], 2); ?></div>

        <div class="details">
          <p><br><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
          <p><strong>Condition:</strong> <?php echo htmlspecialchars($book['book_condition']); ?></p>
          <p><strong>Subject:</strong> <?php echo htmlspecialchars($book['subject']); ?></p>
          <p><strong>Semester:</strong> <?php echo htmlspecialchars($book['semester']); ?></p>
          <p><strong>Status:</strong> <?php echo htmlspecialchars($book['status']); ?></p>
        </div>
      </div>
    </div>

    <div class="description">
      <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
    </div>

    <a href="index.php" class="back-link">← Back to book list</a>
  </div>

</body>
</html>
