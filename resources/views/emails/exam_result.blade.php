<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result - {{ $exam->examName }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #2c3e50;
            margin-bottom: 25px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .info-box {
            flex: 1;
            min-width: 250px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 0 10px 10px 0;
        }
        .score-summary {
            display: flex;
            justify-content: space-around;
            background: #f1f8ff;
            border: 1px solid #c0d6e4;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .score-item {
            text-align: center;
            padding: 10px;
        }
        .score-value {
            font-size: 24px;
            font-weight: bold;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .question {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .answer {
            margin-top: 10px;
            padding: 10px;
            border-radius: 3px;
        }
        .correct {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        .wrong {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">Exam Result</div>
        <h2>{{ $exam->examName }}</h2>
    </div>

    <div class="student-info">
        <div class="info-box">
            <div><strong>Student Name:</strong> {{ $student->name ?? 'N/A' }}</div>
            <div><strong>Email:</strong> {{ $student->email ?? 'N/A' }}</div>
        </div>
        <div class="info-box">
            <div><strong>Exam ID:</strong> #{{ $exam->id }}</div>
            <div><strong>Attempt:</strong> #{{ $submission->total_attempts }}</div>
            <div><strong>Date:</strong> {{ $currentDate }}</div>
        </div>
    </div>

    <div class="score-summary">
        <div class="score-item">
            <div class="score-value">{{ $submission->marks_obtained }}/{{ $totalMarks }}</div>
            <div>Score</div>
        </div>
        <div class="score-item">
            <div class="score-value">{{ round(($submission->marks_obtained/$totalMarks)*100) }}%</div>
            <div>Percentage</div>
        </div>
        <div class="score-item">
            <div class="score-value">{{ ($submission->marks_obtained/$totalMarks)*100 >= 60 ? 'PASS' : 'FAIL' }}</div>
            <div>Result</div>
        </div>
    </div>

    <div class="section-title">Detailed Results</div>

    @foreach($questions as $question)
        @php
            $studentAnswer = collect($studentAnswers)
                ->firstWhere('question_id', $question->id);
            
            $correctAnswer = $answerRows[$question->id]
                ->where('is_correct', 1)
                ->first();
            
            $isCorrect = false;
            $answerStatus = 'skipped';
            $studentResponse = 'Not attempted';
            
            if ($studentAnswer && isset($studentAnswer['selected'])) {
                if ($question->question_type === 'mcq') {
                    $isCorrect = $studentAnswer['selected'] == $correctAnswer->id;
                } elseif ($question->question_type === 'true_false') {
                    $isCorrect = $studentAnswer['selected'] == $correctAnswer->id;
                } else {
                    $isCorrect = strtolower(trim($studentAnswer['selected'])) == strtolower(trim($correctAnswer->answer_title));
                }
                
                $answerStatus = $isCorrect ? 'correct' : 'wrong';
                $studentResponse = $studentAnswer['selected'];
            }
        @endphp

        <div class="question">
            <div class="question-header">
                <div><strong>Q{{ $question->question_order }}.</strong></div>
                <div>{{ $question->question_title }}</div>
                <div>{{ $question->question_mark }} marks</div>
            </div>
            <div class="answer {{ $answerStatus }}">
                <div>
                    <strong>Your Answer:</strong> 
                    {{ $studentResponse ?? 'Not attempted' }}
                </div>
                @if(!$isCorrect && $answerStatus !== 'skipped')
                    <div>
                        <strong>Correct Answer:</strong> 
                        {{ $correctAnswer->answer_title ?? $correctAnswer->answer_two_gap_match }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="footer">
        <p>This is an automatically generated exam result. Please contact your instructor if you have questions.</p>
        <p>Generated on {{ $currentTimestamp }}</p>
    </div>
</body>
</html>