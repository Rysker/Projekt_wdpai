<?php

class Dog {
    private string $name;
    public $breed;
    public $description;
    public $color;
    public $photoUrl;

    public function __construct($name, $breed, $description, $color, $photoUrl) {
        $this->name = $name;
        $this->breed = $breed;
        $this->description = $description;
        $this->color = $color;
        $this->photoUrl = $photoUrl;
    }

    // Getter and setter methods (optional)
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getBreed() {
        return $this->breed;
    }

    public function setBreed($breed) {
        $this->breed = $breed;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getPhotoUrl() {
        return $this->photoUrl;
    }

    public function setPhotoUrl($photoUrl) {
        $this->photoUrl = $photoUrl;
    }
}
