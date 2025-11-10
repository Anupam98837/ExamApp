<!DOCTYPE html>
<html>
<head>
    <title>Answer Sheet</title>
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
    <div class="container">
        <!-- Header section -->
        <div class="header">
            <h1><?= e($exam->examName ?? 'Exam Results') ?></h1>
        </div>

        <!-- Student info -->
        <div class="student-info">
            <p>Name: <?= e($student->name ?? '') ?></p>
            <!-- Other student details -->
        </div>

        <!-- Questions and answers -->
        <?php foreach ($questions as $question): ?>
            <div class="question">
                <h3>Q<?= $question->question_order ?>: <?= e($question->question_title) ?></h3>
                <!-- Answer display logic -->
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>