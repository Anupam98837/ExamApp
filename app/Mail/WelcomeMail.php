<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Part\DataPart;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $msg;
    public string $sub;
    public ?string $attachmentContent;
    public ?string $attachmentName;

    public function __construct(string $msg, string $sub, ?string $attachmentContent = null, ?string $attachmentName = null)
    {
        $this->msg = $msg;
        $this->sub = $sub;
        $this->attachmentContent = $attachmentContent;
        $this->attachmentName = $attachmentName;
    }

    public function build()
    {
        $mail = $this->subject($this->sub)
                    ->html($this->msg);

        if ($this->attachmentContent && $this->attachmentName) {
            // Create a temporary file for the attachment
            $tempPath = tempnam(sys_get_temp_dir(), 'exam_');
            file_put_contents($tempPath, $this->attachmentContent);
            
            // Attach the file with proper headers
            $mail->attach($tempPath, [
                'as' => $this->attachmentName,
                'mime' => 'text/html',
                'Content-Disposition' => 'attachment'
            ]);
            
            // Note: In production, you might want to clean up this temp file later
        }

        return $mail;
    }
}