<?php

declare(strict_types=1);

namespace IndoWater\Api\Services;

use IndoWater\Api\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendEmailVerification(User $user, string $token): bool
    {
        $subject = 'Verify Your Email Address - IndoWater';
        $verificationUrl = $this->config['app_url'] . '/api/auth/verify-email/' . $token;
        
        $body = $this->getEmailTemplate('email-verification', [
            'name' => $user->getName(),
            'verification_url' => $verificationUrl,
        ]);

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        $subject = 'Reset Your Password - IndoWater';
        $resetUrl = $this->config['frontend_url'] . '/reset-password?token=' . $token;
        
        $body = $this->getEmailTemplate('password-reset', [
            'name' => $user->getName(),
            'reset_url' => $resetUrl,
        ]);

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    public function sendWelcomeEmail(User $user): bool
    {
        $subject = 'Welcome to IndoWater';
        
        $body = $this->getEmailTemplate('welcome', [
            'name' => $user->getName(),
            'login_url' => $this->config['frontend_url'] . '/login',
        ]);

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    public function sendPaymentConfirmation(User $user, array $paymentData): bool
    {
        $subject = 'Payment Confirmation - IndoWater';
        
        $body = $this->getEmailTemplate('payment-confirmation', [
            'name' => $user->getName(),
            'amount' => $paymentData['amount'],
            'transaction_id' => $paymentData['transaction_id'],
            'payment_date' => $paymentData['payment_date'],
        ]);

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    public function sendLowCreditAlert(User $user, array $meterData): bool
    {
        $subject = 'Low Credit Alert - IndoWater';
        
        $body = $this->getEmailTemplate('low-credit-alert', [
            'name' => $user->getName(),
            'meter_id' => $meterData['meter_id'],
            'current_credit' => $meterData['current_credit'],
            'topup_url' => $this->config['frontend_url'] . '/topup',
        ]);

        return $this->sendEmail($user->getEmail(), $subject, $body);
    }

    private function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['mail_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['mail_username'];
            $mail->Password = $this->config['mail_password'];
            $mail->SMTPSecure = $this->config['mail_encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['mail_port'];

            // Recipients
            $mail->setFrom($this->config['mail_from_address'], $this->config['mail_from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function getEmailTemplate(string $template, array $variables = []): string
    {
        // Simple template system - in production, you might use Twig or similar
        $templates = [
            'email-verification' => '
                <h2>Verify Your Email Address</h2>
                <p>Hello {{name}},</p>
                <p>Thank you for registering with IndoWater. Please click the link below to verify your email address:</p>
                <p><a href="{{verification_url}}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Verify Email</a></p>
                <p>If you did not create an account, please ignore this email.</p>
                <p>Best regards,<br>IndoWater Team</p>
            ',
            'password-reset' => '
                <h2>Reset Your Password</h2>
                <p>Hello {{name}},</p>
                <p>You have requested to reset your password. Please click the link below to reset your password:</p>
                <p><a href="{{reset_url}}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Best regards,<br>IndoWater Team</p>
            ',
            'welcome' => '
                <h2>Welcome to IndoWater</h2>
                <p>Hello {{name}},</p>
                <p>Welcome to IndoWater! Your account has been successfully created.</p>
                <p><a href="{{login_url}}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Login Now</a></p>
                <p>Best regards,<br>IndoWater Team</p>
            ',
            'payment-confirmation' => '
                <h2>Payment Confirmation</h2>
                <p>Hello {{name}},</p>
                <p>Your payment has been successfully processed.</p>
                <p><strong>Amount:</strong> Rp {{amount}}</p>
                <p><strong>Transaction ID:</strong> {{transaction_id}}</p>
                <p><strong>Date:</strong> {{payment_date}}</p>
                <p>Best regards,<br>IndoWater Team</p>
            ',
            'low-credit-alert' => '
                <h2>Low Credit Alert</h2>
                <p>Hello {{name}},</p>
                <p>Your water meter credit is running low.</p>
                <p><strong>Meter ID:</strong> {{meter_id}}</p>
                <p><strong>Current Credit:</strong> Rp {{current_credit}}</p>
                <p><a href="{{topup_url}}" style="background-color: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Top Up Now</a></p>
                <p>Best regards,<br>IndoWater Team</p>
            ',
        ];

        $template = $templates[$template] ?? '';
        
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
}