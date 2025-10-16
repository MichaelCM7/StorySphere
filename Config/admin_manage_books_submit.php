<?php
  require "../Config/constants.php";
  require "../Config/dbconnection.php";

  header('Content-Type: text/plain'); 

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      // If not a POST request, redirect back to the book management page
      header("Location: ../Pages/admin_manage_books.php");
      exit();
  }

  echo "<pre>";
  print_r($_POST);
  echo "</pre>";

  $title = trim($_POST['title'] ?? '');
  $author_name = trim($_POST['author'] ?? '');
  $isbn = trim($_POST['isbn'] ?? '');
  $category_id = intval($_POST['category'] ?? 0); 
  $copies = intval($_POST['number'] ?? 0);

  // Ensure required fields are not empty
  if (empty($title) || empty($author_name) || empty($isbn) || $category_id <= 0 || $copies <= 0 || !isset($connection) || $connection->connect_error) {
      error_log("Invalid book submission data or database connection error.");
      // Optionally set a session message here to inform the user
      // $_SESSION['error'] = "Missing or invalid submission data.";
      header("Location: ../Pages/admin_manage_books.php");
      exit();
  }

  $author_id = null;

  $connection->begin_transaction();

  try {
      // Check if the author already exists (case-insensitive)
      $stmt_check_author = $connection->prepare("SELECT author_id FROM authors WHERE LOWER(author_name) = LOWER(?)");
      if (!$stmt_check_author) {
          throw new Exception("Prepare statement failed: " . $connection->error);
      }

      $stmt_check_author->bind_param("s", $author_name);
      $stmt_check_author->execute();
      $result = $stmt_check_author->get_result();
      
      if ($row = $result->fetch_assoc()) {
          // Author found, use existing ID
          $author_id = $row['author_id'];
      } else {
          // Author not found, insert new author
          $stmt_insert_author = $connection->prepare("INSERT INTO authors (author_name) VALUES (?)");
          if (!$stmt_insert_author) {
              throw new Exception("Prepare statement failed: " . $connection->error);
          }
          $stmt_insert_author->bind_param("s", $author_name);
          $stmt_insert_author->execute();
          
          // Get the ID of the newly inserted author
          $author_id = $connection->insert_id;
      }

      // Ensure we have a valid author ID before proceeding
      if (!$author_id) {
          throw new Exception("Failed to retrieve or create author ID.");
      }

      // Insert the book using the retrieved author_id
      $stmt_insert_book = $connection->prepare("
          INSERT INTO books 
              (title, author_id, isbn, category_id, total_copies, available_copies) 
          VALUES 
              (?, ?, ?, ?, ?, ?)
      ");
      
      if (!$stmt_insert_book) {
          throw new Exception("Prepare statement failed: " . $connection->error);
      }
      
      // total_copies and available_copies are both set to $copies
      $stmt_insert_book->bind_param("sisiii", $title, $author_id, $isbn, $category_id, $copies, $copies);
      $stmt_insert_book->execute();

      // If everything succeeded, commit the transaction
      $connection->commit();

      // Optionally set a success session message
      // $_SESSION['success'] = "Book '{$title}' added successfully!";

  } catch (Exception $e) {
      // An error occurred, rollback the transaction
      $connection->rollback();
      error_log("Book submission failed: " . $e->getMessage());
      // Optionally set an error session message
      // $_SESSION['error'] = "An error occurred while adding the book.";
  }

  $connection->close();
  header("Location: ../Pages/admin_manage_books.php");
  exit();
?>