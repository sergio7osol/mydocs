<?php

class Category {
    public $id;
    public $name;

    public function __construct($id = null, $name = '') {
        $this->id = $id;
        $this->name = $name;
    }

    // Placeholder function to simulate saving a Category
    public function save() {
        // In a real application, save to the database
        return true;
    }

    // Placeholder function to simulate fetching all categories
    public static function getAll() {
        // In a real application, return categories from the database
        return [];
    }
}
