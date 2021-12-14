<?php

namespace App\SearchClass;

use App\Entity\Category;

class Search

{
    /**
     * @var string
     */
    public string $string = "";

    /**
     * @var Category[]
     */
    public array $categories = [];
}