<?php

function classifyDocumentText($text) {

    $text = strtolower($text); // normalize

    // Keyword sets for each category
    $categories = [
        "Research Paper" => ["abstract", "issn", "introduction", "methodology", "results", "journal", "research article"],
        "Book / Chapter" => ["isbn", "publisher", "chapter", "edited book", "book title"],
        "Conference Presentation" => ["conference", "proceedings", "presentation", "seminar", "symposium", "workshop"],
        "Patent" => ["patent", "granted", "patent number", "intellectual property", "ipo", "wipo"],
        "Research Project" => ["funded", "research project", "grant", "principal investigator", "project summary"]
    ];

    $scores = [];

    foreach($categories as $category => $keywords){
        $score = 0;
        foreach($keywords as $key){
            if(strpos($text, $key) !== false){
                $score += 1;
            }
        }
        $scores[$category] = $score;
    }

    // Pick best category
    arsort($scores);
    $best = array_key_first($scores);

    return $best;
}
