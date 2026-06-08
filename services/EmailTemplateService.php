<?php

class EmailTemplateService
{
    /**
     * Wrap content inside the main HTML layout.
     */
    private static function getBaseLayout(string $title, string $contentHtml): string
    {
        $year = date('Y');
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/Task-5';
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #020617;
            color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            background-color: #020617;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        .header {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            padding: 35px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.025em;
        }
        .header p {
            margin: 5px 0 0 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
            color: #cbd5e1;
            font-size: 16px;
        }
        .content h2 {
            color: #f8fafc;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .highlight-box {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            color: #f8fafc;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
            font-size: 15px;
        }
        .footer {
            background-color: #090d16;
            padding: 25px 30px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>SmartHire Pro</h1>
                <p>Next-Gen Talent Acquisition Platform</p>
            </div>
            <div class="content">
                {$contentHtml}
            </div>
            <div class="footer">
                <p>&copy; {$year} SmartHire Pro. All rights reserved.</p>
                <p>Designed for FutureTech recruitment. <a href="{$baseUrl}">Visit Platform</a></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function getOtpTemplate(string $name, string $otp, string $purposeText): string
    {
        $title = "Your OTP Verification Code";
        $content = <<<HTML
<h2>Hello {$name},</h2>
<p>To secure your account, please verify your action with the one-time password (OTP) below. This OTP is valid for <strong>10 minutes</strong>.</p>
<div class="highlight-box" style="text-align: center; font-size: 32px; letter-spacing: 6px; font-weight: 700; color: #3b82f6;">
    {$otp}
</div>
<p style="font-size: 14px; color: #64748b;">Action: {$purposeText}</p>
<p>If you did not request this, please ignore this email or contact support if you believe this is unauthorized.</p>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getWelcomeTemplate(string $name, string $role): string
    {
        $title = "Welcome to SmartHire Pro!";
        $dashboardUrl = defined('BASE_URL') ? BASE_URL . '/auth/login.php' : '#';
        $roleText = $role === 'recruiter' ? 'Recruiter' : 'Job Seeker';
        
        $content = <<<HTML
<h2>Welcome to SmartHire Pro, {$name}!</h2>
<p>We're thrilled to have you join our Next-Gen recruitment ecosystem as a <strong>{$roleText}</strong>.</p>
<p>SmartHire Pro leverages smart matches, sleek developer-level layouts, and clean pipelines to make recruitment and job searches feel like a premium experience.</p>
<div class="button-wrapper">
    <a href="{$dashboardUrl}" class="button">Log In to Your Dashboard</a>
</div>
<p>Get started today by completing your profile to experience the full potential of our matching system.</p>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getApplicationSubmittedTemplate(string $name, string $jobTitle, string $companyName): string
    {
        $title = "Application Submitted Successfully";
        $content = <<<HTML
<h2>Hello {$name},</h2>
<p>Your application for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong> has been successfully received.</p>
<p>The recruitment team will review your credentials, profile details, and portfolio shortly. You can track your real-time candidacy progress directly in your jobseeker pipeline.</p>
<div class="highlight-box">
    <strong>Position:</strong> {$jobTitle}<br>
    <strong>Company:</strong> {$companyName}<br>
    <strong>Status:</strong> Applied (Under Review)
</div>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getShortlistedTemplate(string $name, string $jobTitle, string $companyName): string
    {
        $title = "Congratulations! You've been Shortlisted";
        $content = <<<HTML
<h2>Excellent news, {$name}!</h2>
<p>You have been shortlisted for the <strong>{$jobTitle}</strong> role at <strong>{$companyName}</strong>.</p>
<p>The company's hiring manager was impressed with your profile and matching score. They will reach out to schedule an interview session soon. Please keep an eye on your emails and SmartHire dashboard.</p>
<div class="highlight-box">
    <strong>Role:</strong> {$jobTitle}<br>
    <strong>Company:</strong> {$companyName}<br>
    <strong>Current Phase:</strong> Shortlisted
</div>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getInterviewScheduledTemplate(
        string $name,
        string $jobTitle,
        string $companyName,
        string $dateTime,
        string $type,
        string $link,
        string $instructions
    ): string {
        $title = "Interview Scheduled";
        $typeLabel = str_replace('_', ' ', ucfirst($type));
        $buttonHtml = '';
        if ($link) {
            $buttonHtml = <<<HTML
<div class="button-wrapper">
    <a href="{$link}" class="button" target="_blank">Join Interview Session</a>
</div>
HTML;
        }
        
        $instructionsHtml = $instructions ? "<p><strong>Additional Instructions:</strong><br>" . nl2br(htmlspecialchars($instructions)) . "</p>" : "";

        $content = <<<HTML
<h2>Interview Scheduled: {$jobTitle}</h2>
<p>Hello {$name},</p>
<p>An interview session has been scheduled for your application with <strong>{$companyName}</strong>. Here are the session details:</p>
<div class="highlight-box">
    <strong>Interview Type:</strong> {$typeLabel}<br>
    <strong>Date & Time:</strong> {$dateTime}<br>
    <strong>Link/Location:</strong> <a href="{$link}" style="color: #06b6d4;" target="_blank">{$link}</a>
</div>
{$instructionsHtml}
{$buttonHtml}
<p>Please make sure to join or arrive at least 5 minutes prior to the scheduled start time. Best of luck!</p>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getSelectedTemplate(string $name, string $jobTitle, string $companyName): string
    {
        $title = "Job Offer from " . $companyName;
        $content = <<<HTML
<h2>Congratulations, {$name}!</h2>
<p>We are delighted to inform you that you have been selected for the position of <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>!</p>
<p>The recruitment team has marked your candidacy as **Selected** and will contact you directly to share your official offer letter and onboarding schedule.</p>
<div class="highlight-box" style="border-left: 4px solid #22c55e;">
    <h3 style="margin-top: 0; color: #22c55e;">Offer Extended</h3>
    <p style="margin-bottom: 0;">We wish you massive success as you start this exciting new chapter in your career.</p>
</div>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getRejectedTemplate(string $name, string $jobTitle, string $companyName): string
    {
        $title = "Application Update: " . $jobTitle;
        $content = <<<HTML
<h2>Hello {$name},</h2>
<p>Thank you for taking the time to apply and interview for the <strong>{$jobTitle}</strong> role at <strong>{$companyName}</strong>.</p>
<p>We appreciate your interest in our team. However, after careful review, we have decided to proceed with other candidates whose experience more closely matches our current needs.</p>
<p>We will keep your resume on file for future matching opportunities. We wish you the absolute best in your professional endeavors.</p>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getRecruiterApprovedTemplate(string $name, string $companyName): string
    {
        $title = "Recruiter Account Approved";
        $loginUrl = defined('BASE_URL') ? BASE_URL . '/auth/login.php' : '#';
        $content = <<<HTML
<h2>Account Approved!</h2>
<p>Hello {$name},</p>
<p>Your recruiter account and company registration for <strong>{$companyName}</strong> have been reviewed and approved by the SmartHire Pro administration team.</p>
<p>You now have full privileges to post job openings, manage applicants through the Kanban pipeline, and search for candidates.</p>
<div class="button-wrapper">
    <a href="{$loginUrl}" class="button">Access Recruiter Workspace</a>
</div>
HTML;
        return self::getBaseLayout($title, $content);
    }

    public static function getRecruiterRejectedTemplate(string $name, string $companyName, string $reason = ''): string
    {
        $title = "Recruiter Account Application Update";
        $reasonHtml = $reason ? "<p><strong>Reason for rejection:</strong><br>" . nl2br(htmlspecialchars($reason)) . "</p>" : "";
        
        $content = <<<HTML
<h2>Recruiter Registration Status</h2>
<p>Hello {$name},</p>
<p>Thank you for submitting your recruiter and company credentials for <strong>{$companyName}</strong>.</p>
<p>Unfortunately, we are unable to approve your recruiter account request at this time because we could not verify your business credentials.</p>
{$reasonHtml}
<p>If you believe this was an error, please reach out to our system administration team.</p>
HTML;
        return self::getBaseLayout($title, $content);
    }
}
