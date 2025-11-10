<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExamResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $studentName;
    public $examName;
    public $marksObtained;
    public $totalMarks;
    public $percentage;
    public $passFail;
    public $answerSheetContent;
    public $attemptNumber;
    public $submissionDate;

    public $exam;
    public $student;
    public $submission;
    public $questions;
    public $answerRows;
    public $studentAnswers;

    public function __construct(array $mailData)
    {
        $this->studentName = $mailData['studentName'];
        $this->examName = $mailData['examName'];
        $this->marksObtained = $mailData['marksObtained'];
        $this->totalMarks = $mailData['totalMarks'];
        $this->answerSheetContent = $mailData['answerSheet'];
        $this->percentage = $mailData['percentage'] ?? null;
        $this->passFail = $mailData['passFail'] ?? null;
        $this->attemptNumber = $mailData['attemptNumber'] ?? null;
        $this->submissionDate = $mailData['submissionDate'] ?? null;

        // Assign the rest (used in view)
        $this->exam = $mailData['exam'];
        $this->student = $mailData['student'];
        $this->submission = $mailData['submission'];
        $this->questions = $mailData['questions'];
        $this->answerRows = $mailData['answerRows'];
        $this->studentAnswers = $mailData['studentAnswers'];
    }

    public function build()
    {
        return $this->subject("Your Exam Result: {$this->examName}")
                    ->view('emails.exam_result')
                    ->with([
                        'exam' => $this->exam,
                        'student' => $this->student,
                        'submission' => $this->submission,
                        'questions' => $this->questions,
                        'answerRows' => $this->answerRows,
                        'studentAnswers' => $this->studentAnswers,
                        'currentDate' => now()->format('F j, Y'),
                        'currentTimestamp' => now()->format('F j, Y, g:i a'),
                        'totalMarks' => $this->totalMarks,
                        'percentage' => $this->percentage,
                        'passFail' => $this->passFail,
                        'attemptNumber' => $this->attemptNumber,
                        'submissionDate' => $this->submissionDate
                    ]);
    }
}
