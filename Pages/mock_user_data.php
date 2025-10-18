<?php
// Mock data for testing

$mock_user = [
  "name" => "Sarah Johnson",
  "borrowed_books" => 5,
  "pending_returns" => 2,
  "fines" => 12.50,
  "books_read" => 23,
];

$recent_activity = [
  ["action" => "Borrowed", "book" => "The Great Gatsby", "time" => "2 days ago"],
  ["action" => "Returned", "book" => "To Kill a Mockingbird", "time" => "5 days ago"],
  ["action" => "Reserved", "book" => "1984", "time" => "1 week ago"],
];

$books = [
  ["title" => "The Great Gatsby", "author" => "F. Scott Fitzgerald", "category" => "Classic Literature", "status" => "Available", "image" => "gatsby.jpg"],
  ["title" => "To Kill a Mockingbird", "author" => "Harper Lee", "category" => "Fiction", "status" => "Borrowed", "image" => "mockingbird.jpg"],
  ["title" => "1984", "author" => "George Orwell", "category" => "Dystopian", "status" => "Available", "image" => "1984.jpg"],
  ["title" => "Pride and Prejudice", "author" => "Jane Austen", "category" => "Romance", "status" => "Available", "image" => "pride.jpg"],
];

$borrowed_books = [
  ["title" => "The Great Gatsby", "author" => "F. Scott Fitzgerald", "due" => "2024-01-20", "status" => "Active"],
  ["title" => "To Kill a Mockingbird", "author" => "Harper Lee", "due" => "2024-01-24", "status" => "Due Soon"],
  ["title" => "1984", "author" => "George Orwell", "due" => "2024-01-15", "status" => "Overdue"],
  ["title" => "Pride and Prejudice", "author" => "Jane Austen", "due" => "2024-02-08", "status" => "Active"],
  ["title" => "Brave New World", "author" => "Aldous Huxley", "due" => "2024-02-10", "status" => "Active"],
];
?>