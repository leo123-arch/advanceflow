<?php

function classifyDocumentText($text, $filename = "") {

    $text = strtolower($text);
    $filename = strtolower($filename);

    // -------------------------------
    // 1) Regular Expression Patterns
    // -------------------------------
    $patterns = [
        "Research Paper" => [
            "/abstract/",
            "/introduction/",
            "/methodology/",
            "/doi/",
            "/issn/",
            "/references/",
            "/journal/",
        ],
        "Book / Chapter" => [
            "/isbn/",
            "/publisher/",
            "/chapter/",
            "/edited by/",
            "/book title/",
            "/edition/",
        ],
        "Conference Presentation" => [
            "/conference/",
            "/symposium/",
            "/workshop/",
            "/proceedings/",
            "/presentation/",
            "/certificate of participation/",
        ],
        "Patent" => [
            "/patent/",
            "/patent number/",
            "/application number/",
            "/wipo/",
            "/intellectual property/",
            "/granted/",
        ],
        "Research Project" => [
            "/project/",
            "/funded/",
            "/grant/",
            "/principal investigator/",
            "/research proposal/",
            "/ugc/",
            "/dst/",
            "/icmr/",
        ]
    ];

    // -------------------------------
    // 2) Scoring System
    // -------------------------------
    $scores = [
        "Research Paper" => 0,
        "Book / Chapter" => 0,
        "Conference Presentation" => 0,
        "Patent" => 0,
        "Research Project" => 0
    ];

    // Pattern scoring
    foreach ($patterns as $category => $regs) {
        foreach ($regs as $reg) {
            if (preg_match($reg, $text)) {
                $scores[$category] += 3;   // regex hits = stronger signal
            }
        }
    }

    // -------------------------------
    // 3) Filename-based hints
    // -------------------------------
    if (strpos($filename, "paper") !== false) $scores["Research Paper"] += 2;
    if (strpos($filename, "book") !== false) $scores["Book / Chapter"] += 2;
    if (strpos($filename, "chapter") !== false) $scores["Book / Chapter"] += 2;
    if (strpos($filename, "conference") !== false) $scores["Conference Presentation"] += 2;
    if (strpos($filename, "patent") !== false) $scores["Patent"] += 2;
    if (strpos($filename, "project") !== false) $scores["Research Project"] += 2;

    // -------------------------------
    // 4) Phrase detection (High Weight)
    // -------------------------------
    $phrases = [
        "Research Paper" => ["research article", "peer reviewed", "scientific paper"],
        "Book / Chapter" => ["book chapter", "textbook"],
        "Conference Presentation" => ["conference certificate", "oral presentation", "paper presented"],
        "Patent" => ["patent filed", "patent granted", "intellectual property rights"],
        "Research Project" => ["research grant", "project report", "proposal"]
    ];

    foreach ($phrases as $category => $keys) {
        foreach ($keys as $k) {
            if (strpos($text, $k) !== false) {
                $scores[$category] += 5;  // strong indicator
            }
        }
    }

    // -------------------------------
    // 5) Detect structured patterns
    // -------------------------------

    // ISBN detection
    if (preg_match("/isbn[:\- ]?([0-9\-]+)/", $text)) {
        $scores["Book / Chapter"] += 8;
    }

    // Patent Number detection (IN123456)
    if (preg_match("/\b(in)?\s?\d{6,}\b/", $text)) {
        $scores["Patent"] += 8;
    }

    // DOI or ISSN → strong journal indicator
    if (preg_match("/doi[:]/", $text)) {
        $scores["Research Paper"] += 5;
    }
    if (preg_match("/issn[:]/", $text)) {
        $scores["Research Paper"] += 5;
    }

    // Funding agency detection for projects
    $fundingAgencies = ["dst", "icmr", "ugc", "serb", "world bank", "csir"];
    foreach ($fundingAgencies as $fund) {
        if (strpos($text, $fund) !== false) {
            $scores["Research Project"] += 5;
        }
    }

    // -------------------------------
    // 6) FINAL DECISION
    // -------------------------------
    arsort($scores);
    $bestCategory = array_key_first($scores);
    $bestScore = current($scores);

    // If everything is zero → Unknown
    if ($bestScore < 3) {
        return "Unknown Document Type";
    }

    return $bestCategory;
}
