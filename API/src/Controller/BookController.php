<?php 
// src/Controller/BookController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;

class BookController extends AbstractController
{
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    #[Route('/books', name: 'get_all_books', methods: ['GET'])]
public function getAll(): Response {
    $sql = "SELECT * FROM books";
    try {
        $books = $this->dbConnection->fetchAllAssociative($sql);
        return $this->json($books);
    } catch (\Doctrine\DBAL\Exception $e) {
        return new Response('Error fetching books: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}


    #[Route('/books', name: 'create_book', methods: ['POST'])]
    public function create(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'];
        $author = $data['author'];

        $sql = "INSERT INTO books (title, author) VALUES (?, ?)";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindValue(1, $title);
        $stmt->bindValue(2, $author);

        try {
            $stmt->executeStatement();
            return new Response('Book created successfully.', Response::HTTP_CREATED);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new Response('Error creating book: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/books/{id}', name: 'read_book', methods: ['GET'])]
public function get($id): Response {
    $sql = "SELECT * FROM books WHERE id = ?";
    try {
        $book = $this->dbConnection->fetchAssociative($sql, [$id]);
        if ($book) {
            return $this->json($book);
        } else {
            return new Response('Book not found.', Response::HTTP_NOT_FOUND);
        }
    } catch (\Doctrine\DBAL\Exception $e) {
        return new Response('Error reading book: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}

#[Route('/books/{id}', name: 'update_book', methods: ['PUT'])]
public function update($id, Request $request): Response {
    $data = json_decode($request->getContent(), true);
    $sql = "UPDATE books SET title = ?, author = ? WHERE id = ?";
    try {
        $this->dbConnection->executeStatement($sql, [$data['title'], $data['author'], $id]);
        if ($this->dbConnection->rowCount() > 0) {
            return new Response('Book updated successfully.', Response::HTTP_OK);
        } else {
            return new Response('Book not found.', Response::HTTP_NOT_FOUND);
        }
    } catch (\Doctrine\DBAL\Exception $e) {
        return new Response('Error updating book: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}

#[Route('/books/{id}', name: 'delete_book', methods: ['DELETE'])]
public function delete($id): Response {
    $sql = "DELETE FROM books WHERE id = ?";
    try {
        $result = $this->dbConnection->executeStatement($sql, [$id]);
        if ($result > 0) {
            return new Response('Book deleted successfully.', Response::HTTP_OK);
        } else {
            return new Response('Book not found.', Response::HTTP_NOT_FOUND);
        }
    } catch (\Doctrine\DBAL\Exception $e) {
        return new Response('Error deleting book: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}


}
