<?php

class Document {
    public $id;
    public $title;
    public $date;
    public $category;
    public $filepath;

    public function __construct($id = null, $title = '', $date = '', $category = '', $filepath = '') {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->category = $category;
        $this->filepath = $filepath;
    }

    // Placeholder function to simulate saving a Document
    public function save() {
        // In a real application, save to the database
        return true;
    }

    // Placeholder function to simulate fetching documents
    public static function getAll() {
        // In a real application, return documents from the database
        return [];
    }
}
