<?php
declare(strict_types=1);

return [
    'version' => '004_learning_content_metadata',
    'up' => static function (PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS course_learning_outcomes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, course_id BIGINT UNSIGNED NOT NULL, body VARCHAR(1000) NOT NULL, position INT NOT NULL, UNIQUE(course_id, position), FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS course_prerequisites (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, course_id BIGINT UNSIGNED NOT NULL, body VARCHAR(1000) NOT NULL, position INT NOT NULL, UNIQUE(course_id, position), FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS content_references (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, course_id BIGINT UNSIGNED NOT NULL, lesson_id BIGINT UNSIGNED NULL, title VARCHAR(500) NOT NULL, url VARCHAR(1500) NULL, source_type VARCHAR(30) NOT NULL DEFAULT 'external', notes VARCHAR(1500) NULL, position INT NOT NULL DEFAULT 0, FOREIGN KEY(course_id) REFERENCES courses(id) ON DELETE CASCADE, FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE CASCADE) ENGINE=InnoDB");
    },
];
